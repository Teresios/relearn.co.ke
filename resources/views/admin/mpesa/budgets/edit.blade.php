@extends('admin.mpesa.layout')

@section('title', 'Edit Budget - ' . $budget->name)

@section('mpesa-content')
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
                                <label for="period_type" class="form-label">Period Type *</label>
                                <select class="form-select" id="period_type" name="period_type" required>
                                    <option value="daily" {{ old('period_type', $budget->period_type) == 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ old('period_type', $budget->period_type) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ old('period_type', $budget->period_type) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="custom" {{ old('period_type', $budget->period_type) == 'custom' ? 'selected' : '' }}>Custom</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Transaction Types</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach(['c2b' => 'C2B', 'b2c' => 'B2C', 'b2b' => 'B2B', 'stk_push' => 'STK Push'] as $value => $label)
                                        <div class="form-check">
                                            <input type="checkbox" name="transaction_types[]" value="{{ $value }}" class="form-check-input" id="type_{{ $value }}" 
                                                {{ in_array($value, old('transaction_types', $budget->transaction_types ?? [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="type_{{ $value }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Leave unchecked for all types</small>
                            </div>
                        </div>

                        <div class="row custom-period" style="{{ old('period_type', $budget->period_type) == 'custom' ? '' : 'display: none;' }}">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="period_start" class="form-control" value="{{ old('period_start', $budget->period_start?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="period_end" class="form-control" value="{{ old('period_end', $budget->period_end?->format('Y-m-d')) }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="budget_limit" class="form-label">Budget Limit (KES) *</label>
                                <input type="number" class="form-control" id="budget_limit" name="budget_limit" 
                                       value="{{ old('budget_limit', $budget->budget_limit) }}" min="1" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Direction Filter</label>
                                <select name="direction" class="form-select">
                                    <option value="">All Directions</option>
                                    <option value="inbound" {{ old('direction', $budget->direction) == 'inbound' ? 'selected' : '' }}>Inbound Only</option>
                                    <option value="outbound" {{ old('direction', $budget->direction) == 'outbound' ? 'selected' : '' }}>Outbound Only</option>
                                </select>
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

                        <h6 class="border-bottom pb-2 mb-3 mt-3">Alert Settings</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="alert_emails" class="form-label">Alert Emails (comma separated)</label>
                                <input type="text" class="form-control" id="alert_emails" name="alert_emails" 
                                       value="{{ old('alert_emails', is_array($budget->alert_emails) ? implode(', ', $budget->alert_emails) : $budget->alert_emails) }}" 
                                       placeholder="admin@example.com, manager@example.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="alert_phones" class="form-label">Alert Phones (comma separated)</label>
                                <input type="text" class="form-control" id="alert_phones" name="alert_phones" 
                                       value="{{ old('alert_phones', is_array($budget->alert_phones) ? implode(', ', $budget->alert_phones) : $budget->alert_phones) }}" 
                                       placeholder="254712345678, 254798765432">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $budget->description) }}</textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                           {{ old('is_active', $budget->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Budget is Active</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="alerts_enabled" name="alerts_enabled" value="1"
                                           {{ old('alerts_enabled', $budget->alerts_enabled) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="alerts_enabled">Enable Alerts</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="alert_on_warning" name="alert_on_warning" value="1"
                                           {{ old('alert_on_warning', $budget->alert_on_warning) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="alert_on_warning">Alert on Warning</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="alert_on_critical" name="alert_on_critical" value="1"
                                           {{ old('alert_on_critical', $budget->alert_on_critical) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="alert_on_critical">Alert on Critical</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="alert_on_exceeded" name="alert_on_exceeded" value="1"
                                           {{ old('alert_on_exceeded', $budget->alert_on_exceeded) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="alert_on_exceeded">Alert on Exceeded</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="block_on_exceeded" name="block_on_exceeded" value="1"
                                           {{ old('block_on_exceeded', $budget->block_on_exceeded) ? 'checked' : '' }}>
                                    <label class="form-check-label text-danger" for="block_on_exceeded"><strong>Block Transactions When Exceeded</strong></label>
                                </div>
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
                        $usage = $budget->budget_limit > 0 ? ($budget->spent_amount / $budget->budget_limit) * 100 : 0;
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
                    <p class="mb-1"><strong>Remaining:</strong> KES {{ number_format(max(0, $budget->budget_limit - $budget->spent_amount), 2) }}</p>
                    <p class="mb-0"><strong>Transactions:</strong> {{ $budget->transaction_count ?? 0 }}</p>
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

@push('scripts')
<script>
    document.getElementById('period_type').addEventListener('change', function() {
        document.querySelectorAll('.custom-period').forEach(el => {
            el.style.display = this.value === 'custom' ? 'flex' : 'none';
        });
    });
</script>
@endpush
@endsection
