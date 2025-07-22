<?php  

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    
    Route::controller('')->name('');

});