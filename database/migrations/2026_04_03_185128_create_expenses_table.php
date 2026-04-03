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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Tracks which staff member logged it
            $table->string('description'); // e.g., "Makan siang mekanik Budi & Joko"
            $table->integer('amount'); // e.g., 50000
            $table->string('category')->default('Operasional'); // e.g., Konsumsi, Listrik, Alat
            $table->date('expense_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
