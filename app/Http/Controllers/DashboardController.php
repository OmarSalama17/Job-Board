<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
        public function index(){
            $activeUsers = User::where('last_login_at' , '>='  , now()->subDays(30))
            ->where('role' , 'job-seeker')->count();
            $totalJobs = JobVacancy::whereNull(('deleted_at'))->count();
            $totalApplications = JobApplication::whereNull('deleted_at')->count();
            $analytics = [
                'activeUsers'=>$activeUsers,
                'totalJobs'=>$totalJobs,
                'totalApplication'=>$totalApplications
            ];
            $mostAppliedJobs = JobVacancy::withCount('jobApplication as totalCount')
            ->whereNull('deleted_at')
            ->limit(5)
            ->orderByDesc('totalCount')
            ->get();
            $conversionRates = JobVacancy::withCount('jobApplication as totalCount')
            ->having('totalCount' , '>' , 0)
            ->limit(5)
            ->orderByDesc('totalCount')
            ->get()
            ->map(function($job){
                if($job->viewCount > 0){
                    $job->conversionRate = $job->totalCount / $job->viewCount * 100;
                }else{
                    $job->conversionRate = 0;
                }
                return $job;
            });
                $data = ['analytics'=>$analytics ,'mostAppliedJobs'=> $mostAppliedJobs ,'conversionRates'=> $conversionRates];
            return $this->successResponse($data);
        }
}
