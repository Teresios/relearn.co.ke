<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>M-Pesa Report - {{ $report->name ?? 'Transaction Report' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #0b5ed7;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            color: #0b5ed7;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            font-size: 14px;
            color: #666;
        }
        
        .header .report-info {
            margin-top: 15px;
            font-size: 11px;
            color: #888;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 16px;
            color: #0b5ed7;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        
        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #0b5ed7;
        }
        
        .summary-card .label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        table tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        .status-completed {
            color: #198754;
            font-weight: bold;
        }
        
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        
        .status-failed {
            color: #dc3545;
            font-weight: bold;
        }
        
        .type-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            color: white;
            text-transform: uppercase;
        }
        
        .type-c2b { background-color: #0d6efd; }
        .type-b2c { background-color: #198754; }
        .type-b2b { background-color: #0dcaf0; }
        .type-stk_push { background-color: #6f42c1; }
        .type-reversal { background-color: #dc3545; }
        
        .chart-placeholder {
            text-align: center;
            padding: 30px;
            background-color: #f8f9fa;
            border: 1px dashed #ddd;
            color: #666;
        }
        
        .two-column {
            display: table;
            width: 100%;
        }
        
        .two-column > div {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 15px;
        }
        
        .two-column > div:last-child {
            padding-right: 0;
            padding-left: 15px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #888;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .amount {
            text-align: right;
            font-family: monospace;
        }
        
        .meta-table {
            margin-bottom: 20px;
        }
        
        .meta-table td {
            border: none;
            padding: 4px 10px 4px 0;
        }
        
        .meta-table td:first-child {
            font-weight: bold;
            color: #666;
            width: 150px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>M-Pesa Transaction Report</h1>
        <div class="subtitle">{{ $report->name ?? 'Transaction Summary' }}</div>
        <div class="report-info">
            Generated on {{ now()->format('F d, Y \a\t H:i:s') }}
            @if(isset($report))
                | Report ID: {{ $report->id }}
            @endif
        </div>
    </div>

    <!-- Report Meta Information -->
    <div class="section">
        <h2 class="section-title">Report Information</h2>
        <table class="meta-table">
            <tr>
                <td>Report Period:</td>
                <td>{{ ($report->period_start ?? now()->subDays(30))->format('M d, Y') }} - {{ ($report->period_end ?? now())->format('M d, Y') }}</td>
            </tr>
            <tr>
                <td>Report Type:</td>
                <td>{{ ucfirst($report->report_type ?? 'custom') }}</td>
            </tr>
            @if(isset($transactionType) && $transactionType)
            <tr>
                <td>Transaction Type:</td>
                <td>{{ strtoupper($transactionType) }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Summary Section -->
    <div class="section">
        <h2 class="section-title">Summary</h2>
        <div class="summary-cards">
            <div class="summary-card">
                <div class="value">{{ number_format($summary['total_transactions'] ?? 0) }}</div>
                <div class="label">Total Transactions</div>
            </div>
            <div class="summary-card">
                <div class="value">KES {{ number_format($summary['total_amount'] ?? 0, 2) }}</div>
                <div class="label">Total Volume</div>
            </div>
            <div class="summary-card">
                <div class="value">{{ number_format($summary['success_rate'] ?? 0, 1) }}%</div>
                <div class="label">Success Rate</div>
            </div>
            <div class="summary-card">
                <div class="value">KES {{ number_format($summary['average_amount'] ?? 0, 2) }}</div>
                <div class="label">Average Amount</div>
            </div>
        </div>
    </div>

    <!-- Transaction Type Breakdown -->
    <div class="section">
        <h2 class="section-title">Transaction Type Breakdown</h2>
        <table>
            <thead>
                <tr>
                    <th>Transaction Type</th>
                    <th>Count</th>
                    <th>Total Amount</th>
                    <th>Average</th>
                    <th>% of Volume</th>
                </tr>
            </thead>
            <tbody>
                @forelse($typeBreakdown ?? [] as $type => $data)
                    <tr>
                        <td><span class="type-badge type-{{ $type }}">{{ strtoupper($type) }}</span></td>
                        <td>{{ number_format($data['count']) }}</td>
                        <td class="amount">KES {{ number_format($data['total'], 2) }}</td>
                        <td class="amount">KES {{ number_format($data['average'], 2) }}</td>
                        <td>{{ number_format($data['percentage'], 1) }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #666;">No data available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Status Breakdown -->
    <div class="section">
        <h2 class="section-title">Status Breakdown</h2>
        <div class="two-column">
            <div>
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statusBreakdown ?? [] as $status => $data)
                            <tr>
                                <td class="status-{{ $status }}">{{ ucfirst($status) }}</td>
                                <td>{{ number_format($data['count']) }}</td>
                                <td>{{ number_format($data['percentage'], 1) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; color: #666;">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>
                <table>
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Successful Transactions</td>
                            <td>{{ number_format($summary['successful_count'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td>Failed Transactions</td>
                            <td>{{ number_format($summary['failed_count'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td>Pending Transactions</td>
                            <td>{{ number_format($summary['pending_count'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td>Total Fees (Estimated)</td>
                            <td>KES {{ number_format($summary['total_fees'] ?? 0, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(isset($transactions) && count($transactions) > 0)
    <!-- Page Break before transaction list -->
    <div class="page-break"></div>

    <!-- Transaction List -->
    <div class="section">
        <h2 class="section-title">Transaction Details</h2>
        <table>
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Type</th>
                    <th>Phone Number</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date/Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                    <tr>
                        <td style="font-family: monospace; font-size: 10px;">{{ $transaction->transaction_id }}</td>
                        <td><span class="type-badge type-{{ $transaction->type }}">{{ strtoupper($transaction->type) }}</span></td>
                        <td>{{ $transaction->sender_phone ?? $transaction->receiver_phone }}</td>
                        <td class="amount">KES {{ number_format($transaction->amount, 2) }}</td>
                        <td class="status-{{ $transaction->status }}">{{ ucfirst($transaction->status) }}</td>
                        <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator && $transactions->hasMorePages())
            <p style="text-align: center; color: #666; font-style: italic;">
                Showing {{ $transactions->count() }} of {{ $transactions->total() }} transactions
            </p>
        @endif
    </div>
    @endif

    <!-- Daily Summary -->
    @if(isset($dailySummary) && count($dailySummary) > 0)
    <div class="section">
        <h2 class="section-title">Daily Summary</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Transactions</th>
                    <th>Total Volume</th>
                    <th>Successful</th>
                    <th>Failed</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dailySummary as $day)
                    <tr>
                        <td>{{ $day['date'] }}</td>
                        <td>{{ number_format($day['count']) }}</td>
                        <td class="amount">KES {{ number_format($day['total'], 2) }}</td>
                        <td class="status-completed">{{ number_format($day['successful']) }}</td>
                        <td class="status-failed">{{ number_format($day['failed']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This report was automatically generated by the M-Pesa Admin Portal</p>
        <p>{{ config('app.name') }} | {{ config('app.url') }}</p>
        <p>&copy; {{ date('Y') }} All Rights Reserved</p>
    </div>
</body>
</html>
