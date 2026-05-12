<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobCategoryController;
use App\Http\Controllers\JobVacancyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});
Route::middleware(['auth:sanctum', 'role:admin,company-owner'])->group(function () {
    Route::get('/me', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    //job-applications
    Route::resource('/job-applications', JobApplicationController::class);
    Route::put('/job-applications/{id}/restore', [JobApplicationController::class, 'restore']);

    //job-vacancy
    Route::resource('/job-vacancy', JobVacancyController::class);
    Route::put('/job-vacancy/{id}/restore', [JobVacancyController::class, 'restore']);
});

Route::middleware(['auth:sanctum', 'role:company-owner'])->group(function () {
    Route::get('my-company', [CompanyController::class, 'show']);
    Route::put('my-company', [CompanyController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    //job-categories
    Route::resource('/job-categories', JobCategoryController::class);
    Route::put('/job-categories/{id}/restore', [JobCategoryController::class, 'restore']);

    //companies
    Route::resource('/companies', CompanyController::class);
    Route::put('/companies/{id}/restore', [CompanyController::class, 'restore']);
});
