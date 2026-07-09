<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
	// default (unnamespaced) login
	Route::post('login', [AuthController::class, 'login']);

	// versioned API groups
	Route::middleware('auth:sanctum')->group(function () {
		Route::prefix('v1')->group(function () {
			// add v1 endpoints here
		});
	});
});