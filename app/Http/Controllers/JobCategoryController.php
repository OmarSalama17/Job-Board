<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobCategoryCreateRequest;
use App\Http\Requests\JobCategoryUpdateRequest;
use App\Models\JobCategory;
use Illuminate\Http\Request;

class JobCategoryController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $query = JobCategory::latest();
        if($request->input("archived")){
            $query->onlyTrashed();
        }
        $categories = $query->paginate(2);
        return $this->successResponse(
            $categories
        ,'success',200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(JobCategoryCreateRequest $request)
    {
        $validated = $request->validated();
        JobCategory::create($validated);
        return $this->successResponse($validated , 'success' , 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JobCategoryUpdateRequest $request, string $id)
    {
        $validated = $request->validated();
        $category = JobCategory::findOrFail($id);
        $category->update($validated);
        return $this->successResponse($category , "successfully" , 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = JobCategory::findOrFail($id);
        $category->delete();
        return $this->successResponse($category , 'deleted' , 200);
    }
        public function restore(string $id)
    {
        $category = JobCategory::withTrashed()->findOrFail($id);
        $category->restore();
        return $this->successResponse($category , 'restored' , 200);
    }
}
