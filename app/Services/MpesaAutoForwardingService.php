<?php

namespace App\Services;

use App\Models\MpesaTransaction;
use App\Models\MpesaForwardingRule;
use App\Events\MpesaTransactionReceived;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MpesaAutoForwardingService
{
    protected MpesaService $mpesaService;

    public function __construct(MpesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }

    /**
     * Process an incoming C2B payment and check for auto-forwarding rules
     */
    public function processIncomingPayment(array $paymentData): ?array
    {
        $phoneNumber = $this->formatPhoneNumber($paymentData['MSISDN'] ?? $paymentData['phone'] ?? '');
        $amount = floatval($paymentData['TransAmount'] ?? $paymentData['amount'] ?? 0);
        $transactionId = $paymentData['TransID'] ?? $paymentData['transaction_id'] ?? '';
        $billRefNumber = $paymentData['BillRefNumber'] ?? $paymentData['account'] ?? '';
        
        // Record the incoming transaction
        $transaction = $this->recordTransaction($paymentData);
        
        // Broadcast real-time event
        event(new MpesaTransactionReceived($transaction));
        
        // Find applicable forwarding rules
        $rules = $this->findApplicableRules($phoneNumber, $amount);
        
        if ($rules->isEmpty()) {
            Log::info('No auto-forwarding rules found for payment', [
                'phone' => $phoneNumber,
                'amount' => $amount,
                'transaction_id' => $transactionId
            ]);
            return null;
        }
        
        $forwardingResults = [];
        
        foreach ($rules as $rule) {
            $result = $this->executeForwarding($rule, $paymentData, $transaction);
            $forwardingResults[] = $result;
            
            // Update rule execution count
            $rule->increment('times_executed');
            $rule->update(['last_executed_at' => now()]);
        }
        
        return $forwardingResults;
    }

    /**
     * Record incoming C2B transaction
     */
    protected function recordTransaction(array $data): MpesaTransaction
    {
        return MpesaTransaction::create([
            'transaction_id' => $data['TransID'] ?? $data['transaction_id'] ?? 'C2B_' . uniqid(),
            'transaction_type' => MpesaTransaction::TYPE_C2B,
            'phone_number' => $this->formatPhoneNumber($data['MSISDN'] ?? $data['phone'] ?? ''),
            'amount' => floatval($data['TransAmount'] ?? $data['amount'] ?? 0),
            'account_reference' => $data['BillRefNumber'] ?? $data['account'] ?? '',
            'result_code' => 0,
            'result_desc' => 'Payment received successfully',
            'status' => MpesaTransaction::STATUS_COMPLETED,
            'raw_request' => json_encode($data),
            'raw_response' => null,
        ]);
    }

    /**
     * Find forwarding rules that apply to this payment
     */
    protected function findApplicableRules(string $phoneNumber, float $amount): \Illuminate\Database\Eloquent\Collection
    {
        return MpesaForwardingRule::where('is_active', true)
            ->where(function ($query) use ($phoneNumber) {
                // Match personal number rules
                $query->where('source_type', 'personal')
                      ->where(function ($q) use ($phoneNumber) {
                          $q->where('source_identifier', $phoneNumber)
                            ->orWhereNull('source_identifier')
                            ->orWhere('source_identifier', '');
                      });
            })
            ->orWhere(function ($query) {
                // Also get shortcode rules that match
                $query->where('is_active', true)
                      ->where('source_type', 'shortcode');
            })
            ->where(function ($query) use ($amount) {
                // Check amount thresholds
                $query->where(function ($q) use ($amount) {
                    $q->whereNull('min_amount')
                      ->orWhere('min_amount', '<=', $amount);
                })->where(function ($q) use ($amount) {
                    $q->whereNull('max_amount')
                      ->orWhere('max_amount', '>=', $amount);
                });
            })
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Execute the forwarding action based on rule type
     */
    protected function executeForwarding(MpesaForwardingRule $rule, array $paymentData, MpesaTransaction $originalTransaction): array
    {
        $amount = floatval($paymentData['TransAmount'] ?? $paymentData['amount'] ?? 0);
        
        // Apply percentage if set
        if ($rule->percentage && $rule->percentage > 0 && $rule->percentage < 100) {
            $amount = round($amount * ($rule->percentage / 100), 2);
        }
        
        // Apply flat deduction if set
        if ($rule->flat_deduction && $rule->flat_deduction > 0) {
            $amount = max(0, $amount - $rule->flat_deduction);
        }
        
        if ($amount <= 0) {
            Log::warning('Forward amount is zero or negative after deductions', [
                'rule_id' => $rule->id,
                'original_amount' => $paymentData['TransAmount'] ?? 0,
                'calculated_amount' => $amount
            ]);
            return [
                'success' => false,
                'rule_id' => $rule->id,
                'message' => 'Calculated forward amount is zero or negative',
            ];
        }

        try {
            $result = null;
            
            switch ($rule->destination_type) {
                case 'shortcode':
                    // B2B transfer to business shortcode
                    $result = $this->mpesaService->b2b(
                        $amount,
                        $rule->destination_identifier,
                        $rule->destination_account ?? 'AUTO_FORWARD_' . $originalTransaction->transaction_id,
                        'BusinessPayBill',
                        $rule->description ?? 'Auto-forwarded payment'
                    );
                    break;
                    
                case 'phone':
                    // B2C transfer to phone number
                    $result = $this->mpesaService->b2c(
                        $amount,
                        $rule->destination_identifier,
                        'BusinessPayment',
                        $rule->description ?? 'Auto-forwarded payment'
                    );
                    break;
                    
                case 'paybill':
                    // B2B transfer to paybill
                    $result = $this->mpesaService->b2b(
                        $amount,
                        $rule->destination_identifier,
                        $rule->destination_account ?? '',
                        'BusinessPayBill',
                        $rule->description ?? 'Auto-forwarded payment'
                    );
                    break;
                    
                case 'till':
                    // B2B transfer to till number
                    $result = $this->mpesaService->b2b(
                        $amount,
                        $rule->destination_identifier,
                        '',
                        'BusinessBuyGoods',
                        $rule->description ?? 'Auto-forwarded payment'
                    );
                    break;
                    
                default:
                    Log::error('Unknown destination type in forwarding rule', [
                        'rule_id' => $rule->id,
                        'destination_type' => $rule->destination_type
                    ]);
                    return [
                        'success' => false,
                        'rule_id' => $rule->id,
                        'message' => 'Unknown destination type: ' . $rule->destination_type,
                    ];
            }
            
            // Record the forwarding transaction
            $forwardTransaction = MpesaTransaction::create([
                'transaction_id' => $result['ConversationID'] ?? 'FWD_' . uniqid(),
                'transaction_type' => $rule->destination_type === 'phone' ? MpesaTransaction::TYPE_B2C : MpesaTransaction::TYPE_B2B,
                'phone_number' => $rule->destination_identifier,
                'amount' => $amount,
                'account_reference' => $rule->destination_account ?? 'AUTO_FWD',
                'description' => 'Auto-forwarded from ' . $originalTransaction->transaction_id,
                'result_code' => null,
                'result_desc' => 'Pending',
                'status' => MpesaTransaction::STATUS_PENDING,
                'raw_request' => json_encode($result),
                'metadata' => json_encode([
                    'forwarding_rule_id' => $rule->id,
                    'original_transaction_id' => $originalTransaction->id,
                    'original_amount' => $paymentData['TransAmount'] ?? 0,
                ]),
            ]);
            
            // Link to original transaction
            $originalTransaction->update([
                'forwarded_transaction_id' => $forwardTransaction->id,
                'metadata' => json_encode(array_merge(
                    json_decode($originalTransaction->metadata ?? '{}', true) ?: [],
                    ['forwarded_to' => $forwardTransaction->transaction_id]
                )),
            ]);
            
            Log::info('Payment auto-forwarded successfully', [
                'rule_id' => $rule->id,
                'original_transaction_id' => $originalTransaction->transaction_id,
                'forward_transaction_id' => $forwardTransaction->transaction_id,
                'amount' => $amount,
            ]);
            
            return [
                'success' => true,
                'rule_id' => $rule->id,
                'forward_transaction_id' => $forwardTransaction->transaction_id,
                'amount' => $amount,
                'destination' => $rule->destination_identifier,
            ];
            
        } catch (\Exception $e) {
            Log::error('Error executing forwarding rule', [
                'rule_id' => $rule->id,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'rule_id' => $rule->id,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format phone number to standard format (254XXXXXXXXX)
     */
    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '+254')) {
            $phone = substr($phone, 1);
        } elseif (!str_starts_with($phone, '254')) {
            $phone = '254' . $phone;
        }
        
        return $phone;
    }

    /**
     * Test a forwarding rule with a simulated payment
     */
    public function testRule(MpesaForwardingRule $rule, float $amount = 100): array
    {
        $simulatedPayment = [
            'TransID' => 'TEST_' . uniqid(),
            'MSISDN' => '254700000000',
            'TransAmount' => $amount,
            'BillRefNumber' => 'TEST_ACCOUNT',
        ];

        // Calculate what would happen without actually executing
        $forwardAmount = $amount;
        
        if ($rule->percentage && $rule->percentage > 0 && $rule->percentage < 100) {
            $forwardAmount = round($amount * ($rule->percentage / 100), 2);
        }
        
        if ($rule->flat_deduction && $rule->flat_deduction > 0) {
            $forwardAmount = max(0, $forwardAmount - $rule->flat_deduction);
        }

        return [
            'rule' => $rule->toArray(),
            'simulated_payment' => $simulatedPayment,
            'would_forward' => $this->checkRuleApplies($rule, $amount),
            'original_amount' => $amount,
            'forward_amount' => $forwardAmount,
            'destination_type' => $rule->destination_type,
            'destination' => $rule->destination_identifier,
            'estimated_charges' => $this->estimateCharges($forwardAmount, $rule->destination_type),
        ];
    }

    /**
     * Check if a rule would apply to a given amount
     */
    protected function checkRuleApplies(MpesaForwardingRule $rule, float $amount): bool
    {
        if (!$rule->is_active) {
            return false;
        }
        
        if ($rule->min_amount && $amount < $rule->min_amount) {
            return false;
        }
        
        if ($rule->max_amount && $amount > $rule->max_amount) {
            return false;
        }
        
        return true;
    }

    /**
     * Estimate M-Pesa charges for a transfer
     */
    protected function estimateCharges(float $amount, string $destinationType): float
    {
        // These are approximate M-Pesa charges (actual may vary)
        if ($destinationType === 'phone') {
            // B2C charges (approximate)
            if ($amount <= 100) return 0;
            if ($amount <= 500) return 15;
            if ($amount <= 1000) return 20;
            if ($amount <= 1500) return 25;
            if ($amount <= 2500) return 30;
            if ($amount <= 3500) return 35;
            if ($amount <= 5000) return 40;
            if ($amount <= 7500) return 55;
            if ($amount <= 10000) return 70;
            if ($amount <= 15000) return 87;
            if ($amount <= 20000) return 97;
            return 108; // 20001+
        }
        
        // B2B charges are typically negotiated with Safaricom
        return 0;
    }

    /**
     * Get forwarding statistics
     */
    public function getStatistics(string $period = 'today'): array
    {
        $startDate = match ($period) {
            'today' => today(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => today(),
        };

        $forwardedTransactions = MpesaTransaction::where('description', 'like', 'Auto-forwarded%')
            ->where('created_at', '>=', $startDate)
            ->get();

        return [
            'period' => $period,
            'total_forwarded' => $forwardedTransactions->count(),
            'total_amount' => $forwardedTransactions->sum('amount'),
            'successful' => $forwardedTransactions->where('status', MpesaTransaction::STATUS_COMPLETED)->count(),
            'pending' => $forwardedTransactions->where('status', MpesaTransaction::STATUS_PENDING)->count(),
            'failed' => $forwardedTransactions->where('status', MpesaTransaction::STATUS_FAILED)->count(),
            'by_destination_type' => $forwardedTransactions->groupBy(function ($t) {
                return $t->transaction_type;
            })->map->count(),
            'active_rules' => MpesaForwardingRule::where('is_active', true)->count(),
        ];
    }
}
