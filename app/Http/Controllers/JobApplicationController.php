<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobApplicationUpdateRequest;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobApplicationController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = JobApplication::latest();
        if (Auth()->user()->role == 'company-owner') {
            $query->whereHas('jobVacancy', function ($query) {
                $query->where('companyId', Auth()->user()->company->id);
            });
        }

        if ($request->input("archived")) {
            $query->onlyTrashed();
        }

        $categories = $query->paginate(2);
        return $this->successResponse(
            $categories,
            'success',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $jobApplication = JobApplication::with('jobVacancies.jobApplication.user')->findOrFail($id);
        return $this->successResponse($jobApplication, "success", 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JobApplicationUpdateRequest $request, string $id)
    {
        $jobApplication = JobApplication::findOrFail($id);
        $jobApplication->update([
            'status' => $request->input('status')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $jobApplication = JobApplication::findOrFail($id);
        $jobApplication->delete();
        return $this->successResponse($jobApplication, 'deleted', 200);
    }
    public function restore(string $id)
    {
        $jobApplication = JobApplication::withTrashed()->findOrFail($id);
        $jobApplication->restore();
        return $this->successResponse($jobApplication, 'restored', 200);
    }
}
