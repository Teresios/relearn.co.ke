<?php

namespace App\Listeners;

use App\Events\AffiliateLinkClicked;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendDiscordAffiliateLinkNotification
{
    /**
     * Handle the event.
     */
    public function handle(AffiliateLinkClicked $event): void
    {
        // Deduplicate: skip if same link + IP was notified in the last 30 seconds
        $dedupeKey = 'discord-affiliate-click:' . $event->link->id . ':' . ($event->visitorIp ?? 'unknown');
        if (Cache::has($dedupeKey)) {
            return;
        }
        Cache::put($dedupeKey, true, 30);

        $webhookUrl = config('discord.affiliate_webhook');

        if (empty($webhookUrl)) {
            Log::warning('Discord affiliate webhook URL not configured. Set DISCORD_AFFILIATE_WEBHOOK in .env');
            return;
        }

        try {
            $link = $event->link->load(['affiliate.user', 'product']);
            $referral = $event->referral;

            $affiliateName = $link->affiliate?->user?->name ?? 'Unknown';
            $affiliateEmail = $link->affiliate?->user?->email ?? 'N/A';
            $productName = $link->product?->title ?? $link->product?->name ?? 'Unknown Product';
            $productPrice = $link->product?->price ?? 'N/A';
            $clickedAt = $referral->clicked_at ?? now();
            $visitorIp = $event->visitorIp ?? 'Unknown';
            $linkCode = $link->unique_code;
            $linkUrl = url("/ref/{$linkCode}");

            // Count total clicks on this specific link
            $totalLinkClicks = $link->referrals()->count();

            $embed = [
                'title' => '🔗 Affiliate Link Clicked!',
                'color' => 0x00D4AA, // Teal green
                'fields' => [
                    [
                        'name' => '👤 Affiliate',
                        'value' => "{$affiliateName}\n{$affiliateEmail}",
                        'inline' => true,
                    ],
                    [
                        'name' => '📦 Product',
                        'value' => "{$productName}\nKES " . number_format((float) $productPrice, 2),
                        'inline' => true,
                    ],
                    [
                        'name' => '🔗 Link Code',
                        'value' => "`{$linkCode}`",
                        'inline' => true,
                    ],
                    [
                        'name' => '📊 Total Clicks (this link)',
                        'value' => (string) $totalLinkClicks,
                        'inline' => true,
                    ],
                    [
                        'name' => '🌐 Visitor IP',
                        'value' => $visitorIp,
                        'inline' => true,
                    ],
                    [
                        'name' => '🕐 Clicked At',
                        'value' => $clickedAt->format('d M Y, H:i:s') . ' (EAT)',
                        'inline' => true,
                    ],
                ],
                'footer' => [
                    'text' => 'Relearn Affiliate System',
                ],
                'timestamp' => now()->toIso8601String(),
            ];

            Http::post($webhookUrl, [
                'embeds' => [$embed],
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to send Discord affiliate link notification', [
                'error' => $e->getMessage(),
                'link_id' => $event->link->id ?? null,
            ]);
        }
    }
}
