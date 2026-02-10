<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MpesaTransaction;
use App\Models\MpesaBudget;
use App\Models\MpesaForwardingRule;
use App\Models\MpesaReport;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MpesaDashboardController extends Controller
{
    public function index()
    {
        // Today's stats
        $todayStats = MpesaTransaction::getTodayStats();
        
        // This week's stats
        $weeklyStats = MpesaTransaction::getWeeklyStats();
        
        // This month's stats
        $monthlyStats = MpesaTransaction::getMonthlyStats();
        
        // Active budgets with status
        $budgets = MpesaBudget::active()->get()->map(function ($budget) {
            $budget->recalculateSpending();
            return $budget;
        });
        
        // Forwarding rules summary
        $forwardingRules = MpesaForwardingRule::active()->get();
        $totalForwarded = $forwardingRules->sum('total_forwarded_amount');
        
        // Recent transactions
        $recentTransactions = MpesaTransaction::with(['user', 'order'])
            ->latest()
            ->limit(10)
            ->get();
        
        // Pending transactions
        $pendingTransactions = MpesaTransaction::pending()->count();
        
        // Failed transactions today
        $failedToday = MpesaTransaction::today()->failed()->count();
        
        // Transaction volume chart data (last 7 days)
        $chartData = $this->getTransactionChartData(7);
        
        // Transaction type breakdown
        $typeBreakdown = $this->getTypeBreakdown();
        
        return view('admin.mpesa.dashboard', compact(
            'todayStats',
            'weeklyStats',
            'monthlyStats',
            'budgets',
            'forwardingRules',
            'totalForwarded',
            'recentTransactions',
            'pendingTransactions',
            'failedToday',
            'chartData',
            'typeBreakdown'
        ));
    }

    public function realTimeData()
    {
        return response()->json([
            'today' => MpesaTransaction::getTodayStats(),
            'pending' => MpesaTransaction::pending()->count(),
            'recent' => MpesaTransaction::latest()->limit(5)->get(['id', 'type', 'amount', 'status', 'created_at']),
            'budgets' => MpesaBudget::active()->get()->map(fn($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'percentage' => $b->getUsedPercentage(),
                'status' => $b->getStatus(),
            ]),
        ]);
    }

    protected function getTransactionChartData(int $days): array
    {
        $data = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            
            $dayTransactions = MpesaTransaction::whereDate('created_at', $date);
            
            $data[] = [
                'date' => $date->format('M d'),
                'inbound' => (clone $dayTransactions)->inbound()->successful()->sum('amount'),
                'outbound' => (clone $dayTransactions)->outbound()->successful()->sum('amount'),
                'count' => (clone $dayTransactions)->count(),
            ];
        }
        
        return $data;
    }

    protected function getTypeBreakdown(): array
    {
        $thisMonth = MpesaTransaction::thisMonth()->successful();
        
        return [
            'c2b' => [
                'count' => (clone $thisMonth)->ofType(MpesaTransaction::TYPE_C2B)->count(),
                'amount' => (clone $thisMonth)->ofType(MpesaTransaction::TYPE_C2B)->sum('amount'),
            ],
            'b2c' => [
                'count' => (clone $thisMonth)->ofType(MpesaTransaction::TYPE_B2C)->count(),
                'amount' => (clone $thisMonth)->ofType(MpesaTransaction::TYPE_B2C)->sum('amount'),
            ],
            'b2b' => [
                'count' => (clone $thisMonth)->ofType(MpesaTransaction::TYPE_B2B)->count(),
                'amount' => (clone $thisMonth)->ofType(MpesaTransaction::TYPE_B2B)->sum('amount'),
            ],
            'stk_push' => [
                'count' => (clone $thisMonth)->ofType(MpesaTransaction::TYPE_STK_PUSH)->count(),
                'amount' => (clone $thisMonth)->ofType(MpesaTransaction::TYPE_STK_PUSH)->sum('amount'),
            ],
            'reversal' => [
                'count' => (clone $thisMonth)->ofType(MpesaTransaction::TYPE_REVERSAL)->count(),
                'amount' => (clone $thisMonth)->ofType(MpesaTransaction::TYPE_REVERSAL)->sum('amount'),
            ],
        ];
    }
}
