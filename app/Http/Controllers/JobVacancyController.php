<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobVacancyCreateRequest;
use App\Http\Requests\JobVacancyUpdateRequest;
use App\Models\JobVacancy;
use Illuminate\Http\Request;

class JobVacancyController extends BaseController
{
    /**
     * Display a listing of the resource.
     */    public function index(Request $request)
    {

        $query = JobVacancy::latest();
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
    public function store(JobVacancyCreateRequest $request)
    {
        $validated = $request->validated();
        JobVacancy::create($validated);
        return $this->successResponse($validated, 'success', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $JobVacancy = JobVacancy::with('jobVacancies.jobApplication.user')->findOrFail($id);
        return $this->successResponse($JobVacancy, "success", 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JobVacancyUpdateRequest $request, string $id)
    {
        $validated = $request->validated();
        $jobVacancy = JobVacancy::findOrFail($id);
        $jobVacancy->update($validated);

        return $this->successResponse($jobVacancy, "successfully", 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $JobVacancy = JobVacancy::findOrFail($id);
        $JobVacancy->delete();
        return $this->successResponse($JobVacancy, 'deleted', 200);
    }
    public function restore(string $id)
    {
        $JobVacancy = JobVacancy::withTrashed()->findOrFail($id);
        $JobVacancy->restore();
        return $this->successResponse($JobVacancy, 'restored', 200);
    }
}
