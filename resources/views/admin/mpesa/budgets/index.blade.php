@extends('admin.mpesa.layout')

@section('mpesa-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-wallet2 me-2"></i>Budget Management</h2>
    <a href="{{ route('admin.mpesa.budgets.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i>Create Budget
    </a>
</div>

<div class="row g-4">
    @forelse($budgets as $budget)
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100 {{ $budget->is_active ? '' : 'bg-light' }}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $budget->name }}</h5>
                    <span class="badge bg-{{ $budget->getStatusBadgeClass() }}">{{ ucfirst($budget->getStatus()) }}</span>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">{{ $budget->description ?? 'No description' }}</p>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ number_format($budget->getUsedPercentage(), 1) }}% Used</span>
                            <span class="text-muted">{{ ucfirst($budget->period_type) }}</span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-{{ $budget->getStatusBadgeClass() }}" 
                                 role="progressbar" 
                                 style="width: {{ min($budget->getUsedPercentage(), 100) }}%">
                            </div>
                        </div>
                    </div>

                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <h5 class="mb-0">KES {{ number_format($budget->spent_amount, 0) }}</h5>
                            <small class="text-muted">Spent</small>
                        </div>
                        <div class="col-6">
                            <h5 class="mb-0">KES {{ number_format($budget->budget_limit, 0) }}</h5>
                            <small class="text-muted">Limit</small>
                        </div>
                    </div>

                    <div class="small text-muted">
                        <i class="bi bi-bell me-1"></i>
                        Alerts: {{ $budget->alerts_enabled ? 'Enabled' : 'Disabled' }}
                        @if($budget->block_on_exceeded)
                            | <span class="text-danger">Blocks on exceed</span>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="btn-group w-100">
                        <a href="{{ route('admin.mpesa.budgets.show', $budget) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('admin.mpesa.budgets.edit', $budget) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.mpesa.budgets.toggle', $budget) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-{{ $budget->is_active ? 'warning' : 'success' }} btn-sm">
                                <i class="bi bi-{{ $budget->is_active ? 'pause' : 'play' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.mpesa.budgets.destroy', $budget) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this budget?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-wallet2 display-1 text-muted"></i>
                    <h4 class="mt-3">No Budgets Created</h4>
                    <p class="text-muted">Create your first budget to start tracking M-Pesa spending.</p>
                    <a href="{{ route('admin.mpesa.budgets.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-lg me-1"></i>Create Budget
                    </a>
                </div>
            </div>
        </div>
    @endforelse
</div>

@if($budgets->hasPages())
    <div class="mt-4">
        {{ $budgets->links() }}
    </div>
@endif
@endsection
