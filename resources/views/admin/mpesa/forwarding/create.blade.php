@extends('admin.mpesa.layout')

@section('mpesa-content')
<div class="mb-4">
    <a href="{{ route('admin.mpesa.forwarding.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Forwarding Rules
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h4 class="mb-0"><i class="bi bi-plus-lg me-2"></i>Create Forwarding Rule</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.mpesa.forwarding.store') }}" method="POST">
            @csrf
            
            <div class="row g-4">
                <!-- Basic Info -->
                <div class="col-md-6">
                    <h5 class="border-bottom pb-2 mb-3">Basic Information</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Rule Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" name="auto_forward" class="form-check-input" id="autoForward" value="1" {{ old('auto_forward', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="autoForward">Enable Auto-Forward</label>
                    </div>
                </div>

                <!-- Source & Destination -->
                <div class="col-md-6">
                    <h5 class="border-bottom pb-2 mb-3">Source & Destination</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Source Phone Number (optional)</label>
                        <input type="text" name="source_phone" class="form-control" value="{{ old('source_phone') }}" placeholder="254XXXXXXXXX">
                        <small class="text-muted">Leave empty to match any phone</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Source Shortcode (optional)</label>
                        <input type="text" name="source_shortcode" class="form-control" value="{{ old('source_shortcode') }}" placeholder="e.g., 174379">
                        <small class="text-muted">Leave empty to match any shortcode</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Destination Shortcode <span class="text-danger">*</span></label>
                        <input type="text" name="destination_shortcode" class="form-control @error('destination_shortcode') is-invalid @enderror" value="{{ old('destination_shortcode') }}" required placeholder="e.g., 600996">
                        @error('destination_shortcode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Destination Account Reference</label>
                        <input type="text" name="destination_account" class="form-control" value="{{ old('destination_account') }}" maxlength="50">
                    </div>
                </div>

                <!-- Forwarding Settings -->
                <div class="col-md-6">
                    <h5 class="border-bottom pb-2 mb-3">Forwarding Settings</h5>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Min Amount (KES)</label>
                                <input type="number" name="min_amount" class="form-control" value="{{ old('min_amount') }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Max Amount (KES)</label>
                                <input type="number" name="max_amount" class="form-control" value="{{ old('max_amount') }}" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Forward Percentage <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="forward_percentage" class="form-control" value="{{ old('forward_percentage', 100) }}" min="0" max="100" step="0.01" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Flat Fee (KES)</label>
                                <input type="number" name="flat_fee" class="form-control" value="{{ old('flat_fee', 0) }}" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Delay Before Forward (seconds)</label>
                        <input type="number" name="delay_seconds" class="form-control" value="{{ old('delay_seconds', 0) }}" min="0">
                    </div>
                </div>

                <!-- Schedule -->
                <div class="col-md-6">
                    <h5 class="border-bottom pb-2 mb-3">Schedule (optional)</h5>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Active Hours Start</label>
                                <input type="time" name="active_hours_start" class="form-control" value="{{ old('active_hours_start') }}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Active Hours End</label>
                                <input type="time" name="active_hours_end" class="form-control" value="{{ old('active_hours_end') }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Active Days</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(['0' => 'Sun', '1' => 'Mon', '2' => 'Tue', '3' => 'Wed', '4' => 'Thu', '5' => 'Fri', '6' => 'Sat'] as $day => $label)
                                <div class="form-check">
                                    <input type="checkbox" name="active_days[]" value="{{ $day }}" class="form-check-input" id="day_{{ $day }}"
                                        {{ in_array($day, old('active_days', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="day_{{ $day }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted">Leave unchecked to allow all days</small>
                    </div>
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.mpesa.forwarding.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-lg me-1"></i>Create Rule
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
