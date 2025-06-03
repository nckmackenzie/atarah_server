<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\GlAccountController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller((AuthController::class))->group(function () {
    Route::post('/login', 'store');
    Route::post('/logout', 'logout');
});

Route::middleware('is.admin')->group(function () {
    Route::apiResource('clients',ClientController::class)->only('destroy');
    Route::apiResource('services',ServiceController::class)->only('destroy');
    Route::apiResource('projects',ProjectController::class)->only('destroy');
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/forms',[FormController::class, 'index']);
    Route::apiResource('users',UserController::class);
    Route::apiResource('roles',RoleController::class);
    Route::apiResource('clients',ClientController::class)->except('destroy');
    Route::apiResource('services',ServiceController::class)->except(['destroy']);
    Route::apiResource('accounts',GlAccountController::class)->except(['destroy']);
    Route::apiResource('projects',ProjectController::class)->except(['destroy']);
});