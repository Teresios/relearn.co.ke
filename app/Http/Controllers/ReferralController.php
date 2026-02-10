<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AffiliateLink;
use App\Models\AffiliateReferral;
use App\Events\AffiliateLinkClicked;

class ReferralController extends Controller
{
    /**
     * Known bot/crawler user agent patterns.
     */
    private const BOT_PATTERNS = [
        'facebookexternalhit',
        'Facebot',
        'FacebookBot',
        'Twitterbot',
        'LinkedInBot',
        'WhatsApp',
        'Googlebot',
        'bingbot',
        'Slackbot',
        'Discordbot',
        'TelegramBot',
        'Pinterest',
        'Embedly',
        'Quora Link Preview',
        'Showyoubot',
        'outbrain',
        'vkShare',
        'Applebot',
        'crawler',
        'spider',
        'bot/',
    ];

    /**
     * Check if the current request is from a known bot/crawler.
     */
    private function isBot(): bool
    {
        $userAgent = request()->userAgent() ?? '';
        foreach (self::BOT_PATTERNS as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    public function trackAndRedirect($code)
    {
        // Find the link by unique code
        $link = AffiliateLink::where('unique_code', $code)->firstOrFail();

        // Skip tracking and notifications for bots/crawlers (Facebook, Twitter, etc.)
        if ($this->isBot()) {
            \Log::info('Bot/crawler detected, skipping referral tracking', [
                'code' => $code,
                'user_agent' => request()->userAgent(),
                'ip' => request()->ip(),
            ]);
            return redirect()->route('products.show', $link->product_id);
        }

        // Log the referral/click
        $referral = $link->referrals()->create([
            'user_id'         => auth()->id(), // or null for guests
            'referrer_ip'     => request()->ip(),
            'referral_code'   => $code,
            'clicked_at'      => now(),
        ]);

        // Store referral code in session so OrderController can link purchases
        session(['referral_code' => $code, 'referral_id' => $referral->id]);
        
        \Log::info('Referral tracked and stored in session', [
            'code' => $code,
            'referral_id' => $referral->id,
            'user_id' => auth()->id(),
        ]);

        // Fire event to notify Discord about the click
        AffiliateLinkClicked::dispatch($link, $referral, request()->ip());

        // Redirect to the associated product page
        return redirect()->route('products.show', $link->product_id);
    }
}