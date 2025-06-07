<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get("me", [AuthController::class, 'me']);
    Route::post('update', [AuthController::class,'update']);
    Route::post('change-password', [AuthController::class, 'changePassword']);
});


