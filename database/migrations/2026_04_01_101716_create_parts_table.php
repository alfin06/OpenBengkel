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
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Oli Shell Helix 10W-40"
            $table->string('sku')->nullable(); // Barcode or Part Number
            $table->decimal('buy_price', 15, 2)->default(0); // For calculating profit
            $table->decimal('sell_price', 15, 2)->default(0); // Price on Invoice
            $table->integer('stock')->default(0); // Current quantity in cabinet
            $table->string('category')->default('Part'); // 'Part' or 'Service' (Labor)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
