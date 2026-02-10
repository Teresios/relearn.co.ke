<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AffiliateLink;
use App\Models\AffiliateReferral;
use App\Events\AffiliateLinkClicked;

class ReferralController extends Controller
{
    public function trackAndRedirect($code)
    {
        // Find the link by unique code
        $link = AffiliateLink::where('unique_code', $code)->firstOrFail();

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