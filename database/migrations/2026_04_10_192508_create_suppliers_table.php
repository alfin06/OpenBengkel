<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone_office')->nullable();
            $table->string('phone_whatsapp')->nullable();
            $table->string('phone_sales')->nullable();
            $table->string('sales_name')->nullable();
            $table->string('email')->nullable();
            $table->string('npwp')->nullable(); // Tax ID for Indonesian business
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes(); // For the deleted_at column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
