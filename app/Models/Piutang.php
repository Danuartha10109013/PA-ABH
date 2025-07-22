<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Piutang extends Model
{
    protected $primaryKey = "collection_id";
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'amount',
        'ext_doc_ref',
        'payment_from',
        'due_date',
        'moneyin_id',
        'note',
        'type',
        'is_paid',
        'installment_count',
        'reminded_besok',
    ];


    protected static function boot()
    {
        parent::boot();
        static::creating(function ($piutang) {
            $piutang->collection_id = 'COLL-' . rand(100000000, 999999999);
        });
    }

    public function moneyin()
    {
        return $this->belongsTo(MoneyIn::class, 'moneyin_id', 'trx_id');
    }

    public function installments()
    {
        return $this->hasMany(PiutangInstallement::class, 'piutang_collection_id', 'collection_id');
    }

}
