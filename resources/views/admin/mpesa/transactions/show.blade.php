@extends('admin.mpesa.layout')

@section('mpesa-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.mpesa.transactions.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left"></i> Back to Transactions
        </a>
        <h2><i class="bi bi-receipt me-2"></i>Transaction Details</h2>
    </div>
    <div>
        @if($transaction->canBeReversed())
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#reverseModal">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reverse Transaction
            </button>
        @endif
    </div>
</div>

<div class="row g-4">
    <!-- Main Details -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Transaction Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th class="text-muted">Transaction ID</th>
                                <td><code>{{ $transaction->transaction_id ?? 'N/A' }}</code></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Conversation ID</th>
                                <td><code>{{ $transaction->conversation_id ?? 'N/A' }}</code></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Checkout Request ID</th>
                                <td><code>{{ $transaction->checkout_request_id ?? 'N/A' }}</code></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Type</th>
                                <td><span class="badge bg-secondary">{{ $transaction->getTypeLabel() }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Direction</th>
                                <td>
                                    @if($transaction->isInbound())
                                        <span class="badge bg-success"><i class="bi bi-arrow-down"></i> Inbound</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-arrow-up"></i> Outbound</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th class="text-muted">Amount</th>
                                <td class="fs-4 fw-bold {{ $transaction->isInbound() ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->getFormattedAmount() }}
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Status</th>
                                <td><span class="badge bg-{{ $transaction->getStatusBadgeClass() }} fs-6">{{ ucfirst($transaction->status) }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Result Code</th>
                                <td>{{ $transaction->result_code ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Result Description</th>
                                <td>{{ $transaction->result_desc ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Created At</th>
                                <td>{{ $transaction->created_at->format('M d, Y H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parties -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Parties Involved</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Sender</h6>
                        <p class="mb-1"><strong>Phone:</strong> {{ $transaction->sender_phone ?? 'N/A' }}</p>
                        <p class="mb-0"><strong>Shortcode:</strong> {{ $transaction->sender_shortcode ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Receiver</h6>
                        <p class="mb-1"><strong>Phone:</strong> {{ $transaction->receiver_phone ?? 'N/A' }}</p>
                        <p class="mb-0"><strong>Shortcode:</strong> {{ $transaction->receiver_shortcode ?? 'N/A' }}</p>
                    </div>
                </div>
                @if($transaction->account_reference)
                    <hr>
                    <p class="mb-0"><strong>Account Reference:</strong> {{ $transaction->account_reference }}</p>
                @endif
                @if($transaction->transaction_desc)
                    <p class="mb-0"><strong>Description:</strong> {{ $transaction->transaction_desc }}</p>
                @endif
            </div>
        </div>

        <!-- Payloads -->
        @if($transaction->request_payload || $transaction->response_payload || $transaction->callback_payload)
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">API Payloads</h5>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="payloadTabs" role="tablist">
                    @if($transaction->request_payload)
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#request">Request</button>
                    </li>
                    @endif
                    @if($transaction->response_payload)
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#response">Response</button>
                    </li>
                    @endif
                    @if($transaction->callback_payload)
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#callback">Callback</button>
                    </li>
                    @endif
                </ul>
                <div class="tab-content mt-3">
                    @if($transaction->request_payload)
                    <div class="tab-pane fade show active" id="request">
                        <pre class="bg-light p-3 rounded"><code>{{ json_encode($transaction->request_payload, JSON_PRETTY_PRINT) }}</code></pre>
                    </div>
                    @endif
                    @if($transaction->response_payload)
                    <div class="tab-pane fade" id="response">
                        <pre class="bg-light p-3 rounded"><code>{{ json_encode($transaction->response_payload, JSON_PRETTY_PRINT) }}</code></pre>
                    </div>
                    @endif
                    @if($transaction->callback_payload)
                    <div class="tab-pane fade" id="callback">
                        <pre class="bg-light p-3 rounded"><code>{{ json_encode($transaction->callback_payload, JSON_PRETTY_PRINT) }}</code></pre>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Status Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center">
                <div class="display-1 mb-3">
                    @if($transaction->isSuccessful())
                        <i class="bi bi-check-circle text-success"></i>
                    @elseif($transaction->isPending())
                        <i class="bi bi-hourglass-split text-warning"></i>
                    @elseif($transaction->isFailed())
                        <i class="bi bi-x-circle text-danger"></i>
                    @else
                        <i class="bi bi-question-circle text-secondary"></i>
                    @endif
                </div>
                <h4>{{ ucfirst($transaction->status) }}</h4>
                @if($transaction->is_reversed)
                    <span class="badge bg-dark">Transaction Reversed</span>
                @endif
                @if($transaction->is_forwarded)
                    <span class="badge bg-info">Auto-Forwarded</span>
                @endif
            </div>
        </div>

        <!-- Related Info -->
        @if($transaction->user)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0">Associated User</h6>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Name:</strong> {{ $transaction->user->name }}</p>
                <p class="mb-0"><strong>Email:</strong> {{ $transaction->user->email }}</p>
            </div>
        </div>
        @endif

        @if($transaction->order)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0">Associated Order</h6>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Order ID:</strong> #{{ $transaction->order->id }}</p>
                <p class="mb-0"><strong>Status:</strong> {{ ucfirst($transaction->order->status) }}</p>
                <a href="{{ route('admin.orders.show', $transaction->order) }}" class="btn btn-sm btn-outline-primary mt-2">View Order</a>
            </div>
        </div>
        @endif

        @if($transaction->reversal)
        <div class="card shadow-sm mb-4 border-danger">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0">Reversal Information</h6>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Reversal ID:</strong> {{ $transaction->reversal->transaction_id ?? 'N/A' }}</p>
                <p class="mb-1"><strong>Status:</strong> {{ ucfirst($transaction->reversal->status) }}</p>
                <p class="mb-0"><strong>Reason:</strong> {{ $transaction->reversal->reversal_reason ?? 'N/A' }}</p>
            </div>
        </div>
        @endif

        @if($transaction->reversedTransaction)
        <div class="card shadow-sm mb-4 border-warning">
            <div class="card-header bg-warning">
                <h6 class="mb-0">Original Transaction</h6>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Transaction ID:</strong> {{ $transaction->reversedTransaction->transaction_id ?? 'N/A' }}</p>
                <p class="mb-0"><strong>Amount:</strong> KES {{ number_format($transaction->reversedTransaction->amount, 2) }}</p>
                <a href="{{ route('admin.mpesa.transactions.show', $transaction->reversedTransaction) }}" class="btn btn-sm btn-outline-warning mt-2">View Original</a>
            </div>
        </div>
        @endif

        <!-- Metadata -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">Metadata</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Initiated By</td>
                        <td>{{ $transaction->initiated_by ?? 'System' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">IP Address</td>
                        <td>{{ $transaction->ip_address ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Balance After</td>
                        <td>{{ $transaction->balance_after ? 'KES ' . number_format($transaction->balance_after, 2) : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Reversal Modal -->
@if($transaction->canBeReversed())
<div class="modal fade" id="reverseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.mpesa.transactions.reverse', $transaction) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reverse Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        This action will attempt to reverse the transaction. This cannot be undone.
                    </div>
                    <p><strong>Transaction ID:</strong> {{ $transaction->transaction_id }}</p>
                    <p><strong>Amount:</strong> KES {{ number_format($transaction->amount, 2) }}</p>
                    <div class="mb-3">
                        <label class="form-label">Reason for Reversal <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Please provide a reason for this reversal..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reverse Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
