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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->decimal('parts_total', 15, 2); 
            $table->decimal('labor_fee', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2);
            $table->string('payment_method'); // Cash, Transfer, QRIS
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
