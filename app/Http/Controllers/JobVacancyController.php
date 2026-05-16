<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobVacancyCreateRequest;
use App\Http\Requests\JobVacancyUpdateRequest;
use App\Models\JobVacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobVacancyController extends BaseController
{
    /**
     * Display a listing of the resource.
     */    public function index(Request $request)
    {

        $query = JobVacancy::latest();
        if (Auth()->user()->role == 'company-owner') {
            $query->where('companyId', Auth()->user()->company->id);
        }
        if ($request->input("archived")) {
            $query->onlyTrashed();
        }
        $categories = $query->paginate(10);
        return $this->successResponse(
            $categories,
            'success',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(JobVacancyCreateRequest $request)
    {
        $validated = $request->validated();

        if (auth()->user()->role == 'company-owner') {
            $validated['companyId'] = auth()->user()->company->id;
        }

        JobVacancy::create($validated);

        return $this->successResponse($validated, 'success', 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!auth()->check() || auth()->user()->role === 'user') {
            $jobVacancy = JobVacancy::findOrFail($id);
            return $this->successResponse($jobVacancy, "success", 200);
        }

        $jobVacancy = JobVacancy::with('jobApplication.user')->findOrFail($id);

        $this->Unauthorized($jobVacancy);

        return $this->successResponse($jobVacancy, "success", 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JobVacancyUpdateRequest $request, string $id)
    {
        $validated = $request->validated();
        $jobVacancy = JobVacancy::findOrFail($id);
        $this->Unauthorized($jobVacancy);
        $jobVacancy->update($validated);

        return $this->successResponse($jobVacancy, "successfully", 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $jobVacancy = JobVacancy::findOrFail($id);
        $this->Unauthorized($jobVacancy);

        $jobVacancy->delete();
        return $this->successResponse($jobVacancy, 'deleted', 200);
    }
    public function restore(string $id)
    {
        $jobVacancy = JobVacancy::withTrashed()->findOrFail($id);
        $this->Unauthorized($jobVacancy);

        $jobVacancy->restore();
        return $this->successResponse($jobVacancy, 'restored', 200);
    }
    private function Unauthorized($jobVacancy)
    {
        if (auth()->user()->role === 'admin') {
            return;
        }

        if ($jobVacancy->company->ownerId !== auth()->user()->id) {
            abort(403, 'Unauthorized action. You cannot perform this action on this job vacancy.');
        }
    }
}
