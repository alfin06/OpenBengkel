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
        Schema::table('invoice_details', function (Blueprint $table) {
            // We make it nullable so manual entries (no stock link) still work
            $table->foreignId('part_id')->nullable()->constrained('parts')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('invoice_details', function (Blueprint $table) {
            $table->dropConstrainedForeignId('part_id');
        });
    }
};
