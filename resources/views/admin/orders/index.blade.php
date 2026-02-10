@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">Orders</h1>
            <p class="text-muted">Manage customer orders and payments</p>
        </div>
    </div>

    <!-- Stats -->
    @php
        $totalOrders  = $stats['total'] ?? 0;
        $completed    = $stats['completed'] ?? 0;
        $pending      = $stats['pending'] ?? 0;
        $revenue      = $stats['total_revenue'] ?? 0;
    @endphp

    <div class="row mb-4">
        @foreach([
            ['label'=>'Total Orders','value'=>$totalOrders,'class'=>'text-primary'],
            ['label'=>'Completed','value'=>$completed,'class'=>'text-success'],
            ['label'=>'Pending','value'=>$pending,'class'=>'text-warning'],
            ['label'=>'Total Revenue','value'=>'KES '.number_format($revenue,2),'class'=>'text-info'],
        ] as $stat)
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="{{ $stat['class'] }}">{{ $stat['value'] }}</h5>
                        <p class="text-muted mb-0">{{ $stat['label'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.orders.index') }}">
                <div class="row g-3">

                    <!-- Search -->
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Order ID, customer name..."
                            value="{{ request('search') }}"
                        >
                    </div>

                    <!-- Status -->
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach(['pending','completed','failed','cancelled'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Product -->
                    <div class="col-md-2">
                        <label class="form-label">Product</label>
                        <select name="product" class="form-select">
                            <option value="">All Products</option>
                            @foreach($products ?? [] as $product)
                                <option value="{{ $product->id }}" @selected(request('product') == $product->id)>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Divider -->
                    <div class="col-12"></div>

                    <!-- Date From -->
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input
                            type="date"
                            name="date_from"
                            class="form-control"
                            value="{{ request('date_from') }}"
                        >
                    </div>

                    <!-- Date To -->
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input
                            type="date"
                            name="date_to"
                            class="form-control"
                            value="{{ request('date_to') }}"
                        >
                    </div>

                    <!-- Price From -->
                    <div class="col-md-2">
                        <label class="form-label">Min Price (KES)</label>
                        <input
                            type="number"
                            name="price_from"
                            class="form-control"
                            placeholder="0"
                            step="0.01"
                            value="{{ request('price_from') }}"
                        >
                    </div>

                    <!-- Price To -->
                    <div class="col-md-2">
                        <label class="form-label">Max Price (KES)</label>
                        <input
                            type="number"
                            name="price_to"
                            class="form-control"
                            placeholder="99999"
                            step="0.01"
                            value="{{ request('price_to') }}"
                        >
                    </div>

                    <!-- Actions -->
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-body">

            @if($orders->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                        @foreach($orders as $order)
                            <tr>

                                <!-- Order -->
                                <td>
                                    <strong>#{{ $order->id }}</strong>
                                    @if($order->mpesa_checkout_request_id)
                                        <br>
                                        <small class="text-muted">
                                            {{ Str::limit($order->mpesa_checkout_request_id, 20) }}
                                        </small>
                                    @endif
                                </td>

                                <!-- Customer -->
                                <td>
                                    <strong>{{ $order->user?->name ?? 'Guest / Deleted User' }}</strong><br>
                                    <small class="text-muted">
                                        {{ $order->user?->email ?? 'N/A' }}
                                    </small>
                                </td>

                                <!-- Product -->
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($order->product?->image_path)
                                            <img
                                                src="{{ asset('storage/'.$order->product->image_path) }}"
                                                class="rounded me-2"
                                                style="width:40px;height:40px;object-fit:cover"
                                            >
                                        @endif

                                        <div>
                                            <strong>{{ $order->product?->name ?? 'Product not found' }}</strong><br>
                                            <small class="text-muted">
                                                {{ $order->product?->category
                                                    ? ucfirst($order->product->category)
                                                    : '-' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <!-- Amount -->
                                <td>
                                    <strong>KES {{ number_format($order->amount, 2) }}</strong>
                                </td>

                                <!-- Status -->
                                <td>
                                    @php
                                        $statusMap = [
                                            'completed' => 'success',
                                            'pending'   => 'warning',
                                            'failed'    => 'danger',
                                            'cancelled' => 'secondary',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusMap[$order->status] ?? 'secondary' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>

                                <!-- Payment -->
                                <td>
                                    {{ $order->payment_method
                                        ? ucfirst($order->payment_method)
                                        : '-' }}
                                </td>

                                <!-- Date -->
                                <td>
                                    {{ $order->created_at->format('M d, Y') }}<br>
                                    <small class="text-muted">
                                        {{ $order->created_at->format('g:i A') }}
                                    </small>
                                </td>

                                <!-- Actions -->
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.orders.show', $order) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if($order->status === 'pending')
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-warning dropdown-toggle"
                                                        data-bs-toggle="dropdown">
                                                    <i class="bi bi-gear"></i>
                                                </button>

                                                <ul class="dropdown-menu">
                                                    @foreach(['completed'=>'success','failed'=>'danger'] as $status=>$color)
                                                        <li>
                                                            <form method="POST"
                                                                  action="{{ route('admin.orders.update-status', $order) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="status" value="{{ $status }}">
                                                                <button class="dropdown-item text-{{ $color }}">
                                                                    Mark as {{ ucfirst($status) }}
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between mt-4">
                    <small class="text-muted">
                        Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }}
                        of {{ $orders->total() }} results
                    </small>
                    {{ $orders->links() }}
                </div>

            @else
                <div class="text-center py-5">
                    <i class="bi bi-cart-x display-1 text-muted"></i>
                    <h4 class="mt-3">No Orders Found</h4>
                    <p class="text-muted">No orders match your current filters.</p>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary">
                        Clear Filters
                    </a>
                </div>
            @endif

        </div>
    </div>

</div>
@endsection
