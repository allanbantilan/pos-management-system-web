<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    protected $fillable = [
        'transaction_id',
        'user_id',
        'receipt_number',
        'payment_method',
        'status',
        'total',
        'provider_payment_id',
        'provider_reference',
        'payload',
        'issued_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'payload' => 'array',
        'issued_at' => 'datetime',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

