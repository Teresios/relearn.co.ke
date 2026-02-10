@extends('admin.mpesa.layout')

@section('title', 'Budget Details - ' . $budget->name)

@section('mpesa-content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.mpesa.dashboard') }}">M-Pesa Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.mpesa.budgets.index') }}">Budgets</a></li>
                    <li class="breadcrumb-item active">{{ $budget->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-wallet text-primary me-2"></i>{{ $budget->name }}</h2>
            <p class="text-muted">{{ $budget->description }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.mpesa.budgets.edit', $budget) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <form action="{{ route('admin.mpesa.budgets.toggle', $budget) }}" method="POST" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn {{ $budget->is_active ? 'btn-warning' : 'btn-success' }}">
                    <i class="fas {{ $budget->is_active ? 'fa-pause' : 'fa-play' }} me-1"></i>
                    {{ $budget->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Main Budget Info -->
        <div class="col-md-8">
            <!-- Usage Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-pie text-info me-2"></i>Budget Usage</h5>
                </div>
                <div class="card-body">
                    @php
                        $usage = $budget->budget_limit > 0 ? ($budget->spent_amount / $budget->budget_limit) * 100 : 0;
                        $remaining = max(0, $budget->budget_limit - $budget->spent_amount);
                        $statusClass = $usage >= ($budget->critical_threshold ?? 90) ? 'danger' : 
                                      ($usage >= ($budget->warning_threshold ?? 75) ? 'warning' : 'success');
                    @endphp
                    
                    <div class="row text-center mb-4">
                        <div class="col-md-4">
                            <h3 class="text-primary">KES {{ number_format($budget->budget_limit, 2) }}</h3>
                            <p class="text-muted mb-0">Total Budget</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-{{ $statusClass }}">KES {{ number_format($budget->spent_amount, 2) }}</h3>
                            <p class="text-muted mb-0">Spent</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-success">KES {{ number_format($remaining, 2) }}</h3>
                            <p class="text-muted mb-0">Remaining</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span><strong>Usage Progress</strong></span>
                            <span class="text-{{ $statusClass }}">{{ number_format($usage, 1) }}%</span>
                        </div>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar bg-{{ $statusClass }}" 
                                 style="width: {{ min($usage, 100) }}%"
                                 role="progressbar">
                                {{ number_format($usage, 1) }}%
                            </div>
                        </div>
                    </div>

                    <!-- Threshold Indicators -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="alert alert-warning py-2 mb-0">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Warning at {{ $budget->warning_threshold ?? 75 }}%
                                @if($usage >= ($budget->warning_threshold ?? 75))
                                    <span class="badge bg-warning ms-2">TRIGGERED</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-danger py-2 mb-0">
                                <i class="fas fa-times-circle me-1"></i>
                                Critical at {{ $budget->critical_threshold ?? 90 }}%
                                @if($usage >= ($budget->critical_threshold ?? 90))
                                    <span class="badge bg-danger ms-2">TRIGGERED</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history text-secondary me-2"></i>Recent Transactions</h5>
                    <a href="{{ route('admin.mpesa.transactions.index') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($recentTransactions) && $recentTransactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Transaction ID</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTransactions as $transaction)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.mpesa.transactions.show', $transaction) }}">
                                                    {{ $transaction->transaction_id }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ strtoupper($transaction->transaction_type) }}</span>
                                            </td>
                                            <td>KES {{ number_format($transaction->amount, 2) }}</td>
                                            <td>
                                                @if($transaction->status === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($transaction->status === 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @else
                                                    <span class="badge bg-danger">Failed</span>
                                                @endif
                                            </td>
                                            <td>{{ $transaction->created_at->format('M d, H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No transactions found for this budget period</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Budget Details -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Budget Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Period Type</td>
                            <td><strong>{{ ucfirst($budget->period_type) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Transaction Types</td>
                            <td>
                                @if(!empty($budget->transaction_types))
                                    @foreach($budget->transaction_types as $type)
                                        <span class="badge bg-info">{{ strtoupper($type) }}</span>
                                    @endforeach
                                @else
                                    <span class="badge bg-secondary">All Types</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Direction</td>
                            <td>{{ $budget->direction ? ucfirst($budget->direction) : 'All' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @if($budget->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Alerts</td>
                            <td>{{ $budget->alerts_enabled ? 'Enabled' : 'Disabled' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Alert Emails</td>
                            <td>{{ !empty($budget->alert_emails) ? implode(', ', $budget->alert_emails) : 'Not set' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Block on Exceeded</td>
                            <td>{{ $budget->block_on_exceeded ? 'Yes' : 'No' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created</td>
                            <td>{{ $budget->created_at->format('M d, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header bg-warning">
                    <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.mpesa.budgets.reset', $budget) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning w-100" 
                                onclick="return confirm('Reset the spent amount to zero?')">
                            <i class="fas fa-redo me-1"></i> Reset Spent Amount
                        </button>
                    </form>
                    <form action="{{ route('admin.mpesa.budgets.recalculate', $budget) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-outline-info w-100">
                            <i class="fas fa-calculator me-1"></i> Recalculate
                        </button>
                    </form>
                    <a href="{{ route('admin.mpesa.budgets.edit', $budget) }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-edit me-1"></i> Edit Budget
                    </a>
                    <form action="{{ route('admin.mpesa.budgets.destroy', $budget) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100" 
                                onclick="return confirm('Delete this budget permanently?')">
                            <i class="fas fa-trash me-1"></i> Delete Budget
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
