@extends('admin.mpesa.layout')

@section('mpesa-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.mpesa.reports.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left"></i> Back to Reports
        </a>
        <h2><i class="bi bi-file-earmark-bar-graph me-2"></i>{{ ucfirst($report->report_type) }} Report</h2>
        <p class="text-muted mb-0">{{ $report->period_start->format('M d, Y') }} - {{ $report->period_end->format('M d, Y') }}</p>
    </div>
    <div>
        <a href="{{ route('admin.mpesa.reports.export.pdf', $report) }}" class="btn btn-outline-danger">
            <i class="bi bi-file-pdf me-1"></i>Export PDF
        </a>
        <a href="{{ route('admin.mpesa.reports.export.csv', $report) }}" class="btn btn-outline-success">
            <i class="bi bi-file-csv me-1"></i>Export CSV
        </a>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-success text-white shadow-sm">
            <div class="card-body">
                <h6 class="text-white-50">Total Inbound</h6>
                <h3 class="mb-0">KES {{ number_format($report->total_inbound, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white shadow-sm">
            <div class="card-body">
                <h6 class="text-white-50">Total Outbound</h6>
                <h3 class="mb-0">KES {{ number_format($report->total_outbound, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-{{ $report->net_amount >= 0 ? 'primary' : 'warning' }} text-white shadow-sm">
            <div class="card-body">
                <h6 class="text-white-50">Net Amount</h6>
                <h3 class="mb-0">KES {{ number_format($report->net_amount, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white shadow-sm">
            <div class="card-body">
                <h6 class="text-white-50">Success Rate</h6>
                <h3 class="mb-0">{{ number_format($report->success_rate, 1) }}%</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Transaction Type Breakdown -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Transaction Type Breakdown</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th class="text-end">Count</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge bg-success">C2B</span></td>
                            <td class="text-end">{{ number_format($report->c2b_count) }}</td>
                            <td class="text-end">KES {{ number_format($report->c2b_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-danger">B2C</span></td>
                            <td class="text-end">{{ number_format($report->b2c_count) }}</td>
                            <td class="text-end">KES {{ number_format($report->b2c_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-primary">B2B</span></td>
                            <td class="text-end">{{ number_format($report->b2b_count) }}</td>
                            <td class="text-end">KES {{ number_format($report->b2b_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-warning text-dark">STK Push</span></td>
                            <td class="text-end">{{ number_format($report->stk_push_count) }}</td>
                            <td class="text-end">KES {{ number_format($report->stk_push_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-secondary">Reversal</span></td>
                            <td class="text-end">{{ number_format($report->reversal_count) }}</td>
                            <td class="text-end">KES {{ number_format($report->reversal_amount, 2) }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>Total</th>
                            <th class="text-end">{{ number_format($report->successful_transactions + $report->failed_transactions) }}</th>
                            <th class="text-end">-</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Success/Failure -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Success vs Failure</h5>
            </div>
            <div class="card-body">
                <div class="row text-center mb-4">
                    <div class="col-6">
                        <h2 class="text-success mb-0">{{ number_format($report->successful_transactions) }}</h2>
                        <p class="text-muted">Successful</p>
                    </div>
                    <div class="col-6">
                        <h2 class="text-danger mb-0">{{ number_format($report->failed_transactions) }}</h2>
                        <p class="text-muted">Failed</p>
                    </div>
                </div>
                <div class="progress" style="height: 30px;">
                    @php
                        $total = $report->successful_transactions + $report->failed_transactions;
                        $successPercent = $total > 0 ? ($report->successful_transactions / $total) * 100 : 0;
                    @endphp
                    <div class="progress-bar bg-success" style="width: {{ $successPercent }}%">
                        {{ number_format($successPercent, 1) }}%
                    </div>
                    <div class="progress-bar bg-danger" style="width: {{ 100 - $successPercent }}%">
                        {{ number_format(100 - $successPercent, 1) }}%
                    </div>
                </div>
            </div>
        </div>

        <!-- Forwarding Summary -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Auto-Forwarding Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h3 class="text-info">{{ number_format($report->forwarded_count) }}</h3>
                        <p class="text-muted mb-0">Transactions Forwarded</p>
                    </div>
                    <div class="col-6">
                        <h3 class="text-info">KES {{ number_format($report->forwarded_amount, 0) }}</h3>
                        <p class="text-muted mb-0">Amount Forwarded</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hourly Breakdown Chart -->
    @if($report->hourly_breakdown)
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Hourly Transaction Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="hourlyChart" height="80"></canvas>
            </div>
        </div>
    </div>
    @endif

    <!-- Top Senders & Receivers -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Top Senders</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Phone</th>
                                <th class="text-end">Transactions</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report->top_senders ?? [] as $sender)
                                <tr>
                                    <td>{{ $sender['sender_phone'] }}</td>
                                    <td class="text-end">{{ number_format($sender['count']) }}</td>
                                    <td class="text-end">KES {{ number_format($sender['total'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Top Receivers</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Phone</th>
                                <th class="text-end">Transactions</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report->top_receivers ?? [] as $receiver)
                                <tr>
                                    <td>{{ $receiver['receiver_phone'] }}</td>
                                    <td class="text-end">{{ number_format($receiver['count']) }}</td>
                                    <td class="text-end">KES {{ number_format($receiver['total'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Metadata -->
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <small class="text-muted">
            Report generated on {{ $report->generated_at->format('M d, Y \a\t H:i:s') }}
            @if($report->generator)
                by {{ $report->generator->name }}
            @endif
        </small>
    </div>
</div>

@if($report->hourly_breakdown)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const hourlyData = @json($report->hourly_breakdown);
    const hours = Array.from({length: 24}, (_, i) => i);
    const counts = hours.map(h => hourlyData[h]?.count || 0);

    new Chart(document.getElementById('hourlyChart'), {
        type: 'bar',
        data: {
            labels: hours.map(h => h.toString().padStart(2, '0') + ':00'),
            datasets: [{
                label: 'Transactions',
                data: counts,
                backgroundColor: 'rgba(13, 110, 253, 0.6)',
                borderColor: 'rgb(13, 110, 253)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
@endpush
@endif
@endsection
