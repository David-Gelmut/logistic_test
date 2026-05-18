<?php

use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function(){
    Route::prefix('notifications')->group(function (){
        Route::post('', [NotificationController::class, 'send']);
        Route::get('history/{user}', [NotificationController::class, 'history']);
    });
});


