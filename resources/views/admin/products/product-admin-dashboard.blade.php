@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="bi bi-box me-2"></i>Product Manager
                    </h1>
                    <p class="text-muted">Manage and upload your products</p>
                </div>
                <a href="{{ route('products-admin.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>
                    Add New Product
                </a>
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
            <div class="card h-100">
                <div class="card-body py-3 text-center">
                    <h5 class="card-title text-primary mb-1">{{ $stats['total'] ?? 0 }}</h5>
                    <p class="card-text text-muted mb-0 small">Total Products</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card h-100">
                <div class="card-body py-3 text-center">
                    <h5 class="card-title text-success mb-1">{{ $stats['active'] ?? 0 }}</h5>
                    <p class="card-text text-muted mb-0 small">Active Products</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card h-100">
                <div class="card-body py-3 text-center">
                    <h5 class="card-title text-warning mb-1">{{ $stats['inactive'] ?? 0 }}</h5>
                    <p class="card-text text-muted mb-0 small">Inactive Products</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card h-100">
                <div class="card-body py-3 text-center">
                    <h5 class="card-title text-info mb-1">{{ $stats['total_orders'] ?? 0 }}</h5>
                    <p class="card-text text-muted mb-0 small">Total Orders</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('products-admin.dashboard') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Product name...">
                    </div>
                    <div class="col-md-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            @php
                                $categories = \App\Models\Product::distinct()->pluck('category');
                            @endphp
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-outline-primary flex-grow-1">Filter</button>
                        <a href="{{ route('products-admin.dashboard') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-body">
            @if($products->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Orders</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="rounded" 
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ Str::limit($product->name, 40) }}</strong>
                                            <br><small class="text-muted">ID: {{ $product->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $product->category }}</span>
                                </td>
                                <td>
                                    <strong>KES {{ number_format($product->price, 2) }}</strong>
                                    @if($product->sale_price)
                                        <br><small class="text-success">Sale: KES {{ number_format($product->sale_price, 2) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-x-circle me-1"></i>Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $product->orders_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $product->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('products-admin.show', $product) }}" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('products-admin.edit', $product) }}" 
                                           class="btn btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('products-admin.toggle-status', $product) }}" 
                                              method="POST" class="d-inline" 
                                              onsubmit="return confirm('Toggle product status?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-warning" title="Toggle Status">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <p class="text-muted mt-3 mb-4">No products found</p>
                    <a href="{{ route('products-admin.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>
                        Create Your First Product
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Help Card -->
    <div class="card mt-4 bg-light">
        <div class="card-body">
            <h6 class="card-title">
                <i class="bi bi-lightbulb me-2"></i>Product Upload Guide
            </h6>
            <p class="card-text mb-2">
                As a Product Admin, you can:
            </p>
            <ul class="mb-0">
                <li>Upload and manage your digital products</li>
                <li>Edit product details, pricing, and descriptions</li>
                <li>Upload product files (ePub, PDF, ZIP formats)</li>
                <li>Toggle product status between active and inactive</li>
                <li>View product sales and download statistics</li>
                <li>View customer order information</li>
            </ul>
        </div>
    </div>
</div>

@endsection
