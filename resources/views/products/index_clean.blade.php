@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.css" rel="stylesheet">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .products-page {
        background: #f5f5f5;
        min-height: 100vh;
        padding-top: 0;
    }

    .products-header {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .products-header .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
    }

    .products-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .products-header p {
        font-size: 1rem;
        opacity: 0.9;
        margin: 0;
    }

    .products-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem 2rem 1rem;
    }

    .products-grid {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 1.5rem !important;
        margin-top: 1.5rem !important;
        width: 100% !important;
        grid-auto-flow: row !important;
    }

    @media (max-width: 1399px) {
        .products-grid {
            grid-template-columns: repeat(3, 1fr) !important;
        }
    }

    @media (max-width: 1000px) {
        .products-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    @media (max-width: 600px) {
        .products-grid {
            grid-template-columns: repeat(1, 1fr) !important;
        }
    }

    .product-card {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 1px solid #e0e0e0;
        display: flex;
        flex-direction: column;
    }

    .product-card:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        transform: translateY(-4px);
    }

    .product-image-container {
        position: relative;
        width: 100%;
        height: auto;
        min-height: 380px;
        background: linear-gradient(135deg, #f5f5f5 0%, #efefef 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        overflow: visible;
    }

    .product-image {
        max-width: 100%;
        max-height: 350px;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .product-card:hover .product-image {
        transform: scale(1.08);
    }

    .product-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);
        color: #999;
        font-size: 2.5rem;
    }

    .price-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #ff6b35;
        color: white;
        padding: 0.5rem 0.8rem;
        border-radius: 4px;
        font-weight: 700;
        font-size: 1.1rem;
        z-index: 2;
    }

    .product-info {
        padding: 1rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .product-name {
        font-size: 1rem;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 0.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.5rem;
    }

    .product-category {
        font-size: 0.8rem;
        color: #888;
        margin-bottom: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .product-description {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 0.7rem;
        flex-grow: 1;
    }

    .product-footer {
        display: flex;
        gap: 0.5rem;
        padding-top: 1rem;
        border-top: 1px solid #f0f0f0;
    }

    .btn-view, .btn-purchase {
        flex: 1;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        font-weight: 600;
        text-decoration: none;
        text-align: center;
        cursor: pointer;
        border: none;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .btn-view {
        background: #e9ecef;
        color: #667eea;
    }

    .btn-view:hover {
        background: #dee2e6;
        color: #667eea;
    }

    .btn-purchase {
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        color: white;
    }

    .btn-purchase:hover {
        background: linear-gradient(135deg, #f7931e 0%, #ff6b35 100%);
        transform: translateY(-2px);
    }

    /* Pagination Styles */
    .custom-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-top: 2rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .page-btn {
        background: white;
        color: #667eea;
        border: 1px solid #e9ecef;
        padding: 0.5rem 0.8rem;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        font-weight: 600;
        min-width: 40px;
        text-align: center;
    }

    .page-btn:hover {
        background: #e9ecef;
        color: #667eea;
    }

    .page-btn.active {
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        color: white;
        border-color: #ff6b35;
    }

    .pagination-status {
        margin-left: 1rem;
        color: #666;
        font-size: 0.9rem;
    }

    .no-results {
        text-align: center;
        padding: 2rem;
        background: white;
        border-radius: 8px;
        margin: 2rem 0;
    }

    .no-results-icon {
        font-size: 3rem;
        color: #ccc;
        margin-bottom: 1rem;
    }

    .no-results h3 {
        color: #333;
        margin-bottom: 0.5rem;
    }

    .no-results p {
        color: #666;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
<div class="products-page">
    <!-- Products Header -->
    <div class="products-header">
        <div class="container">
            <h1>Digital Products Marketplace</h1>
            <p>Discover premium digital products to grow your business</p>
        </div>
    </div>

    <div class="products-container">
        <!-- Category Filter -->
        @if($categoryCounts && count($categoryCounts) > 0)
            <div style="margin-bottom: 1.5rem;">
                <strong>Filter by Category:</strong>
                <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="{{ route('products.index') }}" style="padding: 0.5rem 1rem; background: #e9ecef; border-radius: 20px; text-decoration: none; color: #333;">All</a>
                    @foreach($categoryCounts as $category => $count)
                        <a href="{{ route('products.index', ['category' => $category]) }}" style="padding: 0.5rem 1rem; background: {{ request('category') == $category ? '#ff6b35' : '#e9ecef' }}; color: {{ request('category') == $category ? 'white' : '#333' }}; border-radius: 20px; text-decoration: none;">
                            {{ ucfirst($category) }} ({{ $count }})
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Results Count -->
        @if($products->count() > 0)
            <div style="margin-bottom: 1.5rem; color: #666;">
                <strong>{{ $products->total() }} Products Found</strong>
                <span style="float: right;">Showing {{ $products->firstItem() }}-{{ $products->lastItem() }} of {{ $products->total() }}</span>
            </div>

            <!-- Products Grid -->
            <div class="products-grid">
                @foreach($products as $product)
                    <div class="product-card">
                        <div class="product-image-container">
                            @if($product->image)
                                <img src="{{ asset('storage/products/images/' . $product->image) }}" alt="{{ $product->name }}" class="product-image" onerror="this.parentElement.innerHTML='<div class=\"product-placeholder\"><i class=\"fas fa-file-alt\"></i></div>'">
                            @else
                                <div class="product-placeholder">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            @endif
                        </div>
                        <div class="product-info">
                            <div class="product-category">{{ ucfirst($product->category ?? 'Other') }}</div>
                            <h3 class="product-name">{{ $product->name }}</h3>
                            <p class="product-description">
                                {{ \Illuminate\Support\Str::limit($product->description ?? 'Premium digital product', 60) }}
                            </p>
                            <div style="font-weight: 700; color: #ff6b35; margin-top: auto;">{{ $product->formatted_price }}</div>
                            <div class="product-footer">
                                <a href="{{ route('products.show', $product) }}" class="btn-view">View</a>
                                <a href="{{ route('orders.checkout', $product->id) }}" class="btn-purchase">Purchase</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($products instanceof \Illuminate\Pagination\LengthAwarePaginator && $products->lastPage() > 1)
                <div class="custom-pagination">
                    <!-- Previous Page -->
                    @if($products->onFirstPage())
                        <span class="page-btn" style="opacity: 0.5; cursor: not-allowed;">← Prev</span>
                    @else
                        <a href="{{ $products->previousPageUrl() }}" class="page-btn">← Prev</a>
                    @endif

                    <!-- Page Numbers -->
                    @for($i = 1; $i <= $products->lastPage(); $i++)
                        @if($i == $products->currentPage())
                            <span class="page-btn active">{{ $i }}</span>
                        @else
                            <a href="{{ $products->url($i) }}" class="page-btn">{{ $i }}</a>
                        @endif
                    @endfor

                    <!-- Next Page -->
                    @if($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}" class="page-btn">Next →</a>
                    @else
                        <span class="page-btn" style="opacity: 0.5; cursor: not-allowed;">Next →</span>
                    @endif

                    <!-- Page Status -->
                    <span class="pagination-status">
                        Page {{ $products->currentPage() }} of {{ $products->lastPage() }}
                    </span>
                </div>
            @endif
        @else
            <div class="no-results">
                <div class="no-results-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>No Products Found</h3>
                <p>No digital products are currently available. Please check back later.</p>
                <a href="{{ route('products.index') }}" style="padding: 0.5rem 1rem; background: #ff6b35; color: white; border-radius: 4px; text-decoration: none; display: inline-block;">Clear Filters</a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.js"></script>
@endpush
