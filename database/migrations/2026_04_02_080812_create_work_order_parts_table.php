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
        Schema::create('work_order_parts', function (Blueprint $table) {
            $table->id();
            // This is the line Laravel is looking for:
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('part_id')->constrained(); 
            $table->integer('qty')->default(1);
            $table->decimal('price_sold', 15, 2);
            $table->decimal('cost_at_sale', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_parts');
    }
};
