<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MpesaDashboardUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $stats;

    public function __construct(array $stats)
    {
        $this->stats = $stats;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('mpesa-admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'dashboard.update';
    }

    public function broadcastWith(): array
    {
        return $this->stats;
    }

    public static function sendUpdate(): void
    {
        $stats = [
            'today' => \App\Models\MpesaTransaction::getTodayStats(),
            'pending' => \App\Models\MpesaTransaction::pending()->count(),
            'timestamp' => now()->toIso8601String(),
        ];

        event(new self($stats));
    }
}
