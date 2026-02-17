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
        Schema::create('pos_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('category');
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(0); 
            $table->string('image')->nullable();
            $table->string('barcode')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_taxable')->default(true);
            $table->decimal('tax_rate', 5, 2)->default(10.00); 
            $table->string('unit')->default('pcs'); 
            $table->json('metadata')->nullable(); 
            $table->timestamps();
            $table->softDeletes(); 

            // Indexes for better query performance
            $table->index('category');
            $table->index('sku');
            $table->index('barcode');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_items');
    }
};
