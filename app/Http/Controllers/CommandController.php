<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CommandController extends Controller
{
    public function reminderUtang(){

        Artisan::call('utang:reminder');

        return response()->json([
            'message' => 'Reminder berhasil dikirim.',
            'status' => 'success',
        ]);
    }
    public function reminderPiutang(){

        Artisan::call('piutang:reminder');

        return response()->json([
            'message' => 'Reminder berhasil dikirim.',
            'status' => 'success',
        ]);
    }
    
}
