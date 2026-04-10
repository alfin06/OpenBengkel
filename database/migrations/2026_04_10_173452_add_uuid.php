<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Invoice;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // 1. Add the column as nullable first so it doesn't fail on existing data
            $table->uuid('uuid')->nullable()->after('id')->unique();
        });

        // 2. Generate UUIDs for all existing invoices
        Invoice::all()->each(function ($invoice) {
            $invoice->uuid = (string) Str::uuid();
            $invoice->save();
        });

        Schema::table('invoices', function (Blueprint $table) {
            // 3. Optional: Change to non-nullable once all data is populated
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
