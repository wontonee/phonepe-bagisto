<?php

use Illuminate\Support\Facades\Route;
use Wontonee\Phonepe\Http\Controllers\Shop\PhonepeController;

Route::group(['middleware' => ['web', 'theme', 'locale', 'currency'], 'prefix' => 'Phonepe'], function () {
    Route::get('', [PhonepeController::class, 'index'])->name('shop.Phonepe.index');
});
