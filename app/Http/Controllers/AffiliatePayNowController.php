<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Mail\AffiliateWeeklyPaymentMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class AffiliatePayNowController extends Controller
{
    /**
     * Process the "Pay Now" button click - mark commissions as paid and send email
     */
    public function processPayment(Request $request, Affiliate $affiliate)
    {
        // Verify user is authorized (admin only)
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super_admin')) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            // Get all pending commissions for this affiliate
            $pendingCommissions = $affiliate->commissions()
                ->where('status', 'approved')
                ->where('payment_status', 'pending')
                ->get();

            if ($pendingCommissions->isEmpty()) {
                DB::rollBack();
                return redirect()->back()->with('warning', 'No pending commissions found for this affiliate.');
            }

            // Calculate total earnings
            $totalEarnings = $pendingCommissions->sum('amount');

            // Get affiliate's statistics for email
            $paymentData = $this->buildPaymentData($affiliate, $pendingCommissions);

            // Update all pending commissions to paid status
            foreach ($pendingCommissions as $commission) {
                $commission->update([
                    'payment_status' => 'paid',
                    'payment_processed_at' => now(),
                    'status' => 'paid', // Also update main status to paid
                    'paid_at' => now(),
                ]);
            }

            // Send email notification to affiliate
            try {
                $affiliateEmail = $affiliate->user->email;
                $mailable = new AffiliateWeeklyPaymentMail($affiliate, $paymentData);
                Mail::send($mailable);
                \Log::info('Affiliate payment email sent successfully', [
                    'affiliate_id' => $affiliate->id,
                    'affiliate_name' => $affiliate->user->name,
                    'recipient_email' => $affiliateEmail,
                    'amount' => $totalEarnings,
                    'mail_mailer' => config('mail.mailer'),
                ]);
            } catch (\Exception $mailException) {
                \Log::error('Failed to send affiliate payment email', [
                    'affiliate_id' => $affiliate->id,
                    'affiliate_name' => $affiliate->user->name,
                    'recipient_email' => $affiliate->user->email,
                    'error' => $mailException->getMessage(),
                    'mail_mailer' => config('mail.mailer'),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 
                "Payment of Ksh " . number_format($totalEarnings, 2) . " processed successfully for {$affiliate->user->name}. Email notification sent."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error processing affiliate payment', [
                'affiliate_id' => $affiliate->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    /**
     * Build comprehensive payment data for email
     */
    private function buildPaymentData(Affiliate $affiliate, $commissions): array
    {
        $totalEarnings = $commissions->sum('amount');
        
        // Get linked referrals with their order details
        $referrals = $affiliate->referrals()
            ->whereNotNull('purchased_at')
            ->with('link.product', 'order')
            ->get();

        // Group products and count
        $productsDetail = [];
        $totalSales = 0;
        $totalConversionValue = 0;

        foreach ($referrals as $referral) {
            if ($referral->order) {
                $product = $referral->link->product ?? null;
                $productName = $product->title ?? 'Unknown Product';
                $productPrice = $product->price ?? $referral->order->amount;

                if (!isset($productsDetail[$productName])) {
                    $productsDetail[$productName] = [
                        'name' => $productName,
                        'count' => 0,
                        'price' => $productPrice,
                    ];
                }

                $productsDetail[$productName]['count']++;
                $totalSales++;
                $totalConversionValue += $referral->order->amount;
            }
        }

        // Calculate conversion rate
        $totalReferrals = $affiliate->referrals()->count();
        $conversionRate = $totalReferrals > 0 ? round(($totalSales / $totalReferrals) * 100, 2) : 0;

        return [
            'affiliate_code' => $affiliate->code,
            'links_generated' => $affiliate->links()->count(),
            'total_sales' => $totalSales,
            'products_sold' => count($productsDetail),
            'products_detail' => array_values($productsDetail),
            'total_conversion_value' => $totalConversionValue,
            'commission_earned' => $totalEarnings,
            'conversion_rate' => $conversionRate,
            'active_links' => $affiliate->links()->count(),
            'total_referrals' => $totalReferrals,
            'payment_status' => 'Paid',
        ];
    }

    /**
     * Get available earnings for an affiliate (for modal display)
     */
    public function getAvailableEarnings(Request $request, Affiliate $affiliate)
    {
        $pendingCommissions = $affiliate->commissions()
            ->where('status', 'approved')
            ->where('payment_status', 'pending')
            ->get();

        $totalEarnings = $pendingCommissions->sum('amount');

        return response()->json([
            'affiliate_name' => $affiliate->user->name,
            'affiliate_code' => $affiliate->code,
            'available_earnings' => $totalEarnings,
            'commission_count' => $pendingCommissions->count(),
            'formatted_amount' => 'Ksh ' . number_format($totalEarnings, 2),
        ]);
    }
}
