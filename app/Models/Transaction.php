<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Transaction extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id',
        'subtotal',
        'tax',
        'discount',
        'total',
        'payment_method',
        'payment_provider',
        'status',
        'receipt_number',
        'notes',
        'provider_checkout_id',
        'provider_payment_id',
        'provider_reference',
        'paid_at',
        'stock_deducted_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'stock_deducted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'payment_method', 'notes'])
            ->logOnlyDirty()
            ->useLogName('transactions')
            ->setDescriptionForEvent(fn (string $eventName) => "Transaction has been {$eventName}");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }
}

