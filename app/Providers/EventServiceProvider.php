<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        \App\Events\AffiliateLinkClicked::class => [
            \App\Listeners\SendDiscordAffiliateLinkNotification::class,
        ],
        \App\Events\OrderCompleted::class => [
            \App\Listeners\SendDiscordPurchaseNotification::class,
            \App\Listeners\SendOrderThankYouEmail::class,
        ],
    ];

    /**
     * Determine whether events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return true;
    }
}
