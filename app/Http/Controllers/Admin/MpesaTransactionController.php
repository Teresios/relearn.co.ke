<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MpesaTransaction;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaTransactionController extends Controller
{
    protected MpesaService $mpesaService;

    public function __construct(MpesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }

    public function index(Request $request)
    {
        $query = MpesaTransaction::with(['user', 'order']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhere('sender_phone', 'like', "%{$search}%")
                    ->orWhere('receiver_phone', 'like', "%{$search}%")
                    ->orWhere('account_reference', 'like', "%{$search}%");
            });
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Direction filter
        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Amount range filter
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }
        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }

        $transactions = $query->latest()->paginate(20)->withQueryString();

        // Get filter options
        $types = [
            MpesaTransaction::TYPE_C2B => 'C2B',
            MpesaTransaction::TYPE_B2C => 'B2C',
            MpesaTransaction::TYPE_B2B => 'B2B',
            MpesaTransaction::TYPE_STK_PUSH => 'STK Push',
            MpesaTransaction::TYPE_REVERSAL => 'Reversal',
        ];

        $statuses = [
            MpesaTransaction::STATUS_PENDING => 'Pending',
            MpesaTransaction::STATUS_PROCESSING => 'Processing',
            MpesaTransaction::STATUS_SUCCESS => 'Success',
            MpesaTransaction::STATUS_FAILED => 'Failed',
            MpesaTransaction::STATUS_CANCELLED => 'Cancelled',
            MpesaTransaction::STATUS_REVERSED => 'Reversed',
        ];

        return view('admin.mpesa.transactions.index', compact('transactions', 'types', 'statuses'));
    }

    public function show(MpesaTransaction $transaction)
    {
        $transaction->load(['user', 'order', 'reversedTransaction', 'reversal', 'forwardedTransaction']);
        
        return view('admin.mpesa.transactions.show', compact('transaction'));
    }

    public function initiateB2C(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'amount' => 'required|numeric|min:10',
            'remarks' => 'nullable|string|max:100',
            'occasion' => 'nullable|string|max:100',
        ]);

        try {
            $result = $this->mpesaService->b2cPayment(
                $request->phone,
                $request->amount,
                $request->remarks ?? 'Business Payment',
                $request->occasion ?? ''
            );

            if ($result['success']) {
                // Create transaction record
                MpesaTransaction::create([
                    'conversation_id' => $result['ConversationID'] ?? null,
                    'originator_conversation_id' => $result['OriginatorConversationID'] ?? null,
                    'type' => MpesaTransaction::TYPE_B2C,
                    'direction' => MpesaTransaction::DIRECTION_OUTBOUND,
                    'amount' => $request->amount,
                    'receiver_phone' => $this->mpesaService->formatPhoneNumber($request->phone),
                    'transaction_desc' => $request->remarks,
                    'status' => MpesaTransaction::STATUS_PENDING,
                    'request_payload' => $request->all(),
                    'response_payload' => $result,
                    'initiated_by' => 'admin',
                    'user_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                ]);

                return back()->with('success', 'B2C payment initiated successfully');
            }

            return back()->with('error', $result['message'] ?? 'Failed to initiate B2C payment');
        } catch (\Exception $e) {
            Log::error('B2C Payment Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to process B2C payment');
        }
    }

    public function initiateB2B(Request $request)
    {
        $request->validate([
            'shortcode' => 'required|string',
            'amount' => 'required|numeric|min:10',
            'account_reference' => 'nullable|string|max:13',
            'remarks' => 'nullable|string|max:100',
        ]);

        try {
            $result = $this->mpesaService->b2bPayment(
                $request->shortcode,
                $request->amount,
                $request->account_reference ?? '',
                $request->remarks ?? 'Business Payment'
            );

            if ($result['success']) {
                MpesaTransaction::create([
                    'conversation_id' => $result['ConversationID'] ?? null,
                    'originator_conversation_id' => $result['OriginatorConversationID'] ?? null,
                    'type' => MpesaTransaction::TYPE_B2B,
                    'direction' => MpesaTransaction::DIRECTION_OUTBOUND,
                    'amount' => $request->amount,
                    'receiver_shortcode' => $request->shortcode,
                    'account_reference' => $request->account_reference,
                    'transaction_desc' => $request->remarks,
                    'status' => MpesaTransaction::STATUS_PENDING,
                    'request_payload' => $request->all(),
                    'response_payload' => $result,
                    'initiated_by' => 'admin',
                    'user_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                ]);

                return back()->with('success', 'B2B payment initiated successfully');
            }

            return back()->with('error', $result['message'] ?? 'Failed to initiate B2B payment');
        } catch (\Exception $e) {
            Log::error('B2B Payment Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to process B2B payment');
        }
    }

    public function reverseTransaction(Request $request, MpesaTransaction $transaction)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        if (!$transaction->canBeReversed()) {
            return back()->with('error', 'This transaction cannot be reversed');
        }

        try {
            $result = $this->mpesaService->reverseTransaction(
                $transaction->transaction_id,
                $transaction->amount,
                $request->reason
            );

            if ($result['success']) {
                // Create reversal transaction record
                $reversalTransaction = MpesaTransaction::create([
                    'conversation_id' => $result['ConversationID'] ?? null,
                    'originator_conversation_id' => $result['OriginatorConversationID'] ?? null,
                    'type' => MpesaTransaction::TYPE_REVERSAL,
                    'direction' => MpesaTransaction::DIRECTION_INBOUND,
                    'amount' => $transaction->amount,
                    'reversed_transaction_id' => $transaction->id,
                    'reversal_reason' => $request->reason,
                    'status' => MpesaTransaction::STATUS_PENDING,
                    'request_payload' => $request->all(),
                    'response_payload' => $result,
                    'initiated_by' => 'admin',
                    'user_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                ]);

                return back()->with('success', 'Reversal initiated successfully');
            }

            return back()->with('error', $result['message'] ?? 'Failed to initiate reversal');
        } catch (\Exception $e) {
            Log::error('Reversal Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to process reversal');
        }
    }

    public function checkStatus(MpesaTransaction $transaction)
    {
        try {
            if ($transaction->checkout_request_id) {
                $result = $this->mpesaService->stkQuery($transaction->checkout_request_id);
            } else {
                $result = $this->mpesaService->transactionStatus(
                    $transaction->transaction_id,
                    $transaction->type
                );
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function export(Request $request)
    {
        $query = MpesaTransaction::query();

        // Apply same filters as index
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()->get();

        $filename = 'mpesa_transactions_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'ID', 'Transaction ID', 'Type', 'Direction', 'Amount', 'Currency',
                'Sender Phone', 'Receiver Phone', 'Status', 'Created At'
            ]);

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->id,
                    $t->transaction_id,
                    $t->type,
                    $t->direction,
                    $t->amount,
                    $t->currency,
                    $t->sender_phone,
                    $t->receiver_phone,
                    $t->status,
                    $t->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
