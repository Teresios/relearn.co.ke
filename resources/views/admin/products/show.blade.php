@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">{{ $product->name }}</h1>
                    <p class="text-muted">Product Details</p>
                </div>
                <div>
                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning me-2">
                        <i class="bi bi-pencil me-1"></i>
                        Edit Product
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back to Products
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Product Information -->
        <div class="col-lg-8">
            <!-- Basic Details -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Product Information</h5>
                    @if($product->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-warning">Inactive</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            @if($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}" 
                                     alt="{{ $product->name }}" 
                                     class="img-fluid rounded">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <i class="bi bi-file-earmark display-4 text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $product->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td><span class="badge bg-secondary">{{ ucfirst($product->category) }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Price:</strong></td>
                                    <td><strong class="text-success">KES {{ number_format($product->price, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $product->created_at->format('F d, Y g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $product->updated_at->format('F d, Y g:i A') }}</td>
                                </tr>
                                @if($product->tags)
                                <tr>
                                    <td><strong>Tags:</strong></td>
                                    <td>
                                    @php
                                        $tags = is_array($product->tags) ? $product->tags : explode(',', $product->tags ?? '');
                                    @endphp
                                    @foreach($tags as $tag)
                                        <span class="badge bg-primary">{{ trim($tag) }}</span>
                                    @endforeach
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h6>Description:</h6>
                        <p class="text-muted">{{ $product->description }}</p>
                    </div>
                    
                    @if($product->preview_url)
                    <div class="mt-3">
                        <h6>Preview:</h6>
                        <a href="{{ $product->preview_url }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye me-1"></i>
                            View Preview
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Files -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Product Files</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6><i class="bi bi-image me-2"></i>Product Image</h6>
                            @if($product->image_path)
                                <p class="text-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Image uploaded
                                </p>
                                <small class="text-muted">{{ basename($product->image_path) }}</small>
                            @else
                                <p class="text-muted">
                                    <i class="bi bi-x-circle me-1"></i>
                                    No image uploaded
                                </p>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <h6><i class="bi bi-book me-2"></i>ePub File</h6>
                            @if($product->file_path)
                                <p class="text-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    File uploaded
                                </p>
                                <small class="text-muted">
                                    {{ basename($product->file_path) }}<br>
                                    Size: {{ $product->formatted_file_size }}
                                </small>
                            @else
                                <p class="text-danger">
                                    <i class="bi bi-x-circle me-1"></i>
                                    No file uploaded
                                </p>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <h6><i class="bi bi-file-pdf me-2"></i>PDF File</h6>
                            @if($product->pdf_file_path)
                                <p class="text-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    PDF uploaded
                                </p>
                                <small class="text-muted">
                                    {{ basename($product->pdf_file_path) }}<br>
                                    Size: {{ $product->formatted_pdf_file_size }}
                                </small>
                            @else
                                <p class="text-muted">
                                    <i class="bi bi-x-circle me-1"></i>
                                    No PDF uploaded
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maxed-out Downloads Table -->
            @if(isset($maxedDownloads) && $maxedDownloads->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Users With Maxed Out Downloads</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Download Count</th>
                                    <th>Max Downloads</th>
                                    <th>Reset</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($maxedDownloads as $download)
                                <tr>
                                    <td>{{ $download->user->name ?? '-' }}</td>
                                    <td>{{ $download->user->email ?? '-' }}</td>
                                    <td>{{ $download->download_count }}</td>
                                    <td>{{ $download->max_downloads }}</td>
                                    <td>
                                        <form action="{{ route('admin.products.reset-downloads-user', [$product, $download->user]) }}" method="POST"
                                            onsubmit="return confirm('Reset download limits for this user?')">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-info" type="submit">
                                                <i class="bi bi-arrow-clockwise"></i>
                                                Reset
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Sales History -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Sales</h5>
                </div>
                <div class="card-body">
                    @if($recentOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                    <tr>
                                        <td>#{{ $order->id }}</td>
                                        <td>{{ $order->user->name }}</td>
                                        <td>KES {{ number_format($order->amount, 2) }}</td>
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
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($totalOrders > 5)
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.orders.index', ['product' => $product->id]) }}" class="btn btn-outline-primary btn-sm">
                                View All {{ $totalOrders }} Orders
                            </a>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-cart-x display-4 text-muted"></i>
                            <p class="text-muted mt-2">No sales yet for this product</p>
                        </div>
                    @endif
                </div>
            </div>
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
                            <h4 class="text-primary">{{ $totalOrders }}</h4>
                            <small class="text-muted">Total Sales</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">KES {{ number_format($totalRevenue, 2) }}</h4>
                            <small class="text-muted">Revenue</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <h5 class="text-info">{{ $totalDownloads }}</h5>
                            <small class="text-muted">Downloads</small>
                        </div>
                        <div class="col-6">
                            <h5 class="text-warning">{{ $recentDownloads }}</h5>
                            <small class="text-muted">This Month</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning">
                            <i class="bi bi-pencil me-1"></i>
                            Edit Product
                        </a>
                        @if($product->is_active)
                            <form action="{{ route('admin.products.toggle-status', $product) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-pause-circle me-1"></i>
                                    Deactivate Product
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.products.toggle-status', $product) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-success w-100">
                                    <i class="bi bi-play-circle me-1"></i>
                                    Activate Product
                                </button>
                            </form>
                        @endif

                        <!-- Reset download limits for all users who have maxed out for this product -->
                        <form action="{{ route('admin.products.reset-downloads', $product) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to reset download limits for all users who have maxed out for this product?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-info w-100">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Reset Download Limits
                            </button>
                        </form>

                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                            <i class="bi bi-trash me-1"></i>
                            Delete Product
                        </button>
                    </div>
                </div>
            </div>

            <!-- Product Link -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Public Link</h6>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ route('products.show', $product) }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyLink()">
                            <i class="bi bi-copy"></i>
                        </button>
                    </div>
                    <small class="text-muted">Share this link with customers</small>
                </div>
            </div>
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
                <p>Are you sure you want to delete "<strong>{{ $product->name }}</strong>"?</p>
                <p class="text-muted">This action cannot be undone. Customers who have already purchased this product will still be able to download it.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" style="display: inline;">
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
function confirmDelete() {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

function copyLink() {
    const linkInput = document.querySelector('input[readonly]');
    linkInput.select();
    document.execCommand('copy');
    
    // Show feedback
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="bi bi-check"></i>';
    button.classList.add('btn-success');
    
    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.classList.remove('btn-success');
    }, 2000);
}
</script>
@endpush