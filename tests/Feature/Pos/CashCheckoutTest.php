<?php

namespace Tests\Feature\Pos;

use App\Models\PosItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function cashier(): User
    {
        $user = User::factory()->create();
        $user->assignRole('cashier');

        return $user;
    }

    public function test_cashier_can_complete_a_cash_sale_and_stock_is_deducted_once(): void
    {
        $item = PosItem::factory()->create(['price' => 19.99, 'stock' => 10]);

        $response = $this->actingAs($this->cashier())->postJson('/pos/checkout', [
            'items' => [['id' => $item->id, 'quantity' => 3]],
            'payment_method' => 'cash',
            'cash_received' => 100,
            'change' => 40.03,
            'notes' => 'walk-in',
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        // 19.99 * 3 = 59.97 exactly (bcmath, no float drift).
        $this->assertDatabaseHas('transactions', [
            'status' => 'completed',
            'payment_method' => 'cash',
            'total' => '59.97',
        ]);
        $this->assertSame(7, $item->fresh()->stock);
    }

    public function test_cash_sale_is_rejected_when_cash_received_is_insufficient(): void
    {
        $item = PosItem::factory()->create(['price' => 50, 'stock' => 10]);

        $response = $this->actingAs($this->cashier())->postJson('/pos/checkout', [
            'items' => [['id' => $item->id, 'quantity' => 2]],
            'payment_method' => 'cash',
            'cash_received' => 10,
            'change' => 0,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('cash_received');
        $this->assertSame(10, $item->fresh()->stock);
    }

    public function test_sale_is_rejected_when_stock_is_insufficient(): void
    {
        $item = PosItem::factory()->create(['price' => 50, 'stock' => 1]);

        $response = $this->actingAs($this->cashier())->postJson('/pos/checkout', [
            'items' => [['id' => $item->id, 'quantity' => 5]],
            'payment_method' => 'cash',
            'cash_received' => 1000,
            'change' => 0,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('items');
        $this->assertSame(1, $item->fresh()->stock);
    }

    public function test_user_without_process_sale_permission_cannot_checkout(): void
    {
        $item = PosItem::factory()->create(['stock' => 10]);
        $clerk = User::factory()->create();
        $clerk->assignRole('inventory-clerk');

        $response = $this->actingAs($clerk)->postJson('/pos/checkout', [
            'items' => [['id' => $item->id, 'quantity' => 1]],
            'payment_method' => 'cash',
            'cash_received' => 1000,
            'change' => 0,
        ]);

        $response->assertForbidden();
        $this->assertSame(10, $item->fresh()->stock);
    }

    public function test_role_less_self_registered_user_cannot_checkout(): void
    {
        $item = PosItem::factory()->create(['stock' => 10]);
        $user = User::factory()->create(); // no role assigned

        $response = $this->actingAs($user)->postJson('/pos/checkout', [
            'items' => [['id' => $item->id, 'quantity' => 1]],
            'payment_method' => 'cash',
            'cash_received' => 1000,
            'change' => 0,
        ]);

        $response->assertForbidden();
        $this->assertSame(10, $item->fresh()->stock);
    }
}
