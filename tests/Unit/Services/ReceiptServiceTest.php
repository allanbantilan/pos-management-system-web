<?php

namespace Tests\Unit\Services;

use App\Models\PosItem;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ReceiptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiptServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReceiptService $receiptService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->receiptService = new ReceiptService();
    }

    public function test_build_payload_creates_correct_structure(): void
    {
        $user = User::factory()->create();
        $item = PosItem::factory()->create(['price' => 100.00, 'name' => 'Burger']);

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'subtotal' => 200.00,
            'total' => 200.00,
            'payment_method' => 'cash',
            'status' => 'completed',
            'receipt_number' => 'RCPT-20260101-0001',
        ]);

        $transaction->items()->create([
            'pos_item_id' => $item->id,
            'quantity' => 2,
            'price' => 100.00,
            'subtotal' => 200.00,
        ]);

        $payload = $this->receiptService->buildPayload($transaction);

        $this->assertEquals('RCPT-20260101-0001', $payload['receipt_number']);
        $this->assertEquals('cash', $payload['payment_method']);
        $this->assertEquals(200.00, $payload['total']);
        $this->assertCount(1, $payload['items']);
        $this->assertEquals('Burger', $payload['items'][0]['name']);
        $this->assertEquals(2, $payload['items'][0]['quantity']);
    }

    public function test_persist_snapshot_creates_receipt(): void
    {
        $user = User::factory()->create();
        $item = PosItem::factory()->create(['price' => 50.00]);

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'subtotal' => 50.00,
            'total' => 50.00,
            'payment_method' => 'cash',
            'status' => 'completed',
            'receipt_number' => 'RCPT-20260101-0002',
        ]);

        $transaction->items()->create([
            'pos_item_id' => $item->id,
            'quantity' => 1,
            'price' => 50.00,
            'subtotal' => 50.00,
        ]);

        $this->receiptService->persistSnapshot($transaction);

        $this->assertDatabaseHas('receipts', [
            'receipt_number' => 'RCPT-20260101-0002',
            'payment_method' => 'cash',
            'total' => 50.00,
        ]);
    }

    public function test_persist_snapshot_only_for_completed_transactions(): void
    {
        $user = User::factory()->create();
        $item = PosItem::factory()->create(['price' => 50.00]);

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'subtotal' => 50.00,
            'total' => 50.00,
            'payment_method' => 'cash',
            'status' => 'pending',
            'receipt_number' => 'RCPT-20260101-0003',
        ]);

        $transaction->items()->create([
            'pos_item_id' => $item->id,
            'quantity' => 1,
            'price' => 50.00,
            'subtotal' => 50.00,
        ]);

        $this->receiptService->persistSnapshot($transaction);

        $this->assertDatabaseMissing('receipts', [
            'receipt_number' => 'RCPT-20260101-0003',
        ]);
    }
}
