<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mpesa_budgets', function (Blueprint $table) {
            $table->id();
            
            // Budget identification
            $table->string('name');
            $table->text('description')->nullable();
            
            // Budget type: daily, weekly, monthly, custom
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'custom'])->index();
            
            // For custom periods
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            
            // Budget limits
            $table->decimal('budget_limit', 15, 2);
            $table->decimal('warning_threshold', 5, 2)->default(80.00); // percentage
            $table->decimal('critical_threshold', 5, 2)->default(95.00); // percentage
            
            // Current spending tracking
            $table->decimal('spent_amount', 15, 2)->default(0);
            $table->integer('transaction_count')->default(0);
            
            // Transaction type filters (null = all types)
            $table->json('transaction_types')->nullable(); // ['b2c', 'b2b']
            
            // Direction filter (null = both)
            $table->enum('direction', ['inbound', 'outbound'])->nullable();
            
            // Alert settings
            $table->boolean('alerts_enabled')->default(true);
            $table->json('alert_emails')->nullable();
            $table->json('alert_phones')->nullable(); // for SMS alerts
            $table->boolean('alert_on_warning')->default(true);
            $table->boolean('alert_on_critical')->default(true);
            $table->boolean('alert_on_exceeded')->default(true);
            $table->boolean('block_on_exceeded')->default(false);
            
            // Last alert timestamps
            $table->timestamp('warning_alert_sent_at')->nullable();
            $table->timestamp('critical_alert_sent_at')->nullable();
            $table->timestamp('exceeded_alert_sent_at')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mpesa_budgets');
    }
};
