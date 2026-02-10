@extends('admin.mpesa.layout')

@section('mpesa-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-arrow-repeat me-2"></i>Auto-Forwarding Rules</h2>
    <a href="{{ route('admin.mpesa.forwarding.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i>Create Rule
    </a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Rule Name</th>
                    <th>Source</th>
                    <th>Destination</th>
                    <th>Forward %</th>
                    <th>Amount Range</th>
                    <th>Stats</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                    <tr class="{{ $rule->is_active ? '' : 'table-secondary' }}">
                        <td>
                            <strong>{{ $rule->name }}</strong>
                            @if($rule->auto_forward)
                                <span class="badge bg-info ms-1">Auto</span>
                            @endif
                            <br>
                            <small class="text-muted">{{ Str::limit($rule->description, 40) }}</small>
                        </td>
                        <td>
                            @if($rule->source_phone)
                                <i class="bi bi-phone me-1"></i>{{ $rule->source_phone }}
                            @elseif($rule->source_shortcode)
                                <i class="bi bi-building me-1"></i>{{ $rule->source_shortcode }}
                            @else
                                <span class="text-muted">Any Source</span>
                            @endif
                        </td>
                        <td>
                            <i class="bi bi-building me-1"></i>{{ $rule->destination_shortcode }}
                            @if($rule->destination_account)
                                <br><small class="text-muted">Acc: {{ $rule->destination_account }}</small>
                            @endif
                        </td>
                        <td>{{ $rule->forward_percentage }}%</td>
                        <td>
                            @if($rule->min_amount || $rule->max_amount)
                                KES {{ number_format($rule->min_amount ?? 0, 0) }} - {{ $rule->max_amount ? number_format($rule->max_amount, 0) : '∞' }}
                            @else
                                <span class="text-muted">No limit</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-success">{{ $rule->total_forwarded_count }}</span> forwarded<br>
                            <small class="text-muted">KES {{ number_format($rule->total_forwarded_amount, 0) }}</small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $rule->getStatusBadgeClass() }}">
                                {{ $rule->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($rule->failed_forward_count > 0)
                                <br><small class="text-danger">{{ $rule->failed_forward_count }} failed</small>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.mpesa.forwarding.show', $rule) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.mpesa.forwarding.edit', $rule) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.mpesa.forwarding.toggle', $rule) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-{{ $rule->is_active ? 'warning' : 'success' }}">
                                        <i class="bi bi-{{ $rule->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.mpesa.forwarding.destroy', $rule) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this rule?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-arrow-repeat display-1 text-muted"></i>
                            <h4 class="mt-3">No Forwarding Rules</h4>
                            <p class="text-muted">Create auto-forwarding rules to automatically transfer payments.</p>
                            <a href="{{ route('admin.mpesa.forwarding.create') }}" class="btn btn-success">
                                <i class="bi bi-plus-lg me-1"></i>Create Rule
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rules->hasPages())
        <div class="card-footer">
            {{ $rules->links() }}
        </div>
    @endif
</div>

<!-- Info Card -->
<div class="card shadow-sm mt-4 border-info">
    <div class="card-body">
        <h5><i class="bi bi-info-circle me-2 text-info"></i>How Auto-Forwarding Works</h5>
        <ol class="mb-0">
            <li>When a C2B payment is received on your personal number or shortcode</li>
            <li>The system checks if it matches any active forwarding rule</li>
            <li>If matched, it automatically forwards the specified percentage to the destination shortcode</li>
            <li>You can set amount limits, active hours, and active days for each rule</li>
        </ol>
    </div>
</div>
@endsection
