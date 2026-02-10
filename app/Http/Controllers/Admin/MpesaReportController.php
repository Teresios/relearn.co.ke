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
        $pdf = Pdf::loadView('admin.mpesa.reports.pdf', compact('report'));
        
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

        // Daily transaction volume
        $dailyVolume = MpesaTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        // Type distribution
        $typeDistribution = MpesaTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('type, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('type')
            ->get();

        // Status distribution
        $statusDistribution = MpesaTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Hourly pattern
        $hourlyPattern = MpesaTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count, AVG(amount) as avg_amount')
            ->groupByRaw('HOUR(created_at)')
            ->orderBy('hour')
            ->get();

        // Weekly pattern
        $weeklyPattern = MpesaTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DAYOFWEEK(created_at) as day, COUNT(*) as count, AVG(amount) as avg_amount')
            ->groupByRaw('DAYOFWEEK(created_at)')
            ->orderBy('day')
            ->get();

        return view('admin.mpesa.reports.analytics', compact(
            'dailyVolume',
            'typeDistribution',
            'statusDistribution',
            'hourlyPattern',
            'weeklyPattern',
            'period'
        ));
    }
}
