@extends('admin.mpesa.layout')

@section('title', 'M-Pesa Analytics')

@section('mpesa-content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.mpesa.dashboard') }}">M-Pesa Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.mpesa.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active">Analytics</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-chart-line text-primary me-2"></i>M-Pesa Analytics</h2>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary active" data-period="7">7 Days</button>
                <button type="button" class="btn btn-outline-primary" data-period="30">30 Days</button>
                <button type="button" class="btn btn-outline-primary" data-period="90">90 Days</button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Volume</h6>
                            <h3 class="mb-0">KES {{ number_format($analytics['total_volume'] ?? 0, 2) }}</h3>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                    </div>
                    <small class="opacity-75">
                        <i class="fas {{ ($analytics['volume_change'] ?? 0) >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                        {{ abs($analytics['volume_change'] ?? 0) }}% from last period
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Transactions</h6>
                            <h3 class="mb-0">{{ number_format($analytics['total_transactions'] ?? 0) }}</h3>
                        </div>
                        <i class="fas fa-exchange-alt fa-2x opacity-50"></i>
                    </div>
                    <small class="opacity-75">
                        <i class="fas {{ ($analytics['transaction_change'] ?? 0) >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                        {{ abs($analytics['transaction_change'] ?? 0) }}% from last period
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Success Rate</h6>
                            <h3 class="mb-0">{{ number_format($analytics['success_rate'] ?? 0, 1) }}%</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                    <small class="opacity-75">{{ $analytics['successful_count'] ?? 0 }} successful</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Avg. Amount</h6>
                            <h3 class="mb-0">KES {{ number_format($analytics['average_amount'] ?? 0, 2) }}</h3>
                        </div>
                        <i class="fas fa-calculator fa-2x opacity-50"></i>
                    </div>
                    <small class="text-dark opacity-75">Per transaction</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Volume Trend Chart -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-area text-primary me-2"></i>Transaction Volume Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="volumeTrendChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <!-- Transaction Type Distribution -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-pie text-info me-2"></i>By Type</h5>
                </div>
                <div class="card-body">
                    <canvas id="typeDistributionChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Hourly Distribution -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock text-warning me-2"></i>Hourly Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="hourlyChart" height="150"></canvas>
                </div>
            </div>
        </div>

        <!-- Daily Distribution -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-week text-success me-2"></i>Daily Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Phone Numbers -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-phone text-primary me-2"></i>Top Phone Numbers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Phone Number</th>
                                    <th>Transactions</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['top_phones'] ?? [] as $index => $phone)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $phone->phone_number }}</td>
                                        <td>{{ $phone->count }}</td>
                                        <td>KES {{ number_format($phone->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Distribution -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tasks text-info me-2"></i>Status Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="statusChart" height="200"></canvas>
                        </div>
                        <div class="col-md-6">
                            <div class="mt-4">
                                @foreach($analytics['status_distribution'] ?? [] as $status => $data)
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>
                                            <i class="fas fa-circle text-{{ $status === 'completed' ? 'success' : ($status === 'pending' ? 'warning' : 'danger') }} me-2"></i>
                                            {{ ucfirst($status) }}
                                        </span>
                                        <span><strong>{{ $data['count'] }}</strong> ({{ number_format($data['percentage'], 1) }}%)</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Amount Range Analysis -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-layer-group text-warning me-2"></i>Amount Range Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Amount Range</th>
                                    <th>Transactions</th>
                                    <th>Total Volume</th>
                                    <th>Avg Amount</th>
                                    <th>% of Total</th>
                                    <th>Distribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['amount_ranges'] ?? [] as $range)
                                    <tr>
                                        <td><strong>{{ $range['label'] }}</strong></td>
                                        <td>{{ number_format($range['count']) }}</td>
                                        <td>KES {{ number_format($range['total'], 2) }}</td>
                                        <td>KES {{ number_format($range['average'], 2) }}</td>
                                        <td>{{ number_format($range['percentage'], 1) }}%</td>
                                        <td style="width: 200px;">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-primary" style="width: {{ $range['percentage'] }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Options -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-download text-success me-2"></i>Export Analytics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{ route('admin.mpesa.reports.generate', ['type' => 'custom', 'format' => 'pdf']) }}" class="btn btn-outline-danger w-100">
                                <i class="fas fa-file-pdf me-2"></i>Export as PDF
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.mpesa.reports.generate', ['type' => 'custom', 'format' => 'csv']) }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-file-csv me-2"></i>Export as CSV
                            </a>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>Print Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Volume Trend Chart
const volumeTrendCtx = document.getElementById('volumeTrendChart').getContext('2d');
new Chart(volumeTrendCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($analytics['trend_labels'] ?? []) !!},
        datasets: [{
            label: 'Volume (KES)',
            data: {!! json_encode($analytics['trend_data'] ?? []) !!},
            borderColor: 'rgb(13, 110, 253)',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            fill: true,
            tension: 0.4
        }, {
            label: 'Transactions',
            data: {!! json_encode($analytics['trend_count'] ?? []) !!},
            borderColor: 'rgb(25, 135, 84)',
            backgroundColor: 'transparent',
            yAxisID: 'y1',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Volume (KES)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Transaction Count'
                },
                grid: {
                    drawOnChartArea: false,
                },
            },
        }
    }
});

// Type Distribution Chart
const typeCtx = document.getElementById('typeDistributionChart').getContext('2d');
new Chart(typeCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode(array_keys($analytics['type_distribution'] ?? [])) !!},
        datasets: [{
            data: {!! json_encode(array_values($analytics['type_distribution'] ?? [])) !!},
            backgroundColor: [
                'rgb(13, 110, 253)',
                'rgb(25, 135, 84)',
                'rgb(255, 193, 7)',
                'rgb(220, 53, 69)',
                'rgb(13, 202, 240)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Hourly Chart
const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
new Chart(hourlyCtx, {
    type: 'bar',
    data: {
        labels: Array.from({length: 24}, (_, i) => `${i}:00`),
        datasets: [{
            label: 'Transactions',
            data: {!! json_encode($analytics['hourly_data'] ?? array_fill(0, 24, 0)) !!},
            backgroundColor: 'rgba(255, 193, 7, 0.8)'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Hour of Day'
                }
            }
        }
    }
});

// Daily Chart
const dailyCtx = document.getElementById('dailyChart').getContext('2d');
new Chart(dailyCtx, {
    type: 'bar',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Transactions',
            data: {!! json_encode($analytics['daily_data'] ?? array_fill(0, 7, 0)) !!},
            backgroundColor: 'rgba(25, 135, 84, 0.8)'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode(array_keys($analytics['status_distribution'] ?? [])) !!},
        datasets: [{
            data: {!! json_encode(array_column($analytics['status_distribution'] ?? [], 'count')) !!},
            backgroundColor: [
                'rgb(25, 135, 84)',
                'rgb(255, 193, 7)',
                'rgb(220, 53, 69)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Period selector
document.querySelectorAll('[data-period]').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('[data-period]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        window.location.href = '{{ route('admin.mpesa.reports.analytics') }}?period=' + this.dataset.period;
    });
});
</script>
@endpush
@endsection
