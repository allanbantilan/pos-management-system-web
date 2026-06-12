<?php

namespace Tests\Unit\Services;

use App\Models\PosItem;
use App\Models\Transaction;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockService = new StockService();
    }

    public function test_deduct_stock_decreases_item_stock(): void
    {
        $item = PosItem::factory()->create(['stock' => 10]);
        $transaction = Transaction::factory()->create([
            'stock_deducted_at' => null,
        ]);

        $transaction->items()->create([
            'pos_item_id' => $item->id,
            'quantity' => 3,
            'price' => $item->price,
            'subtotal' => $item->price * 3,
        ]);

        $this->stockService->deduct($transaction);

        $item->refresh();
        $transaction->refresh();

        $this->assertEquals(7, $item->stock);
        $this->assertNotNull($transaction->stock_deducted_at);
    }

    public function test_deduct_stock_is_idempotent(): void
    {
        $item = PosItem::factory()->create(['stock' => 10]);
        $transaction = Transaction::factory()->create([
            'stock_deducted_at' => now(),
        ]);

        $transaction->items()->create([
            'pos_item_id' => $item->id,
            'quantity' => 3,
            'price' => $item->price,
            'subtotal' => $item->price * 3,
        ]);

        $this->stockService->deduct($transaction);

        $item->refresh();
        $this->assertEquals(10, $item->stock);
    }

    public function test_restore_stock_increases_item_stock(): void
    {
        $item = PosItem::factory()->create(['stock' => 7]);
        $transaction = Transaction::factory()->create([
            'stock_deducted_at' => now(),
        ]);

        $transaction->items()->create([
            'pos_item_id' => $item->id,
            'quantity' => 3,
            'price' => $item->price,
            'subtotal' => $item->price * 3,
        ]);

        $this->stockService->restore($transaction);

        $item->refresh();
        $transaction->refresh();

        $this->assertEquals(10, $item->stock);
        $this->assertNull($transaction->stock_deducted_at);
    }

    public function test_restore_stock_is_noop_when_not_deducted(): void
    {
        $item = PosItem::factory()->create(['stock' => 10]);
        $transaction = Transaction::factory()->create([
            'stock_deducted_at' => null,
        ]);

        $transaction->items()->create([
            'pos_item_id' => $item->id,
            'quantity' => 3,
            'price' => $item->price,
            'subtotal' => $item->price * 3,
        ]);

        $this->stockService->restore($transaction);

        $item->refresh();
        $this->assertEquals(10, $item->stock);
    }
}
