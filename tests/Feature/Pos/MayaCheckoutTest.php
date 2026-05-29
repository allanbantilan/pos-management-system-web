<?php

namespace Tests\Feature\Pos;

use App\Models\PosItem;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MayaCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.maya.public_key' => 'pk-test',
            'services.maya.secret_key' => 'sk-test',
            'services.maya.base_url' => 'https://pg-sandbox.paymaya.com',
        ]);
    }

    private function cashier(): User
    {
        $user = User::factory()->create();
        $user->assignRole('cashier');

        return $user;
    }

    /**
     * Register a SINGLE Http fake handling both checkout creation (POST) and,
     * optionally, the verification lookups (GET). $lookup returns a response
     * for a lookup request, or null to fall through to a 404.
     */
    private function fakeMaya(?callable $lookup = null): void
    {
        Http::fake(function ($request) use ($lookup) {
            if ($request->method() === 'POST' && str_contains($request->url(), '/checkout/v1/checkouts')) {
                return Http::response(['checkoutId' => 'chk_123', 'redirectUrl' => 'https://pay.test/redirect'], 200);
            }

            if ($lookup !== null && ($response = $lookup($request)) !== null) {
                return $response;
            }

            return Http::response([], 404);
        });
    }

    private function createPendingMayaTransaction(PosItem $item, int $quantity): Transaction
    {
        $response = $this->actingAs($this->cashier())->postJson('/pos/checkout', [
            'items' => [['id' => $item->id, 'quantity' => $quantity]],
            'payment_method' => 'maya_checkout',
            'notes' => '',
        ]);

        $response->assertOk()->assertJson(['success' => true, 'status' => 'pending']);

        return Transaction::findOrFail($response->json('transaction_id'));
    }

    public function test_creating_a_maya_checkout_reserves_stock(): void
    {
        $this->fakeMaya();
        $item = PosItem::factory()->create(['price' => 100, 'stock' => 10]);

        $transaction = $this->createPendingMayaTransaction($item, 2);

        $this->assertSame('pending', $transaction->status);
        $this->assertNotNull($transaction->stock_deducted_at);
        $this->assertSame(8, $item->fresh()->stock); // reserved up-front
    }

    public function test_successful_callback_completes_sale_without_double_deducting_stock(): void
    {
        $reference = null;

        $this->fakeMaya(function ($request) use (&$reference) {
            if (str_contains($request->url(), '/checkout/v1/checkouts/')) {
                return Http::response([
                    'paymentStatus' => 'PAYMENT_SUCCESS',
                    'requestReferenceNumber' => $reference,
                    'totalAmount' => ['value' => 200],
                    'id' => 'chk_123',
                ], 200);
            }

            return null;
        });

        $item = PosItem::factory()->create(['price' => 100, 'stock' => 10]);
        $transaction = $this->createPendingMayaTransaction($item, 2); // stock now 8
        $reference = $transaction->provider_reference;

        $response = $this->get(route('pos.checkout.callback', [
            'transaction' => $transaction->id,
            'result' => 'success',
        ]));

        $response->assertRedirect();
        $transaction->refresh();
        $this->assertSame('completed', $transaction->status);
        $this->assertSame(8, $item->fresh()->stock); // NOT deducted a second time
    }

    public function test_cancelled_callback_restores_reserved_stock(): void
    {
        $this->fakeMaya();
        $item = PosItem::factory()->create(['price' => 100, 'stock' => 10]);
        $transaction = $this->createPendingMayaTransaction($item, 2); // stock now 8

        $response = $this->get(route('pos.checkout.callback', [
            'transaction' => $transaction->id,
            'result' => 'cancelled',
        ]));

        $response->assertRedirect();
        $transaction->refresh();
        $this->assertSame('cancelled', $transaction->status);
        $this->assertNull($transaction->stock_deducted_at);
        $this->assertSame(10, $item->fresh()->stock); // restored
    }

    public function test_success_callback_with_mismatched_reference_is_rejected_and_stock_restored(): void
    {
        // The checkout lookup says "pending", but a reference lookup returns a
        // SUCCESS payment whose reference does NOT belong to this transaction.
        // Before the H2 fix this would have been trusted; it must now be rejected.
        $this->fakeMaya(function ($request) {
            if (str_contains($request->url(), '/payments/v1/payment-rrns/')) {
                return Http::response([[
                    'paymentStatus' => 'PAYMENT_SUCCESS',
                    'rrn' => 'SOME-OTHER-REFERENCE',
                    'amount' => ['value' => 200],
                ]], 200);
            }

            if (str_contains($request->url(), '/checkout/v1/checkouts/')) {
                return Http::response(['paymentStatus' => 'PENDING_PAYMENT'], 200);
            }

            return null;
        });

        $item = PosItem::factory()->create(['price' => 100, 'stock' => 10]);
        $transaction = $this->createPendingMayaTransaction($item, 2); // stock now 8

        $response = $this->get(route('pos.checkout.callback', [
            'transaction' => $transaction->id,
            'result' => 'success',
        ]));

        $response->assertRedirect();
        $transaction->refresh();
        $this->assertSame('failed', $transaction->status);
        $this->assertNull($transaction->stock_deducted_at);
        $this->assertSame(10, $item->fresh()->stock); // restored
    }
}
