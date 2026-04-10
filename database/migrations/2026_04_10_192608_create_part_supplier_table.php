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
        // We add a check here "if not exists" to prevent this error from happening again
        if (!Schema::hasTable('part_supplier')) {
            Schema::create('part_supplier', function (Blueprint $table) {
                $table->id();
                $table->foreignId('part_id')->constrained()->onDelete('cascade');
                $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
                $table->decimal('purchase_price', 15, 2)->default(0);
                $table->string('supplier_sku')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_supplier');
    }
};
