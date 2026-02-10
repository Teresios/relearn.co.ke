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
        Schema::table('affiliate_applications', function (Blueprint $table) {
            $table->string('setup_token')->unique()->nullable()->after('status');
            $table->timestamp('setup_token_expires_at')->nullable()->after('setup_token');
            $table->boolean('credentials_set')->default(false)->after('setup_token_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_applications', function (Blueprint $table) {
            $table->dropColumn(['setup_token', 'setup_token_expires_at', 'credentials_set']);
        });
    }
};
