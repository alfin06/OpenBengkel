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
        Schema::table('invoices', function (Blueprint $table) {
            // 'paid' for instant cash, 'unpaid' for company/debt (Piutang)
            $table->string('status')->default('paid')->after('payment_method');
            
            // Track how much has actually been received (useful for partial payments)
            $table->decimal('amount_paid', 15, 2)->default(0)->after('grand_total');
            
            // When the company is expected to pay (e.g., 30 days later)
            $table->date('due_date')->nullable()->after('status');
            
            // To store company name or PIC if it's a corporate car
            $table->string('customer_reference')->nullable()->after('due_date');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['status', 'amount_paid', 'due_date', 'customer_reference']);
        });
    }
};
