<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MpesaReport;
use App\Models\MpesaTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class MpesaReportController extends Controller
{
    public function index()
    {
        $reports = MpesaReport::with('generator')
            ->latest('generated_at')
            ->paginate(20);

        return view('admin.mpesa.reports.index', compact('reports'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:daily,weekly,monthly,custom',
            'start_date' => 'required_if:report_type,custom|nullable|date',
            'end_date' => 'required_if:report_type,custom|nullable|date|after_or_equal:start_date',
        ]);

        $report = MpesaReport::generateReport(
            $request->report_type,
            $request->start_date,
            $request->end_date,
            auth()->id()
        );

        return redirect()->route('admin.mpesa.reports.show', $report)
            ->with('success', 'Report generated successfully');
    }

    public function show(MpesaReport $report)
    {
        return view('admin.mpesa.reports.show', compact('report'));
    }

    public function daily()
    {
        $report = MpesaReport::generateReport(MpesaReport::TYPE_DAILY, null, null, auth()->id());
        
        return view('admin.mpesa.reports.show', compact('report'));
    }

    public function weekly()
    {
        $report = MpesaReport::generateReport(MpesaReport::TYPE_WEEKLY, null, null, auth()->id());
        
        return view('admin.mpesa.reports.show', compact('report'));
    }

    public function monthly()
    {
        $report = MpesaReport::generateReport(MpesaReport::TYPE_MONTHLY, null, null, auth()->id());
        
        return view('admin.mpesa.reports.show', compact('report'));
    }

    public function custom(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $report = MpesaReport::generateReport(
            MpesaReport::TYPE_CUSTOM,
            $request->start_date,
            $request->end_date,
            auth()->id()
        );

        return view('admin.mpesa.reports.show', compact('report'));
    }

    public function exportPdf(MpesaReport $report)
    {
        // Build summary data
        $totalTransactions = $report->successful_transactions + $report->failed_transactions;
        $summary = [
            'total_transactions' => $totalTransactions,
            'total_amount' => $report->total_inbound + $report->total_outbound,
            'success_rate' => $report->success_rate,
            'average_amount' => $totalTransactions > 0 ? ($report->total_inbound + $report->total_outbound) / $totalTransactions : 0,
            'successful_count' => $report->successful_transactions,
            'failed_count' => $report->failed_transactions,
            'pending_count' => 0,
            'total_fees' => 0,
        ];

        // Build type breakdown
        $typeBreakdown = [];
        $totalVolume = $report->c2b_amount + $report->b2c_amount + $report->b2b_amount + $report->stk_push_amount + $report->reversal_amount;
        foreach (['c2b', 'b2c', 'b2b', 'stk_push', 'reversal'] as $type) {
            $count = $report->{$type . '_count'};
            $total = $report->{$type . '_amount'};
            if ($count > 0) {
                $typeBreakdown[$type] = [
                    'count' => $count,
                    'total' => $total,
                    'average' => $count > 0 ? $total / $count : 0,
                    'percentage' => $totalVolume > 0 ? ($total / $totalVolume) * 100 : 0,
                ];
            }
        }

        // Build status breakdown
        $statusBreakdown = [];
        if ($report->successful_transactions > 0) {
            $statusBreakdown['completed'] = [
                'count' => $report->successful_transactions,
                'percentage' => $totalTransactions > 0 ? ($report->successful_transactions / $totalTransactions) * 100 : 0,
            ];
        }
        if ($report->failed_transactions > 0) {
            $statusBreakdown['failed'] = [
                'count' => $report->failed_transactions,
                'percentage' => $totalTransactions > 0 ? ($report->failed_transactions / $totalTransactions) * 100 : 0,
            ];
        }

        // Get transactions for the period
        $transactions = MpesaTransaction::whereBetween('created_at', [$report->period_start, $report->period_end])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        // Build daily summary
        $dailySummary = MpesaTransaction::whereBetween('created_at', [$report->period_start, $report->period_end])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful, SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'count' => $item->count,
                'total' => $item->total,
                'successful' => $item->successful,
                'failed' => $item->failed,
            ])
            ->toArray();

        $pdf = Pdf::loadView('admin.mpesa.reports.pdf', compact('report', 'summary', 'typeBreakdown', 'statusBreakdown', 'transactions', 'dailySummary'));
        
        $filename = "mpesa_report_{$report->report_type}_{$report->period_start->format('Ymd')}.pdf";
        
        return $pdf->download($filename);
    }

    public function exportCsv(MpesaReport $report)
    {
        $filename = "mpesa_report_{$report->report_type}_{$report->period_start->format('Ymd')}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($report) {
            $file = fopen('php://output', 'w');
            
            // Report header
            fputcsv($file, ['M-Pesa Report']);
            fputcsv($file, ['Type', ucfirst($report->report_type)]);
            fputcsv($file, ['Period', $report->period_start->format('Y-m-d') . ' to ' . $report->period_end->format('Y-m-d')]);
            fputcsv($file, ['Generated', $report->generated_at->format('Y-m-d H:i:s')]);
            fputcsv($file, []);
            
            // Summary
            fputcsv($file, ['Summary']);
            fputcsv($file, ['Total Inbound', 'KES ' . number_format($report->total_inbound, 2)]);
            fputcsv($file, ['Total Outbound', 'KES ' . number_format($report->total_outbound, 2)]);
            fputcsv($file, ['Net Amount', 'KES ' . number_format($report->net_amount, 2)]);
            fputcsv($file, ['Success Rate', $report->success_rate . '%']);
            fputcsv($file, []);
            
            // Transaction Type Breakdown
            fputcsv($file, ['Transaction Type', 'Count', 'Amount']);
            fputcsv($file, ['C2B', $report->c2b_count, 'KES ' . number_format($report->c2b_amount, 2)]);
            fputcsv($file, ['B2C', $report->b2c_count, 'KES ' . number_format($report->b2c_amount, 2)]);
            fputcsv($file, ['B2B', $report->b2b_count, 'KES ' . number_format($report->b2b_amount, 2)]);
            fputcsv($file, ['STK Push', $report->stk_push_count, 'KES ' . number_format($report->stk_push_amount, 2)]);
            fputcsv($file, ['Reversal', $report->reversal_count, 'KES ' . number_format($report->reversal_amount, 2)]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroy(MpesaReport $report)
    {
        $report->delete();

        return redirect()->route('admin.mpesa.reports.index')
            ->with('success', 'Report deleted successfully');
    }

    public function analytics(Request $request)
    {
        $period = $request->get('period', 30); // days
        $startDate = Carbon::now()->subDays($period);
        $endDate = Carbon::now();
        $prevStartDate = Carbon::now()->subDays($period * 2);
        $prevEndDate = $startDate->copy();

        // Current period stats
        $currentQuery = MpesaTransaction::whereBetween('created_at', [$startDate, $endDate]);
        $totalVolume = (clone $currentQuery)->sum('amount');
        $totalTransactions = (clone $currentQuery)->count();
        $successfulCount = (clone $currentQuery)->where('status', 'completed')->count();
        $successRate = $totalTransactions > 0 ? ($successfulCount / $totalTransactions) * 100 : 0;
        $averageAmount = $totalTransactions > 0 ? $totalVolume / $totalTransactions : 0;

        // Previous period for change calculations
        $prevQuery = MpesaTransaction::whereBetween('created_at', [$prevStartDate, $prevEndDate]);
        $prevVolume = (clone $prevQuery)->sum('amount');
        $prevTransactions = (clone $prevQuery)->count();
        $volumeChange = $prevVolume > 0 ? (($totalVolume - $prevVolume) / $prevVolume) * 100 : 0;
        $transactionChange = $prevTransactions > 0 ? (($totalTransactions - $prevTransactions) / $prevTransactions) * 100 : 0;

        // Daily transaction volume (for trend chart)
        $dailyVolume = MpesaTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        // Type distribution
        $typeDistribution = MpesaTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('type, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('type')
            ->get()
            ->mapWithKeys(fn($item) => [strtoupper($item->type) => $item->count])
            ->toArray();

        // Status distribution
        $statusRaw = MpesaTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
        $statusDistribution = [];
        foreach ($statusRaw as $item) {
            $statusDistribution[$item->status] = [
                'count' => $item->count,
                'percentage' => $totalTransactions > 0 ? ($item->count / $totalTransactions) * 100 : 0,
            ];
        }

        // Hourly pattern
        $hourlyRaw = MpesaTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupByRaw('HOUR(created_at)')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->toArray();
        $hourlyData = [];
        for ($i = 0; $i < 24; $i++) {
            $hourlyData[] = $hourlyRaw[$i] ?? 0;
        }

        // Weekly pattern (Mon=1 through Sun=7 in MySQL DAYOFWEEK returns 1=Sun...7=Sat)
        $weeklyRaw = MpesaTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DAYOFWEEK(created_at) as day, COUNT(*) as count')
            ->groupByRaw('DAYOFWEEK(created_at)')
            ->orderBy('day')
            ->get()
            ->pluck('count', 'day')
            ->toArray();
        // MySQL: 1=Sun, 2=Mon... reorder to Mon-Sun
        $dailyData = [];
        for ($d = 2; $d <= 7; $d++) {
            $dailyData[] = $weeklyRaw[$d] ?? 0;
        }
        $dailyData[] = $weeklyRaw[1] ?? 0; // Sunday last

        // Top phone numbers
        $topPhones = MpesaTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('sender_phone')
            ->selectRaw('sender_phone as phone_number, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('sender_phone')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Amount ranges
        $ranges = [
            ['label' => 'KES 0 - 100', 'min' => 0, 'max' => 100],
            ['label' => 'KES 100 - 500', 'min' => 100, 'max' => 500],
            ['label' => 'KES 500 - 1,000', 'min' => 500, 'max' => 1000],
            ['label' => 'KES 1,000 - 5,000', 'min' => 1000, 'max' => 5000],
            ['label' => 'KES 5,000 - 10,000', 'min' => 5000, 'max' => 10000],
            ['label' => 'KES 10,000 - 50,000', 'min' => 10000, 'max' => 50000],
            ['label' => 'KES 50,000+', 'min' => 50000, 'max' => PHP_FLOAT_MAX],
        ];
        $amountRanges = [];
        foreach ($ranges as $range) {
            $rangeQuery = (clone $currentQuery)->whereBetween('amount', [$range['min'], $range['max']]);
            $rangeCount = $rangeQuery->count();
            $rangeTotal = (clone $currentQuery)->whereBetween('amount', [$range['min'], $range['max']])->sum('amount');
            $amountRanges[] = [
                'label' => $range['label'],
                'count' => $rangeCount,
                'total' => $rangeTotal,
                'average' => $rangeCount > 0 ? $rangeTotal / $rangeCount : 0,
                'percentage' => $totalTransactions > 0 ? ($rangeCount / $totalTransactions) * 100 : 0,
            ];
        }

        $analytics = [
            'total_volume' => $totalVolume,
            'volume_change' => round($volumeChange, 1),
            'total_transactions' => $totalTransactions,
            'transaction_change' => round($transactionChange, 1),
            'success_rate' => $successRate,
            'successful_count' => $successfulCount,
            'average_amount' => $averageAmount,
            'top_phones' => $topPhones,
            'status_distribution' => $statusDistribution,
            'trend_labels' => $dailyVolume->pluck('date')->toArray(),
            'trend_data' => $dailyVolume->pluck('total')->toArray(),
            'trend_count' => $dailyVolume->pluck('count')->toArray(),
            'type_distribution' => $typeDistribution,
            'hourly_data' => $hourlyData,
            'daily_data' => $dailyData,
            'amount_ranges' => $amountRanges,
        ];

        return view('admin.mpesa.reports.analytics', compact('analytics', 'period'));
    }
}
