<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoneyOut extends Model
{
    protected $primaryKey = "trx_id";
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'category_id',
        'amount',
        'payment_method',
        'proof',
        'ext_doc_ref',
        'payment_to',
        'payment_date',
        'note',
        'tax',
    ];

    /**
     * Boot the model and add a creating event listener to generate a unique
     * transaction ID for each new MoneyOut record.
     **/
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($moneyout) {
            $moneyout->trx_id = 'IN-' . rand(100000000, 999999999);
        });
    }

    public function category()
    {
        return $this->belongsTo(Payment::class);
    }

    public function utangs()
    {
        return $this->hasMany(Utang::class, 'moneyout_id', 'trx_id');
    }

    public function utang()
    {
        return $this->hasOne(Utang::class, 'moneyout_id', 'trx_id');
    }
}
