@extends('admin.mpesa.layout')

@section('title', 'Edit Budget - ' . $budget->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.mpesa.dashboard') }}">M-Pesa Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.mpesa.budgets.index') }}">Budgets</a></li>
                    <li class="breadcrumb-item active">Edit {{ $budget->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit text-primary me-2"></i>Edit Budget</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.mpesa.budgets.update', $budget) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Budget Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="{{ old('name', $budget->name) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Budget Type *</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="daily" {{ old('type', $budget->type) == 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ old('type', $budget->type) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ old('type', $budget->type) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="transaction_type" class="form-label">Transaction Type *</label>
                                <select class="form-select" id="transaction_type" name="transaction_type" required>
                                    <option value="">All Types</option>
                                    <option value="c2b" {{ old('transaction_type', $budget->transaction_type) == 'c2b' ? 'selected' : '' }}>C2B (Customer to Business)</option>
                                    <option value="b2c" {{ old('transaction_type', $budget->transaction_type) == 'b2c' ? 'selected' : '' }}>B2C (Business to Customer)</option>
                                    <option value="b2b" {{ old('transaction_type', $budget->transaction_type) == 'b2b' ? 'selected' : '' }}>B2B (Business to Business)</option>
                                    <option value="stk_push" {{ old('transaction_type', $budget->transaction_type) == 'stk_push' ? 'selected' : '' }}>STK Push</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="limit_amount" class="form-label">Budget Limit (KES) *</label>
                                <input type="number" class="form-control" id="limit_amount" name="limit_amount" 
                                       value="{{ old('limit_amount', $budget->limit_amount) }}" min="1" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="spent_amount" class="form-label">Current Spent Amount (KES)</label>
                                <input type="number" class="form-control" id="spent_amount" name="spent_amount" 
                                       value="{{ old('spent_amount', $budget->spent_amount) }}" min="0" step="0.01">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="warning_threshold" class="form-label">Warning Threshold (%)</label>
                                <input type="number" class="form-control" id="warning_threshold" name="warning_threshold" 
                                       value="{{ old('warning_threshold', $budget->warning_threshold) }}" min="1" max="99" placeholder="75">
                                <small class="text-muted">Alert when budget reaches this percentage</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="critical_threshold" class="form-label">Critical Threshold (%)</label>
                                <input type="number" class="form-control" id="critical_threshold" name="critical_threshold" 
                                       value="{{ old('critical_threshold', $budget->critical_threshold) }}" min="1" max="100" placeholder="90">
                                <small class="text-muted">Critical alert threshold</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="reset_day" class="form-label">Reset Day</label>
                                <input type="number" class="form-control" id="reset_day" name="reset_day" 
                                       value="{{ old('reset_day', $budget->reset_day) }}" min="1" max="31" placeholder="1">
                                <small class="text-muted">Day of month for monthly reset, or day of week (1=Mon) for weekly</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="reset_time" class="form-label">Reset Time</label>
                                <input type="time" class="form-control" id="reset_time" name="reset_time" 
                                       value="{{ old('reset_time', $budget->reset_time) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="alert_email" class="form-label">Alert Email</label>
                            <input type="email" class="form-control" id="alert_email" name="alert_email" 
                                   value="{{ old('alert_email', $budget->alert_email) }}" placeholder="admin@example.com">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $budget->description) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $budget->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Budget is Active
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="auto_reset" name="auto_reset" value="1"
                                       {{ old('auto_reset', $budget->auto_reset) ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_reset">
                                    Auto-reset budget at period end
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Budget
                            </button>
                            <a href="{{ route('admin.mpesa.budgets.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Current Status</h6>
                </div>
                <div class="card-body">
                    @php
                        $usage = $budget->limit_amount > 0 ? ($budget->spent_amount / $budget->limit_amount) * 100 : 0;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Budget Usage</span>
                            <span>{{ number_format($usage, 1) }}%</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar {{ $usage >= ($budget->critical_threshold ?? 90) ? 'bg-danger' : ($usage >= ($budget->warning_threshold ?? 75) ? 'bg-warning' : 'bg-success') }}" 
                                 style="width: {{ min($usage, 100) }}%">
                            </div>
                        </div>
                    </div>
                    <p class="mb-1"><strong>Spent:</strong> KES {{ number_format($budget->spent_amount, 2) }}</p>
                    <p class="mb-1"><strong>Remaining:</strong> KES {{ number_format(max(0, $budget->limit_amount - $budget->spent_amount), 2) }}</p>
                    <p class="mb-0"><strong>Last Reset:</strong> {{ $budget->last_reset_at ? $budget->last_reset_at->format('M d, Y H:i') : 'Never' }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-warning">
                    <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.mpesa.budgets.reset', $budget) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning w-100" onclick="return confirm('Reset this budget to zero?')">
                            <i class="fas fa-redo me-1"></i> Reset Spent Amount
                        </button>
                    </form>
                    <form action="{{ route('admin.mpesa.budgets.recalculate', $budget) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-outline-info w-100">
                            <i class="fas fa-calculator me-1"></i> Recalculate from Transactions
                        </button>
                    </form>
                    <form action="{{ route('admin.mpesa.budgets.destroy', $budget) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Delete this budget? This action cannot be undone.')">
                            <i class="fas fa-trash me-1"></i> Delete Budget
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
