@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">{{ $user->name }}</h1>
                    <p class="text-muted">User Details</p>
                </div>
                <div>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- User Information -->
        <div class="col-lg-8">
            <!-- Basic Details -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">User Information</h5>
                    <div>
                        @if($user->hasRole('admin'))
                            <span class="badge bg-danger">Admin</span>
                        @else
                            <span class="badge bg-primary">Customer</span>
                        @endif
                        @if($user->email_verified_at)
                            <span class="badge bg-success">Verified</span>
                        @else
                            <span class="badge bg-warning">Unverified</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email Verified:</strong></td>
                                    <td>
                                        @if($user->email_verified_at)
                                            <span class="text-success">
                                                <i class="bi bi-check-circle me-1"></i>
                                                {{ $user->email_verified_at->format('F d, Y') }}
                                            </span>
                                        @else
                                            <span class="text-warning">
                                                <i class="bi bi-x-circle me-1"></i>
                                                Not verified
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Member Since:</strong></td>
                                    <td>{{ $user->created_at->format('F d, Y g:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Role:</strong></td>
                                    <td>
                                        @if($user->hasRole('super_admin'))
                                            <span class="badge bg-danger">Super Admin</span>
                                        @elseif($user->hasRole('admin'))
                                            <span class="badge bg-danger">Administrator</span>
                                        @elseif($user->hasRole('product_admin'))
                                            <span class="badge bg-warning">Product Admin</span>
                                        @elseif($user->hasRole('affiliate'))
                                            <span class="badge bg-success">Affiliate</span>
                                        @else
                                            <span class="badge bg-primary">Customer</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Total Orders:</strong></td>
                                    <td><strong>{{ $user->orders->count() }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Completed Orders:</strong></td>
                                    <td><strong>{{ $user->orders->where('status', 'completed')->count() }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Spent:</strong></td>
                                    <td><strong class="text-success">KES {{ number_format($user->orders->where('status', 'completed')->sum('amount'), 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Recent Orders</h5>
                </div>
                <div class="card-body">
                    @if($user->orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Product</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->orders->take(10) as $order)
                                    <tr>
                                        <td><strong>#{{ $order->id }}</strong></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($order->product->image_path)
                                                    <img src="{{ asset('storage/' . $order->product->image_path) }}" 
                                                         alt="{{ $order->product->name }}" 
                                                         class="rounded me-2" 
                                                         style="width: 30px; height: 30px; object-fit: cover;">
                                                @endif
                                                {{ Str::limit($order->product->name, 30) }}
                                            </div>
                                        </td>
                                        <td><strong>KES {{ number_format($order->amount, 2) }}</strong></td>
                                        <td>
                                            @if($order->status === 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($order->status === 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @else
                                                <span class="badge bg-danger">{{ ucfirst($order->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($user->orders->count() > 10)
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.orders.index', ['search' => $user->email]) }}" class="btn btn-outline-primary btn-sm">
                                View All {{ $user->orders->count() }} Orders
                            </a>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-cart-x display-4 text-muted"></i>
                            <p class="text-muted mt-2">No orders yet</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Download History -->
            @if($user->downloads->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Download History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Downloaded</th>
                                    <th>IP Address</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->downloads->take(10) as $download)
                                <tr>
                                    <td>{{ $download->order->product->name }}</td>
                                    <td>
                                        @if($download->downloaded_at)
                                            {{ $download->downloaded_at->format('M d, Y g:i A') }}
                                        @else
                                            <span class="text-muted">Not downloaded</span>
                                        @endif
                                    </td>
                                    <td>{{ $download->ip_address ?? '-' }}</td>
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
            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-primary">{{ $user->orders->count() }}</h4>
                            <small class="text-muted">Total Orders</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">KES {{ number_format($user->orders->where('status', 'completed')->sum('amount'), 2) }}</h4>
                            <small class="text-muted">Total Spent</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <h5 class="text-info">{{ $user->downloads->count() }}</h5>
                            <small class="text-muted">Downloads</small>
                        </div>
                        <div class="col-6">
                            <h5 class="text-warning">{{ $user->downloads->where('downloaded_at', '!=', null)->count() }}</h5>
                            <small class="text-muted">Used</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            @if($user->id !== auth()->id())
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    @if(auth()->user()->hasPermissionTo('manage-admin-roles'))
                        <div class="d-grid gap-2">
                            <!-- Current Role Display -->
                            <div class="bg-light p-3 rounded mb-2 text-center">
                                <small class="text-muted">Current Role</small><br>
                                @if($user->hasRole('super_admin'))
                                    <span class="badge bg-danger">Super Admin</span>
                                @elseif($user->hasRole('admin'))
                                    <span class="badge bg-danger">Administrator</span>
                                @elseif($user->hasRole('product_admin'))
                                    <span class="badge bg-warning">Product Admin</span>
                                @elseif($user->hasRole('affiliate'))
                                    <span class="badge bg-success">Affiliate</span>
                                @else
                                    <span class="badge bg-primary">Customer</span>
                                @endif
                            </div>

                            <!-- Role Assignment Options -->
                            @if(!($user->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')))
                                <div class="btn-group-vertical w-100" role="group">
                                    <!-- Super Admin Option (only for super admins) -->
                                    @if(auth()->user()->hasRole('super_admin') && !$user->hasRole('super_admin'))
                                        <form action="{{ route('admin.users.assign-role', $user) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="role" value="super_admin">
                                            <button type="submit" class="btn btn-outline-danger w-100 text-start">
                                                <i class="bi bi-shield-check me-2"></i>Make Super Admin
                                                <small class="d-block text-muted">Full system access</small>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Regular Admin Option -->
                                    @if(!$user->hasRole('admin'))
                                        <form action="{{ route('admin.users.assign-role', $user) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="role" value="admin">
                                            <button type="submit" class="btn btn-outline-danger w-100 text-start">
                                                <i class="bi bi-person-badge me-2"></i>Make Administrator
                                                <small class="d-block text-muted">Full admin access</small>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Product Admin Option -->
                                    @if(!$user->hasRole('product_admin'))
                                        <form action="{{ route('admin.users.assign-role', $user) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="role" value="product_admin">
                                            <button type="submit" class="btn btn-outline-warning w-100 text-start">
                                                <i class="bi bi-box me-2"></i>Make Product Admin
                                                <small class="d-block text-muted">Manage products only</small>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Affiliate Option -->
                                    @if(!$user->hasRole('affiliate'))
                                        <form action="{{ route('admin.users.assign-role', $user) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="role" value="affiliate">
                                            <button type="submit" class="btn btn-outline-success w-100 text-start">
                                                <i class="bi bi-link-45deg me-2"></i>Make Affiliate
                                                <small class="d-block text-muted">Affiliate program access</small>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Remove All Roles Option -->
                                    @if($user->hasRole('super_admin') || $user->hasRole('admin') || $user->hasRole('product_admin') || $user->hasRole('affiliate'))
                                        <hr class="my-1">
                                        <form action="{{ route('admin.users.toggle-role', $user) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-secondary w-100 text-start text-danger">
                                                <i class="bi bi-trash me-2"></i>Remove All Roles
                                                <small class="d-block text-muted">Make customer</small>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @else
                                <div class="alert alert-warning mb-0">
                                    <i class="bi bi-lock me-1"></i>
                                    <small>Only Super Admins can manage Super Admin roles.</small>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <small><i class="bi bi-info-circle me-1"></i>You do not have permission to manage admin roles.</small>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Account Activity -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Account Activity</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Account Created</h6>
                                <p class="timeline-text">{{ $user->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        
                        @if($user->email_verified_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Email Verified</h6>
                                <p class="timeline-text">{{ $user->email_verified_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($user->orders->count() > 0)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">First Order</h6>
                                <p class="timeline-text">{{ $user->orders->last()->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 3rem;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 0;
    left: 1rem;
    height: 100%;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    top: 0;
    left: -2.5rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    border-left: 3px solid #007bff;
}

.timeline-title {
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.timeline-text {
    margin-bottom: 0;
    font-size: 0.75rem;
    color: #6c757d;
}
</style>
@endsection
