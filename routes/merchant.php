<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Hello Merchant!',
    ]);
});

Route::prefix('auth')->middleware('setAuthRole:merchant')->group(base_path('routes/auth.php'));

Route::middleware(['auth:sanctum'])->group(function () {
    
});