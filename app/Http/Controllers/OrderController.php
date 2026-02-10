<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Download;
use App\Models\Product;
use App\Models\Referral;
use App\Models\AffiliateCommission;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ProductDownloadLinkMail;
use App\Notifications\ReferralCompletedPurchase;

class OrderController extends Controller
{
    protected $mpesaService;

    public function __construct(MpesaService $mpesaService)
    {
        // Require auth only on actions that must be restricted to account owners.
        $this->middleware('auth')->only([
            'index',
            'show',
            'download',
        ]);
        $this->mpesaService = $mpesaService;
    }

    /**
     * Display user's orders.
     */
    public function index()
    {
        $orders = Auth::user()->orders()
            ->with(['product'])
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Show the checkout page.
     */
    public function checkout(Product $product)
    {
        if (!$product->is_active) {
            return redirect()->route('products.index')
                ->with('error', 'Product is not available.');
        }

        return view('orders.checkout', compact('product'));
    }

    /**
     * Process the order and initiate M-Pesa payment.
     */
    public function store(Request $request, Product $product)
    {
    Log::info('=== CHECKOUT FORM SUBMITTED ===', [
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'phone' => $request->customer_phone,
            'all_request_data' => $request->all()
        ]);

        $rules = [
            // Accept either local format (0XXXXXXXXX) or international (254XXXXXXXXX)
            'customer_phone' => ['required', 'string', 'regex:/^(?:0[0-9]{9}|254[0-9]{9}|7[0-9]{8})$/'],
        ];

        // For guests, require email. For authenticated users, email is optional (from their profile)
        if (!Auth::check()) {
            $rules['customer_email'] = ['required', 'email'];
        }

        $request->validate($rules);

    Log::info('Validation passed', ['phone' => $request->customer_phone]);

        // Check if user already owns this product. For guests, check by phone.
        // Normalize phone into consistent forms for lookup
        $rawPhone = $request->customer_phone;
        $normalizedPhone = $rawPhone;
        // If user entered local format starting with 0 (e.g., 0723...), convert to 254723...
        if (preg_match('/^0[0-9]{9}$/', $rawPhone)) {
            $normalizedPhone = '254' . substr($rawPhone, 1);
        }
        // If user entered local without leading zero (e.g., 723...), convert to 254723...
        if (preg_match('/^[7][0-9]{8}$/', $rawPhone)) {
            $normalizedPhone = '254' . $rawPhone;
        }

        // Allow multiple purchases of the same product by the same user or phone.
        // Previous logic prevented duplicate purchases by checking for completed orders
        // with the same user_id or payment_phone. That check was removed to allow
        // customers to purchase the same product multiple times (for gifts, multiple devices, etc.).

        DB::beginTransaction();
        try {
            // Create order
            $order = Order::create([
                'user_id' => Auth::id(), // may be null for guests
                'product_id' => $product->id,
                'amount' => $product->getEffectivePrice(),
                'status' => Order::STATUS_PENDING,
                // Store normalized phone in international format for consistency with MPESA
                'payment_phone' => (isset($normalizedPhone) ? $normalizedPhone : $request->customer_phone),
                // For guests, store the email for sending download links
                'customer_email' => !Auth::check() ? $request->customer_email : null,
                // Optionally, generate a unique download token for security
                'download_token' => bin2hex(random_bytes(16)),
            ]);

            // For guest checkouts, mark the session so the guest can view payment status.
            if (!Auth::check()) {
                session()->put('guest_order_' . $order->id, $request->customer_phone);
                session()->save(); // Ensure session is saved before redirect
            }

            // Log the STK Push attempt
            Log::info('Initiating STK Push for order', [
                'order_id' => $order->id,
                'phone' => $request->customer_phone,
                'amount' => $product->getEffectivePrice(),
                'product' => $product->name
            ]);

            // Initiate M-Pesa STK Push
            // Ensure we pass an international phone string to M-Pesa (2547...)
            $mpesaPhone = $normalizedPhone ?? $request->customer_phone;
            $response = $this->mpesaService->stkPush(
                $mpesaPhone,
                $product->getEffectivePrice(),
                'RELEARN-' . $order->id, // Account Reference
                'Payment for ' . $product->name // Transaction Description
            );

            Log::info('STK Push response', [
                'order_id' => $order->id,
                'response' => $response
            ]);

            if ($response['success']) {
                $order->update([
                    'mpesa_checkout_request_id' => $response['CheckoutRequestID']
                ]);

                DB::commit();

                // ===== Affiliate Referral Link Tracking =====
                // Check if this purchase came through an affiliate referral link
                $referralCode = session('referral_code');
                $referralId = session('referral_id');
                
                if ($referralCode || $referralId) {
                    \Log::info('Processing affiliate purchase', [
                        'order_id' => $order->id,
                        'referral_code' => $referralCode,
                        'referral_id' => $referralId,
                    ]);
                    
                    // Find the AffiliateReferral record
                    $affiliateReferral = \App\Models\AffiliateReferral::find($referralId);
                    
                    if ($affiliateReferral) {
                        // Update the referral to mark it as purchased
                        $affiliateReferral->update([
                            'purchased_at' => now(),
                            'order_id' => $order->id,
                        ]);
                        
                        // Find the affiliate link to get the affiliate
                        $affiliateLink = $affiliateReferral->link;
                        if ($affiliateLink && $affiliateLink->affiliate) {
                            // Create commission: 30% of order amount
                            $commissionAmount = $order->amount * 0.30;
                            
                            AffiliateCommission::create([
                                'affiliate_id' => $affiliateLink->affiliate->id,
                                'order_id' => $order->id,
                                'amount' => $commissionAmount,
                                'status' => 'approved',
                                'note' => '30% commission for referral purchase via link ' . $referralCode . '. Order #' . $order->id
                            ]);
                            
                            \Log::info('Affiliate commission created', [
                                'affiliate_id' => $affiliateLink->affiliate->id,
                                'order_id' => $order->id,
                                'amount' => $commissionAmount,
                            ]);
                        }
                    }
                    
                    // Clear the referral from session
                    session()->forget(['referral_code', 'referral_id']);
                }
                // ===== End affiliate referral processing =====

                // ===== Old User Referral System (kept for backward compatibility) =====
                if (Auth::check()) {
                    $referral = Referral::where('referred_user_id', Auth::id())
                        ->where('status', 'registered')
                        ->latest()
                        ->first();

                    if ($referral) {
                        $referral->update([
                            'status' => 'purchased',
                        ]);

                        if ($referral->affiliate) {
                            AffiliateCommission::create([
                                'affiliate_id' => $referral->affiliate->id,
                                'order_id' => $order->id,
                                'amount' => $order->amount * 0.20,
                                'status' => 'approved',
                                'note' => 'Commission for referral purchase. Order #' . $order->id
                            ]);
                        }

                        if ($referral->affiliate && $referral->affiliate->user) {
                            $referral->affiliate->user->notify(
                                new ReferralCompletedPurchase(Auth::user(), $order)
                            );
                        }
                    }
                }
                // ===== End old user referral system =====

                if (!Auth::check()) {
                    // For guests include the download token in the redirect so they can access status
                    // Use urlencode to ensure special characters are properly encoded
                    $token = urlencode($order->download_token);
                    $url = route('orders.payment.status', ['order' => $order->id]) . '?token=' . $token;
                    return redirect()->to($url)
                        ->with('success', 'Payment request sent to your phone. Please complete the payment.');
                }

                return redirect()->route('orders.payment.status', $order)
                    ->with('success', 'Payment request sent to your phone. Please complete the payment.');
            } else {
                DB::rollback();
                Log::error('STK Push failed', [
                    'order_id' => $order->id,
                    'error' => $response['message']
                ]);
                return back()->with('error', 'Failed to initiate payment: ' . $response['message']);
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'user_id' => Auth::id()
            ]);
            return back()->with('error', 'An error occurred. Please try again. Error: ' . $e->getMessage());
        }
    }

    /**
     * Show payment status page.
     */
    public function paymentStatus(Request $request, Order $order)
    {
        // Refresh to ensure we display the latest order status when rendering
        $order->refresh();

    Log::info('Payment status access attempt', [
            'order_id' => $order->id,
            'order_user_id' => $order->user_id,
            'current_user_id' => Auth::id(),
            'user_authenticated' => Auth::check()
        ]);
        // Allow access if the authenticated user owns the order, or if this is a guest
        // who initiated the order in the current session.
        if (Auth::check()) {
            if ($order->user_id != Auth::id()) {
                Log::warning('Unauthorized payment status access', [
                    'order_id' => $order->id,
                    'order_user_id' => $order->user_id,
                    'current_user_id' => Auth::id()
                ]);

                return redirect()->route('orders.index')
                    ->with('error', 'You can only view your own orders.');
            }
        } else {
            // Guest: check session marker set at order creation
            $guestPhone = session('guest_order_' . $order->id);
            $token = urldecode($request->query('token', ''));
            
            Log::info('Guest payment status access', [
                'order_id' => $order->id,
                'has_session_marker' => !empty($guestPhone),
                'token_provided' => !empty($token),
                'token_matches' => $token === $order->download_token,
                'provided_token_length' => strlen($token),
                'order_token_length' => strlen($order->download_token)
            ]);
            
            // If a matching token is provided, set the session marker for this guest so polling works
            if (!$guestPhone && $token === $order->download_token) {
                session()->put('guest_order_' . $order->id, $order->payment_phone);
                session()->save();
                $guestPhone = session('guest_order_' . $order->id);
                Log::info('Session marker set from token', ['order_id' => $order->id]);
            }

            // Allow access if session marker exists OR a valid download token is provided
            if (!$guestPhone && $token !== $order->download_token) {
                Log::warning('Guest payment status access denied', [
                    'order_id' => $order->id,
                    'reason' => 'No session marker and token mismatch',
                    'provided_token' => $token,
                    'order_token' => $order->download_token
                ]);
                return redirect()->route('login')
                    ->with('error', 'Please log in to view payment status.');
            }
        }

        // If the order is completed, send thank you email with download link (if not already sent)
        // You may want to move this logic into a queue or event in production for better performance.
        if ($order->status === Order::STATUS_COMPLETED && !$order->thank_you_sent) {
            $this->sendThankYouEmail($order);
        }

        return view('orders.payment-status', compact('order'));
    }

    public function show(Order $order)
    {
        // Only allow access if the authenticated user owns the order
        if (auth()->id() !== $order->user_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('orders.show', compact('order'));
    }

    public function download(Order $order)
    {
        // Allow access if the authenticated user owns the order
        if (auth()->check()) {
            if ($order->user_id !== auth()->id()) {
                abort(403, 'Unauthorized.');
            }
        } else {
            abort(403, 'Authentication required. Please log in.');
        }
        
        if ($order->status !== Order::STATUS_COMPLETED) {
            return back()->with('error', 'Order is not completed yet.');
        }

        // Create or get download record with token for this order
        $download = Download::where('order_id', $order->id)
            ->where('user_id', auth()->id())
            ->first();
        
        if (!$download) {
            $download = Download::create([
                'user_id' => auth()->id(),
                'product_id' => $order->product_id,
                'order_id' => $order->id,
                'token' => \Illuminate\Support\Str::random(40),
            ]);
        }

        // Redirect to DownloadController which handles format selection logic
        return redirect()->route('downloads.file', $download->token);
    }

    /**
     * Guest download with token validation.
     */
    public function downloadGuest(Order $order)
    {
        // Guest download requires valid token
        $token = request('token');
        if (!$token || $token !== $order->download_token) {
            abort(403, 'Unauthorized. Invalid or missing download token.');
        }

        if ($order->status !== Order::STATUS_COMPLETED) {
            return back()->with('error', 'Order is not completed yet.');
        }

        // Ensure this is a guest order (no user_id)
        if ($order->user_id !== null) {
            abort(403, 'This is not a guest order.');
        }

        // Create or get download record for this guest order
        $download = Download::where('order_id', $order->id)
            ->where('user_id', null)
            ->first();
        
        if (!$download) {
            $download = Download::create([
                'user_id' => null,
                'product_id' => $order->product_id,
                'order_id' => $order->id,
                'token' => \Illuminate\Support\Str::random(40),
            ]);
        }

        Log::info('Guest download initiated', ['order_id' => $order->id, 'download_token' => $download->token]);

        // Redirect to DownloadController which handles format selection logic
        return redirect()->route('downloads.file', $download->token);
    }

    /**
     * Check payment status via AJAX.
     */
    public function checkPaymentStatus(Order $order)
    {
        // Refresh the order instance so we always read the latest DB state
        // when this endpoint is polled repeatedly from the browser.
        $order->refresh();

        $token = urldecode(request()->query('token', ''));

        // Log the poll for diagnostics
        Log::info('CheckPaymentStatus polled', [
            'order_id' => $order->id,
            'status' => $order->status,
            'user_id' => Auth::id(),
            'is_authenticated' => Auth::check(),
            'has_token' => !empty($token),
            'token_valid' => $token === $order->download_token
        ]);

        // Authorization check
        if (Auth::check()) {
            // Authenticated user: must own the order
            if ($order->user_id != Auth::id()) {
                Log::warning('Unauthorized payment status access (authenticated)', [
                    'order_id' => $order->id,
                    'order_user_id' => $order->user_id,
                    'current_user_id' => Auth::id()
                ]);
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        } else {
            // Guest: must have valid token
            if (empty($token) || $token !== $order->download_token) {
                Log::warning('Unauthorized payment status access (guest)', [
                    'order_id' => $order->id,
                    'has_token' => !empty($token),
                    'token_valid' => $token === $order->download_token
                ]);
                return response()->json(['error' => 'Invalid or missing token'], 401);
            }
        }

        // Return current order status
        Log::info('Payment status check successful', [
            'order_id' => $order->id,
            'status' => $order->status,
            'is_completed' => $order->status === Order::STATUS_COMPLETED
        ]);

        $redirect_url = null;
        if ($order->status === Order::STATUS_COMPLETED) {
            // If authenticated user, redirect to orders show page
            if (Auth::check()) {
                $redirect_url = route('orders.show', $order);
            } else {
                // If guest, redirect to guest download page with token
                $redirect_url = route('orders.download-guest', [
                    'order' => $order->id,
                    'token' => $token
                ]);
            }
        }

        return response()->json([
            'status' => $order->status,
            'is_completed' => $order->status === Order::STATUS_COMPLETED,
            'redirect_url' => $redirect_url
        ]);
    }

    /**
     * Send a thank you email with the download link if not already sent.
     */
    protected function sendThankYouEmail(Order $order)
    {
        $user = $order->user;
        $product = $order->product;
        $token = null;

        // Handle both authenticated and guest users
        if (!$user && !$order->customer_email) {
            Log::info('No user/email associated with order — skipping thank you email', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'payment_phone' => $order->payment_phone
            ]);
            return;
        }

        // Get or create download record for the order
        try {
            $download = $order->downloads()->first();
            if (!$download) {
                $download = Download::create([
                    'user_id' => $user ? $user->id : null,
                    'product_id' => $order->product_id,
                    'order_id' => $order->id,
                    'token' => $order->download_token ?? \Illuminate\Support\Str::random(40),
                    'expires_at' => now()->addDays(7),
                    'download_count' => 0,
                    'max_downloads' => 3,
                ]);
            }
            $token = $download->token;
        } catch (\Exception $e) {
            Log::warning('Failed to create download record', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            $token = $order->download_token ?? \Illuminate\Support\Str::random(40);
        }

        // Generate download URLs for all formats
        $epubDownloadUrl = !empty($product->file_path)
            ? route('downloads.serve-file', [$token, 'epub'])
            : null;
        
        $pdfDownloadUrl = $product->hasPdf()
            ? route('downloads.serve-file', [$token, 'pdf'])
            : null;
        
        $zipDownloadUrl = $product->hasZip()
            ? route('downloads.serve-file', [$token, 'zip'])
            : null;

        // Determine email address
        $emailAddress = $user ? $user->email : $order->customer_email;
        $userObject = $user ?? (object)['name' => 'Valued Customer', 'email' => $order->customer_email, 'id' => null];

        try {
            Mail::to($emailAddress)->send(new ProductDownloadLinkMail(
                $userObject,
                $product,
                $epubDownloadUrl,
                $pdfDownloadUrl,
                $zipDownloadUrl
            ));
            $order->thank_you_sent = true;
            $order->save();
            Log::info('Thank you email sent', [
                'order_id' => $order->id,
                'email' => $emailAddress,
                'is_guest' => $user ? false : true,
                'has_epub' => !empty($epubDownloadUrl),
                'has_pdf' => !empty($pdfDownloadUrl),
                'has_zip' => !empty($zipDownloadUrl)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send thank you email', [
                'order_id' => $order->id,
                'email' => $emailAddress,
                'error' => $e->getMessage()
            ]);
        }
    }
}