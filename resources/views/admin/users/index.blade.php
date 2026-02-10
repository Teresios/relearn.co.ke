@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header & Bulk Email Button -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-0">Users</h1>
                    <p class="text-muted">Manage customer accounts</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkEmailModal">
                    <i class="bi bi-envelope-fill me-1"></i>
                    Email Filtered Users
                </button>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-2">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <h5 class="card-title text-primary">{{ $stats['total'] ?? 0 }}</h5>
                    <p class="card-text text-muted mb-0">Total Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <h5 class="card-title text-success">{{ $stats['customers'] ?? 0 }}</h5>
                    <p class="card-text text-muted mb-0">Customers</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <h5 class="card-title text-warning">{{ $stats['admins'] ?? 0 }}</h5>
                    <p class="card-text text-muted mb-0">Admins</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <h5 class="card-title text-info">{{ $stats['new_this_month'] ?? 0 }}</h5>
                    <p class="card-text text-muted mb-0">New This Month</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Name or email...">
                    </div>
                    <div class="col-md-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role">
                            <option value="">All Roles</option>
                            <option value="customer" {{ request('role') == 'customer' ? 'selected' : '' }}>Customer</option>
                            <option value="affiliate" {{ request('role') == 'affiliate' ? 'selected' : '' }}>Affiliate</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="product_admin" {{ request('role') == 'product_admin' ? 'selected' : '' }}>Product Admin</option>
                            <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="verified" class="form-label">Email Status</label>
                        <select class="form-select" id="verified" name="verified">
                            <option value="">All Users</option>
                            <option value="1" {{ request('verified') == '1' ? 'selected' : '' }}>Verified</option>
                            <option value="0" {{ request('verified') == '0' ? 'selected' : '' }}>Unverified</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2">Filter</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
            @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Email Status</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        <br><small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($user->hasRole('super_admin'))
                                        <span class="badge bg-danger">Super Admin</span>
                                    @elseif($user->hasRole('admin'))
                                        <span class="badge bg-danger">Admin</span>
                                    @elseif($user->hasRole('product_admin'))
                                        <span class="badge bg-warning">Product Admin</span>
                                    @elseif($user->hasRole('affiliate'))
                                        <span class="badge bg-success">Affiliate</span>
                                    @else
                                        <span class="badge bg-primary">Customer</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success">Verified</span>
                                    @else
                                        <span class="badge bg-warning">Unverified</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $user->orders_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <strong>KES {{ number_format($user->orders->where('status', 'completed')->sum('amount'), 2) }}</strong>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.users.show', $user) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($user->id !== auth()->id() && auth()->user()->hasPermissionTo('manage-admin-roles'))
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                        type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-gear"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <!-- Make Super Admin -->
                                                    @if(!$user->hasRole('super_admin'))
                                                        @if(auth()->user()->hasRole('super_admin'))
                                                            <li>
                                                                <form action="{{ route('admin.users.assign-role', $user) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <input type="hidden" name="role" value="super_admin">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="bi bi-shield-check me-2"></i>Make Super Admin
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @else
                                                            <li>
                                                                <span class="dropdown-item disabled" title="Only Super Admins can create Super Admins">
                                                                    <i class="bi bi-shield-check me-2"></i>Make Super Admin
                                                                </span>
                                                            </li>
                                                        @endif
                                                    @endif

                                                    <!-- Make Admin -->
                                                    @if(!$user->hasRole('admin'))
                                                        @if(!($user->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')))
                                                            <li>
                                                                <form action="{{ route('admin.users.assign-role', $user) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <input type="hidden" name="role" value="admin">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="bi bi-person-badge me-2"></i>Make Admin
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                    @endif

                                                    <!-- Make Product Admin -->
                                                    @if(!$user->hasRole('product_admin'))
                                                        @if(!($user->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')))
                                                            <li>
                                                                <form action="{{ route('admin.users.assign-role', $user) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <input type="hidden" name="role" value="product_admin">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="bi bi-box me-2"></i>Make Product Admin
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                    @endif

                                                    <!-- Make Affiliate -->
                                                    @if(!$user->hasRole('affiliate'))
                                                        @if(!($user->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')))
                                                            <li>
                                                                <form action="{{ route('admin.users.assign-role', $user) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <input type="hidden" name="role" value="affiliate">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="bi bi-link-45deg me-2"></i>Make Affiliate
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                    @endif

                                                    <!-- Make Customer -->
                                                    @if($user->hasRole('super_admin') || $user->hasRole('admin') || $user->hasRole('product_admin') || $user->hasRole('affiliate'))
                                                        @if(!($user->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')))
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <form action="{{ route('admin.users.toggle-role', $user) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="dropdown-item text-danger">
                                                                        <i class="bi bi-trash me-2"></i>Remove All Roles
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @else
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <span class="dropdown-item disabled text-danger" title="Cannot modify Super Admin">
                                                                    <i class="bi bi-lock me-2"></i>Cannot Modify
                                                                </span>
                                                            </li>
                                                        @endif
                                                    @endif
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

                <!-- Custom Pagination -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-2">
                    <small class="text-muted">
                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} 
                        of {{ $users->total() }} results
                    </small>
                    <nav>
                        <ul class="pagination custom-pagination mb-0">
                            @php
                                $total = $users->lastPage();
                                $current = $users->currentPage();
                                $start = max(1, $current - 1);
                                $end = min($total, $current + 2);
                                if($current == 1) $end = min(4, $total);
                            @endphp
                            @if($start > 1)
                                <li class="page-item"><a class="page-link" href="{{ $users->url(1) }}">1</a></li>
                                @if($start > 2)
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                @endif
                            @endif
                            @for($i = $start; $i <= $end; $i++)
                                <li class="page-item{{ $current == $i ? ' active' : '' }}">
                                    <a class="page-link" href="{{ $users->url($i) }}">{{ $i }}</a>
                                </li>
                            @endfor
                            @if($end < $total)
                                @if($end < $total - 1)
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                @endif
                                <li class="page-item"><a class="page-link" href="{{ $users->url($total) }}">{{ $total }}</a></li>
                            @endif
                        </ul>
                    </nav>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-people display-1 text-muted"></i>
                    <h4 class="mt-3">No Users Found</h4>
                    <p class="text-muted">No users match your current filters.</p>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                        Clear Filters
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Email Modal -->
<div class="modal fade" id="bulkEmailModal" tabindex="-1" aria-labelledby="bulkEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="bulkEmailForm" method="POST" action="{{ route('admin.users.bulk-email') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkEmailModalLabel">Send Email to Users</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <strong>This will email all users currently shown in the filtered list ({{ $users->total() }} users on current filter).</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" class="form-control" name="subject" required placeholder="Email subject">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" name="message" rows="7" required placeholder="Write your message here"></textarea>
                        <div class="form-text">You may use variables like <code>{name}</code> for the user's name.</div>
                    </div>
                    <input type="hidden" name="filters" value="{{ json_encode(request()->all()) }}">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Send Email
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
/* Mobile-first enhancements and custom pagination */
@media (max-width: 991px) {
    .custom-pagination { flex-wrap: wrap; }
}
.custom-pagination .page-link {
    color: #764ba2;
    background: #fff;
    border: 1px solid #e4e1f5;
    border-radius: 5px;
    padding: 0.21rem 0.5rem;
    font-weight: 600;
    font-size: 0.93rem;
    min-width: 28px;
    text-align: center;
}
.custom-pagination .page-item.active .page-link,
.custom-pagination .page-link:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    color: #fff;
    border: 1px solid #764ba2;
}
.custom-pagination .page-item.disabled .page-link {
    color: #b4b4b4;
    background: #f9f9fa;
    pointer-events: none;
}
</style>
@endpush
@endsection