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
        Schema::table('affiliate_commissions', function (Blueprint $table) {
            // Add payment_status column if it doesn't exist
            if (!Schema::hasColumn('affiliate_commissions', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid'])->default('pending')->after('status');
            }
            
            // Add paid_at timestamp to track when payment was processed
            if (!Schema::hasColumn('affiliate_commissions', 'payment_processed_at')) {
                $table->timestamp('payment_processed_at')->nullable()->after('paid_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_commissions', function (Blueprint $table) {
            $table->dropColumn('payment_status');
            $table->dropColumn('payment_processed_at');
        });
    }
};
