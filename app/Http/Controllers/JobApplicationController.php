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

        $jobApplications = $query->paginate(2);
        return $this->successResponse(
            $jobApplications,
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
        $jobApplication = JobApplication::with(['jobVacancy', 'user'])->findOrFail($id);

        if (auth()->user()->role === 'user') {
            if ($jobApplication->userId !== auth()->user()->id) {
                abort(403, 'Unauthorized. You can only view your own applications.');
            }
        } else {
            $this->Unauthorized($jobApplication);
        }

        return $this->successResponse($jobApplication, "success", 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JobApplicationUpdateRequest $request, string $id)
    {
        $jobApplication = JobApplication::findOrFail($id);
        $this->Unauthorized($jobApplication);
        $jobApplication->update([
            'status' => $request->input('status')
        ]);
        return $this->successResponse($jobApplication, "successfully", 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $jobApplication = JobApplication::findOrFail($id);
        $this->Unauthorized($jobApplication);
        $jobApplication->delete();
        return $this->successResponse($jobApplication, 'deleted', 200);
    }
    public function restore(string $id)
    {
        $jobApplication = JobApplication::withTrashed()->findOrFail($id);
        $this->Unauthorized($jobApplication);
        $jobApplication->restore();
        return $this->successResponse($jobApplication, 'restored', 200);
    }
    private function Unauthorized($jobApplication)
    {
        if (auth()->user()->role === 'admin') {
            return;
        }

        if ($jobApplication->jobVacancy->company->ownerId !== auth()->id) {
            abort(403, 'Unauthorized action. You cannot perform this action on this application.');
        }
    }
}
