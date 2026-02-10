@extends('admin.mpesa.layout')

@section('mpesa-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-speedometer2 me-2"></i>M-Pesa Dashboard</h2>
    <div>
        <span class="badge bg-success" id="live-indicator">
            <i class="bi bi-circle-fill me-1"></i>Live
        </span>
        <button class="btn btn-outline-primary btn-sm ms-2" onclick="refreshDashboard()">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <!-- Today's Inbound -->
    <div class="col-md-3">
        <div class="card stat-card inbound shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Today's Inbound</h6>
                        <h3 class="mb-0 text-success" id="today-inbound">KES {{ number_format($todayStats['total_inbound'], 2) }}</h3>
                    </div>
                    <div class="text-success">
                        <i class="bi bi-arrow-down-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Today's Outbound -->
    <div class="col-md-3">
        <div class="card stat-card outbound shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Today's Outbound</h6>
                        <h3 class="mb-0 text-danger" id="today-outbound">KES {{ number_format($todayStats['total_outbound'], 2) }}</h3>
                    </div>
                    <div class="text-danger">
                        <i class="bi bi-arrow-up-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Transactions Today -->
    <div class="col-md-3">
        <div class="card stat-card total shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Transactions Today</h6>
                        <h3 class="mb-0" id="today-count">{{ $todayStats['transaction_count'] }}</h3>
                        <small class="text-success">{{ $todayStats['success_count'] }} successful</small>
                    </div>
                    <div class="text-primary">
                        <i class="bi bi-receipt fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending -->
    <div class="col-md-3">
        <div class="card stat-card pending shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Pending</h6>
                        <h3 class="mb-0 text-warning" id="pending-count">{{ $pendingTransactions }}</h3>
                        @if($failedToday > 0)
                            <small class="text-danger">{{ $failedToday }} failed today</small>
                        @endif
                    </div>
                    <div class="text-warning">
                        <i class="bi bi-hourglass-split fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Transaction Volume Chart -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Transaction Volume (Last 7 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="volumeChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Transaction Type Breakdown -->
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>This Month by Type</h5>
            </div>
            <div class="card-body">
                <canvas id="typeChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <!-- Budget Status -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Budget Status</h5>
                <a href="{{ route('admin.mpesa.budgets.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @forelse($budgets as $budget)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $budget->name }}</span>
                            <span class="badge bg-{{ $budget->getStatusBadgeClass() }}">{{ ucfirst($budget->getStatus()) }}</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-{{ $budget->getStatusBadgeClass() }}" 
                                 role="progressbar" 
                                 style="width: {{ min($budget->getUsedPercentage(), 100) }}%"
                                 aria-valuenow="{{ $budget->getUsedPercentage() }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ number_format($budget->getUsedPercentage(), 1) }}%
                            </div>
                        </div>
                        <small class="text-muted">
                            KES {{ number_format($budget->spent_amount, 2) }} / {{ number_format($budget->budget_limit, 2) }}
                        </small>
                    </div>
                @empty
                    <p class="text-muted mb-0">No active budgets</p>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- Auto-Forwarding Status -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-arrow-repeat me-2"></i>Auto-Forwarding</h5>
                <a href="{{ route('admin.mpesa.forwarding.index') }}" class="btn btn-sm btn-outline-primary">Manage</a>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h4 class="text-primary">{{ $forwardingRules->count() }}</h4>
                        <small class="text-muted">Active Rules</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-success">{{ $forwardingRules->sum('total_forwarded_count') }}</h4>
                        <small class="text-muted">Total Forwarded</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-info">KES {{ number_format($totalForwarded, 0) }}</h4>
                        <small class="text-muted">Amount Forwarded</small>
                    </div>
                </div>
                <hr>
                @forelse($forwardingRules->take(3) as $rule)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>{{ $rule->name }}</span>
                        <span class="badge bg-{{ $rule->is_active ? 'success' : 'secondary' }}">
                            {{ $rule->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                @empty
                    <p class="text-muted mb-0">No forwarding rules configured</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="card shadow-sm mt-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Transactions</h5>
        <a href="{{ route('admin.mpesa.transactions.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody id="recent-transactions">
                    @forelse($recentTransactions as $t)
                        <tr>
                            <td><code>{{ $t->transaction_id ?? $t->id }}</code></td>
                            <td>
                                <span class="badge bg-{{ $t->direction === 'inbound' ? 'success' : 'danger' }}">
                                    {{ strtoupper($t->type) }}
                                </span>
                            </td>
                            <td class="{{ $t->direction === 'inbound' ? 'text-success' : 'text-danger' }}">
                                {{ $t->getFormattedAmount() }}
                            </td>
                            <td>{{ $t->sender_phone ?? $t->receiver_phone ?? '-' }}</td>
                            <td><span class="badge bg-{{ $t->getStatusBadgeClass() }}">{{ ucfirst($t->status) }}</span></td>
                            <td>{{ $t->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No transactions yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Volume Chart
    const volumeCtx = document.getElementById('volumeChart').getContext('2d');
    const chartData = @json($chartData);
    
    new Chart(volumeCtx, {
        type: 'line',
        data: {
            labels: chartData.map(d => d.date),
            datasets: [{
                label: 'Inbound',
                data: chartData.map(d => d.inbound),
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Outbound',
                data: chartData.map(d => d.outbound),
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Type Chart
    const typeCtx = document.getElementById('typeChart').getContext('2d');
    const typeData = @json($typeBreakdown);
    
    new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: ['C2B', 'B2C', 'B2B', 'STK Push', 'Reversal'],
            datasets: [{
                data: [
                    typeData.c2b.count,
                    typeData.b2c.count,
                    typeData.b2b.count,
                    typeData.stk_push.count,
                    typeData.reversal.count
                ],
                backgroundColor: ['#28a745', '#dc3545', '#007bff', '#ffc107', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Auto-refresh every 30 seconds
    function refreshDashboard() {
        fetch('{{ route("admin.mpesa.dashboard.realtime") }}')
            .then(r => r.json())
            .then(data => {
                document.getElementById('today-inbound').textContent = 'KES ' + numberFormat(data.today.total_inbound);
                document.getElementById('today-outbound').textContent = 'KES ' + numberFormat(data.today.total_outbound);
                document.getElementById('today-count').textContent = data.today.transaction_count;
                document.getElementById('pending-count').textContent = data.pending;
            })
            .catch(console.error);
    }

    function numberFormat(num) {
        return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    setInterval(refreshDashboard, 30000);
</script>
@endpush
@endsection
