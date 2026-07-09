<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
	Route::get('users', [UserController::class, 'index']);
	Route::apiResource('tasks', TaskController::class);
});