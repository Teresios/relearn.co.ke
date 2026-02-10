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
        // Assign affiliate role to all users where is_affiliate = 1 and they don't already have the role
        $affiliateUsers = \App\Models\User::where('is_affiliate', true)->get();
        
        foreach ($affiliateUsers as $user) {
            if (!$user->hasRole('affiliate')) {
                $user->assignRole('affiliate');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove affiliate role from users
        $affiliateUsers = \App\Models\User::where('is_affiliate', true)->get();
        
        foreach ($affiliateUsers as $user) {
            $user->removeRole('affiliate');
        }
    }
};
