<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();

Schedule::command('utang:reminder')->everyMinute();
Schedule::command('piutang:reminder')->everyMinute();
// Schedule::command('piutang:reminder')->hourly();
// Schedule::command('utang:reminder')->hourly();
// Schedule::command('utang:reminder')->daily();
