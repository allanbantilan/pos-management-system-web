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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_provider')->nullable()->after('payment_method');
            $table->string('provider_checkout_id')->nullable()->after('payment_provider');
            $table->string('provider_payment_id')->nullable()->after('provider_checkout_id');
            $table->string('provider_reference')->nullable()->after('provider_payment_id');
            $table->timestamp('paid_at')->nullable()->after('provider_reference');
            $table->timestamp('stock_deducted_at')->nullable()->after('paid_at');

            $table->index('provider_reference');
            $table->index('provider_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['provider_reference']);
            $table->dropIndex(['provider_payment_id']);

            $table->dropColumn([
                'payment_provider',
                'provider_checkout_id',
                'provider_payment_id',
                'provider_reference',
                'paid_at',
                'stock_deducted_at',
            ]);
        });
    }
};

