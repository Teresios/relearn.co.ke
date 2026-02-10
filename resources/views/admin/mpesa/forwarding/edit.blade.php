@extends('admin.mpesa.layout')

@section('title', 'Edit Forwarding Rule - ' . $forwarding->name)

@section('mpesa-content')
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
                                        <label for="source_phone" class="form-label">Source Phone Number</label>
                                        <input type="text" class="form-control" id="source_phone" name="source_phone" 
                                               value="{{ old('source_phone', $forwarding->source_phone) }}" placeholder="254XXXXXXXXX">
                                        <small class="text-muted">Leave empty to match any phone</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="source_shortcode" class="form-label">Source Shortcode</label>
                                        <input type="text" class="form-control" id="source_shortcode" name="source_shortcode" 
                                               value="{{ old('source_shortcode', $forwarding->source_shortcode) }}" placeholder="e.g., 174379">
                                        <small class="text-muted">Leave empty to match any shortcode</small>
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
                                        <label for="destination_shortcode" class="form-label">Destination Shortcode *</label>
                                        <input type="text" class="form-control" id="destination_shortcode" name="destination_shortcode" 
                                               value="{{ old('destination_shortcode', $forwarding->destination_shortcode) }}" required placeholder="e.g., 600996">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="destination_account" class="form-label">Account Number</label>
                                        <input type="text" class="form-control" id="destination_account" name="destination_account" 
                                               value="{{ old('destination_account', $forwarding->destination_account) }}" placeholder="Account reference for paybill">
                                    </div>
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
                                        <label for="forward_percentage" class="form-label">Forward Percentage (%)</label>
                                        <input type="number" class="form-control" id="forward_percentage" name="forward_percentage" 
                                               value="{{ old('forward_percentage', $forwarding->forward_percentage) }}" min="1" max="100" step="0.1" placeholder="100">
                                        <small class="text-muted">Percentage of amount to forward (default: 100%)</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="flat_fee" class="form-label">Flat Fee (KES)</label>
                                        <input type="number" class="form-control" id="flat_fee" name="flat_fee" 
                                               value="{{ old('flat_fee', $forwarding->flat_fee) }}" min="0" step="0.01" placeholder="0">
                                        <small class="text-muted">Fixed amount to deduct before forwarding</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-light mb-3">
                            <div class="card-header">
                                <strong><i class="fas fa-clock text-info me-2"></i>Schedule & Options</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="delay_seconds" class="form-label">Delay (seconds)</label>
                                        <input type="number" class="form-control" id="delay_seconds" name="delay_seconds" 
                                               value="{{ old('delay_seconds', $forwarding->delay_seconds) }}" min="0" placeholder="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Active Hours Start</label>
                                        <input type="time" name="active_hours_start" class="form-control" 
                                               value="{{ old('active_hours_start', $forwarding->active_hours['start'] ?? '') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Active Hours End</label>
                                        <input type="time" name="active_hours_end" class="form-control" 
                                               value="{{ old('active_hours_end', $forwarding->active_hours['end'] ?? '') }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Active Days</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach(['0' => 'Sun', '1' => 'Mon', '2' => 'Tue', '3' => 'Wed', '4' => 'Thu', '5' => 'Fri', '6' => 'Sat'] as $day => $label)
                                            <div class="form-check">
                                                <input type="checkbox" name="active_days[]" value="{{ $day }}" class="form-check-input" id="day_{{ $day }}"
                                                    {{ in_array((int)$day, old('active_days', $forwarding->active_days ?? [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="day_{{ $day }}">{{ $label }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <small class="text-muted">Leave unchecked to allow all days</small>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="auto_forward" name="auto_forward" value="1"
                                           {{ old('auto_forward', $forwarding->auto_forward) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auto_forward">Enable Auto-Forward</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2">{{ old('description', $forwarding->description) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
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
                    <p class="mb-2"><strong>Forwarded:</strong> {{ $forwarding->total_forwarded_count ?? 0 }} times</p>
                    <p class="mb-2"><strong>Total Amount:</strong> KES {{ number_format($forwarding->total_forwarded_amount ?? 0, 2) }}</p>
                    <p class="mb-2"><strong>Last Forward:</strong> {{ $forwarding->last_forward_at ? $forwarding->last_forward_at->format('M d, Y H:i') : 'Never' }}</p>
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
    const percentage = parseFloat(document.getElementById('forward_percentage').value) || 100;
    const flatFee = parseFloat(document.getElementById('flat_fee').value) || 0;
    
    const afterPercentage = amount * (percentage / 100);
    const afterDeduction = Math.max(0, afterPercentage - flatFee);
    
    document.getElementById('calc_original').textContent = amount.toLocaleString();
    document.getElementById('calc_percentage').textContent = afterPercentage.toLocaleString();
    document.getElementById('calc_final').textContent = afterDeduction.toLocaleString();
    document.getElementById('calculation_result').style.display = 'block';
}
</script>
@endpush
@endsection
