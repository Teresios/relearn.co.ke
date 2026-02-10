<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Discord Webhook URLs
    |--------------------------------------------------------------------------
    |
    | Webhook URLs for sending notifications to Discord channels.
    |
    */

    'affiliate_webhook' => env('DISCORD_AFFILIATE_WEBHOOK', ''),

    'purchase_webhook' => env('DISCORD_PURCHASE_WEBHOOK', ''),

];
