<?php

use Illuminate\Support\Facades\Route;
use Wontonee\Phonepe\Http\Controllers\Admin\PhonepeController;

Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin/Phonepe'], function () {
    Route::controller(PhonepeController::class)->group(function () {
        Route::get('', 'index')->name('admin.Phonepe.index');
    });
});
