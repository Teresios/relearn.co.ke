<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Note: We don't need to change the database default because the controller
     * explicitly sets status to 'approved' when creating applications.
     * This migration is kept as a placeholder to maintain migration history.
     */
    public function up(): void
    {
        // No-op: Status is set to 'approved' in AffiliateApplicationController::store()
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
