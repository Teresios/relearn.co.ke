@extends('admin.mpesa.layout')

@section('title', 'Edit Forwarding Rule - ' . $forwarding->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.mpesa.dashboard') }}">M-Pesa Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.mpesa.forwarding.index') }}">Forwarding Rules</a></li>
                    <li class="breadcrumb-item active">Edit {{ $forwarding->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit text-primary me-2"></i>Edit Forwarding Rule</h5>
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

                    <form action="{{ route('admin.mpesa.forwarding.update', $forwarding) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Rule Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="{{ old('name', $forwarding->name) }}" required>
                        </div>

                        <div class="card bg-light mb-3">
                            <div class="card-header">
                                <strong><i class="fas fa-arrow-right text-primary me-2"></i>Source Configuration</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="source_type" class="form-label">Source Type *</label>
                                        <select class="form-select" id="source_type" name="source_type" required>
                                            <option value="personal" {{ old('source_type', $forwarding->source_type) == 'personal' ? 'selected' : '' }}>Personal Number</option>
                                            <option value="shortcode" {{ old('source_type', $forwarding->source_type) == 'shortcode' ? 'selected' : '' }}>Shortcode</option>
                                            <option value="any" {{ old('source_type', $forwarding->source_type) == 'any' ? 'selected' : '' }}>Any Source</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="source_identifier" class="form-label">Source Identifier</label>
                                        <input type="text" class="form-control" id="source_identifier" name="source_identifier" 
                                               value="{{ old('source_identifier', $forwarding->source_identifier) }}" placeholder="e.g., 254712345678 or leave empty for any">
                                        <small class="text-muted">Leave empty to match any source of the selected type</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-light mb-3">
                            <div class="card-header">
                                <strong><i class="fas fa-arrow-left text-success me-2"></i>Destination Configuration</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="destination_type" class="form-label">Destination Type *</label>
                                        <select class="form-select" id="destination_type" name="destination_type" required>
                                            <option value="shortcode" {{ old('destination_type', $forwarding->destination_type) == 'shortcode' ? 'selected' : '' }}>Business Shortcode (Paybill)</option>
                                            <option value="till" {{ old('destination_type', $forwarding->destination_type) == 'till' ? 'selected' : '' }}>Till Number</option>
                                            <option value="phone" {{ old('destination_type', $forwarding->destination_type) == 'phone' ? 'selected' : '' }}>Phone Number</option>
                                            <option value="paybill" {{ old('destination_type', $forwarding->destination_type) == 'paybill' ? 'selected' : '' }}>Paybill (B2B)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="destination_identifier" class="form-label">Destination Identifier *</label>
                                        <input type="text" class="form-control" id="destination_identifier" name="destination_identifier" 
                                               value="{{ old('destination_identifier', $forwarding->destination_identifier) }}" required placeholder="e.g., 123456">
                                    </div>
                                </div>
                                <div class="mb-3" id="destination_account_group">
                                    <label for="destination_account" class="form-label">Account Number</label>
                                    <input type="text" class="form-control" id="destination_account" name="destination_account" 
                                           value="{{ old('destination_account', $forwarding->destination_account) }}" placeholder="Account reference for paybill">
                                </div>
                            </div>
                        </div>

                        <div class="card bg-light mb-3">
                            <div class="card-header">
                                <strong><i class="fas fa-filter text-warning me-2"></i>Amount Filters & Deductions</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="min_amount" class="form-label">Minimum Amount (KES)</label>
                                        <input type="number" class="form-control" id="min_amount" name="min_amount" 
                                               value="{{ old('min_amount', $forwarding->min_amount) }}" min="0" step="0.01" placeholder="0">
                                        <small class="text-muted">Leave empty for no minimum</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="max_amount" class="form-label">Maximum Amount (KES)</label>
                                        <input type="number" class="form-control" id="max_amount" name="max_amount" 
                                               value="{{ old('max_amount', $forwarding->max_amount) }}" min="0" step="0.01" placeholder="Unlimited">
                                        <small class="text-muted">Leave empty for no maximum</small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="percentage" class="form-label">Forward Percentage (%)</label>
                                        <input type="number" class="form-control" id="percentage" name="percentage" 
                                               value="{{ old('percentage', $forwarding->percentage) }}" min="1" max="100" step="0.1" placeholder="100">
                                        <small class="text-muted">Percentage of amount to forward (default: 100%)</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="flat_deduction" class="form-label">Flat Deduction (KES)</label>
                                        <input type="number" class="form-control" id="flat_deduction" name="flat_deduction" 
                                               value="{{ old('flat_deduction', $forwarding->flat_deduction) }}" min="0" step="0.01" placeholder="0">
                                        <small class="text-muted">Fixed amount to deduct before forwarding</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <input type="number" class="form-control" id="priority" name="priority" 
                                       value="{{ old('priority', $forwarding->priority) }}" min="0" max="100" placeholder="0">
                                <small class="text-muted">Higher priority rules are checked first</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2">{{ old('description', $forwarding->description) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $forwarding->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Rule is Active
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Rule
                            </button>
                            <a href="{{ route('admin.mpesa.forwarding.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Rule Statistics</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Times Executed:</strong> {{ $forwarding->times_executed ?? 0 }}</p>
                    <p class="mb-2"><strong>Last Executed:</strong> {{ $forwarding->last_executed_at ? $forwarding->last_executed_at->format('M d, Y H:i') : 'Never' }}</p>
                    <p class="mb-2"><strong>Created:</strong> {{ $forwarding->created_at->format('M d, Y H:i') }}</p>
                    <p class="mb-0"><strong>Updated:</strong> {{ $forwarding->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-warning">
                    <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Forwarding Calculator</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Test Amount (KES)</label>
                        <input type="number" class="form-control" id="test_amount" value="1000" min="1">
                    </div>
                    <button type="button" class="btn btn-warning w-100" onclick="calculateForward()">
                        Calculate
                    </button>
                    <div id="calculation_result" class="mt-3" style="display: none;">
                        <hr>
                        <p class="mb-1"><strong>Original:</strong> KES <span id="calc_original">0</span></p>
                        <p class="mb-1"><strong>After Percentage:</strong> KES <span id="calc_percentage">0</span></p>
                        <p class="mb-1"><strong>After Deduction:</strong> KES <span id="calc_final">0</span></p>
                    </div>
                </div>
            </div>

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
    const amount = parseFloat(document.getElementById('test_amount').value) || 0;
    const percentage = parseFloat(document.getElementById('percentage').value) || 100;
    const flatDeduction = parseFloat(document.getElementById('flat_deduction').value) || 0;
    
    const afterPercentage = amount * (percentage / 100);
    const afterDeduction = Math.max(0, afterPercentage - flatDeduction);
    
    document.getElementById('calc_original').textContent = amount.toLocaleString();
    document.getElementById('calc_percentage').textContent = afterPercentage.toLocaleString();
    document.getElementById('calc_final').textContent = afterDeduction.toLocaleString();
    document.getElementById('calculation_result').style.display = 'block';
}

// Show/hide account field based on destination type
document.getElementById('destination_type').addEventListener('change', function() {
    const accountGroup = document.getElementById('destination_account_group');
    if (this.value === 'phone' || this.value === 'till') {
        accountGroup.style.display = 'none';
    } else {
        accountGroup.style.display = 'block';
    }
});
</script>
@endpush
@endsection
