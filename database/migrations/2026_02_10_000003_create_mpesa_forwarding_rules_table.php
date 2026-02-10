<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mpesa_forwarding_rules', function (Blueprint $table) {
            $table->id();
            
            // Rule identification
            $table->string('name');
            $table->text('description')->nullable();
            
            // Source configuration (where payment comes from)
            $table->string('source_phone')->nullable(); // personal number
            $table->string('source_shortcode')->nullable();
            
            // Destination configuration (where to forward)
            $table->string('destination_shortcode'); // business shortcode
            $table->string('destination_account')->nullable(); // account reference
            
            // Forwarding settings
            $table->decimal('min_amount', 15, 2)->nullable();
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->decimal('forward_percentage', 5, 2)->default(100.00);
            $table->decimal('flat_fee', 15, 2)->default(0);
            
            // Schedule settings
            $table->boolean('auto_forward')->default(true);
            $table->integer('delay_seconds')->default(0); // delay before forwarding
            $table->json('active_hours')->nullable(); // {"start": "08:00", "end": "22:00"}
            $table->json('active_days')->nullable(); // [1,2,3,4,5] for weekdays
            
            // Status tracking
            $table->boolean('is_active')->default(true);
            $table->integer('total_forwarded_count')->default(0);
            $table->decimal('total_forwarded_amount', 15, 2)->default(0);
            $table->timestamp('last_forward_at')->nullable();
            
            // Error tracking
            $table->integer('failed_forward_count')->default(0);
            $table->timestamp('last_failed_at')->nullable();
            $table->text('last_error')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index(['source_phone', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mpesa_forwarding_rules');
    }
};
