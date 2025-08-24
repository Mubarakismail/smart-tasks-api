<?php

use Illuminate\Support\Facades\Route;
use Modules\Task\Http\Controllers\StatusController;
use Modules\Task\Http\Controllers\TaskController;

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::apiResource('tasks', TaskController::class)->except(['index', 'show']);
    Route::post('tasks/{task}/assign', [TaskController::class, 'assign']);
    Route::post('tasks/{task}/change-status', [TaskController::class, 'changeStatus']);
    Route::post('tasks/{task}/restore', [TaskController::class, 'restore']);
});


Route::prefix('v1')->group(function () {
    Route::apiResource('tasks', TaskController::class)->only(['index', 'show']);
    Route::get('statuses', [StatusController::class, 'index']);
});
