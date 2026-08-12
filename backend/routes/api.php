<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\HostingController;
use App\Http\Controllers\Api\MetaController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/dashboard', [DashboardController::class, 'summary']);

    Route::get('/search', [SearchController::class, 'global']);

    Route::get('/meta/options', [MetaController::class, 'options']);
    Route::get('/meta/enums', [MetaController::class, 'enums']);

    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('tasks', TaskController::class);

    Route::patch('/tasks/{task}/status', [TaskController::class, 'changeStatus']);

    Route::get('/hosting', [HostingController::class, 'index']);
    Route::get('/hosting/project/{projectId}', [HostingController::class, 'show']);
    Route::get('/hosting/{id}', [HostingController::class, 'record']);
    Route::post('/hosting', [HostingController::class, 'store']);
    Route::delete('/hosting/project/{projectId}', [HostingController::class, 'destroy']);

    Route::get('/finance', [FinanceController::class, 'index']);
    Route::get('/finance/project/{projectId}', [FinanceController::class, 'show']);
    Route::get('/finance/{id}', [FinanceController::class, 'record']);
    Route::post('/finance', [FinanceController::class, 'store']);
    Route::delete('/finance/project/{projectId}', [FinanceController::class, 'destroy']);
});
