<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Simplifies AffiliateApplication form to only require:
     * - Full Name, Email, County, MPESA Phone Number
     * All data is stored in JSON format in application_data column.
     */
    public function up(): void
    {
        // The AffiliateApplication table structure remains the same:
        // - id, user_id (nullable for guests), application_data (JSON), status, created_at, updated_at
        // 
        // The application_data JSON now contains simplified fields:
        // {
        //   "full_name": "John Doe",
        //   "email": "john@example.com",
        //   "county": "Nairobi",
        //   "payment_details": "254723071290"
        // }
        //
        // Previous fields removed:
        // - phone (no longer needed, using payment_details only)
        // - preferred_contact (removed)
        // - social_handles (removed)
        // - bio (removed)
        // - referral_source (removed)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No database schema changes needed - data structure change only
    }
};
