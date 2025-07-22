<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();

// Schedule::command('utang:reminder')->everyMinuted();
// Schedule::command('piutang:reminder')->everyMinuted();
Schedule::command('piutang:reminder')->hourly();
Schedule::command('utang:reminder')->hourly();
// Schedule::command('utang:reminder')->daily();
