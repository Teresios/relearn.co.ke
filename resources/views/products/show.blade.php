@extends('layouts.app')

@push('styles')
<style>
/* Page background and overall layout */
.product-detail-page {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 3.5rem 0 2.5rem 0;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
}

/* Main snapshot card */
.product-detail-snapshot {
    background: rgba(255,255,255,0.98);
    border-radius: 24px;
    box-shadow: 0 8px 44px 0 rgba(102,126,234,0.12), 0 1.5px 24px 0 rgba(102,126,234,0.10);
    max-width: 950px;
    width: 100%;
    display: flex;
    gap: 2.8rem;
    padding: 2.1rem 2.9rem;
    align-items: flex-start;
    margin: 0 auto 1.7rem auto;
    transition: box-shadow .2s cubic-bezier(.4,.2,.2,1);
    animation: fadeInUpCard 0.8s cubic-bezier(.4,.2,.2,1);
}
@keyframes fadeInUpCard {
    from { opacity: 0; transform: translateY(42px);}
    to { opacity: 1; transform: translateY(0);}
}
.product-detail-snapshot:hover {
    box-shadow: 0 16px 60px 0 rgba(102,126,234,0.14), 0 3px 28px 0 rgba(102,126,234,0.19);
}

.product-image-section {
    flex: 0 0 300px;
    min-width: 220px;
    max-width: 350px;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    position: relative;
}
.product-image,
.product-placeholder {
    width: 280px;
    height: 280px;
    object-fit: contain;
    border-radius: 18px;
    box-shadow: 0 4px 18px rgba(59,130,246,0.16);
    background: linear-gradient(135deg, #f8fafc 0%, #e9ecef 100%);
    border: 2px solid #e0e7ff;
    transition: box-shadow .21s, transform .2s;
    padding: 1rem;
}
.product-image:hover {
    box-shadow: 0 16px 32px rgba(102,126,234,0.22);
    transform: scale(1.03) rotate(-2deg);
}
.product-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #7b809a;
    font-size: 3.1rem;
    background: linear-gradient(135deg, #f8fafc 60%, #e0eafc 100%);
}
.image-badge {
    position: absolute;
    left: 10px; bottom: 10px;
    background: linear-gradient(120deg, #667eea, #764ba2 80%);
    color: #fff;
    font-size: 0.77rem;
    font-weight: 600;
    border-radius: 12px;
    padding: 0.26rem 0.7rem;
    box-shadow: 0 2px 6px rgba(118, 75, 162, 0.10);
}

/* Right side content */
.product-details-main {
    flex: 1 1 0%;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    min-width: 0;
}

/* Title & meta */
.product-title {
    font-size: 2rem;
    font-weight: 900;
    margin-bottom: 0.08rem;
    color: #22253f;
    line-height: 1.07;
    text-shadow: 0 1px 4px rgba(118,75,162,0.03);
    letter-spacing: 0.5px;
}
.product-meta-row {
    display: flex;
    gap: 0.65rem;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 0.18rem;
}
.product-category-badge {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 0.23rem 0.83rem;
    border-radius: 15px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.45px;
    font-size: 0.97rem;
    box-shadow: 0 1.5px 6px rgba(40,167,69,0.11);
}
.product-price {
    background: linear-gradient(120deg, #e0e7ff, #c7d2fe);
    color: #5a67d8;
    padding: 0.31rem 0.88rem;
    border-radius: 14px;
    font-weight: 800;
    font-size: 1.18rem;
    margin-left: 0.12rem;
    border: 1px solid #e0e7ff;
    box-shadow: 0 1px 4px rgba(102,126,234,0.06);
    letter-spacing: .2px;
}
.file-info {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 0.39rem 0.85rem 0.39rem 0.7rem;
    font-size: 0.92rem;
    color: #393e4e;
    margin-bottom: 0.32rem;
    display: inline-block;
    border-left: 3.5px solid #4a90e2;
    box-shadow: 0 1px 6px rgba(102,126,234,0.04);
}
.ownership-status {
    background: linear-gradient(120deg, #28a745 60%, #20c997 100%);
    color: #fff;
    padding: 0.5rem 1rem;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 0.38rem;
    font-size: 1rem;
    font-weight: 600;
    box-shadow: 0 1px 6px rgba(40,167,69,0.10);
    letter-spacing: 0.2px;
}

/* Buttons */
.action-btn, .action-btn.success, .action-btn.outline, .back-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    font-size: 0.99rem;
    border: none;
    border-radius: 19px;
    font-weight: 700;
    padding: 0.46rem 1.32rem;
    margin: 0 0.3rem 0.3rem 0;
    background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
    color: white;
    cursor: pointer;
    box-shadow: 0 2px 12px rgba(102,126,234,0.10);
    transition: all 0.21s cubic-bezier(.4,.2,.2,1);
    text-decoration: none;
    letter-spacing: 0.17px;
    border: 1.5px solid transparent;
    position: relative;
    overflow: hidden;
}
.action-btn.success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}
.action-btn.outline {
    background: linear-gradient(135deg, #fff 100%, #fff 100%);
    color: #4a90e2;
    border: 2px solid #4a90e2;
}
.action-btn.outline:hover,
.action-btn.outline:focus {
    background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
    color: white;
}
.action-btn.success:hover { box-shadow: 0 8px 25px rgba(40,167,69,0.09);}
.action-btn:hover, .back-button:hover { filter: brightness(1.08); box-shadow: 0 6px 18px rgba(102,126,234,0.12);}
.back-button {
    background: #f8f9fa;
    color: #4a90e2;
    border: 2px solid #4a90e2;
    margin-top: 0.7rem;
    margin-bottom: 0.2rem;
    font-weight: 700;
}
.back-button:hover {
    background: #4a90e2;
    color: #fff;
}
.action-btn:focus, .back-button:focus { box-shadow: 0 0 0 2px #4a90e2; }

/* Features "What you get" */
.features-list {
    list-style: none;
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem 1.3rem;
    padding: 0;
    margin: 0.18rem 0 0.45rem 0;
    font-size: 0.97rem;
}
.features-list li {
    display: flex;
    align-items: center;
    padding: 0;
    border: none;
    background: none;
}
.feature-icon {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    width: 19px;
    height: 19px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.53rem;
    font-size: 1rem;
    box-shadow: 0 1px 4px rgba(40,167,69,0.09);
    transition: transform 0.15s;
}

/* Description */
.product-description-text {
    font-size: 1.07rem;
    line-height: 1.6;
    color: #495057;
    margin-bottom: 0.18rem;
    max-height: 67px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: pre-line;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    background: #f7f9fb;
    padding: 0.62rem 1rem;
    border-radius: 9px;
    box-shadow: 0 1px 6px rgba(102,126,234,0.05);
}

/* Tags */
.tags-section {
    margin-top: 0.3rem;
}
.tag-badge {
    background: linear-gradient(135deg, #fd7e14 0%, #e55a4e 100%);
    color: white;
    padding: 0.22rem 0.7rem;
    border-radius: 13px;
    margin-right: 0.3rem;
    margin-bottom: 0.13rem;
    font-size: 0.81rem;
    font-weight: 600;
    display: inline-block;
    letter-spacing: 0.17px;
    box-shadow: 0 1px 4px rgba(253,126,20,0.09);
    transition: transform 0.18s;
}
.tag-badge:hover,
.tag-badge:focus {
    transform: translateY(-1.5px) scale(1.06);
    filter: brightness(1.07);
}

/* Mobile responsiveness */
@media (max-width: 1000px) {
    .product-detail-snapshot { max-width: 99vw; padding: 1.1rem 0.3rem; gap: 1.2rem;}
    .product-image-section { flex: 0 0 220px; }
    .product-image, .product-placeholder { width: 200px; height: 200px; }
    .product-title { font-size: 1.3rem;}
}
@media (max-width: 650px) {
    .product-detail-snapshot { flex-direction: column; align-items: stretch; padding: 0.65rem 0.09rem; }
    .product-details-main { padding: 0.38rem 0.05rem 0.1rem 0.05rem; }
    .product-image-section { justify-content: flex-start; }
    .product-image, .product-placeholder { width: 260px; height: 260px; }
    .product-title { font-size: 1.07rem;}
    .action-btn, .back-button { font-size: 0.89rem; padding: 0.48rem 1rem; }
}
</style>
@endpush

@section('content')
<div class="product-detail-page" role="main">
    <div class="product-detail-snapshot fade-in-up" tabindex="0" aria-label="Product details summary snapshot">
        <div class="product-image-section">
            @if($product->image)
                <img src="{{ Storage::url($product->image) }}" class="product-image" alt="{{ $product->name }} product image">
                <span class="image-badge"><i class="fas fa-image" aria-hidden="true"></i></span>
            @else
                <div class="product-placeholder" aria-label="No product image available">
                    <i class="fas fa-file-alt" aria-hidden="true"></i>
                </div>
            @endif
        </div>
        <div class="product-details-main">
            <div class="product-title">{{ $product->name }}</div>
            <div class="product-meta-row">
                <span class="product-category-badge">{{ ucfirst(str_replace('-', ' ', $product->category)) }}</span>
                @if($product->hasDiscount())
                    <span class="product-price" style="text-decoration: line-through; color: #999; margin-right: 8px;">{{ $product->formatted_price }}</span>
                    <span class="product-price" style="color: #e74c3c; font-weight: bold;">KES {{ number_format($product->sale_price, 2) }}</span>
                @else
                    <span class="product-price">{{ $product->formatted_price }}</span>
                @endif
                @if($product->file_size)
                    <span class="file-info">
                        <i class="fas fa-download me-2 text-primary"></i>
                        {{ $product->formatted_file_size }}
                    </span>
                @endif
            </div>
            @if($product->preview_url)
                <a href="{{ $product->preview_url }}" target="_blank" class="action-btn outline" aria-label="Preview Product">
                    <i class="fas fa-eye me-2" aria-hidden="true"></i>
                    Preview
                </a>
            @endif
            @if($product->hasSample())
                <a href="{{ route('products.sample', $product) }}" class="action-btn outline" style="border-color: #28a745; color: #28a745;" aria-label="Download Free Sample">
                    <i class="fas fa-file-pdf me-2" aria-hidden="true"></i>
                    📖 Free Sample
                    <small style="opacity:.7; margin-left:4px;">({{ $product->formatted_sample_file_size }})</small>
                </a>
            @endif
            @auth
                @php
                    $userHasPurchased = Auth::user()->orders()
                        ->where('product_id', $product->id)
                        ->where('status', 'completed')
                        ->exists();
                @endphp
                @if($userHasPurchased)
                    <a href="{{ route('downloads.index') }}" class="action-btn success" aria-label="Download Now">
                        <i class="fas fa-download me-2"></i>
                        Download
                    </a>
                    <span class="ownership-status">
                        <i class="fas fa-check-circle me-2"></i>
                        You own this product
                    </span>
                @else
                    <a href="{{ route('orders.checkout', $product) }}" class="action-btn m-pesa-btn" aria-label="Buy Now with M-Pesa">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Buy with M-Pesa
                    </a>
                @endif
            @else
                <a href="{{ route('orders.checkout', $product) }}" class="action-btn m-pesa-btn" aria-label="Buy Now with M-Pesa">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Buy with M-Pesa
                </a>
                <div>
                    <small class="text-muted">
                        You can purchase without creating an account. After purchase you'll receive a download link.
                    </small>
                </div>
            @endauth
            <ul class="features-list">
                <li><span class="feature-icon"><i class="fas fa-bolt"></i></span>Instant download</li>
                <li><span class="feature-icon"><i class="fas fa-shield-alt"></i></span>M-Pesa payment</li>
                <li><span class="feature-icon"><i class="fas fa-envelope"></i></span>Email link</li>
                <li><span class="feature-icon"><i class="fas fa-clock"></i></span>24/7 access</li>
                <li><span class="feature-icon"><i class="fas fa-headset"></i></span>Support</li>
            </ul>
            <div class="product-description-text" title="{{ $product->description }}">
                {{ $product->description ?? 'This premium digital product is crafted to provide value and help your business. Download instantly after purchase.' }}
            </div>
            @if(!empty($product->tags) && is_array($product->tags))
                <div class="tags-section">
                    @foreach($product->tags as $tag)
                        <span class="tag-badge">{{ $tag }}</span>
                    @endforeach
                </div>
            @endif
            <a href="{{ route('products.index') }}" class="back-button mt-2">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Products
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate in
    document.querySelectorAll('.fade-in-up').forEach(el => {
        el.classList.add('animate');
    });

    // Lightbox image zoom
    const productImage = document.querySelector('.product-image');
    if (productImage) {
        productImage.addEventListener('click', function() {
            const overlay = document.createElement('div');
            overlay.style.cssText = `
                position: fixed;
                top: 0; left: 0; width: 100vw; height: 100vh;
                background: rgba(0,0,0,0.93);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                opacity: 0;
                transition: opacity 0.26s cubic-bezier(.4,.2,.2,1);
            `;
            const enlargedImage = document.createElement('img');
            enlargedImage.src = this.src;
            enlargedImage.alt = this.alt;
            enlargedImage.style.cssText = `
                max-width: 93vw; max-height: 93vh; object-fit: contain;
                border-radius: 10px; box-shadow: 0 20px 60px rgba(0,0,0,0.55);
                transform: scale(0.8); transition: transform 0.3s cubic-bezier(.4,.2,.2,1);
            `;
            overlay.appendChild(enlargedImage);
            document.body.appendChild(overlay);

            setTimeout(() => {
                overlay.style.opacity = '1';
                enlargedImage.style.transform = 'scale(1)';
            }, 18);

            overlay.addEventListener('click', function() {
                overlay.style.opacity = '0';
                enlargedImage.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    document.body.removeChild(overlay);
                }, 200);
            });
        });
    }
});
</script>
@endpush