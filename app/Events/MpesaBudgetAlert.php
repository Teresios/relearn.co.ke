<?php

namespace App\Events;

use App\Models\MpesaBudget;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MpesaBudgetAlert implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public MpesaBudget $budget;
    public string $level;
    public float $percentage;
    public float $spent;
    public float $limit;

    public function __construct(MpesaBudget $budget, string $level)
    {
        $this->budget = $budget;
        $this->level = $level;
        $this->percentage = $budget->getUsedPercentage();
        $this->spent = $budget->spent_amount;
        $this->limit = $budget->budget_limit;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('mpesa-admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'budget.alert';
    }

    public function broadcastWith(): array
    {
        return [
            'budget_id' => $this->budget->id,
            'budget_name' => $this->budget->name,
            'level' => $this->level,
            'percentage' => $this->percentage,
            'spent' => $this->spent,
            'limit' => $this->limit,
            'remaining' => $this->budget->getRemainingAmount(),
            'message' => $this->getAlertMessage(),
        ];
    }

    protected function getAlertMessage(): string
    {
        return match($this->level) {
            'exceeded' => "Budget '{$this->budget->name}' has been EXCEEDED! Spent: KES " . number_format($this->spent, 2),
            'critical' => "Budget '{$this->budget->name}' is at CRITICAL level ({$this->percentage}%)",
            'warning' => "Budget '{$this->budget->name}' has reached WARNING level ({$this->percentage}%)",
            default => "Budget alert for '{$this->budget->name}'",
        };
    }
}
