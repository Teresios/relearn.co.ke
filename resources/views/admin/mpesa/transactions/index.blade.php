@extends('admin.mpesa.layout')

@section('mpesa-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-arrow-left-right me-2"></i>Transactions</h2>
    <div>
        <a href="{{ route('admin.mpesa.transactions.export', request()->query()) }}" class="btn btn-outline-success">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.mpesa.transactions.index') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search ID, phone, reference..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    @foreach($types as $value => $label)
                        <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="direction" class="form-select">
                    <option value="">All Directions</option>
                    <option value="inbound" {{ request('direction') == 'inbound' ? 'selected' : '' }}>Inbound</option>
                    <option value="outbound" {{ request('direction') == 'outbound' ? 'selected' : '' }}>Outbound</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" placeholder="From Date" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Transactions Table -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Transaction ID</th>
                        <th>Type</th>
                        <th>Direction</th>
                        <th>Amount</th>
                        <th>Sender</th>
                        <th>Receiver</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                        <tr>
                            <td>
                                <code>{{ $t->transaction_id ?? 'N/A' }}</code>
                                @if($t->is_reversed)
                                    <span class="badge bg-dark ms-1">Reversed</span>
                                @endif
                                @if($t->is_forwarded)
                                    <span class="badge bg-info ms-1">Forwarded</span>
                                @endif
                            </td>
                            <td><span class="badge bg-secondary">{{ strtoupper($t->type) }}</span></td>
                            <td>
                                @if($t->direction === 'inbound')
                                    <span class="text-success"><i class="bi bi-arrow-down"></i> Inbound</span>
                                @else
                                    <span class="text-danger"><i class="bi bi-arrow-up"></i> Outbound</span>
                                @endif
                            </td>
                            <td class="{{ $t->direction === 'inbound' ? 'text-success' : 'text-danger' }} fw-bold">
                                {{ $t->getFormattedAmount() }}
                            </td>
                            <td>{{ $t->sender_phone ?? $t->sender_shortcode ?? '-' }}</td>
                            <td>{{ $t->receiver_phone ?? $t->receiver_shortcode ?? '-' }}</td>
                            <td><span class="badge bg-{{ $t->getStatusBadgeClass() }}">{{ ucfirst($t->status) }}</span></td>
                            <td>{{ $t->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.mpesa.transactions.show', $t) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($t->canBeReversed())
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reverseModal{{ $t->id }}">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>

                        <!-- Reversal Modal -->
                        @if($t->canBeReversed())
                        <div class="modal fade" id="reverseModal{{ $t->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.mpesa.transactions.reverse', $t) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reverse Transaction</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to reverse this transaction?</p>
                                            <p><strong>Amount:</strong> KES {{ number_format($t->amount, 2) }}</p>
                                            <p><strong>Transaction ID:</strong> {{ $t->transaction_id }}</p>
                                            <div class="mb-3">
                                                <label class="form-label">Reason for Reversal</label>
                                                <textarea name="reason" class="form-control" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Reverse Transaction</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No transactions found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($transactions->hasPages())
        <div class="card-footer">
            {{ $transactions->links() }}
        </div>
    @endif
</div>

<!-- Initiate Payment Modals -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
    <div class="btn-group-vertical">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#b2cModal">
            <i class="bi bi-person-plus"></i> B2C
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#b2bModal">
            <i class="bi bi-building"></i> B2B
        </button>
    </div>
</div>

<!-- B2C Modal -->
<div class="modal fade" id="b2cModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.mpesa.transactions.b2c') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Send B2C Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="254XXXXXXXXX" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (KES)</label>
                        <input type="number" name="amount" class="form-control" min="10" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" class="form-control" maxlength="100">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Send Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- B2B Modal -->
<div class="modal fade" id="b2bModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.mpesa.transactions.b2b') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Send B2B Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Receiver Shortcode</label>
                        <input type="text" name="shortcode" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (KES)</label>
                        <input type="number" name="amount" class="form-control" min="10" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Reference</label>
                        <input type="text" name="account_reference" class="form-control" maxlength="13">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" class="form-control" maxlength="100">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
