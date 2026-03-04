<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'patient_id',
        'gross_amount',
        'discount_rate',
        'discount_amount',
        'total_amount',
    ];

    protected static function booted()
    {
        static::creating(function ($transaction) {
            $transaction->transaction_id = 'TXN-' . strtoupper(uniqid());
        });
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}