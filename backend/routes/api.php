<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidentController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/incidents/export', [IncidentController::class, 'export']);
    Route::apiResource('incidents', IncidentController::class);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/users', function () {
        return User::select('id', 'name')->orderBy('name')->get();
    });
});
