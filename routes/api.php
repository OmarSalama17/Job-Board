<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\JobCategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', function (Request $request) {
            return $request->user();
        });
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::get('/job-categories' , [JobCategoryController::class , 'index']);
Route::post('/job-categories' , [JobCategoryController::class , 'store']);
Route::put('/job-categories/{id}' , [JobCategoryController::class , 'update']);
Route::delete('/job-categories/{id}' , [JobCategoryController::class , 'destroy']);
Route::put('/job-categories/{id}/restore' , [JobCategoryController::class , 'restore']);

Route::get('/companies' , [CompanyController::class , 'index']);
Route::post('/companies' , [CompanyController::class , 'store']);
Route::get('/companies/{id}' , [CompanyController::class , 'show']);
Route::put('/companies/{id}' , [CompanyController::class , 'update']);
Route::delete('/companies/{id}' , [CompanyController::class , 'destroy']);
Route::put('/companies/{id}/restore' , [CompanyController::class , 'restore']);
