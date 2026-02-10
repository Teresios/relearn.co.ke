<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Download;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Events\OrderCompleted;
use App\Models\MasterClassRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\MpesaCallback;

class MpesaController extends Controller
{
    protected $mpesaService;

    public function __construct(MpesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }

    /**
     * Handle M-Pesa STK Push callback.
     */
    public function stkCallback(Request $request)
    {
        Log::info('M-Pesa STK Callback received', $request->all());
        // Extra diagnostics: log raw body, headers and request IP to help debug
        try {
            Log::info('M-Pesa STK Callback raw', [
                'raw' => $request->getContent(),
                'headers' => $request->headers->all(),
                'ip' => $request->ip(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to log raw callback data', ['error' => $e->getMessage()]);
        }

        try {
            // Persist raw callback for audit and deferred processing
            try {
                $rawBody = $request->getContent();
                $headers = $request->headers->all();
                $ip = $request->ip();
                MpesaCallback::create([
                    'checkout_request_id' => $request->input('Body.stkCallback.CheckoutRequestID'),
                    'merchant_request_id' => $request->input('Body.stkCallback.MerchantRequestID'),
                    'headers' => json_encode($headers),
                    'body' => $rawBody,
                    'ip' => $ip,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to persist raw mpesa callback', ['error' => $e->getMessage()]);
            }

            $callbackData = $request->input('Body.stkCallback');
            $checkoutRequestId = $callbackData['CheckoutRequestID'] ?? null;
            $resultCode = $callbackData['ResultCode'] ?? null;
            $resultDesc = $callbackData['ResultDesc'] ?? null;

            if (!$checkoutRequestId) {
                Log::error('CheckoutRequestID missing in callback data', $callbackData);
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data']);
            }

            // 1. Try to find an Order by CheckoutRequestID (with product relationship)
            $order = Order::with('product')->where('mpesa_checkout_request_id', $checkoutRequestId)->first();
            Log::info('Looking up order', [
                'checkout_request_id' => $checkoutRequestId,
                'order_found' => $order ? true : false,
                'order_id' => $order ? $order->id : null,
                'is_guest' => $order ? ($order->user_id ? false : true) : null
            ]);
            if ($order) {
                $payment = Payment::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'checkout_request_id' => $checkoutRequestId,
                        'result_code' => $resultCode,
                        'result_desc' => $resultDesc,
                        'amount' => $order->amount,
                        'phone_number' => $order->payment_phone,
                    ]
                );

                if ($resultCode == 0) {
                    // Successful payment
                    Log::info('Processing successful payment', [
                        'order_id' => $order->id,
                        'is_guest' => $order->user_id ? false : true,
                        'payment_phone' => $order->payment_phone
                    ]);
                    $callbackMetadata = $callbackData['CallbackMetadata']['Item'] ?? [];
                    $mpesaReceiptNumber = null;
                    $transactionDate = null;
                    foreach ($callbackMetadata as $item) {
                        if (($item['Name'] ?? '') === 'MpesaReceiptNumber') {
                            $mpesaReceiptNumber = $item['Value'] ?? null;
                        }
                        if (($item['Name'] ?? '') === 'TransactionDate') {
                            $transactionDate = isset($item['Value'])
                                ? \DateTime::createFromFormat('YmdHis', $item['Value'])
                                : null;
                        }
                    }

                    try {
                        $payment->update([
                            'status' => Payment::STATUS_SUCCESS,
                            'mpesa_receipt_number' => $mpesaReceiptNumber,
                            'transaction_date' => $transactionDate,
                        ]);
                        Log::info('Payment record updated successfully', [
                            'payment_id' => $payment->id,
                            'order_id' => $order->id,
                            'new_status' => Payment::STATUS_SUCCESS
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to update payment record', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage()
                        ]);
                    }

                    try {
                        $order->update([
                            'status' => Order::STATUS_COMPLETED,
                            'mpesa_receipt_number' => $mpesaReceiptNumber,
                            'transaction_date' => $transactionDate,
                        ]);
                        $order->refresh();

                        if ($order->user_id) {
                            // Ensure only one download record
                            $existingDownload = Download::where('user_id', $order->user_id)
                                ->where('product_id', $order->product_id)
                                ->where('order_id', $order->id)
                                ->first();

                            if (!$existingDownload) {
                                Download::create([
                                    'user_id' => $order->user_id,
                                    'product_id' => $order->product_id,
                                    'order_id' => $order->id,
                                ]);
                            }

                            // Log and dispatch thank-you email event
                            Log::info("Dispatching OrderCompleted event for order: {$order->id}");
                            event(new OrderCompleted($order));

                            Log::info('Order thank_you_sent status', ['order_id' => $order->id, 'thank_you_sent' => $order->thank_you_sent]);

                            // Send thank-you email immediately if not already sent
                            if ($order->status === Order::STATUS_COMPLETED && !$order->thank_you_sent) {
                                $user = $order->user;
                                $product = $order->product;
                                
                                // Get download token
                                $download = $order->downloads()->first();
                                $token = $download ? $download->token : ($order->download_token ?? '');
                                
                                // Generate separate URLs for each format
                                $epubDownloadUrl = !empty($product->file_path)
                                    ? route('downloads.serve-file', [$token, 'epub'])
                                    : null;
                                    
                                $pdfDownloadUrl = $product->hasPdf()
                                    ? route('downloads.serve-file', [$token, 'pdf'])
                                    : null;
                                    
                                $zipDownloadUrl = $product->hasZip()
                                    ? route('downloads.serve-file', [$token, 'zip'])
                                    : null;
                                
                                try {
                                    Mail::to($user->email)->send(new \App\Mail\ProductDownloadLinkMail(
                                        $user,
                                        $product,
                                        $epubDownloadUrl,
                                        $pdfDownloadUrl,
                                        $zipDownloadUrl
                                    ));
                                    $order->thank_you_sent = true;
                                    $order->save();
                                    Log::info('Thank you email sent', [
                                        'order_id' => $order->id,
                                        'email' => $user->email,
                                        'has_epub' => !empty($epubDownloadUrl),
                                        'has_pdf' => !empty($pdfDownloadUrl)
                                    ]);
                                } catch (\Exception $e) {
                                    Log::error('Failed to send thank you email', [
                                        'order_id' => $order->id,
                                        'email' => $user->email,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }
                        } else {
                            // Guest order: send email with download link to customer_email
                            Log::info('Guest order - sending download link via email', [
                                'order_id' => $order->id,
                                'payment_phone' => $order->payment_phone,
                                'customer_email' => $order->customer_email
                            ]);

                            // Send download link email to guest
                            if ($order->customer_email && !$order->thank_you_sent) {
                                $product = $order->product;
                                $token = $order->download_token ?? \Illuminate\Support\Str::random(40);
                                
                                // Try to create/get download record for guest
                                try {
                                    $download = $order->downloads()->first();
                                    if (!$download) {
                                        $download = \App\Models\Download::create([
                                            'user_id' => null, // Guest user
                                            'product_id' => $order->product_id,
                                            'order_id' => $order->id,
                                            'token' => $token,
                                            'expires_at' => now()->addDays(7),
                                            'download_count' => 0,
                                            'max_downloads' => 3,
                                        ]);
                                    }
                                    $token = $download->token;
                                } catch (\Exception $e) {
                                    Log::warning('Failed to create download record for guest, using fallback token', [
                                        'order_id' => $order->id,
                                        'error' => $e->getMessage()
                                    ]);
                                    // Continue with generated token even if download record creation fails
                                }
                                
                                // Generate separate URLs for each format
                                $epubDownloadUrl = !empty($product->file_path) 
                                    ? route('downloads.serve-file', [$token, 'epub']) 
                                    : null;
                                    
                                $pdfDownloadUrl = $product->hasPdf() 
                                    ? route('downloads.serve-file', [$token, 'pdf']) 
                                    : null;
                                    
                                $zipDownloadUrl = $product->hasZip() 
                                    ? route('downloads.serve-file', [$token, 'zip']) 
                                    : null;
                                
                                try {
                                    Mail::to($order->customer_email)->send(new \App\Mail\ProductDownloadLinkMail(
                                        (object)['name' => 'Valued Customer', 'email' => $order->customer_email, 'id' => null],
                                        $product,
                                        $epubDownloadUrl,
                                        $pdfDownloadUrl,
                                        $zipDownloadUrl
                                    ));
                                    $order->thank_you_sent = true;
                                    $order->save();
                                    Log::info('Download link email sent to guest', [
                                        'order_id' => $order->id,
                                        'email' => $order->customer_email,
                                        'has_epub' => !empty($epubDownloadUrl),
                                        'has_pdf' => !empty($pdfDownloadUrl),
                                        'has_zip' => !empty($zipDownloadUrl)
                                    ]);
                                } catch (\Exception $e) {
                                    Log::error('Failed to send download link email to guest', [
                                        'order_id' => $order->id,
                                        'email' => $order->customer_email,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }

                            // Still dispatch the OrderCompleted event to keep downstream hooks working
                            event(new OrderCompleted($order));
                        }

                        Log::info("Payment successful for order: {$order->id}");
                    } catch (\Exception $e) {
                        Log::error('Failed to finalize order processing', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                } else {
                    // Payment failed
                    $payment->update(['status' => Payment::STATUS_FAILED]);
                    $order->update(['status' => Order::STATUS_FAILED]);
                    Log::error("Payment failed for order: {$order->id} - {$resultDesc}", ['order_id' => $order->id, 'result_desc' => $resultDesc]);
                }

                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
            }

            // 2. If no order found, attempt conservative fallback matching for guest orders
            if (!$order) {
                $callbackMetadataItems = $callbackData['CallbackMetadata']['Item'] ?? [];
                $phoneFromCallback = null;
                $amountFromCallback = null;
                foreach ($callbackMetadataItems as $item) {
                    if (($item['Name'] ?? '') === 'PhoneNumber') {
                        $phoneFromCallback = preg_replace('/[^0-9]/', '', (string)($item['Value'] ?? ''));
                    }
                    if (($item['Name'] ?? '') === 'Amount') {
                        $amountFromCallback = $item['Value'] ?? null;
                    }
                }

                if ($phoneFromCallback && $amountFromCallback) {
                    // Normalize phone to 254... if it begins with 0
                    if (preg_match('/^0[0-9]{9}$/', $phoneFromCallback)) {
                        $phoneFromCallback = '254' . substr($phoneFromCallback, 1);
                    }
                    // Search for a recent pending order with same phone and amount
                    $candidate = Order::where('payment_phone', $phoneFromCallback)
                        ->where('amount', $amountFromCallback)
                        ->where('status', Order::STATUS_PENDING)
                        ->where('created_at', '>=', Carbon::now()->subMinutes(30))
                        ->latest()
                        ->first();

                    if ($candidate) {
                        $order = $candidate;
                        // Attach the CheckoutRequestID for future exact matches
                        try {
                            $order->update(['mpesa_checkout_request_id' => $checkoutRequestId]);
                            Log::info('Attached CheckoutRequestID to pending order via fallback', ['order_id' => $order->id, 'checkout_request_id' => $checkoutRequestId]);
                        } catch (\Exception $e) {
                            Log::warning('Failed to attach CheckoutRequestID to fallback order', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                        }
                    }
                }
            }

            // 3. Try to find a MasterClassRegistration by CheckoutRequestID
            $registration = MasterClassRegistration::where('checkout_request_id', $checkoutRequestId)->first();
            if ($registration) {
                if ($resultCode == 0) {
                    $callbackMetadata = $callbackData['CallbackMetadata']['Item'] ?? [];
                    $mpesaReceiptNumber = null;
                    $transactionDate = null;
                    foreach ($callbackMetadata as $item) {
                        if (($item['Name'] ?? '') === 'MpesaReceiptNumber') {
                            $mpesaReceiptNumber = $item['Value'] ?? null;
                        }
                        if (($item['Name'] ?? '') === 'TransactionDate') {
                            $transactionDate = isset($item['Value'])
                                ? \DateTime::createFromFormat('YmdHis', $item['Value'])
                                : null;
                        }
                    }

                    $registration->update([
                        'paid' => true,
                        'payment_result_code' => $resultCode,
                        'payment_result_desc' => $resultDesc,
                        'mpesa_receipt_number' => $mpesaReceiptNumber,
                        'transaction_date' => $transactionDate,
                    ]);
                    Log::info("Payment successful for masterclass registration: {$registration->id}");
                } else {
                    $registration->update([
                        'paid' => false,
                        'payment_result_code' => $resultCode,
                        'payment_result_desc' => $resultDesc,
                    ]);
                    Log::error("Payment failed for masterclass registration: {$registration->id} - {$resultDesc}", ['registration_id' => $registration->id, 'result_desc' => $resultDesc]);
                }
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
            }

            // No matching order or registration found
            Log::error("No Order or MasterClassRegistration found for CheckoutRequestID: {$checkoutRequestId}", ['checkout_request_id' => $checkoutRequestId]);
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'No matching record found']);
        } catch (\Exception $e) {
            Log::error('Error processing M-Pesa callback: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Error processing callback']);
        }
    }

    /**
     * Handle M-Pesa timeout callback.
     */
    public function timeoutCallback(Request $request)
    {
        Log::info('M-Pesa Timeout Callback received', $request->all());
        // Extra diagnostics for timeout callbacks
        try {
            Log::info('M-Pesa Timeout Callback raw', [
                'raw' => $request->getContent(),
                'headers' => $request->headers->all(),
                'ip' => $request->ip(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to log raw timeout callback data', ['error' => $e->getMessage()]);
        }

        try {
            $checkoutRequestId = $request->input('CheckoutRequestID');

            // 1. Try to find an Order
            $order = Order::where('mpesa_checkout_request_id', $checkoutRequestId)->first();
            if ($order) {
                $order->update(['status' => Order::STATUS_CANCELLED]);

                Payment::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'status' => Payment::STATUS_CANCELLED,
                        'result_desc' => 'Payment timeout',
                    ]
                );
                Log::warning("Payment timeout for order: {$order->id}");
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
            }

            // 2. Try to find a MasterClassRegistration
            $registration = MasterClassRegistration::where('mpesa_checkout_request_id', $checkoutRequestId)->first();
            if ($registration) {
                $registration->update([
                    'paid' => false,
                    'payment_result_code' => -1,
                    'payment_result_desc' => 'Payment timeout',
                ]);
                Log::warning("Payment timeout for masterclass registration: {$registration->id}");
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
            }

            // No matching order or registration found
            Log::error("No Order or MasterClassRegistration found for CheckoutRequestID: {$checkoutRequestId}", ['checkout_request_id' => $checkoutRequestId]);
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
        } catch (\Exception $e) {
            Log::error('Error processing M-Pesa timeout: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Error processing timeout']);
        }
    }
}