<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Beginning extends Model
{
    protected $table = 'balance_beginning';

    protected $fillable = [
        'amount',
    ];

    public $timestamps = true;

    protected $casts = [
        'amount' => 'integer',
    ];
}
