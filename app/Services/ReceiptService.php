<?php

namespace App\Services;

use App\Models\Receipt;
use App\Models\Transaction;

class ReceiptService
{
    /**
     * @return array<string, mixed>
     */
    public function buildPayload(Transaction $transaction, array $metadata = []): array
    {
        $transaction->loadMissing(['items.item']);

        $payload = [
            'id' => $transaction->id,
            'receipt_number' => $transaction->receipt_number,
            'date' => optional($transaction->paid_at ?? $transaction->created_at)->toDateTimeString(),
            'status' => $transaction->status,
            'payment_method' => $transaction->payment_method,
            'subtotal' => (float) $transaction->subtotal,
            'discount' => (float) $transaction->discount,
            'tax' => (float) $transaction->tax,
            'total' => (float) $transaction->total,
            'items' => $transaction->items->map(function ($line): array {
                return [
                    'name' => (string) ($line->item?->name ?? 'Item'),
                    'quantity' => (int) $line->quantity,
                    'price' => (float) $line->price,
                    'subtotal' => (float) $line->subtotal,
                ];
            })->values()->all(),
        ];

        if (array_key_exists('cash_received', $metadata)) {
            $payload['cash_received'] = round((float) $metadata['cash_received'], 2);
        }

        if (array_key_exists('change', $metadata)) {
            $payload['change'] = round((float) $metadata['change'], 2);
        }

        return $payload;
    }

    public function persistSnapshot(Transaction $transaction, array $metadata = []): void
    {
        if ($transaction->status !== 'completed') {
            return;
        }

        $payload = $this->buildPayload($transaction, $metadata);

        Receipt::query()->updateOrCreate(
            ['transaction_id' => $transaction->id],
            [
                'user_id' => $transaction->user_id,
                'receipt_number' => (string) $transaction->receipt_number,
                'payment_method' => (string) $transaction->payment_method,
                'status' => (string) $transaction->status,
                'total' => (float) $transaction->total,
                'provider_payment_id' => $transaction->provider_payment_id,
                'provider_reference' => $transaction->provider_reference,
                'payload' => $payload,
                'issued_at' => $transaction->paid_at ?? $transaction->created_at,
            ]
        );
    }
}
