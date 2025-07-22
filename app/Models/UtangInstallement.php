<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UtangInstallement extends Model
{
    protected $fillable = [
        'utang_trx_id',
        'amount',
        'due_date',
        'is_paid',
        'reminded_besok',
    ];

    public function utang()
    {
        return $this->belongsTo(Utang::class, 'utang_trx_id');
    }
}
