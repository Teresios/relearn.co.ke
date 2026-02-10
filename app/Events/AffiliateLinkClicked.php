<?php

namespace App\Events;

use App\Models\AffiliateLink;
use App\Models\AffiliateReferral;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AffiliateLinkClicked
{
    use Dispatchable, SerializesModels;

    public AffiliateLink $link;
    public AffiliateReferral $referral;
    public ?string $visitorIp;

    /**
     * Create a new event instance.
     */
    public function __construct(AffiliateLink $link, AffiliateReferral $referral, ?string $visitorIp = null)
    {
        $this->link = $link;
        $this->referral = $referral;
        $this->visitorIp = $visitorIp;
    }
}
