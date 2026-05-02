<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\JobApplication;
use App\Models\JobCategory;
use App\Models\JobVacancy;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Util\PHP\Job;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
        $jobData = json_decode(file_get_contents(database_path('data/job-data.json')), true);
        $jobApplications = json_decode(file_get_contents(database_path('data/job-applications.json')), true);
        // create job categories
        foreach ($jobData['jobCategories'] as $jobCategory) {
            JobCategory::firstOrCreate(
                ['name' => $jobCategory]
            );
        }
        // create companies
        foreach ($jobData['companies'] as $company) {
            $companyOwner = User::firstOrCreate(
                [
                    'email' => fake()->unique()->safeEmail(),
                ],
                [
                    'name' => fake()->name(),
                    'password' => Hash::make('12345678'),
                    'role' => 'company-owner',
                    'email_verified_at' => now(),
                ]
            );
            Company::firstOrCreate(
                ['name' => $company['name']],
                [
                    'address' => $company['address'],
                    'industry' => $company['industry'],
                    'website' => $company['website'],
                    'ownerId' => $companyOwner->id,
                ]
            );
        }
        // create Job Vacancy
        foreach ($jobData['jobVacancies'] as $job) {
            $company = Company::where('name', $job['company'])->firstOrFail();
            $jobCategory = JobCategory::where('name', $job['category'])->firstOrFail();
            JobVacancy::firstOrCreate(
                [
                    'title' => $job['title'],
                    'companyId' => $company->id,
                ],
                [
                    'description' => $job['description'],
                    'location' => $job['location'],
                    'type' => $job['type'],
                    'salary' => $job['salary'],
                    'jobCategoryId' => $jobCategory->id,
                ]
            );
        }
        // create job applications
        foreach ($jobApplications['jobApplications'] as $jobApplication) {
            $jobVacancy = JobVacancy::inRandomOrder()->firstOrFail();
            $applicant = User::firstOrCreate([
                'email' => fake()->unique()->safeEmail(),
            ], [
                'name' => fake()->name(),
                'password' => Hash::make('12345678'),
                'role' => 'job-seeker',
                'email_verified_at' => now(),
            ]);
            //create resume
            $resume = Resume::create([
                'userId' => $applicant->id,
                'filename' => $jobApplication['resume']['filename'],
                'fileUri' => $jobApplication['resume']['fileUri'],
                'contactDetails' => $jobApplication['resume']['contactDetails'],
                'education' => $jobApplication['resume']['education'],
                'experience' => $jobApplication['resume']['experience'],
                'skills' => $jobApplication['resume']['skills'],
                'summary' => $jobApplication['resume']['summary'],

            ]);
            JobApplication::create([
                'jobVacancyId' => $jobVacancy->id,
                'userId' => $applicant->id,
                'resumeId' => $resume->id,
                'status' => $jobApplication['status'],
                'aiGeneratedScore' => $jobApplication['aiGeneratedScore'],
                'aiGeneratedFeedback' => $jobApplication['aiGeneratedFeedback'],
            ]);
        }
    }
}
