<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::get('', 'index')->name('login');
        Route::get('/login', 'index')->name('login');
        Route::post('/login', 'store')->name('login.store');
    });
});