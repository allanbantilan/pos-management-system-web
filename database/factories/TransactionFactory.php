<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subtotal' => $this->faker->randomFloat(2, 10, 500),
            'tax' => 0,
            'discount' => 0,
            'total' => $this->faker->randomFloat(2, 10, 500),
            'payment_method' => 'cash',
            'status' => 'completed',
            'receipt_number' => 'RCPT-' . now()->format('YmdHis') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'provider_reference' => 'RRN-' . now()->format('YmdHis') . '-' . strtoupper($this->faker->bothify('######')),
            'notes' => null,
        ];
    }
}
