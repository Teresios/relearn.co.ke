<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Affiliate;
use App\Models\AffiliateApplication;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use App\Notifications\AffiliateWithdrawOtpNotification;
use App\Models\AffiliateWithdrawal;

class AffiliateController extends Controller
{
    /**
     * Show the affiliate dashboard.
     */
    public function dashboard()
    {
        $user = auth()->user();
        $affiliate = Affiliate::with(['links.referrals.user', 'commissions', 'payouts', 'withdrawals'])
            ->where('user_id', $user->id)
            ->first();

        $application = AffiliateApplication::where('user_id', $user->id)->latest()->first();

        if (!$affiliate) {
            return redirect('/')->with('error', 'You are not registered as an affiliate.');
        }

        $stats = [
            'total_earned' => $affiliate->total_earned ?? 0,
            'total_paid' => $affiliate->total_paid ?? 0,
            'total_clicks' => $affiliate->total_clicks ?? 0,
            'total_referrals' => $affiliate->total_referrals ?? 0,
            'total_conversions' => $affiliate->total_conversions ?? 0,
            'available_earnings' => $affiliate->getAvailableEarnings() ?? 0,
        ];

        $links = $affiliate->links()->with('product', 'referrals.user')->get();
        $commissions = $affiliate->commissions()->latest()->limit(10)->get();
        $recent_referrals = $affiliate->links->flatMap->referrals->sortByDesc('created_at')->take(10);
        $payouts = $affiliate->payouts()->latest()->limit(10)->get();
        $withdrawals = $affiliate->withdrawals()->latest()->limit(10)->get();

        $leaderboard = Affiliate::with('user')
            ->get()
            ->map(function($a) {
                return [
                    'user_id' => $a->user_id,
                    'name' => $a->user->name,
                    'total' => $a->commissions()->whereIn('status', ['approved', 'paid'])->sum('amount'),
                    'referrals' => $a->links->flatMap->referrals->whereNotNull('user_id')->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();

        $products = Product::all();

        return view('affiliate.dashboard', compact(
            'affiliate', 'links', 'commissions', 'recent_referrals', 'payouts', 'withdrawals', 'leaderboard', 'stats', 'application', 'products'
        ));
    }

    /**
     * Create a new affiliate link.
     */
    public function createLink(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $affiliate = Affiliate::where('user_id', auth()->id())->firstOrFail();

        if ($affiliate->links()->where('product_id', $request->product_id)->exists()) {
            return redirect()->back()->with('error', 'You have already created a link for this product.');
        }

        $code = Str::random(8);

        $affiliate->links()->create([
            'product_id' => $request->product_id,
            'unique_code' => $code,
        ]);

        return redirect()->back()->with('success', 'Link created!');
    }

    /**
     * Delete an affiliate link.
     */
    public function deleteLink($id)
    {
        $affiliate = Affiliate::where('user_id', auth()->id())->firstOrFail();
        $link = $affiliate->links()->where('id', $id)->firstOrFail();
        $link->delete();

        return redirect()->back()->with('success', 'Affiliate link deleted successfully.');
    }

    /**
     * (Deprecated) Handle payout request from affiliate dashboard.
     */
    public function requestPayout(Request $request)
    {
        return back()->with('error', 'This payout method is deprecated. Please use the withdrawal request flow.');
    }

    /**
     * Send OTP code for affiliate withdrawal (via email).
     */
    public function sendWithdrawOtp(Request $request)
    {
        $user = auth()->user();
        $otpKey = 'withdraw-otp:' . $user->id;
        $rateKey = 'withdraw-otp-attempts:' . $user->id;
        $otpTtl = 370; // 6 minutes + 10 seconds grace

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            \Log::warning("OTP rate limit hit for user {$user->id} at " . now());
            return response()->json(['message' => 'Too many OTP requests. Please wait before trying again.'], 429);
        }
        RateLimiter::hit($rateKey, 3600);

        $otp = random_int(100000, 999999);

        if (!Cache::has($otpKey)) {
            Cache::put($otpKey, $otp, $otpTtl);
            \Log::info("Setting OTP for user {$user->id} with value $otp and expiry {$otpTtl}s at " . now());
            $user->notify(new AffiliateWithdrawOtpNotification($otp));
            return response()->json(['message' => 'OTP sent to your email.']);
        } else {
            \Log::info("OTP send attempted for user {$user->id} but OTP still valid at " . now());
            return response()->json(['message' => 'An OTP was already sent. Please check your email or try again later.'], 429);
        }
    }

    /**
     * Verify the OTP code for affiliate withdrawal.
     */
    public function verifyWithdrawOtp(Request $request)
    {
        $user = auth()->user();
        $otpKey = 'withdraw-otp:' . $user->id;

        $cachedOtp = Cache::get($otpKey);
        \Log::info("Verifying OTP for user {$user->id} at " . now() . ". Value in cache: " . ($cachedOtp ?: 'none') . ", user input: " . $request->otp);

        if (!$cachedOtp) {
            return response()->json(['success' => false, 'message' => 'OTP expired. Please request a new one.'], 400);
        }
        if ($request->otp != $cachedOtp) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP. Please check and try again.'], 401);
        }
        Cache::forget($otpKey);
        \Log::info("OTP for user {$user->id} verified and forgotten at " . now());
        return response()->json(['success' => true]);
    }

    /**
     * Show the withdrawal request form (auto-fills user's MPESA number).
     */
    public function showWithdrawForm()
    {
        $user = auth()->user();
        $affiliate = $user->affiliate;

        if (!$affiliate) {
            return redirect()->route('affiliate.dashboard')->with('error', 'You are not registered as an affiliate.');
        }

        $mpesa_number = $user->phone;

        return view('affiliate.withdraw', [
            'affiliate' => $affiliate,
            'mpesa_number' => $mpesa_number,
            'available_earnings' => $affiliate->getAvailableEarnings(),
        ]);
    }

    /**
     * Handle the affiliate withdrawal request after OTP verification.
     * MPESA number is auto-filled from registration.
     */
    public function withdrawRequest(Request $request)
    {
        $user = auth()->user();
        $affiliate = $user->affiliate;
        $otpKey = 'withdraw-otp:' . $user->id;
        $otp = Cache::get($otpKey);

        if ($otp) {
            \Log::info("Withdrawal attempt blocked for user {$user->id} at " . now() . " due to unverified OTP.");
            return back()->with('error', 'Please verify the OTP before submitting the withdrawal request.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:100|max:' . $affiliate->getAvailableEarnings(),
        ]);

        // Always use the phone number from registration for MPESA
        $mpesa_number = $user->phone;

        $withdrawal = AffiliateWithdrawal::create([
            'affiliate_id' => $affiliate->id,
            'amount' => $request->amount,
            'mpesa_number' => $mpesa_number,
            'status' => 'pending',
        ]);
        // Optionally: Notify admin or trigger payout queue as needed...
        \Log::info("Withdrawal request created for user {$user->id} at " . now() . " for amount {$request->amount}");
        return redirect()->route('affiliate.dashboard')->with('success', 'Withdrawal request submitted!');
    }

    /**
     * Show the 'How it Works' page for affiliates.
     */
    public function howItWorks()
    {
        return view('affiliate.how-it-works');
    }
}
