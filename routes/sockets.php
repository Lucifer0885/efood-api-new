<?php

use App\Http\Controllers\SocketController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.socket'])->group(function () {
    Route::controller(SocketController::class)->group(function () {
        Route::post('driver-location', 'driverLocation');
        Route::post('set-user-socket','setUserSocket');
        Route::delete('delete-user-socket','deleteUserSocket');
        Route::delete('delete-all-socket','deleteAllSockets');
    });
});
