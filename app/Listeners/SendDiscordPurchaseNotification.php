<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendDiscordPurchaseNotification
{
    /**
     * Handle the event.
     */
    public function handle(OrderCompleted $event): void
    {
        $webhookUrl = config('discord.purchase_webhook');

        if (empty($webhookUrl)) {
            Log::warning('Discord purchase webhook URL not configured. Set DISCORD_PURCHASE_WEBHOOK in .env');
            return;
        }

        try {
            $order = $event->order->load(['product', 'user']);

            $productName = $order->product?->title ?? $order->product?->name ?? 'Unknown Product';
            $amount = number_format((float) $order->amount, 2);
            $mpesaReceipt = $order->mpesa_receipt_number ?? 'N/A';
            $paymentPhone = $order->payment_phone ?? 'N/A';

            // Determine buyer info
            if ($order->user_id && $order->user) {
                $buyerName = $order->user->name;
                $buyerEmail = $order->user->email;
                $buyerType = 'Registered User';
            } else {
                $buyerName = $order->customer_name ?? 'Guest';
                $buyerEmail = $order->customer_email ?? 'N/A';
                $buyerType = 'Guest';
            }

            // Check if this was an affiliate referral
            $affiliateInfo = 'None';
            $referralCode = session('referral_code');
            if ($referralCode) {
                $affiliateLink = \App\Models\AffiliateLink::where('unique_code', $referralCode)
                    ->with('affiliate.user')
                    ->first();
                if ($affiliateLink && $affiliateLink->affiliate && $affiliateLink->affiliate->user) {
                    $commissionAmount = $order->amount * 0.30;
                    $affiliateInfo = $affiliateLink->affiliate->user->name 
                        . ' (Code: ' . $referralCode . ')'
                        . "\nCommission: KES " . number_format($commissionAmount, 2);
                }
            }

            // --- Sales statistics ---
            $now = Carbon::now();

            $todayCount = Order::where('status', Order::STATUS_COMPLETED)
                ->whereDate('updated_at', $now->toDateString())
                ->count();
            $todayValue = Order::where('status', Order::STATUS_COMPLETED)
                ->whereDate('updated_at', $now->toDateString())
                ->sum('amount');

            $weekCount = Order::where('status', Order::STATUS_COMPLETED)
                ->whereBetween('updated_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])
                ->count();
            $weekValue = Order::where('status', Order::STATUS_COMPLETED)
                ->whereBetween('updated_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])
                ->sum('amount');

            $monthCount = Order::where('status', Order::STATUS_COMPLETED)
                ->whereMonth('updated_at', $now->month)
                ->whereYear('updated_at', $now->year)
                ->count();
            $monthValue = Order::where('status', Order::STATUS_COMPLETED)
                ->whereMonth('updated_at', $now->month)
                ->whereYear('updated_at', $now->year)
                ->sum('amount');

            $embed = [
                'title' => '💰 New Purchase Completed!',
                'color' => 0x28A745, // Green
                'fields' => [
                    [
                        'name' => '📦 Product',
                        'value' => $productName,
                        'inline' => true,
                    ],
                    [
                        'name' => '💵 Amount',
                        'value' => "KES {$amount}",
                        'inline' => true,
                    ],
                    [
                        'name' => '🧾 M-Pesa Receipt',
                        'value' => "`{$mpesaReceipt}`",
                        'inline' => true,
                    ],
                    [
                        'name' => '👤 Buyer',
                        'value' => "{$buyerName}\n{$buyerEmail}",
                        'inline' => true,
                    ],
                    [
                        'name' => '📱 Phone',
                        'value' => $paymentPhone,
                        'inline' => true,
                    ],
                    [
                        'name' => '🏷️ Buyer Type',
                        'value' => $buyerType,
                        'inline' => true,
                    ],
                    [
                        'name' => '🤝 Affiliate',
                        'value' => $affiliateInfo,
                        'inline' => false,
                    ],
                    [
                        'name' => '📅 Today',
                        'value' => "{$todayCount} sales\nKES " . number_format($todayValue, 2),
                        'inline' => true,
                    ],
                    [
                        'name' => '📆 This Week',
                        'value' => "{$weekCount} sales\nKES " . number_format($weekValue, 2),
                        'inline' => true,
                    ],
                    [
                        'name' => '🗓️ This Month',
                        'value' => "{$monthCount} sales\nKES " . number_format($monthValue, 2),
                        'inline' => true,
                    ],
                ],
                'footer' => [
                    'text' => 'Relearn Purchase System • Order #' . $order->id,
                ],
                'timestamp' => now()->toIso8601String(),
            ];

            Http::post($webhookUrl, [
                'embeds' => [$embed],
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to send Discord purchase notification', [
                'error' => $e->getMessage(),
                'order_id' => $event->order->id ?? null,
            ]);
        }
    }
}
