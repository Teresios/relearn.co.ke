<?php

namespace App\Events;

use App\Models\MpesaTransaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MpesaTransactionReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public MpesaTransaction $transaction;

    public function __construct(MpesaTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('mpesa-admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'transaction.received';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->transaction->id,
            'transaction_id' => $this->transaction->transaction_id,
            'type' => $this->transaction->type,
            'type_label' => $this->transaction->getTypeLabel(),
            'direction' => $this->transaction->direction,
            'amount' => $this->transaction->amount,
            'formatted_amount' => $this->transaction->getFormattedAmount(),
            'status' => $this->transaction->status,
            'status_badge' => $this->transaction->getStatusBadgeClass(),
            'sender_phone' => $this->transaction->sender_phone,
            'receiver_phone' => $this->transaction->receiver_phone,
            'created_at' => $this->transaction->created_at->toIso8601String(),
            'created_at_human' => $this->transaction->created_at->diffForHumans(),
        ];
    }
}
