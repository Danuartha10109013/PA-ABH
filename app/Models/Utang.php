<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Utang extends Model
{
    protected $primaryKey = "trx_id";
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'amount',
        'ext_doc_ref',
        'payment_from',
        'due_date',
        'moneyout_id',
        'is_paid',
        'installment_count',
        'type',
        'note',
        'reminded_besok',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($piutang) {
            $piutang->trx_id = 'UT-' . rand(100000000, 999999999);
        });
    }

    public function moneyout()
    {
        return $this->belongsTo(MoneyOut::class, 'moneyout_id', 'trx_id');
    }

    public function installments()
    {
        return $this->hasMany(UtangInstallement::class, 'utang_trx_id', 'trx_id');
    }
}
