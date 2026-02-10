<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mpesa_transactions', function (Blueprint $table) {
            $table->id();
            
            // Transaction identification
            $table->string('transaction_id')->unique()->nullable();
            $table->string('conversation_id')->nullable();
            $table->string('originator_conversation_id')->nullable();
            $table->string('checkout_request_id')->nullable();
            $table->string('merchant_request_id')->nullable();
            
            // Transaction type: c2b, b2c, b2b, stk_push, reversal
            $table->enum('type', ['c2b', 'b2c', 'b2b', 'stk_push', 'reversal'])->index();
            
            // Direction: inbound (money in) or outbound (money out)
            $table->enum('direction', ['inbound', 'outbound'])->index();
            
            // Amount and currency
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('KES');
            
            // Phone numbers
            $table->string('sender_phone', 20)->nullable();
            $table->string('receiver_phone', 20)->nullable();
            
            // Business details
            $table->string('sender_shortcode')->nullable();
            $table->string('receiver_shortcode')->nullable();
            $table->string('account_reference')->nullable();
            $table->text('transaction_desc')->nullable();
            
            // Status tracking
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'cancelled', 'reversed'])->default('pending')->index();
            $table->string('result_code')->nullable();
            $table->text('result_desc')->nullable();
            
            // Reversal tracking
            $table->boolean('is_reversed')->default(false);
            $table->foreignId('reversed_transaction_id')->nullable()->constrained('mpesa_transactions');
            $table->timestamp('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();
            
            // Forwarding tracking
            $table->boolean('is_forwarded')->default(false);
            $table->foreignId('forwarded_transaction_id')->nullable()->constrained('mpesa_transactions');
            $table->timestamp('forwarded_at')->nullable();
            
            // Balance after transaction
            $table->decimal('balance_after', 15, 2)->nullable();
            
            // Raw API response/request data
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->json('callback_payload')->nullable();
            
            // Metadata
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('order_id')->nullable()->constrained();
            $table->string('initiated_by')->nullable(); // system, admin, api
            $table->ipAddress('ip_address')->nullable();
            
            $table->timestamp('transaction_time')->nullable();
            $table->timestamps();
            
            // Indexes for reporting
            $table->index(['type', 'status', 'created_at']);
            $table->index(['sender_phone', 'created_at']);
            $table->index(['receiver_phone', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mpesa_transactions');
    }
};
