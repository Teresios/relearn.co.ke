@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Order #{{ $order->id }}</h1>
                    <p class="text-muted">Order Details</p>
                </div>
                <div>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back to Orders
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Order Information -->
        <div class="col-lg-8">
            <!-- Order Details -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Order Information</h5>
                    @if($order->status === 'completed')
                        <span class="badge bg-success fs-6">Completed</span>
                    @elseif($order->status === 'pending')
                        <span class="badge bg-warning fs-6">Pending</span>
                    @elseif($order->status === 'failed')
                        <span class="badge bg-danger fs-6">Failed</span>
                    @else
                        <span class="badge bg-secondary fs-6">{{ ucfirst($order->status) }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Order ID:</strong></td>
                                    <td>#{{ $order->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Date:</strong></td>
                                    <td>{{ $order->created_at->format('F d, Y g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($order->status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($order->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($order->status === 'failed')
                                            <span class="badge bg-danger">Failed</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Amount:</strong></td>
                                    <td><strong class="text-success">KES {{ number_format($order->amount, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Payment Method:</strong></td>
                                    <td>{{ $order->payment_method ? ucfirst($order->payment_method) : 'Not specified' }}</td>
                                </tr>
                                @if($order->mpesa_checkout_request_id)
                                <tr>
                                    <td><strong>M-Pesa Request ID:</strong></td>
                                    <td><code>{{ $order->mpesa_checkout_request_id }}</code></td>
                                </tr>
                                @endif
                                @if($order->mpesa_receipt_number)
                                <tr>
                                    <td><strong>M-Pesa Receipt:</strong></td>
                                    <td><code>{{ $order->mpesa_receipt_number }}</code></td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $order->updated_at->format('F d, Y g:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Customer Details</h6>
                            <p><strong>Name:</strong> {{ $order->user?->name ?? 'Guest / Deleted User' }}</p>
                            <p><strong>Email:</strong> {{ $order->customer_email ?? $order->user?->email ?? 'Not available' }}</p>
                            <p><strong>Phone:</strong> {{ $order->payment_phone ?? $order->phone_number ?? $order->user?->phone ?? 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Customer Stats</h6>
                            <p><strong>Total Orders:</strong> {{ $order->user?->orders?->count() ?? 1 }}</p>
                            <p>
                                <strong>Total Spent:</strong>
                                KES {{ number_format($order->user?->orders?->where('status', 'completed')->sum('amount') ?? $order->amount, 2) }}
                            </p>
                            <p><strong>Member Since:</strong> {{ $order->user?->created_at?->format('F Y') ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Product Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            @if($order->product && $order->product->image_path)
                                <img src="{{ asset('storage/' . $order->product->image_path) }}"
                                     alt="{{ $order->product->name }}"
                                     class="img-fluid rounded">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                     style="height: 150px;">
                                    <i class="bi bi-file-earmark display-4 text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <h6>{{ $order->product?->name ?? 'Product not found' }}</h6>
                            <p class="text-muted">{{ $order->product?->description ?? 'No product description available.' }}</p>
                            <div class="row">
                                <div class="col-sm-6">
                                    <p>
                                        <strong>Category:</strong>
                                        <span class="badge bg-secondary">{{ ucfirst($order->product?->category ?? 'N/A') }}</span>
                                    </p>
                                    <p><strong>Price:</strong> KES {{ number_format($order->product?->price ?? 0, 2) }}</p>
                                </div>
                                <div class="col-sm-6">
                                    <p><strong>Status:</strong>
                                        @if($order->product?->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @elseif($order->product)
                                            <span class="badge bg-warning">Inactive</span>
                                        @else
                                            <span class="badge bg-danger">Product Missing</span>
                                        @endif
                                    </p>

                                    @if($order->product)
                                        <a href="{{ route('admin.products.show', $order->product) }}" class="btn btn-sm btn-outline-primary">
                                            View Product
                                        </a>
                                    @else
                                        <span class="text-danger">Product not available</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Download History -->
            @if($order->downloads->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Download History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Token</th>
                                    <th>Downloaded At</th>
                                    <th>IP Address</th>
                                    <th>Expires</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->downloads as $download)
                                <tr>
                                    <td><code>{{ Str::limit($download->token, 20) }}</code></td>
                                    <td>
                                        {{ $download->downloaded_at
                                            ? $download->downloaded_at->format('M d, Y g:i A')
                                            : 'Not downloaded'
                                        }}
                                    </td>
                                    <td>{{ $download->ip_address ?? '-' }}</td>
                                    <td>{{ $download->expires_at->format('M d, Y g:i A') }}</td>
                                    <td>
                                        @if($download->downloaded_at)
                                            <span class="badge bg-success">Downloaded</span>
                                        @elseif($download->expires_at->isPast())
                                            <span class="badge bg-danger">Expired</span>
                                        @else
                                            <span class="badge bg-warning">Active</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    @if($order->status === 'pending')
                        <div class="d-grid gap-2">
                            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Mark as Completed
                                </button>
                            </form>
                            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="failed">
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Mark as Failed
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($order->status === 'completed')
                        <form id="generate-link-form" action="{{ route('downloads.generate') }}" method="POST">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                            <button type="submit" class="btn btn-outline-primary w-100" id="generate-link-btn">
                                <i class="bi bi-download me-1"></i>
                                Generate New Download Link
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Payment Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Payment Details</h6>
                </div>
                <div class="card-body">
                    @forelse($order->payments ?? [] as $payment)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between">
                                <strong>{{ ucfirst($payment->payment_method) }}</strong>
                                <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </div>
                            <small class="text-muted">Amount: KES {{ number_format($payment->amount, 2) }}</small><br>
                            @if($payment->transaction_id)
                                <small class="text-muted">Transaction: {{ $payment->transaction_id }}</small><br>
                            @endif
                            <small class="text-muted">{{ $payment->created_at->format('M d, Y g:i A') }}</small>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No payments found.</p>
                    @endforelse
                </div>
            </div>

            <!-- Order Timeline -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Order Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Order Created</h6>
                                <p class="timeline-text">{{ $order->created_at->format('M d, Y g:i A') }}</p>
                            </div>
                        </div>

                        @if($order->updated_at != $order->created_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'failed' ? 'danger' : 'warning') }}"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Status Updated</h6>
                                <p class="timeline-text">{{ $order->updated_at->format('M d, Y g:i A') }}</p>
                                <small class="text-muted">Status: {{ ucfirst($order->status) }}</small>
                            </div>
                        </div>
                        @endif

                        @if($order->downloads->count() > 0)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Download Links Generated</h6>
                                <p class="timeline-text">{{ $order->downloads->count() }} download(s)</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
