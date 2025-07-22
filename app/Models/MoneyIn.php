<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoneyIn extends Model
{
    protected $primaryKey = "trx_id";
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'category_id',
        'amount',
        'payment_method',
        'source',
        'proof',
        'ext_doc_ref',
        'payment_from',
        'payment_date',
        'note',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($moneyin) {
            $moneyin->trx_id = 'IN-' . rand(100000000, 999999999);
        });
    }

    public function category()
    {
        return $this->belongsTo(Payment::class);
    }

    public function piutangs()
    {
        return $this->hasMany(Piutang::class, 'moneyin_id', 'trx_id');
    }

    public function piutang()
    {
        return $this->hasOne(Piutang::class, 'moneyin_id', 'trx_id');
    }
}
