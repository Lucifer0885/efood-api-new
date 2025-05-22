<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to the API',
    ]);
});

Route::get('/roles', function () {
    $roles = \App\Models\Role::all();

    return response()->json([
        "success" => true,
        "data" => [
            "roles" => $roles,
        ]
    ]);
});

//  Load the routes for each module on specific path

Route::prefix('merchant')->name('merchant')->group(base_path('routes/merchant.php'));
Route::prefix('driver')->name('driver')->group(base_path('routes/driver.php'));
Route::prefix('client')->name('client')->group(base_path('routes/client.php'));


