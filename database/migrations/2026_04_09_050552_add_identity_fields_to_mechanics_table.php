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
        Schema::table('mechanics', function (Blueprint $table) {
            $table->string('nik', 16)->nullable()->after('name');
            $table->string('ktp_path')->nullable()->after('nik');
            $table->timestamp('deleted_at')->nullable()->after('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mechanics', function (Blueprint $table) {
            //
        });
    }
};
