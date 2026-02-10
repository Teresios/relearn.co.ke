@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.css" rel="stylesheet">
<style>
    .products-page {
        background: linear-gradient(120deg, #fbc2eb 0%, #a6c1ee 60%, #cfdef3 100%);
        min-height: 100vh;
        padding-bottom: 3rem;
        position: relative;
        overflow-x: hidden;
    }
    #bubble-bg {
        position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
        z-index: 0; pointer-events: none; overflow: hidden;
    }
    .bubble { position: absolute; border-radius: 50%; filter: blur(3px); opacity: 0.17;
        animation: bubbleMove 18s linear infinite; will-change: transform, opacity; pointer-events: none;}
    .bubble.b1 { width: 90px; height: 90px; background: #fbc2eb; left: 12vw; top: 10vh; animation-delay: 0s;}
    .bubble.b2 { width: 60px; height: 60px; background: #a6c1ee; left: 80vw; top: 25vh; animation-delay: 3s;}
    .bubble.b3 { width: 120px; height: 120px; background: #b0f3f1; left: 50vw; top: 80vh; animation-delay: 7s;}
    .bubble.b4 { width: 80px; height: 80px; background: #ffd6e0; left: 25vw; top: 70vh; animation-delay: 5s;}
    .bubble.b5 { width: 60px; height: 60px; background: #e0c3fc; left: 82vw; top: 60vh; animation-delay: 9s;}
    .bubble.b6 { width: 48px; height: 48px; background: #ece9e6; left: 8vw; top: 86vh; animation-delay: 12s;}
    .bubble.b7 { width: 100px; height: 100px; background: #cfdef3; left: 65vw; top: 6vh; animation-delay: 6s;}
    .bubble.b8 { width: 50px; height: 50px; background: #e7ffde; left: 35vw; top: 88vh; animation-delay: 8s;}
    @keyframes bubbleMove {
        0% { transform: scale(1) translateY(0) translateX(0); opacity: 0.17;}
        50%{ opacity: 0.23;}
        70%{ opacity: 0.10;}
        100% { transform: scale(1.09) translateY(-50px) translateX(18px); opacity: 0.08;}
    }

    .products-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2.2rem 0 1.2rem 0;
        margin-bottom: 1.8rem;
        border-radius: 0 0 24px 24px;
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.09);
        position: relative;
        z-index: 2;
        text-align: center;
    }
    .products-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="rgba(255,255,255,0.14)"><polygon points="1000,100 1000,0 0,100"/></svg>');
        background-size: cover;
        z-index: 1;
    }
    .products-header-content { position: relative; z-index: 2; }
    .products-title {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 0.4rem;
        text-shadow: 2px 2px 8px rgba(0,0,0,0.10);
        letter-spacing: 1px;
        animation: fadeSlideIn 1.2s cubic-bezier(.7,.2,.4,1) 0.2s both;
    }
    .products-subtitle {
        font-size: 1.08rem;
        opacity: 0.92;
        margin-bottom: 1.6rem;
        letter-spacing: 0.3px;
        animation: fadeSlideIn 1.5s cubic-bezier(.7,.2,.4,1) 0.4s both;
    }
    @keyframes fadeSlideIn {
        0% { opacity: 0; transform: translateY(24px);}
        100% { opacity: 1; transform: translateY(0);}
    }
    .search-container {
        background: rgba(255,255,255,0.13);
        border-radius: 50px;
        padding: 0.55rem 1rem;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.18);
        box-shadow: 0 4px 24px rgba(102,126,234,0.09);
        max-width: 97vw;
        margin: 0 auto;
        animation: fadeSlideIn 1.6s cubic-bezier(.7,.2,.4,1) 0.8s both;
    }
    .search-input {
        background: transparent;
        border: none;
        color: white;
        padding: 0.75rem 1.2rem;
        font-size: 1rem;
        width: 70%;
        min-width: 90px;
    }
    .search-input::placeholder {
        color: rgba(255,255,255,0.8);
        font-style: italic;
    }
    .search-input:focus { outline: none; box-shadow: none; }
    .search-btn {
        background: linear-gradient(120deg, #667eea, #764ba2);
        border: none;
        color: white;
        padding: 0.7rem 1.2rem;
        border-radius: 50px;
        transition: all 0.3s;
        font-weight: 700;
        font-size: 1.05rem;
        box-shadow: 0 2px 12px rgba(118, 75, 162, 0.11);
    }
    .search-btn:hover, .search-btn:focus {
        background: linear-gradient(120deg, #764ba2, #667eea);
        color: white;
        transform: scale(1.08);
        outline: none;
    }

    /* --- PRODUCT DISTRIBUTION --- */
    .category-distribution {
        margin-bottom: 1.25rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: flex-start;
    }
    .category-distribution .cat-badge {
        background: linear-gradient(90deg, #ece9e6 60%, #a6c1ee 100%);
        color: #764ba2;
        border-radius: 2em;
        padding: 0.47em 1.15em;
        font-size: 1em;
        font-weight: 600;
        transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        box-shadow: 0 2px 8px rgba(102,126,234,0.08);
        text-decoration: none;
        border: none;
        outline: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.35em;
    }
    .category-distribution .cat-badge.active,
    .category-distribution .cat-badge:hover,
    .category-distribution .cat-badge:focus {
        background: linear-gradient(90deg, #667eea 60%, #764ba2 100%);
        color: #fff;
        outline: none;
        box-shadow: 0 4px 14px rgba(118,75,162,0.13);
        text-decoration: none;
    }

    /* --- FILTER BUTTON & SHEET --- */
    .filter-float-btn {
        position: fixed;
        right: 4vw;
        bottom: 5.5vh;
        z-index: 1250;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(120deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border-radius: 50%;
        box-shadow: 0 3px 18px rgba(102,126,234,0.22);
        width: 60px; height: 60px;
        cursor: pointer;
        font-size: 2rem;
        border: none;
        transition: box-shadow .21s, background .21s;
    }
    .filter-float-btn:hover, .filter-float-btn:focus {
        background: linear-gradient(120deg, #764ba2, #667eea);
        box-shadow: 0 7px 24px rgba(118,75,162,0.22);
        outline: none;
    }
    @media (min-width: 992px) {
        .filter-float-btn { display: none !important; }
    }
    .filter-sheet-backdrop {
        display: none;
        position: fixed; z-index: 1300;
        top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(56, 55, 62, 0.16);
    }
    .filter-sheet {
        position: fixed; z-index: 1310;
        left: 0; bottom: 0; width: 100vw; max-width: 100vw;
        background: #fff;
        border-top-left-radius: 20px; border-top-right-radius: 20px;
        box-shadow: 0 -4px 24px rgba(102,126,234,0.11);
        transform: translateY(110%);
        transition: transform 0.28s cubic-bezier(.7,.2,.4,1);
        padding: 1.1em 1.3em 1.2em 1.3em;
        min-height: 340px;
    }
    .filter-sheet.active { transform: translateY(0); }
    .filter-sheet-backdrop.active { display: block; }
    .filter-sheet-handle {
        width: 46px; height: 5px;
        background: #e9ecef;
        border-radius: 4px;
        margin: 0 auto 14px auto;
    }
    .filter-action-bar {
        display: flex; gap: 0.6em; justify-content: space-between; margin-top: 1.3em;
    }
    .filter-action-bar .btn {
        flex: 1 0 0;
        font-size: 1.06em;
        font-weight: 700;
        border-radius: 11px;
        padding: 0.7em 0;
    }
    .filter-action-bar .btn + .btn { margin-left: 0.6em; }
    .filter-action-bar .reset-btn {
        background: #e9ecef; color: #765ea2;
        border: none;
    }
    .filter-action-bar .apply-btn {
        background: linear-gradient(120deg, #667eea, #764ba2);
        color: #fff; border: none;
    }
    .filter-action-bar .apply-btn:focus,
    .filter-action-bar .apply-btn:hover {
        background: linear-gradient(120deg, #764ba2, #667eea);
        color: #fff;
    }
    @media (min-width: 992px) {
        .filter-sheet, .filter-sheet-backdrop { display: none !important; }
        .sidebar-filters {
            position: static;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(102,126,234,0.08);
            padding: 1.2em 1.1em 1.2em 1.1em;
            margin-bottom: 2em;
        }
    }
    @media (max-width: 991.98px) {
        .sidebar-filters { display: none !important; }
    }

    .filter-label {
        font-size: 1em;
        font-weight: 700;
        color: #667eea;
        margin-bottom: .35em;
        display: flex; align-items: center;
    }
    .filter-icon { font-size: 1.15em; margin-right: 0.4em; }
    .filter-group { margin-bottom: 1.13em; }
    .filter-select, .filter-input {
        width: 100%; border-radius: 12px;
        border: 1px solid #e9ecef;
        font-size: 1em; padding: .58em .95em;
        background: #f8fafc;
        margin-bottom: 0.12em;
    }
    .filter-select:focus, .filter-input:focus {
        border-color: #764ba2;
        outline: none;
    }
    .slider-label {
        font-size: 1em;
        font-weight: 600;
        color: #764ba2;
        margin-bottom: 0.35em;
    }
    .noUi-target { background: #ece9e6; border-radius: 12px; border: none; box-shadow: 0 1px 6px rgba(102,126,234,0.07);}
    .noUi-connect { background: linear-gradient(90deg, #667eea, #764ba2); }
    .noUi-horizontal .noUi-handle { border-radius: 50%; background: #fff; border: 2px solid #764ba2; }
    @media (max-width: 575.98px) {
        .filter-sheet { padding: 0.95em 0.4em 1em 0.4em; }
        .filter-label { font-size: 0.98em; }
    }

    /* --- PRODUCTS GRID --- */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.7rem;
        margin-bottom: 2rem;
        position: relative;
        z-index: 2;
    }
    @media (min-width: 575.98px) { .products-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (min-width: 767.98px) { .products-grid { grid-template-columns: repeat(4, 1fr); } }
    @media (min-width: 1199.98px) { .products-grid { grid-template-columns: repeat(5, 1fr); } }

    .product-card {
        cursor: pointer;
        border: none;
        border-radius: 13px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(102,126,234,0.13);
        transition: all 0.19s cubic-bezier(0.4,0,0.2,1);
        background: var(--product-gradient, linear-gradient(120deg,#f8fafc 60%,#e0eafc 100%));
        height: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
        animation: fadeSlideIn 1.2s cubic-bezier(.7,.2,.4,1) 0.09s both;
        text-decoration: none !important;
        color: inherit !important;
        min-width: 0;
    }
    .product-card:hover, .product-card:focus {
        transform: translateY(-4px) scale(1.03);
        box-shadow: 0 10px 32px rgba(118,75,162,0.15);
        z-index: 3;
        text-decoration: none !important;
    }
    .product-card:active {
        box-shadow: 0 3px 12px rgba(118,75,162,0.09);
        transform: scale(0.98);
    }
    .product-card.category-ebook { --product-gradient: linear-gradient(120deg,#b0f3f1 60%,#fcdff4 100%); }
    .product-card.category-business-plan { --product-gradient: linear-gradient(120deg,#a6c1ee 60%,#e7ffde 100%); }
    .product-card.category-template { --product-gradient: linear-gradient(120deg,#ffd6e0 60%,#b0f3f1 100%); }
    .product-card.category-course { --product-gradient: linear-gradient(120deg,#fbc2eb 60%,#a6c1ee 100%); }
    .product-card.category-software { --product-gradient: linear-gradient(120deg,#e0c3fc 60%,#cfdef3 100%); }
    .product-card.category-other { --product-gradient: linear-gradient(120deg,#ece9e6 60%,#ffffff 100%); }
    .product-image-container { width: 100%; height: 100px; background: rgba(255,255,255,0.4);
        display: flex; align-items: center; justify-content: center; position: relative; z-index: 2;}
    @media (min-width: 575.98px) { .product-image-container { height: 120px; } }
    @media (min-width: 767.98px) { .product-image-container { height: 140px; } }
    @media (min-width: 1199.98px) { .product-image-container { height: 160px; } }
    .product-card .card-img-top {
        max-width: 95%; max-height: 95%; min-width: 70px; min-height: 70px;
        object-fit: contain; border-radius: 7px; background: #fff; margin: 0 auto; display: block; box-shadow: none;
        transition: transform 0.2s cubic-bezier(0.4,0,0.2,1);
        content-visibility: auto;
    }
    .product-card:hover .card-img-top { transform: scale(1.09); }
    .product-placeholder { width: 90%; height: 90%; max-width: 150px; max-height: 150px; display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 7px; margin: 0 auto;}
    .product-placeholder i { font-size: 3.5rem; color: white; opacity: 0.7; }
    .product-card-body { padding: 0.5rem 0.3rem 0.7rem 0.3rem; display: flex; flex-direction: column; flex-grow: 1; position: relative; z-index: 2;}
    .product-header {
        display: flex; justify-content: space-between; align-items: flex-start;
        margin-bottom: 0.35rem; gap: 0.2rem;}
    .product-badge {
        background: linear-gradient(120deg, #667eea, #764ba2 90%);
        border: none;
        color: white;
        padding: 0.14rem 0.45rem;
        border-radius: 13px;
        font-size: 0.69rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
        box-shadow: 0 1px 4px rgba(102,126,234,0.08);
    }
    .product-price {
        font-size: 0.85rem;
        font-weight: 700;
        background: linear-gradient(120deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        white-space: normal;
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .product-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.18rem;
        line-height: 1.3;
        min-height: unset;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-description {
        color: #6c757d;
        font-size: 0.79rem;
        line-height: 1.3;
        margin-bottom: 0.3rem;
        min-height: unset;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-btn {
        pointer-events: none;
        margin-bottom: 0.08rem;
        font-size: 0.74rem !important;
        padding: 0.19rem 0.5rem !important;
        border-radius: 7px !important;
        background: #e9ecef !important;
        color: #667eea !important;
        box-shadow: none !important;
        font-weight: 600 !important;
        text-transform: none !important;
        gap: 0.2rem !important;
        width: auto !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: flex-start;
    }
    .product-btn i {
        font-size: 0.95em;
        margin-right: 0.22em;
    }
    .buy-btn {
        background: linear-gradient(120deg, #16a34a, #22d3ee);
        margin-top: 0;
        font-size: 0.83rem;
        font-weight: 800;
        box-shadow: 0 2px 10px rgba(22, 163, 74, 0.09);
        border: none;
        border-radius: 10px;
        padding: 0.33rem 0.7rem;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        text-decoration: none;
        transition: all 0.3s;
    }
    .buy-btn:hover, .buy-btn:focus {
        background: linear-gradient(120deg, #22d3ee, #16a34a);
        color: white;
        transform: translateY(-1px) scale(1.04);
        outline: none;
        text-decoration: none;
    }

    /* --- PAGINATION --- */
    .custom-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.35rem;
        margin-top: 2.2rem;
        margin-bottom: 1.2rem;
        font-size: 0.98em;
    }
    .custom-pagination .page-btn {
        background: #fff;
        color: #667eea;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 0.38em 1.1em;
        min-width: 2.2em;
        font-weight: 600;
        box-shadow: 0 1px 6px rgba(102,126,234,0.07);
        transition: background .21s, color .21s, box-shadow .21s;
        outline: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    .custom-pagination .page-btn.active,
    .custom-pagination .page-btn:focus {
        background: linear-gradient(120deg, #667eea, #764ba2);
        color: #fff;
        border-color: #667eea;
        box-shadow: 0 2px 12px rgba(102,126,234,0.12);
        cursor: default;
    }
    .custom-pagination .page-btn:not(.active):hover {
        background: #e9ecef;
        color: #4676e8;
    }
    .custom-pagination .pagination-status {
        margin-left: 1.25em;
        color: #6c757d;
        font-size: 0.98em;
        font-weight: 500;
        letter-spacing: .01em;
    }
    .no-results {
        text-align: center;
        padding: 2rem 1rem;
        background: white;
        border-radius: 15px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.07);
        margin-bottom: 1.1rem;
        animation: fadeSlideIn 1.2s cubic-bezier(.7,.2,.4,1) 0.5s both;
    }
    .no-results-icon {
        font-size: 2.2rem;
        color: #e9ecef;
        margin-bottom: 1.2rem;
    }
    .no-results h3 {
        color: #495057;
        margin-bottom: 0.6rem;
        font-size: 1.01rem;
    }
    .no-results p {
        color: #6c757d;
        margin-bottom: 1.2rem;
        font-size: 0.89rem;
    }
</style>
@endpush

@section('content')
<div class="products-page">
    <div id="bubble-bg">
        <div class="bubble b1"></div>
        <div class="bubble b2"></div>
        <div class="bubble b3"></div>
        <div class="bubble b4"></div>
        <div class="bubble b5"></div>
        <div class="bubble b6"></div>
        <div class="bubble b7"></div>
        <div class="bubble b8"></div>
    </div>
    <!-- Products Header -->
    <div class="products-header">
        <div class="container">
            <div class="products-header-content text-center">
                <h1 class="products-title">Digital Products Marketplace</h1>
                <p class="products-subtitle">Discover premium digital products to grow your business</p>
                <div class="d-flex justify-content-center">
                    <form method="GET" action="{{ route('products.index') }}" class="search-container">
                        <div class="d-flex align-items-center w-100">
                            <input type="text" name="search" class="search-input"
                                   placeholder="Search for products, categories, or keywords..."
                                   value="{{ request('search') }}">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        @if(request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif
                        @if(request('price_min'))
                            <input type="hidden" name="price_min" value="{{ request('price_min') }}">
                        @endif
                        @if(request('price_max'))
                            <input type="hidden" name="price_max" value="{{ request('price_max') }}">
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Affiliate CTA Section -->
    <div class="affiliate-cta-section">
        <div class="container">
            <div class="affiliate-cta-card">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <div class="affiliate-cta-content">
                            <h2 class="affiliate-cta-title">
                                <i class="bi bi-trending-up me-2"></i>
                                Earn Money as an Affiliate
                            </h2>
                            <p class="affiliate-cta-subtitle">
                                Share our products and earn <strong>30% commission</strong> on every sale through your unique referral link
                            </p>
                            <ul class="affiliate-benefits-list">
                                <li><i class="bi bi-check-circle-fill me-2"></i>Generate unlimited affiliate links</li>
                                <li><i class="bi bi-check-circle-fill me-2"></i>Earn 30% commission per sale</li>
                                <li><i class="bi bi-check-circle-fill me-2"></i>Real-time dashboard tracking</li>
                                <li><i class="bi bi-check-circle-fill me-2"></i>Weekly automatic payouts via M-Pesa</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-5 text-center">
                        <div class="affiliate-cta-button-group">
                            @guest
                                <a href="{{ route('affiliate.apply') }}" class="btn btn-primary btn-lg fw-bold affiliate-btn-primary mb-3">
                                    <i class="bi bi-rocket me-2"></i>
                                    Start Your Application
                                </a>
                                <p class="text-muted small">Already have an account?</p>
                                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg fw-bold">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Sign In
                                </a>
                            @else
                                @php
                                    $userId = auth()->id();
                                    $affiliate = \App\Models\Affiliate::where('user_id', $userId)->first();
                                    $application = \App\Models\AffiliateApplication::where('user_id', $userId)->latest()->first();
                                @endphp
                                @if($affiliate)
                                    <a href="{{ route('affiliate.dashboard') }}" class="btn btn-success btn-lg fw-bold affiliate-btn-primary">
                                        <i class="bi bi-speedometer2 me-2"></i>
                                        Go to Dashboard
                                    </a>
                                @elseif($application && $application->status === 'pending')
                                    <div class="alert alert-warning fw-bold mb-0">
                                        <i class="bi bi-hourglass-split me-2"></i>
                                        Your application is under review
                                    </div>
                                @else
                                    <a href="{{ route('affiliate.apply') }}" class="btn btn-primary btn-lg fw-bold affiliate-btn-primary">
                                        <i class="bi bi-rocket me-2"></i>
                                        Apply as Affiliate
                                    </a>
                                @endif
                            @endguest
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Distribution by Category -->
    <div class="container">
        <div class="category-distribution">
            @php
                // $categoryCounts passed from controller: [category => count, ...]
                $currentCategory = request('category');
            @endphp
            @foreach($categoryCounts as $category => $count)
                <a href="{{ route('products.index', array_merge(request()->except('page'), ['category' => $category])) }}"
                   class="cat-badge{{ $currentCategory == $category ? ' active' : '' }}">
                    {{ ucfirst(str_replace(['-', '_'],' ', $category)) }} <span>({{ $count }})</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Sidebar Filters (desktop) -->
            <div class="col-lg-3 mb-4 sidebar-filters">
                @include('products._filters', ['isSidebar' => true, 'minPrice' => $minPrice, 'maxPrice' => $maxPrice])
            </div>
            <!-- Products Grid -->
            <div class="col-lg-9">
                <!-- Mobile floating filter button -->
                <button type="button" class="filter-float-btn d-lg-none" id="openFilterSheet" aria-label="Open filters">
                    <i class="fas fa-filter"></i>
                </button>
                <!-- Mobile filter sheet -->
                <div class="filter-sheet-backdrop" id="filterSheetBackdrop"></div>
                <div class="filter-sheet" id="filterSheet" aria-modal="true" role="dialog" tabindex="-1">
                    <div class="filter-sheet-handle"></div>
                    @include('products._filters', ['isSidebar' => false, 'minPrice' => $minPrice, 'maxPrice' => $maxPrice])
                </div>
                @if($products->count() > 0)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2 text-primary"></i>
                            {{ $products->total() }} Products Found
                        </h5>
                        <div class="text-muted" style="font-size:0.92em;">
                            Showing {{ $products->firstItem() }}-{{ $products->lastItem() }} of {{ $products->total() }}
                        </div>
                    </div>
                    <div class="products-grid">
                        @foreach($products as $product)
                            @php
                                $cat = strtolower($product->category ?? '');
                                $categoryClass = match(true) {
                                    str_contains($cat, 'ebook') => 'category-ebook',
                                    str_contains($cat, 'business-plan') => 'category-business-plan',
                                    str_contains($cat, 'template') => 'category-template',
                                    str_contains($cat, 'course') => 'category-course',
                                    str_contains($cat, 'software') => 'category-software',
                                    default => 'category-other'
                                };
                                $detailsUrl = route('products.show', $product);
                            @endphp
                            <div class="product-card fade-in-up {{ $categoryClass }}" onclick="window.location='{{ $detailsUrl }}';" tabindex="0" role="link" aria-label="View details of {{ $product->name }}">
                                <div class="product-image-container">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" 
                                             class="card-img-top" 
                                             alt="{{ $product->name }}"
                                             loading="lazy"
                                             decoding="async"
                                             width="160"
                                             height="160">
                                    @else
                                        <div class="product-placeholder">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="product-card-body">
                                    <div class="product-header">
                                        <span class="product-badge">{{ ucfirst(str_replace('-', ' ', $product->category ?? 'Other')) }}</span>
                                        @if($product->hasDiscount())
                                            <div style="display: flex; gap: 6px; align-items: center; margin-left: auto; flex-wrap: wrap;">
                                                <span style="text-decoration: line-through; color: #999; font-size: 0.70rem; font-weight: 700;">{{ $product->formatted_price }}</span>
                                                <span style="color: #e74c3c; font-weight: bold; font-size: 0.78rem;">KES {{ number_format($product->sale_price, 2) }}</span>
                                            </div>
                                        @else
                                            <span class="product-price">{{ $product->formatted_price }}</span>
                                        @endif
                                    </div>
                                    <h5 class="product-title">{{ $product->name }}</h5>
                                    <p class="product-description">
                                        {{ \Illuminate\Support\Str::limit($product->description ?? 'High-quality digital product designed to help you achieve your business goals and maximize your potential.', 36) }}
                                    </p>
                                    <span class="product-btn">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </span>
                                    <a href="{{ route('orders.checkout', $product->id) }}" class="buy-btn">
                                        <i class="fas fa-shopping-cart"></i> Purchase
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    {{-- Custom Pagination --}}
                    @if($products instanceof \Illuminate\Pagination\LengthAwarePaginator && $products->lastPage() > 1)
                        <div class="custom-pagination">
                            @for($i = 1; $i <= $products->lastPage(); $i++)
                                <a href="{{ $products->url($i) }}"
                                   class="page-btn{{ $i == $products->currentPage() ? ' active' : '' }}"
                                   aria-current="{{ $i == $products->currentPage() ? 'page' : false }}">
                                    {{ $i }}
                                </a>
                            @endfor
                            <span class="pagination-status">
                                Page {{ $products->currentPage() }} of {{ $products->lastPage() }}
                            </span>
                        </div>
                    @endif
                @else
                    <div class="no-results fade-in-up">
                        <div class="no-results-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>No Products Found</h3>
                        <p>
                            @if(request()->hasAny(['category', 'price_min', 'price_max', 'search']))
                                We couldn't find any products matching your current search criteria.<br>
                                Try adjusting your filters or search terms to discover more products.
                            @else
                                No digital products are currently available in our marketplace.<br>
                                Please check back later for new additions to our collection.
                            @endif
                        </p>
                        @if(request()->hasAny(['category', 'price_min', 'price_max', 'search']))
                            <a href="{{ route('products.index') }}" class="btn filter-btn">
                                <i class="fas fa-refresh me-2"></i>Clear All Filters
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* ========== Affiliate CTA Section Styles ========== */
.affiliate-cta-section {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
    padding: 60px 20px;
    margin: 40px 0;
    border-radius: 20px;
}

.affiliate-cta-card {
    background: rgba(255, 255, 255, 0.95);
    border: 2px solid #6366F1;
    border-radius: 16px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(99, 102, 241, 0.15);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.affiliate-cta-card:hover {
    box-shadow: 0 15px 50px rgba(99, 102, 241, 0.25);
    transform: translateY(-2px);
    border-color: #8B5CF6;
}

.affiliate-cta-title {
    font-size: 28px;
    font-weight: 700;
    color: #1F2937;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
}

.affiliate-cta-title i {
    color: #6366F1;
}

.affiliate-cta-subtitle {
    font-size: 16px;
    color: #6B7280;
    margin-bottom: 25px;
    line-height: 1.6;
}

.affiliate-cta-subtitle strong {
    color: #6366F1;
    font-weight: 600;
}

.affiliate-benefits-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.affiliate-benefits-list li {
    font-size: 15px;
    color: #374151;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
}

.affiliate-benefits-list i {
    color: #10B981;
    font-size: 18px;
}

.affiliate-cta-button-group {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.affiliate-btn-primary {
    background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
    border: none;
    color: white;
    padding: 14px 32px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
}

.affiliate-btn-primary:hover {
    background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
    color: white;
}

.affiliate-btn-primary:active {
    transform: translateY(0);
}

.btn-outline-primary.affiliate-btn-primary {
    background: transparent;
    border: 2px solid #6366F1;
    color: #6366F1;
}

.btn-outline-primary.affiliate-btn-primary:hover {
    background: #6366F1;
    color: white;
}

.btn-success.affiliate-btn-primary {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.btn-success.affiliate-btn-primary:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
    color: white;
}

/* Responsive */
@media (max-width: 992px) {
    .affiliate-cta-section {
        padding: 40px 15px;
        margin: 30px 0;
    }

    .affiliate-cta-card {
        padding: 30px;
    }

    .affiliate-cta-title {
        font-size: 24px;
    }

    .affiliate-cta-subtitle {
        font-size: 15px;
    }
}

@media (max-width: 576px) {
    .affiliate-cta-section {
        padding: 30px 10px;
        margin: 20px 0;
    }

    .affiliate-cta-card {
        padding: 20px;
        border-width: 1px;
    }

    .affiliate-cta-title {
        font-size: 20px;
        margin-bottom: 12px;
    }

    .affiliate-cta-subtitle {
        font-size: 14px;
        margin-bottom: 18px;
    }

    .affiliate-benefits-list li {
        font-size: 13px;
        margin-bottom: 10px;
    }

    .affiliate-btn-primary {
        padding: 12px 24px;
        font-size: 14px;
    }
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate elements on scroll
    const observerOptions = { threshold: 0.10, rootMargin: '0px 0px -40px 0px' };
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.fade-in-up').forEach(el => observer.observe(el));

    // Search input focus effect
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('focus', function() {
            this.parentElement.parentElement.classList.add('search-focused');
        });
        searchInput.addEventListener('blur', function() {
            this.parentElement.parentElement.classList.remove('search-focused');
        });
    }

    // Bubbles parallax effect
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        document.querySelectorAll('.bubble').forEach((bubble, i) => {
            bubble.style.transform = `translateY(${scrolled * (0.12 + i * 0.03)}px)`;
        });
        const header = document.querySelector('.products-header');
        if (header) header.style.transform = `translateY(${scrolled * 0.13}px)`;
    });

    // Mobile filter sheet open/close
    const openBtn = document.getElementById('openFilterSheet');
    const sheet = document.getElementById('filterSheet');
    const backdrop = document.getElementById('filterSheetBackdrop');

    function openSheet() {
        sheet.classList.add('active');
        backdrop.classList.add('active');
        sheet.focus();
        document.body.style.overflow = 'hidden';
    }
    function closeSheet() {
        sheet.classList.remove('active');
        backdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (openBtn && sheet && backdrop) {
        openBtn.addEventListener('click', openSheet);
        backdrop.addEventListener('click', closeSheet);
        sheet.addEventListener('keydown', function(e){
            if(e.key === 'Escape'){ closeSheet(); }
        });
    }
});
</script>
@endpush