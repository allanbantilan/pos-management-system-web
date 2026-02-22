<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->string('payment_method');
            $table->string('status')->default('completed');
            $table->decimal('total', 10, 2);
            $table->string('provider_payment_id')->nullable();
            $table->string('provider_reference')->nullable();
            $table->json('payload');
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'issued_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};

