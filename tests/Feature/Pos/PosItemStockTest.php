<?php

namespace Tests\Feature\Pos;

use App\Models\PosItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosItemStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_decrease_stock_succeeds_when_enough_is_available(): void
    {
        $item = PosItem::factory()->create(['stock' => 10]);

        $this->assertTrue($item->decreaseStock(3));
        $this->assertSame(7, $item->fresh()->stock);
    }

    public function test_decrease_stock_fails_and_does_not_change_stock_when_insufficient(): void
    {
        $item = PosItem::factory()->create(['stock' => 2]);

        $this->assertFalse($item->decreaseStock(5));
        $this->assertSame(2, $item->fresh()->stock);
    }
}
