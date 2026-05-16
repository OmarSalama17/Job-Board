<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyCreateRequest;
use App\Http\Requests\CompanyUpdateRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CompanyController extends BaseController
{
    public function index(Request $request)
    {

        $query = Company::latest();
        if ($request->input("archived")) {
            $query->onlyTrashed();
        }
        $companies = $query->paginate(2);
        return $this->successResponse(
            $companies,
            'success',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompanyCreateRequest $request)
    {
        $validated = $request->validated();
        $owner = User::create([
            'name' => $validated['owner_name'],
            'email' => $validated['owner_email'],
            'password' => Hash::make($validated['owner_password']),
            'role' => 'company-owner'
        ]);
        if (!$owner) {
            return $this->errorResponse('error', [], 403);
        }
        $company = Company::create([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'industry' => $validated['industry'],
            'website' => $validated['website'],
            'ownerId' => $validated['ownerId']
        ]);
        return $this->successResponse($company, 'success', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id = null)
    {
        $company = $this->getCompany($id);

        return $this->successResponse($company, "success", 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CompanyUpdateRequest $request, string $id = null)
    {
        $validated = $request->validated();
        $company = $this->getCompany($id);
        $company->update([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'industry' => $validated['industry'],
            'website' => $validated['website'],
        ]);

        return $this->successResponse($company, "successfully", 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $company = Company::findOrFail($id);
        $this->Unauthorized($company);
        $company->delete();
        return $this->successResponse($company, 'deleted', 200);
    }
    public function restore(string $id)
    {
        $company = Company::withTrashed()->findOrFail($id);
        $this->Unauthorized($company);
        $company->restore();
        return $this->successResponse($company, 'restored', 200);
    }
    private function getCompany(string $id = null)
    {
        if ($id) {
            return Company::findOrFail($id);
        }
        return Company::where('ownerId', Auth()->user()->id)->first();
    }
    private function Unauthorized($company)
    {
        if (auth()->user()->role === 'admin') {
            return;
        }

        if ($company->ownerId !== auth()->user()->id) {
            abort(403, 'Unauthorized action. You cannot perform this action on this company.');
        }
    }
}

