<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends BaseController
{
    public function index()
    {
        if (Auth()->user()->role == "admin") {
            $analytics = $this->adminDashboard();
        } else {
            $analytics = $this->companyOwnerDashboard();
        }
        return $this->successResponse($analytics);
    }
    private function adminDashboard()
    {
        $activeUsers = User::where('last_login_at', '>=', now()->subDays(30))
            ->where('role', 'job-seeker')->count();

        $totalJobs = JobVacancy::whereNull(('deleted_at'))->count();

        $totalApplications = JobApplication::whereNull('deleted_at')->count();

        $analytics = [
            'activeUsers' => $activeUsers,
            'totalJobs' => $totalJobs,
            'totalApplication' => $totalApplications
        ];

        $mostAppliedJobs = JobVacancy::withCount('jobApplication as totalCount')
            ->whereNull('deleted_at')
            ->limit(5)
            ->orderByDesc('totalCount')
            ->get();

        $conversionRates = JobVacancy::withCount('jobApplication as totalCount')
            ->having('totalCount', '>', 0)
            ->limit(5)
            ->orderByDesc('totalCount')
            ->get()
            ->map(function ($job) {
                if ($job->viewCount > 0) {
                    $job->conversionRate = $job->totalCount / $job->viewCount * 100;
                } else {
                    $job->conversionRate = 0;
                }
                return $job;
            });

        $data = ['analytics' => $analytics, 'mostAppliedJobs' => $mostAppliedJobs, 'conversionRates' => $conversionRates];
        return $data;
    }
    private function companyOwnerDashboard()
    {
        $company = Auth()->user()->company;
        $activeUsers = User::where('last_login_at', '>=', now()->subDays(30))
            ->where('role', 'job-seeker')
            ->whereHas('jobApplication', function ($query) use ($company) {
                $query->where('jobVacancyId', $company->jobVacancy->pluck('id'));
            })
            ->count();
        $totalJobs = $company->jobVacancies()->count();

        $totalApplication = JobApplication::whereIn('jobVacancyId', $company->jobVacancies->pluck('id'))->count();

        $mostApplication = JobApplication::withCount('jobApplication as totalCount')
            ->limit(5)
            ->orderByDesc('totalCount')
            ->get();

        $conversionRates = JobVacancy::withCount('jopApplication as totalCount')
            ->whereIn('id', $company->jobVacancies->pluck('id'))
            ->having('totalCount', '>', 0)
            ->orderByDesc('totalCount')
            ->get()
            ->map(function ($job) {
                $job->conversionRate = round($job->totalCount / $job->viewCount * 100, 2);
                return $job;
            });
        $analytics = [
            'activeUsers' => $activeUsers,
            'totalJobs' => $totalJobs,
            'totalApplication' => $totalApplication,
            'mostAppliedJobs' => $mostApplication,
            'conversionRates' => $conversionRates
        ];

        return $analytics;
    }
}
