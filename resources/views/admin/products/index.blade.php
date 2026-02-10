@extends('layouts.app')

@section('title', 'Manage Products')

@push('styles')
<style>
/* Header and Stats */
.products-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.2rem 0 .9rem 0;
    margin-bottom: 1rem;
    border-radius: 0.7rem;
}
.products-title {
    font-size: 1.38rem;
    font-weight: 700;
}
.stats-cards-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.7rem;
    margin-bottom: 1.1rem;
}
.stats-card {
    background: #fff;
    border-radius: 11px;
    box-shadow: 0 2px 13px rgba(102,126,234,0.09);
    padding: 0.7rem 1.1rem;
    flex: 1 1 120px;
    min-width: 130px;
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.stats-card-title {
    font-size: 1.07rem;
    font-weight: 600;
}
.stats-card-value {
    font-size: 1.28rem;
    font-weight: 700;
    margin-bottom: 0.1rem;
}
.stats-card-value.primary { color: #667eea; }
.stats-card-value.success { color: #059669; }
.stats-card-value.warning { color: #eab308; }
.stats-card-value.info { color: #0ea5e9; }

/* Toolbar */
.products-toolbar {
    margin-bottom: 1rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.48rem;
    align-items: center;
    justify-content: space-between;
}
.products-toolbar .form-control,
.products-toolbar .form-select {
    max-width: 160px;
    min-width: 90px;
    font-size: 0.97rem;
}
.products-toolbar .btn { font-size: 0.93rem; }

/* Table */
.table-products-responsive {
    overflow-x: auto;
    width: 100%;
}
.products-table {
    min-width: 740px;
    font-size: 0.98rem;
}
.products-table th, .products-table td {
    vertical-align: middle !important;
    padding-top: 0.7em;
    padding-bottom: 0.7em;
}
.products-table td .product-img-thumb {
    width: 38px;
    height: 38px;
    object-fit: cover;
    border-radius: 8px;
    margin-right: 0.65em;
    background: #f8f8fc;
}
.products-table .badge {
    font-size: 0.78em;
    letter-spacing: 0.03em;
    padding: 0.32em 0.9em;
    border-radius: 9px;
}
.products-table .btn-group .btn {
    padding: 0.32em 0.55em;
    font-size: 0.93em;
    border-radius: 7px;
}

/* Pagination */
.custom-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.19rem;
    flex-wrap: wrap;
    margin-bottom: 0;
}
.custom-pagination .page-link {
    color: #764ba2;
    background: #fff;
    border: 1px solid #e4e1f5;
    border-radius: 5px;
    padding: 0.21rem 0.5rem;
    font-weight: 600;
    font-size: 0.93rem;
    transition: background 0.14s, color 0.14s;
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
.pagination-summary {
    font-size: 0.92rem;
    color: #444;
    opacity: 0.78;
    text-align: center;
    font-weight: 500;
    margin-bottom: 0.18rem;
}

/* Mobile */
@media (max-width: 991px) {
    .products-toolbar { flex-direction: column; align-items: start; gap: 0.9rem;}
    .stats-cards-row { flex-direction: column; gap: 0.8rem; }
    .products-header { padding: .8rem 1vw .7rem 1vw;}
}
@media (max-width: 600px) {
    .products-header { padding: 0.6rem 0 0.6rem 0; font-size: 1rem;}
    .products-title { font-size: 1.05rem; }
    .stats-cards-row { gap: 0.38rem; }
    .stats-card { padding: 0.4rem 0.7rem; }
    .table-products-responsive { min-width: 99vw; }
    .products-table { font-size: 0.92rem;}
}
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Page Header -->
    <div class="products-header mb-3">
        <div class="container-fluid px-0">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div>
                    <h1 class="products-title mb-1">
                        <i class="bi bi-box-seam me-2"></i>
                        Manage Products
                    </h1>
                    <div style="font-size:1.02rem;opacity:0.92;">View, edit, and manage your digital products</div>
                </div>
                <div class="mt-3 mt-md-0 text-md-end">
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary px-4 py-2 fw-bold">
                        <i class="bi bi-plus-circle me-2"></i>
                        Add Product
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-cards-row mb-3">
        <div class="stats-card">
            <div class="stats-card-title">Total Products</div>
            <div class="stats-card-value primary">{{ $stats['total'] ?? 0 }}</div>
        </div>
        <div class="stats-card">
            <div class="stats-card-title">Active</div>
            <div class="stats-card-value success">{{ $stats['active'] ?? 0 }}</div>
        </div>
        <div class="stats-card">
            <div class="stats-card-title">Inactive</div>
            <div class="stats-card-value warning">{{ $stats['inactive'] ?? 0 }}</div>
        </div>
        <div class="stats-card">
            <div class="stats-card-title">Total Value</div>
            <div class="stats-card-value info">KES {{ number_format($stats['total_value'] ?? 0, 2) }}</div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="products-toolbar mb-3">
        <form method="GET" action="{{ route('admin.products.index') }}" class="d-flex flex-wrap gap-2 align-items-center">
            <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search...">
            <select class="form-select" name="category">
                <option value="">All Categories</option>
                <option value="ebook" {{ request('category') == 'ebook' ? 'selected' : '' }}>eBook</option>
                <option value="business-plan" {{ request('category') == 'business-plan' ? 'selected' : '' }}>Business Plan</option>
                <option value="template" {{ request('category') == 'template' ? 'selected' : '' }}>Template</option>
                <option value="course" {{ request('category') == 'course' ? 'selected' : '' }}>Course</option>
                <option value="software" {{ request('category') == 'software' ? 'selected' : '' }}>Software</option>
            </select>
            <select class="form-select" name="status">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="btn btn-outline-primary">Filter</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Reset</a>
        </form>
        <span class="text-muted ms-auto" style="font-size:0.99rem;">Total: <b>{{ $products->total() }}</b></span>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-body">
            @if($products->count() > 0)
            <div class="table-products-responsive">
                <table class="table table-hover products-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Sales</th>
                            <th>Created</th>
                            @if(Auth::user()->hasRole('super_admin'))
                                <th>Created By</th>
                                <th>Last Updated By</th>
                            @endif
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($product && $product->image_path)
                                        <img src="{{ asset('storage/' . $product->image_path) }}" 
                                             alt="{{ $product->name }}" 
                                             class="product-img-thumb me-2" >
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center me-2 product-img-thumb">
                                            <i class="bi bi-file-earmark text-muted"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold" style="font-size:1.01em;">{{ $product->name }}</div>
                                        <div class="text-muted" style="font-size:0.92em;">{{ Str::limit($product->description, 44) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($product->category) }}</span>
                            </td>
                            <td>
                                <span class="fw-bold">KES {{ number_format($product->price, 2) }}</span>
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-warning">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $product->orders_count ?? 0 }} sales</span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $product->created_at->format('M d, Y') }}</small>
                            </td>
                            @if(Auth::user()->hasRole('super_admin'))
                                <td>
                                    @if($product->createdByUser)
                                        <small class="text-primary fw-semibold">{{ $product->createdByUser->name }}</small><br>
                                        <small class="text-muted">{{ $product->createdByUser->email }}</small>
                                    @else
                                        <small class="text-muted">N/A</small>
                                    @endif
                                </td>
                                <td>
                                    @if($product->updatedByUser && $product->updatedByUser->id !== $product->createdByUser?->id)
                                        <small class="text-success fw-semibold">{{ $product->updatedByUser->name }}</small><br>
                                        <small class="text-muted">{{ $product->updated_at->format('M d, Y H:i') }}</small>
                                    @elseif($product->updatedByUser && $product->updatedByUser->id === $product->createdByUser?->id && $product->updated_at->format('Y-m-d') !== $product->created_at->format('Y-m-d'))
                                        <small class="text-info fw-semibold">{{ $product->updatedByUser->name }}</small><br>
                                        <small class="text-muted">{{ $product->updated_at->format('M d, Y H:i') }}</small>
                                    @else
                                        <small class="text-muted">Original</small>
                                    @endif
                                </td>
                            @endif
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.products.show', $product) }}" 
                                       class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" 
                                       class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmDelete({{ $product->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Custom Pagination -->
            <div class="d-flex flex-column align-items-center mt-3 gap-1">
                <div class="pagination-summary">
                    Page {{ $products->currentPage() }} of {{ $products->lastPage() }}
                </div>
                <nav>
                    <ul class="pagination custom-pagination">
                        @php
                            $total = $products->lastPage();
                            $current = $products->currentPage();
                            $start = max(1, $current - 1);
                            $end = min($total, $current + 2);
                            if($current == 1) $end = min(4, $total);
                        @endphp
                        @if($start > 1)
                            <li class="page-item"><a class="page-link" href="{{ $products->url(1) }}">1</a></li>
                            @if($start > 2)
                                <li class="page-item disabled"><span class="page-link">…</span></li>
                            @endif
                        @endif
                        @for($i = $start; $i <= $end; $i++)
                            <li class="page-item{{ $current == $i ? ' active' : '' }}">
                                <a class="page-link" href="{{ $products->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor
                        @if($end < $total)
                            @if($end < $total - 1)
                                <li class="page-item disabled"><span class="page-link">…</span></li>
                            @endif
                            <li class="page-item"><a class="page-link" href="{{ $products->url($total) }}">{{ $total }}</a></li>
                        @endif
                    </ul>
                </nav>
            </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-box-seam display-1 text-muted"></i>
                    <h4 class="mt-3">No Products Found</h4>
                    <p class="text-muted">You haven't added any products yet.</p>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>
                        Add Your First Product
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this product? This action cannot be undone.</p>
                <p class="text-muted"><strong>Note:</strong> Customers who have already purchased this product will still be able to download it.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Product</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function confirmDelete(productId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `/admin/products/${productId}`;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
@endpush