<?php

use App\Http\Controllers\CommandController;
use Illuminate\Support\Facades\Route;


// Route::get("/index", function () {
//     return view("page.index");
// })->name('index');

require __DIR__ ."/auth.php";
require __DIR__ ."/data-master.php";

Route::get('/trigger-utang-reminder', [CommandController::class, 'reminderUtang'])
    ->name('trigger-utang-reminder');

Route::get('/trigger-piutang-reminder', [CommandController::class, 'reminderPiutang'])
    ->name('trigger-piutang-reminder');
