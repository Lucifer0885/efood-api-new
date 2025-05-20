<?php

use App\Http\Controllers\AddressController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return response()->json([
        'message' => 'Hello Client!',
    ]);
});

Route::prefix('auth')->group(base_path('routes/auth.php'));

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix("users")
        ->controller(UserController::class)
        ->group(function(){
            Route::get("me", 'me');
            Route::get("addresses", 'addresses');
            Route::get("tokens", 'tokens');
            Route::delete("revoke-all-tokens", 'revokeAllTokens');
        });

    Route::prefix('addresses')
        ->controller(AddressController::class)
        ->group(function(){
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get("/{id}",'show');
            Route::delete('/{id}','destroy');
        });
});