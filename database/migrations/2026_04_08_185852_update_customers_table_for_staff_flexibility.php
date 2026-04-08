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
            // Rename existing columns to follow the new pattern
            $table->renameColumn('phone', 'phone_1');
            $table->renameColumn('phone_secondary', 'phone_2');
        });

        Schema::table('customers', function (Blueprint $table) {
            // Make them optional and add the new fields
            $table->string('phone_1')->nullable()->change();
            $table->string('phone_2')->nullable()->change();
            $table->string('phone_3')->nullable()->after('phone_2');
            $table->text('address')->nullable()->change();
            $table->text('notes')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->renameColumn('phone_1', 'phone');
            $table->renameColumn('phone_2', 'phone_secondary');
            $table->dropColumn(['phone_3', 'notes']);
        });
    }
};
