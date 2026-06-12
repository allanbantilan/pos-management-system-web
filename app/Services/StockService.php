<?php

namespace App\Services;

use App\Models\PosItem;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function deduct(Transaction $transaction): void
    {
        if ($transaction->stock_deducted_at !== null) {
            return;
        }

        $transaction->loadMissing(['items.item']);

        foreach ($transaction->items as $line) {
            $item = PosItem::query()->lockForUpdate()->find($line->pos_item_id);

            if (! $item || ! $item->is_active || $item->stock < $line->quantity) {
                throw ValidationException::withMessages([
                    'items' => "Insufficient stock for {$line->item?->name}.",
                ]);
            }

            $item->decrement('stock', (int) $line->quantity);
        }

        $transaction->update(['stock_deducted_at' => now()]);
    }

    public function restore(Transaction $transaction): void
    {
        if ($transaction->stock_deducted_at === null) {
            return;
        }

        DB::transaction(function () use ($transaction): void {
            $transaction->loadMissing('items');

            foreach ($transaction->items as $line) {
                PosItem::query()
                    ->lockForUpdate()
                    ->find($line->pos_item_id)
                    ?->increment('stock', (int) $line->quantity);
            }

            $transaction->update(['stock_deducted_at' => null]);
        });
    }
}
