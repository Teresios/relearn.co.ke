<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mpesa_reports', function (Blueprint $table) {
            $table->id();
            
            // Report identification
            $table->string('report_type'); // daily, weekly, monthly, custom
            $table->date('period_start');
            $table->date('period_end');
            
            // Summary data
            $table->decimal('total_inbound', 15, 2)->default(0);
            $table->decimal('total_outbound', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            
            // Transaction counts
            $table->integer('c2b_count')->default(0);
            $table->decimal('c2b_amount', 15, 2)->default(0);
            
            $table->integer('b2c_count')->default(0);
            $table->decimal('b2c_amount', 15, 2)->default(0);
            
            $table->integer('b2b_count')->default(0);
            $table->decimal('b2b_amount', 15, 2)->default(0);
            
            $table->integer('stk_push_count')->default(0);
            $table->decimal('stk_push_amount', 15, 2)->default(0);
            
            $table->integer('reversal_count')->default(0);
            $table->decimal('reversal_amount', 15, 2)->default(0);
            
            // Success/failure rates
            $table->integer('successful_transactions')->default(0);
            $table->integer('failed_transactions')->default(0);
            $table->decimal('success_rate', 5, 2)->default(0);
            
            // Forwarding summary
            $table->integer('forwarded_count')->default(0);
            $table->decimal('forwarded_amount', 15, 2)->default(0);
            
            // Full report data (JSON)
            $table->json('detailed_breakdown')->nullable();
            $table->json('hourly_breakdown')->nullable();
            $table->json('top_senders')->nullable();
            $table->json('top_receivers')->nullable();
            
            // Generation metadata
            $table->timestamp('generated_at');
            $table->foreignId('generated_by')->nullable()->constrained('users');
            $table->string('file_path')->nullable(); // For exported PDF/CSV
            
            $table->timestamps();
            
            $table->unique(['report_type', 'period_start', 'period_end']);
            $table->index(['report_type', 'period_start']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mpesa_reports');
    }
};
