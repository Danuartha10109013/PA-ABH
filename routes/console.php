<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();

Schedule::command('utang:reminder')->everyMinute();
Schedule::command('piutang:reminder')->everyMinute();

// Schedule::command('utang:reminder')->daily();
