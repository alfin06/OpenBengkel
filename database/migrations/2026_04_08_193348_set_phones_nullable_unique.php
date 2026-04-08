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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('phone_1')->nullable()->change();
            $table->string('phone_2')->nullable()->change();
            $table->string('phone_3')->nullable()->change();

            // Add unique constraints
            $table->unique('phone_1');
            $table->unique('phone_2');
            $table->unique('phone_3');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['phone_1']);
            $table->dropUnique(['phone_2']);
            $table->dropUnique(['phone_3']);
        });
    }
};
