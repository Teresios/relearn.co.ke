<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AffiliateApplication;
use App\Models\Affiliate;
use App\Models\Referral;
use App\Models\Commission;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Notifications\AffiliateApplicationReviewed;
use App\Mail\AffiliateApprovedMail;
use Illuminate\Support\Facades\Mail;

class AdminAffiliateApplicationController extends Controller
{
    /**
     * Show only pending affiliate applications and show affiliate stats for the dashboard.
     */
    public function index(Request $request)
    {
        // Only pending applications, no filtering or searching
        $pendingApplications = AffiliateApplication::with('user')
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->paginate(20);

        // Get all affiliates for stats table
        $affiliates = Affiliate::with(['user', 'referrals.order.product', 'commissions'])->get();

        // Get all approved applications where credentials have been set (account created)
        $credentialsSetApplications = AffiliateApplication::with('user')
            ->where('status', 'approved')
            ->where('credentials_set', true) // Only those who have created their account
            ->orderByDesc('created_at')
            ->get();

        // Build a map of user_id => Affiliate with relationships for fast lookup
        $affiliatesByUserId = Affiliate::with(['user', 'referrals.order.product', 'commissions'])
            ->whereIn('user_id', $credentialsSetApplications->pluck('user_id')->filter()->unique())
            ->get()
            ->keyBy('user_id');

        // Merge affiliates with approved applications that have completed setup for display
        $affiliateStats = collect();
        $addedAffiliateIds = collect();
        
        // Add existing affiliates
        foreach ($affiliates as $affiliate) {
            $addedAffiliateIds->push($affiliate->id);
            
            // Try to get phone from user, or from application_data if available
            $phone = $affiliate->user->phone ?? '-';
            
            // If phone is empty, check if there's an approved application with payment_details
            if ($phone === '-' && $affiliate->user_id) {
                $application = AffiliateApplication::where('user_id', $affiliate->user_id)
                    ->where('status', 'approved')
                    ->first();
                if ($application) {
                    $appData = json_decode($application->application_data, true);
                    if (isset($appData['payment_details']) && !empty($appData['payment_details'])) {
                        $phone = substr($appData['payment_details'], 3); // Remove country code
                        // Add 254 prefix if not already present
                        if (!str_starts_with($phone, '254') && !str_starts_with($phone, '+')) {
                            $phone = '254' . $phone;
                        }
                    }
                }
            }
            
            // Get purchase details for this affiliate
            $purchases = $affiliate->referrals->whereNotNull('purchased_at')->map(function ($referral) {
                return [
                    'product_name' => $referral->order?->product?->name ?? 'Unknown Product',
                    'amount' => $referral->order?->amount ?? 0,
                    'purchased_at' => $referral->purchased_at ? $referral->purchased_at->format('Y-m-d H:i') : '-',
                ];
            })->values();
            
            $affiliateStats->push([
                'type' => 'affiliate',
                'id' => 'affiliate_' . $affiliate->id,
                'affiliate_id' => $affiliate->id,
                'name' => $affiliate->user->name ?? '-',
                'email' => $affiliate->user->email ?? '-',
                'phone' => $phone,
                'code' => $affiliate->code,
                'links' => $affiliate->links->count(),
                'referrals' => $affiliate->referrals->count(),
                'purchases' => $affiliate->referrals->whereNotNull('purchased_at')->count(),
                'purchases_data' => $purchases,
                'earnings' => $affiliate->commissions->where('payment_status', 'pending')->where('status', 'approved')->sum('amount'),
                'payment_status' => $this->getAffiliatePaymentStatus($affiliate),
                'status' => $affiliate->active ? 'Active' : 'Inactive',
                'joined' => $affiliate->created_at?->format('Y-m-d') ?? '-',
            ]);
        }
        
        // Add guest applications that have completed setup (credentials_set = true)
        // BUT skip if they're already in $affiliates (don't duplicate)
        foreach ($credentialsSetApplications as $application) {
            $appData = json_decode($application->application_data, true);
            
            // Get the affiliate record that was created for this user
            $affiliate = $affiliatesByUserId->get($application->user_id);
            
            // Skip if this affiliate was already added in the first loop
            if ($affiliate && $addedAffiliateIds->contains($affiliate->id)) {
                continue;
            }
            
            // Extract phone from multiple sources - prefer user phone field, then payment_phone, then application data
            $phone = '-';
            if ($affiliate && $affiliate->user) {
                // Try phone field first
                if (!empty($affiliate->user->phone)) {
                    $phone = $affiliate->user->phone;
                }
                // Then try payment_phone field (M-Pesa number)
                elseif (!empty($affiliate->user->payment_phone)) {
                    $phone = $affiliate->user->payment_phone;
                }
                // Then try mpesa_number field
                elseif (!empty($affiliate->user->mpesa_number)) {
                    $phone = $affiliate->user->mpesa_number;
                }
            }
            // Fallback to application data
            if ($phone === '-') {
                if (isset($appData['phone']) && !empty($appData['phone'])) {
                    $phone = $appData['phone'];
                } elseif (isset($appData['contact_phone']) && !empty($appData['contact_phone'])) {
                    $phone = $appData['contact_phone'];
                } elseif (isset($appData['mpesa_number']) && !empty($appData['mpesa_number'])) {
                    $phone = $appData['mpesa_number'];
                } elseif (isset($appData['payment_phone']) && !empty($appData['payment_phone'])) {
                    $phone = $appData['payment_phone'];
                } elseif (isset($appData['payment_details']) && !empty($appData['payment_details'])) {
                    $phone = substr($appData['payment_details'], 3);
                    // Add 254 prefix if not already present
                    if (!str_starts_with($phone, '254') && !str_starts_with($phone, '+')) {
                        $phone = '254' . $phone;
                    }
                }
            }
            
            // Use real stats from Affiliate record if it exists, otherwise 0
            $links = $affiliate ? $affiliate->links->count() : 0;
            $referrals = $affiliate ? $affiliate->referrals->count() : 0;
            $purchases = $affiliate ? $affiliate->referrals->whereNotNull('purchased_at')->count() : 0;
            $earnings = $affiliate ? $affiliate->commissions->where('payment_status', 'pending')->where('status', 'approved')->sum('amount') : 0;
            $code = $affiliate ? $affiliate->code : '-';
            
            // Get purchase details
            $purchasesData = [];
            if ($affiliate) {
                $purchasesData = $affiliate->referrals->whereNotNull('purchased_at')->map(function ($referral) {
                    return [
                        'product_name' => $referral->order?->product?->name ?? 'Unknown Product',
                        'amount' => $referral->order?->amount ?? 0,
                        'purchased_at' => $referral->purchased_at ? $referral->purchased_at->format('Y-m-d H:i') : '-',
                    ];
                })->values();
            }
            
            $affiliateStats->push([
                'type' => 'application',
                'id' => 'application_' . $application->id,
                'name' => $appData['full_name'] ?? '-',
                'email' => $appData['email'] ?? '-',
                'phone' => $phone,
                'code' => $code,
                'links' => $links,
                'referrals' => $referrals,
                'purchases' => $purchases,
                'purchases_data' => $purchasesData,
                'earnings' => $earnings,
                'status' => 'Active',
                'joined' => $application->created_at?->format('Y-m-d') ?? '-',
            ]);
        }

        // Sort affiliate stats by earnings (highest first) - or by request parameter
        $sortBy = request()->get('sort_by', 'earnings');
        $sortOrder = request()->get('sort_order', 'desc');
        
        // Apply sorting based on parameter
        if ($sortBy === 'joined') {
            // Special handling for 'joined' - need to sort by actual timestamp, not formatted string
            // Extract and store the timestamp in a temporary field for sorting
            $affiliateStats = $affiliateStats->map(function ($affiliate) {
                // Store the original created_at timestamp for sorting
                $affiliate['_sort_joined'] = strtotime($affiliate['joined']);
                return $affiliate;
            });
            
            if ($sortOrder === 'asc') {
                $affiliateStats = $affiliateStats->sortBy('_sort_joined');
            } else {
                $affiliateStats = $affiliateStats->sortByDesc('_sort_joined');
            }
            
            // Remove the temporary sort field
            $affiliateStats = $affiliateStats->map(function ($affiliate) {
                unset($affiliate['_sort_joined']);
                return $affiliate;
            });
        } else {
            // Regular sorting for other fields
            if ($sortOrder === 'asc') {
                $affiliateStats = $affiliateStats->sortBy($sortBy);
            } else {
                $affiliateStats = $affiliateStats->sortByDesc($sortBy);
            }
        }
        
        // Apply filters if provided
        $statusFilter = request()->get('status_filter');
        if ($statusFilter && $statusFilter !== 'all') {
            $affiliateStats = $affiliateStats->filter(function ($affiliate) use ($statusFilter) {
                return $affiliate['status'] === $statusFilter;
            });
        }
        
        // Search filter by name or email
        $searchFilter = request()->get('search_filter');
        if ($searchFilter) {
            $affiliateStats = $affiliateStats->filter(function ($affiliate) use ($searchFilter) {
                $search = strtolower($searchFilter);
                return strpos(strtolower($affiliate['name']), $search) !== false || 
                       strpos(strtolower($affiliate['email']), $search) !== false ||
                       strpos(strtolower($affiliate['phone']), $search) !== false;
            });
        }

        // Paginate the collection - 20 per page
        $page = request()->get('page', 1);
        $perPage = 20;
        $total = $affiliateStats->count();
        $affiliateStats = new LengthAwarePaginator(
            $affiliateStats->forPage($page, $perPage)->values(),
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        // Stats calculations for cards - use the actual affiliateStats total count for consistency
        $stats = [
            'total_affiliates'      => $affiliateStats->total(),
            'pending_applications'  => AffiliateApplication::where('status', 'pending')->count(),
            'total_referrals'       => \App\Models\AffiliateReferral::count(),
            'total_purchases'       => \App\Models\AffiliateReferral::whereNotNull('purchased_at')->count(),
            'available_earnings'    => \App\Models\AffiliateCommission::where('status', 'approved')->where('payment_status', 'pending')->sum('amount'),
            'total_earnings'        => \App\Models\AffiliateCommission::whereIn('status', ['approved', 'paid'])->sum('amount'),
        ];

        // Calculate this week's payout (Wednesday to Wednesday)
        $today = now();
        $dayOfWeek = $today->dayOfWeek; // 0 = Sunday, 3 = Wednesday
        
        // Calculate the start of the current week (last Wednesday)
        if ($dayOfWeek >= 3) {
            // Wednesday or later in the week
            $weekStart = $today->copy()->startOfDay()->subDays($dayOfWeek - 3);
        } else {
            // Sunday to Tuesday - go back to last Wednesday
            $weekStart = $today->copy()->startOfDay()->subDays(7 - (3 - $dayOfWeek));
        }
        $weekEnd = $weekStart->copy()->addDays(7);

        // Get all affiliates with sales this week
        $weeklyPayouts = \App\Models\AffiliateCommission::with(['affiliate.user'])
            ->whereIn('status', ['approved', 'paid'])
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->get()
            ->groupBy('affiliate_id')
            ->map(function ($commissions) {
                $totalAmount = $commissions->sum('amount');
                $affiliate = $commissions->first()->affiliate;
                $phone = $affiliate->user->phone ?? '-';
                
                // Try to get phone from application if not available on user
                if ($phone === '-' && $affiliate->user_id) {
                    $application = AffiliateApplication::where('user_id', $affiliate->user_id)
                        ->where('status', 'approved')
                        ->first();
                    if ($application) {
                        $appData = json_decode($application->application_data, true);
                        if (isset($appData['payment_details']) && !empty($appData['payment_details'])) {
                            $phone = $appData['payment_details'];
                        }
                    }
                }
                
                return [
                    'affiliate_id' => $affiliate->id,
                    'affiliate_name' => $affiliate->user->name ?? '-',
                    'email' => $affiliate->user->email ?? '-',
                    'phone' => $phone,
                    'amount' => $totalAmount,
                    'commission_count' => $commissions->count(),
                ];
            })
            ->sortByDesc('amount')
            ->values();

        $weeklyPayoutTotal = $weeklyPayouts->sum('amount');
        $stats['weekly_payout_total'] = $weeklyPayoutTotal;
        $stats['weekly_payout_count'] = $weeklyPayouts->count();

        // Calculate last week's payouts (previous 7 days before current week started)
        $lastWeekStart = $weekStart->copy()->subDays(7);
        $lastWeekEnd = $weekStart->copy();

        $lastWeekPayouts = \App\Models\AffiliateCommission::with(['affiliate.user'])
            ->whereIn('status', ['approved', 'paid'])
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->get()
            ->groupBy('affiliate_id')
            ->map(function ($commissions) {
                $totalAmount = $commissions->sum('amount');
                $affiliate = $commissions->first()->affiliate;
                
                return [
                    'affiliate_id' => $affiliate->id,
                    'affiliate_name' => $affiliate->user->name ?? '-',
                    'amount' => $totalAmount,
                    'commission_count' => $commissions->count(),
                ];
            })
            ->sortByDesc('amount')
            ->values();

        $lastWeekPayoutTotal = $lastWeekPayouts->sum('amount');
        $stats['last_week_payout_total'] = $lastWeekPayoutTotal;
        $stats['last_week_payout_count'] = $lastWeekPayouts->count();

        // ---- REFERRAL STATISTICS SECTION ----
        $topLinks = Referral::select('link', DB::raw('count(*) as total'))
            ->groupBy('link')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topProducts = Referral::select('product_name', DB::raw('count(*) as total'))
            ->whereNotNull('product_name')
            ->where('status', 'purchased')
            ->groupBy('product_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topAffiliates = Referral::select('affiliate_id', DB::raw('count(*) as conversions'))
            ->where('status', 'purchased')
            ->groupBy('affiliate_id')
            ->orderByDesc('conversions')
            ->with(['affiliate.user'])
            ->limit(5)
            ->get();

        $totalReferrals = Referral::count();
        $totalConversions = Referral::where('status', 'purchased')->count();

        $conversionRate = $totalReferrals > 0
            ? round(($totalConversions / $totalReferrals) * 100, 2)
            : 0;

        $firstReferral = Referral::orderBy('created_at')->first();
        $latestConversion = Referral::where('status', 'purchased')->orderByDesc('created_at')->first();

        $referralStats = [
            'top_links'         => $topLinks,
            'top_products'      => $topProducts,
            'top_affiliates'    => $topAffiliates,
            'total_conversions' => $totalConversions,
            'conversion_rate'   => $conversionRate,
            'first_referral'    => $firstReferral,
            'latest_conversion' => $latestConversion,
        ];

        return view('admin.affiliate.applications.index', compact(
            'pendingApplications',
            'affiliates',
            'affiliateStats',
            'stats',
            'referralStats',
            'weeklyPayouts',
            'lastWeekPayouts',
            'weekStart',
            'weekEnd',
            'lastWeekStart',
            'lastWeekEnd'
        ));
    }

    /**
     * Show an individual affiliate application.
     */
    public function show(AffiliateApplication $application)
    {
        return view('admin.affiliate.applications.show', compact('application'));
    }

    /**
     * Approve an affiliate application and create the affiliate record if needed.
     * Also sends a congratulations email to the applicant.
     */
    public function approve(Request $request, AffiliateApplication $application)
    {
        DB::transaction(function () use ($application, $request) {
            $application->update([
                'status' => 'approved',
                'admin_feedback' => $request->input('admin_feedback'),
            ]);
            
            // Only create Affiliate record if this is an authenticated user's application
            // Guest applications (user_id = null) will have their Affiliate created in setupStore()
            if ($application->user_id) {
                Affiliate::firstOrCreate(
                    ['user_id' => $application->user_id],
                    [
                        'approved_at' => now(),
                        'active' => true,
                    ]
                );
                $application->user->update(['is_affiliate' => true]);
                // Assign the affiliate role using Spatie Permission
                $application->user->assignRole('affiliate');
                // Clear the role cache so it's immediately reflected
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
                $application->user->notify(new AffiliateApplicationReviewed($application));

                // Send congratulations email
                Mail::to($application->user->email)->send(new AffiliateApprovedMail($application->user));
            }
        });

        return redirect()->route('admin.affiliate.applications.index')
            ->with('success', 'Application approved' . ($application->user_id ? ', user notified, and congratulations email sent.' : ' and email sent to applicant.'));
    }

    /**
     * Reject an affiliate application.
     */
    public function reject(Request $request, AffiliateApplication $application)
    {
        $request->validate([
            'admin_feedback' => 'required|string|max:1000',
        ]);
        $application->update([
            'status' => 'rejected',
            'admin_feedback' => $request->input('admin_feedback'),
        ]);
        $application->user->notify(new AffiliateApplicationReviewed($application));

        return redirect()->route('admin.affiliate.applications.index')
            ->with('success', 'Application rejected and user notified.');
    }

    /**
     * Export affiliate stats (code, referrals, purchases) as CSV.
     */
    public function exportCsv()
    {
        $affiliates = Affiliate::with(['user', 'referrals'])->get();

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=affiliates.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = ['Affiliate Name', 'Email', 'Code', 'Total Referrals', 'Total Purchases'];

        $callback = function () use ($affiliates, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($affiliates as $affiliate) {
                fputcsv($file, [
                    $affiliate->user->name ?? '',
                    $affiliate->user->email ?? '',
                    $affiliate->code,
                    $affiliate->referrals->count(),
                    $affiliate->referrals->where('status', 'purchased')->count(),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get payment status for an affiliate based on pending commissions
     */
    private function getAffiliatePaymentStatus($affiliate)
    {
        // Check if there are any pending (unpaid) commissions
        $hasPendingCommissions = $affiliate->commissions()
            ->where('status', 'approved')
            ->where('payment_status', 'pending')
            ->exists();

        if ($hasPendingCommissions) {
            return 'pending';
        }

        return 'paid';
    }

    /**
     * Send weekly stats emails to all active affiliates with personalized messages
     * If commission is zero, include an encouraging message
     */
    public function sendWeeklyStatsEmails(Request $request)
    {
        try {
            // Calculate this week's date range (Wednesday to Wednesday)
            $today = now();
            $dayOfWeek = $today->dayOfWeek; // 0 = Sunday, 3 = Wednesday
            
            if ($dayOfWeek >= 3) {
                $weekStart = $today->copy()->startOfDay()->subDays($dayOfWeek - 3);
            } else {
                $weekStart = $today->copy()->startOfDay()->subDays(7 - (3 - $dayOfWeek));
            }
            $weekEnd = $weekStart->copy()->addDays(7);

            // Get all active affiliates
            $affiliates = Affiliate::where('active', true)
                ->with(['user', 'links', 'referrals.order.product', 'commissions'])
                ->get();

            $successCount = 0;
            $failedCount = 0;
            $failedEmails = [];

            foreach ($affiliates as $affiliate) {
                try {
                    // Calculate stats for this affiliate this week
                    $weeklyCommissions = $affiliate->commissions()
                        ->whereIn('status', ['approved', 'paid'])
                        ->whereBetween('affiliate_commissions.created_at', [$weekStart, $weekEnd])
                        ->get();

                    $commissionEarned = $weeklyCommissions->sum('amount');

                    // Get this week's referrals with relationships loaded
                    $weeklyReferrals = $affiliate->referrals()
                        ->with(['order.product'])
                        ->whereBetween('affiliate_referrals.created_at', [$weekStart, $weekEnd])
                        ->get();

                    $weeklyConversions = $weeklyReferrals->whereNotNull('purchased_at')->count();
                    $weeklyClicks = $weeklyReferrals->count();

                    // Get product details for this week's sales
                    $productsSold = $weeklyReferrals
                        ->whereNotNull('purchased_at')
                        ->groupBy(function($item) {
                            return $item->order?->product?->name ?? $item->product_name ?? 'Unknown Product';
                        })
                        ->map(function ($items) {
                            $firstItem = $items->first();
                            $productName = $firstItem->order?->product?->name ?? $firstItem->product_name ?? 'Unknown Product';
                            // Use the sale price (order amount) not the actual product price
                            $productPrice = $firstItem->order?->amount ?? $firstItem->order?->product?->price ?? 0;
                            
                            return [
                                'name' => $productName,
                                'count' => $items->count(),
                                'price' => $productPrice,
                            ];
                        })
                        ->values();

                    // Calculate total conversion value
                    $totalConversionValue = $weeklyReferrals
                        ->whereNotNull('purchased_at')
                        ->sum(function ($referral) {
                            return $referral->order?->amount ?? 0;
                        });

                    // Get total unique products sold
                    $totalProductsSold = $productsSold->count();

                    // Calculate conversion rate
                    $conversionRate = $weeklyClicks > 0 
                        ? round(($weeklyConversions / $weeklyClicks) * 100, 2)
                        : 0;

                    // Prepare payment data
                    $paymentData = [
                        'affiliate_code' => $affiliate->code,
                        'week_start' => $weekStart->format('Y-m-d'),
                        'week_end' => $weekEnd->format('Y-m-d'),
                        'links_generated' => $affiliate->links()->count(),
                        'total_sales' => $weeklyConversions,
                        'products_sold' => $totalProductsSold,
                        'products_detail' => $productsSold->toArray(),
                        'total_conversion_value' => $totalConversionValue,
                        'commission_earned' => round($commissionEarned, 2),
                        'conversion_rate' => $conversionRate,
                        'active_links' => $affiliate->links()->count(),
                        'total_referrals' => $affiliate->referrals()->count(),
                        'total_clicks_this_week' => $weeklyClicks,
                        'payment_status' => $commissionEarned > 0 ? 'Eligible' : 'No Sales',
                        'is_zero_commission' => $commissionEarned <= 0,
                    ];

                    // Send the email
                    Mail::send(
                        new \App\Mail\AffiliateWeeklyPaymentMail($affiliate, $paymentData)
                    );

                    // Mark all pending commissions as paid (all available earnings for the affiliate)
                    // This resets the "Available Earnings" to 0 on their dashboard
                    $affiliate->commissions()
                        ->where('status', 'approved')
                        ->where('payment_status', 'pending')
                        ->update(['payment_status' => 'paid']);

                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $failedEmails[] = [
                        'email' => $affiliate->user->email ?? 'Unknown',
                        'name' => $affiliate->user->name ?? 'Unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // Prepare response message
            $message = "✅ Weekly stats emails sent successfully!\n";
            $message .= "✉️ Successful: {$successCount} affiliates\n";

            if ($failedCount > 0) {
                $message .= "⚠️ Failed: {$failedCount} affiliates\n\n";
                $message .= "Failed emails:\n";
                foreach ($failedEmails as $failed) {
                    $message .= "- {$failed['name']} ({$failed['email']}): {$failed['error']}\n";
                }
            }

            return redirect()->route('admin.affiliate.applications.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.affiliate.applications.index')
                ->with('error', '❌ Error sending weekly stats emails: ' . $e->getMessage());
        }
    }
}