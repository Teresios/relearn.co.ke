@extends('admin.mpesa.layout')

@section('mpesa-content')
<div class="mb-4">
    <a href="{{ route('admin.mpesa.budgets.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Budgets
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h4 class="mb-0"><i class="bi bi-plus-lg me-2"></i>Create New Budget</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.mpesa.budgets.store') }}" method="POST">
            @csrf
            
            <div class="row g-4">
                <!-- Basic Info -->
                <div class="col-md-6">
                    <h5 class="border-bottom pb-2 mb-3">Basic Information</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Budget Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Period Type <span class="text-danger">*</span></label>
                        <select name="period_type" class="form-select @error('period_type') is-invalid @enderror" id="periodType" required>
                            <option value="daily" {{ old('period_type') == 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ old('period_type') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('period_type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="custom" {{ old('period_type') == 'custom' ? 'selected' : '' }}>Custom Period</option>
                        </select>
                        @error('period_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row custom-period" style="display: none;">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="period_start" class="form-control" value="{{ old('period_start') }}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="period_end" class="form-control" value="{{ old('period_end') }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Budget Limit (KES) <span class="text-danger">*</span></label>
                        <input type="number" name="budget_limit" class="form-control @error('budget_limit') is-invalid @enderror" value="{{ old('budget_limit') }}" min="0" step="0.01" required>
                        @error('budget_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <!-- Thresholds & Filters -->
                <div class="col-md-6">
                    <h5 class="border-bottom pb-2 mb-3">Thresholds & Filters</h5>

                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Warning Threshold (%)</label>
                                <input type="number" name="warning_threshold" class="form-control" value="{{ old('warning_threshold', 80) }}" min="0" max="100">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Critical Threshold (%)</label>
                                <input type="number" name="critical_threshold" class="form-control" value="{{ old('critical_threshold', 95) }}" min="0" max="100">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Transaction Types (leave empty for all)</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(['c2b' => 'C2B', 'b2c' => 'B2C', 'b2b' => 'B2B', 'stk_push' => 'STK Push'] as $value => $label)
                                <div class="form-check">
                                    <input type="checkbox" name="transaction_types[]" value="{{ $value }}" class="form-check-input" id="type_{{ $value }}" 
                                        {{ in_array($value, old('transaction_types', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="type_{{ $value }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Direction Filter</label>
                        <select name="direction" class="form-select">
                            <option value="">All Directions</option>
                            <option value="inbound" {{ old('direction') == 'inbound' ? 'selected' : '' }}>Inbound Only</option>
                            <option value="outbound" {{ old('direction') == 'outbound' ? 'selected' : '' }}>Outbound Only</option>
                        </select>
                    </div>
                </div>

                <!-- Alerts -->
                <div class="col-12">
                    <h5 class="border-bottom pb-2 mb-3">Alert Settings</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input type="checkbox" name="alerts_enabled" class="form-check-input" id="alertsEnabled" value="1" {{ old('alerts_enabled', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="alertsEnabled">Enable Alerts</label>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alert Emails (comma separated)</label>
                                <input type="text" name="alert_emails" class="form-control" value="{{ old('alert_emails') }}" placeholder="admin@example.com, manager@example.com">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alert Phone Numbers (comma separated)</label>
                                <input type="text" name="alert_phones" class="form-control" value="{{ old('alert_phones') }}" placeholder="254712345678, 254798765432">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mb-2">
                                <input type="checkbox" name="alert_on_warning" class="form-check-input" id="alertWarning" value="1" {{ old('alert_on_warning', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="alertWarning">Alert on Warning Level</label>
                            </div>

                            <div class="form-check form-switch mb-2">
                                <input type="checkbox" name="alert_on_critical" class="form-check-input" id="alertCritical" value="1" {{ old('alert_on_critical', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="alertCritical">Alert on Critical Level</label>
                            </div>

                            <div class="form-check form-switch mb-2">
                                <input type="checkbox" name="alert_on_exceeded" class="form-check-input" id="alertExceeded" value="1" {{ old('alert_on_exceeded', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="alertExceeded">Alert on Budget Exceeded</label>
                            </div>

                            <div class="form-check form-switch mb-2">
                                <input type="checkbox" name="block_on_exceeded" class="form-check-input" id="blockExceeded" value="1" {{ old('block_on_exceeded') ? 'checked' : '' }}>
                                <label class="form-check-label text-danger" for="blockExceeded">
                                    <strong>Block Transactions When Exceeded</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.mpesa.budgets.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-lg me-1"></i>Create Budget
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('periodType').addEventListener('change', function() {
        document.querySelectorAll('.custom-period').forEach(el => {
            el.style.display = this.value === 'custom' ? 'flex' : 'none';
        });
    });
    // Trigger on load
    document.getElementById('periodType').dispatchEvent(new Event('change'));
</script>
@endpush
@endsection
