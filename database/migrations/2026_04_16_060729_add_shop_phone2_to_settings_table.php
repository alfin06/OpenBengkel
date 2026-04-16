<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            // We place it after the first phone number for organization
            $table->string('shop_phone2')->nullable()->after('shop_phone');
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('shop_phone2');
        });
    }
};
