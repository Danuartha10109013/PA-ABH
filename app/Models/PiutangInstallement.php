<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PiutangInstallement extends Model
{
    protected $table = 'piutang_installements';
    protected $fillable = [
        'piutang_collection_id',
        'amount',
        'due_date',
        'is_paid',
        'reminded_besok',
    ];

    public function piutang()
    {
        return $this->belongsTo(Piutang::class, 'piutang_collection_id', 'collection_id');
    }
} 