<?php

use Illuminate\Support\Facades\Route;
use Wontonee\Phonepe\Http\Controllers\PhonepeController;
use Wontonee\Phonepe\Http\Middleware\CheckLicense;

/**
 * PhonePe Payment Gateway Routes
 */
Route::group(['middleware' => ['web', 'theme', 'locale', 'currency', CheckLicense::class]], function () {
    
    // Payment initiation route
    Route::get('phonepe-redirect', [PhonepeController::class, 'redirect'])
        ->name('phonepe.process');
    
    // Payment callback route (handles both success and failure)
    Route::get('phonepe-callback', [PhonepeController::class, 'callback'])
        ->name('phonepe.callback');
    
    // Payment cancellation route
    Route::get('phonepe-cancel', [PhonepeController::class, 'cancel'])
        ->name('phonepe.cancel');
});
