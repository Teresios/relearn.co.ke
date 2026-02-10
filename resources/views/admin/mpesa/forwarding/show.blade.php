@extends('admin.mpesa.layout')

@section('title', 'Forwarding Rule - ' . $forwarding->name)

@section('mpesa-content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.mpesa.dashboard') }}">M-Pesa Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.mpesa.forwarding.index') }}">Forwarding Rules</a></li>
                    <li class="breadcrumb-item active">{{ $forwarding->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="fas fa-share text-primary me-2"></i>{{ $forwarding->name }}
                @if($forwarding->is_active)
                    <span class="badge bg-success ms-2">Active</span>
                @else
                    <span class="badge bg-secondary ms-2">Inactive</span>
                @endif
            </h2>
            <p class="text-muted">{{ $forwarding->description }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.mpesa.forwarding.edit', $forwarding) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <form action="{{ route('admin.mpesa.forwarding.toggle', $forwarding) }}" method="POST" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn {{ $forwarding->is_active ? 'btn-warning' : 'btn-success' }}">
                    <i class="fas {{ $forwarding->is_active ? 'fa-pause' : 'fa-play' }} me-1"></i>
                    {{ $forwarding->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Flow Diagram Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-project-diagram text-info me-2"></i>Forwarding Flow</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Source -->
                        <div class="col-md-4 text-center">
                            <div class="border rounded p-3 bg-light">
                                <i class="fas {{ $forwarding->source_phone ? 'fa-user' : 'fa-building' }} fa-3x text-primary mb-2"></i>
                                <h6 class="mb-1">Source</h6>
                                @if($forwarding->source_phone)
                                    <p class="mb-0"><strong>Phone</strong></p>
                                    <small class="text-muted">{{ $forwarding->source_phone }}</small>
                                @elseif($forwarding->source_shortcode)
                                    <p class="mb-0"><strong>Shortcode</strong></p>
                                    <small class="text-muted">{{ $forwarding->source_shortcode }}</small>
                                @else
                                    <p class="mb-0"><strong>Any Source</strong></p>
                                    <small class="text-muted">All incoming</small>
                                @endif
                            </div>
                        </div>
                        <!-- Arrow -->
                        <div class="col-md-4 text-center">
                            <div class="py-3">
                                <i class="fas fa-long-arrow-alt-right fa-3x text-success"></i>
                                <div class="mt-2">
                                    @if($forwarding->forward_percentage && $forwarding->forward_percentage < 100)
                                        <span class="badge bg-warning">{{ $forwarding->forward_percentage }}%</span>
                                    @endif
                                    @if($forwarding->flat_fee)
                                        <span class="badge bg-info">-KES {{ number_format($forwarding->flat_fee, 2) }}</span>
                                    @endif
                                    @if((!$forwarding->forward_percentage || $forwarding->forward_percentage == 100) && !$forwarding->flat_fee)
                                        <span class="badge bg-success">100%</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!-- Destination -->
                        <div class="col-md-4 text-center">
                            <div class="border rounded p-3 bg-light">
                                <i class="fas fa-store fa-3x text-success mb-2"></i>
                                <h6 class="mb-1">Destination</h6>
                                <p class="mb-0"><strong>Shortcode</strong></p>
                                <small class="text-muted">{{ $forwarding->destination_shortcode }}</small>
                                @if($forwarding->destination_account)
                                    <br><small class="text-muted">Acc: {{ $forwarding->destination_account }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amount Rules Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter text-warning me-2"></i>Amount Rules</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted">Minimum Amount</td>
                                    <td><strong>{{ $forwarding->min_amount ? 'KES ' . number_format($forwarding->min_amount, 2) : 'No limit' }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Maximum Amount</td>
                                    <td><strong>{{ $forwarding->max_amount ? 'KES ' . number_format($forwarding->max_amount, 2) : 'No limit' }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted">Forward Percentage</td>
                                    <td><strong>{{ $forwarding->forward_percentage ?? 100 }}%</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Flat Fee</td>
                                    <td><strong>{{ $forwarding->flat_fee ? 'KES ' . number_format($forwarding->flat_fee, 2) : 'None' }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Calculator -->
                    <hr>
                    <h6><i class="fas fa-calculator me-2"></i>Quick Calculator</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">KES</span>
                                <input type="number" class="form-control" id="calc_input" value="1000" min="1">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary" onclick="calculateForward()">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-success py-2 mb-0">
                                <strong>Forward Amount:</strong> KES <span id="calc_result">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Forwarded Transactions -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history text-secondary me-2"></i>Recent Forwarded Transactions</h5>
                    <a href="{{ route('admin.mpesa.transactions.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    @if(isset($forwardedTransactions) && $forwardedTransactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Transaction ID</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($forwardedTransactions as $transaction)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.mpesa.transactions.show', $transaction) }}">
                                                    {{ Str::limit($transaction->transaction_id, 15) }}
                                                </a>
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
                            <p class="text-muted">No forwarded transactions yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Statistics Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <h3 class="text-primary mb-0">{{ $forwarding->total_forwarded_count ?? 0 }}</h3>
                            <small class="text-muted">Times Forwarded</small>
                        </div>
                        <div class="col-6">
                            <h3 class="text-success mb-0">KES {{ number_format($forwarding->total_forwarded_amount ?? 0, 0) }}</h3>
                            <small class="text-muted">Total Amount</small>
                        </div>
                    </div>
                    @if($forwarding->failed_forward_count > 0)
                    <div class="text-center mb-3">
                        <span class="text-danger">{{ $forwarding->failed_forward_count }} failed</span>
                        <span class="text-muted"> | {{ $forwarding->getSuccessRate() }}% success rate</span>
                    </div>
                    @endif
                    <hr>
                    <p class="mb-2">
                        <i class="fas fa-clock text-muted me-2"></i>
                        <strong>Last Forward:</strong><br>
                        {{ $forwarding->last_forward_at ? $forwarding->last_forward_at->format('M d, Y H:i') : 'Never' }}
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-robot text-muted me-2"></i>
                        <strong>Auto-Forward:</strong> {{ $forwarding->auto_forward ? 'Enabled' : 'Disabled' }}
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-calendar-plus text-muted me-2"></i>
                        <strong>Created:</strong><br>
                        {{ $forwarding->created_at->format('M d, Y H:i') }}
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-calendar-check text-muted me-2"></i>
                        <strong>Last Updated:</strong><br>
                        {{ $forwarding->updated_at->format('M d, Y H:i') }}
                    </p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.mpesa.forwarding.test', $forwarding) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-outline-info w-100">
                            <i class="fas fa-vial me-1"></i> Test Rule (Dry Run)
                        </button>
                    </form>
                    <a href="{{ route('admin.mpesa.forwarding.edit', $forwarding) }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-edit me-1"></i> Edit Rule
                    </a>
                    <form action="{{ route('admin.mpesa.forwarding.toggle', $forwarding) }}" method="POST" class="mb-2">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-{{ $forwarding->is_active ? 'warning' : 'success' }} w-100">
                            <i class="fas {{ $forwarding->is_active ? 'fa-pause' : 'fa-play' }} me-1"></i>
                            {{ $forwarding->is_active ? 'Deactivate Rule' : 'Activate Rule' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.mpesa.forwarding.destroy', $forwarding) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100" 
                                onclick="return confirm('Delete this forwarding rule? This action cannot be undone.')">
                            <i class="fas fa-trash me-1"></i> Delete Rule
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function calculateForward() {
    const amount = parseFloat(document.getElementById('calc_input').value) || 0;
    const percentage = {{ $forwarding->forward_percentage ?? 100 }};
    const flatFee = {{ $forwarding->flat_fee ?? 0 }};
    
    const afterPercentage = amount * (percentage / 100);
    const afterDeduction = Math.max(0, afterPercentage - flatFee);
    
    document.getElementById('calc_result').textContent = afterDeduction.toLocaleString();
}

// Calculate on load
calculateForward();
</script>
@endpush
@endsection
