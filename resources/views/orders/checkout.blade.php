@extends('layouts.app')

@push('styles')
<style>
.checkout-page {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 1rem 0;
    background-size: cover;
    background-attachment: fixed;
}
.checkout-container {
    background: rgba(255, 255, 255, 0.97);
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(102,126,234,0.15);
    margin-top: 1rem;
    margin-bottom: 1rem;
    overflow: hidden;
}
.checkout-header {
    background: linear-gradient(135deg, #4a90e2 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem 1rem 1rem 1rem;
    border-radius: 18px 18px 0 0;
    position: relative;
    overflow: hidden;
}
.checkout-title {
    font-size: 1.7rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}
.checkout-subtitle {
    opacity: 0.9;
    margin-bottom: 0;
}
.checkout-body {
    padding: 1.3rem 1.1rem 1.1rem 1.1rem;
}
.product-summary-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 13px;
    padding: 1rem;
    margin-bottom: 1.1rem;
    border: 1px solid #e9ecef;
    position: relative;
}
.product-image-container {
    position: relative;
    overflow: hidden;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    margin-bottom: 1rem;
}
.product-image {
    width: 100%;
    height: 165px;
    object-fit: contain;
    transition: transform 0.3s ease;
    border-radius: 10px;
}
.product-placeholder {
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    height: 165px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 2.2rem;
    border-radius: 10px;
}
.product-info {
    padding-left: 0;
}
.product-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.4rem;
}
.product-description {
    color: #6c757d;
    margin-bottom: 0.7rem;
    font-size: 0.97rem;
    line-height: 1.5;
}
.product-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.97rem;
}
.category-badge {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 0.35rem 0.9rem;
    border-radius: 19px;
    font-weight: 500;
    font-size: 0.82rem;
    text-transform: uppercase;
        border: 1px solid #e0e0e0;
        height: fit-content;
        position: sticky;
        top: 1rem;
    }

    .summary-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e0e0e0;
    }

    .product-image-sm {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 4px;
        margin-bottom: 1rem;
    }

    .product-name-sm {
        font-weight: 600;
        color: #1a1a1a;
        font-size: 0.95rem;
        margin-bottom: 1rem;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        color: #666;
        font-size: 0.95rem;
    }

    .summary-row.total {
        font-weight: 700;
        color: #1a1a1a;
        font-size: 1.1rem;
        padding-top: 1rem;
        border-top: 2px solid #e0e0e0;
        margin-bottom: 0;
    }

    .price-highlight {
        color: #ff6b35;
        font-weight: 700;
    }

    .payment-btn {
        width: 100%;
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        color: white;
        border: none;
        padding: 1rem;
        border-radius: 4px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        margin-top: 1.5rem;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .payment-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 107, 53, 0.3);
    }

    .payment-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .trust-badges {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e0e0e0;
        justify-content: center;
    }

    .badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        color: #666;
    }

    .badge i {
        color: #ff6b35;
        font-size: 1rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #ff6b35;
    }

    .loading-spinner {
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top: 3px solid white;
        width: 20px;
        height: 20px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
}
.product-price {
    background: linear-gradient(135deg, #fd7e14 0%, #e55a4e 100%);
    color: white;
    padding: 0.5rem 1.2rem;
    border-radius: 19px;
    font-weight: 700;
    font-size: 1.05rem;
}

.form-section {
    background: white;
    border-radius: 11px;
    padding: 0.7rem 0.8rem;
    margin-bottom: 0.7rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    border: 1px solid #e9ecef;
}
.section-title {
    color: #764ba2;
    font-size: 1.07rem;
    font-weight: 600;
    margin-bottom: 0.65rem;
    padding-bottom: 0.2rem;
    border-bottom: 1px solid #f1f3f4;
    position: relative;
}
.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.2rem;
    font-size: 0.97rem;
}
.form-control {
    border: 2px solid #e9ecef;
    border-radius: 7px;
    padding: 0.5rem 0.8rem;
    font-size: 0.97rem;
    transition: all 0.2s ease;
    background: #f8f9fa;
}
.form-control:focus {
    border-color: #764ba2;
    box-shadow: 0 0 0 2px rgba(118,75,162,0.09);
    background: white;
}
.form-control[readonly] {
    background: #e9ecef;
    color: #6c757d;
}
.order-summary-card {
    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    border: 1px solid #e9ecef;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    margin-bottom: 0.7rem;
}
.order-summary-header {
    background: linear-gradient(135deg, #6f42c1 0%, #5a2d91 100%);
    color: white;
    padding: 0.7rem 1rem;
    font-size: 0.97rem;
    border-radius: 10px 10px 0 0;
}
.order-summary-body {
    padding: 0.8rem 1rem;
}
.summary-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.35rem 0;
    border-bottom: 1px solid #f1f3f4;
    font-size: 0.97rem;
}
.summary-line:last-child {
    border-bottom: none;
    font-weight: 700;
    font-size: 1.03rem;
    color: #2c3e50;
    background: #f8f9fa;
    margin: 0 -1rem -1rem;
    padding: 1rem;
}
.benefits-list {
    list-style: none;
    padding: 0;
    margin: 0.6rem 0 0;
}
.benefits-list li {
    display: flex;
    align-items: center;
    padding: 0.2rem 0;
    color: #495057;
    font-size: 0.97rem;
}
.benefit-icon {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.6rem;
    font-size: 0.7rem;
}

/* Payment Method box smaller & symmetrical, below customer info */
.payment-method-card {
    background: white;
    border: 2px solid #4a90e2;
    border-radius: 8px;
    padding: 0.5rem 0.5rem 0.3rem 0.5rem;
    text-align: center;
    margin-bottom: 0.7rem;
    margin-top: 0.2rem;
    box-shadow: 0 1px 7px rgba(102,126,234,0.12);
    position: relative;
    max-width: 330px;
    margin-left: auto;
    margin-right: auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.payment-content {
    position: relative;
    z-index: 2;
    width: 100%;
}
.mpesa-logo {
    height: 22px;
    margin-bottom: 0.22rem;
}
.payment-title {
    font-size: 0.93rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.13rem;
}
.payment-description {
    color: #6c757d;
    margin-bottom: 0.1rem;
    font-size: 0.91rem;
}

.terms-section {
    background: #f8f9fa;
    border-radius: 7px;
    padding: 0.7rem 0.5rem;
    margin-bottom: 0.6rem;
    border-left: 4px solid #764ba2;
    border-right: 4px solid #764ba2;
    box-shadow: 0 4px 20px rgba(118,75,162,0.08);
    font-size: 0.97rem;
}
.terms-section .form-check {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
}
.form-check-input {
    width: 1.1em;
    height: 1.1em;
    margin-top: 0.09em;
    accent-color: #764ba2;
    border: 2px solid #764ba2;
    box-shadow: 0 2px 7px rgba(118,75,162,0.11);
    transition: box-shadow 0.18s;
}
.form-check-label {
    font-weight: 700;
    font-size: 0.97rem;
    color: #4a2975;
    background: linear-gradient(90deg, #eae2f8 30%, #f8f9fa 100%);
    padding: 0.15em 0.5em;
    border-radius: 4px;
    transition: background 0.2s;
    line-height: 1.5;
    display: block;
}
.form-check-input:focus {
    box-shadow: 0 0 0 2px rgba(118,75,162,0.12);
}
.form-check-input:checked {
    background-color: #764ba2;
    border-color: #764ba2;
}
.action-buttons {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
    flex-wrap: wrap;
    margin-top: 0.5rem;
}
.btn-back {
    background: transparent;
    border: 2px solid #6c757d;
    color: #6c757d;
    padding: 0.5rem 1.2rem;
    border-radius: 13px;
    font-weight: 600;
    font-size: 0.96rem;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
}
.btn-back:hover {
    background: #6c757d;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 3px 9px rgba(108, 117, 125, 0.14);
}
.btn-pay {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    color: white;
    padding: 0.6rem 1.5rem;
    border-radius: 13px;
    font-weight: 700;
    font-size: 0.99rem;
    text-transform: uppercase;
    letter-spacing: 0.34px;
    transition: all 0.2s ease;
    position: relative;
}
.btn-pay:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 13px rgba(40, 167, 69, 0.17);
    color: white;
}
.btn-pay:disabled {
    opacity: 0.6;
    transform: none;
    box-shadow: none;
}
.fade-in-up {
    opacity: 0;
    transform: translateY(30px);
    animation: fadeInUp 0.6s ease-out forwards;
}
@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
/* Responsive mobile-first adjustments */
@media (max-width: 991px) {
    .checkout-container {
        border-radius: 11px;
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .checkout-body {
        padding: 1rem 0.3rem;
    }
    .payment-method-card {
        max-width: 100%;
        margin-left: 0;
        margin-right: 0;
    }
}
@media (max-width: 768px) {
    .checkout-header {
        padding: 1rem 0.7rem 0.8rem 0.7rem;
    }
    .checkout-title {
        font-size: 1.1rem;
    }
    .product-summary-card,
    .form-section,
    .order-summary-card {
        padding: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .product-image,
    .product-placeholder {
        height: 100px;
    }
    .action-buttons {
        justify-content: center;
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 0.4rem;
    }
    .btn-back,
    .btn-pay {
        width: 100%;
        text-align: center;
        justify-content: center;
    }
}
@media (max-width: 576px) {
    .checkout-container {
        border-radius: 6px;
        margin: 0.2rem;
    }
    .checkout-header {
        border-radius: 6px 6px 0 0;
        padding: 0.7rem 0.4rem 0.5rem 0.4rem;
    }
    .checkout-title {
        font-size: 0.97rem;
    }
    .terms-section {
        font-size: 0.97rem;
        padding: 0.5rem 0.2rem;
    }
    .form-check-label {
        font-size: 0.94rem;
    }
}
</style>
@endpush

@section('content')
<div class="checkout-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-10 col-md-12">
                <div class="checkout-container fade-in-up">
                    <!-- Header -->
                    <div class="checkout-header">
                        <h1 class="checkout-title">
                            <i class="fas fa-credit-card me-2"></i>
                            Secure Checkout
                        </h1>
                        <p class="checkout-subtitle">Complete your purchase with M-Pesa payment</p>
                    </div>
                    <div class="checkout-body">
                        <!-- Product Summary -->
                        <div class="product-summary-card fade-in-up">
                            <div class="row g-3 align-items-start">
                                <div class="col-12 col-md-5">
                                    <div class="product-image-container">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" class="product-image" alt="{{ $product->name }}" loading="lazy" decoding="async" onerror="this.parentElement.innerHTML='<div class=\"product-placeholder\"><i class=\"fas fa-image\"></i></div>'">
                                        @else
                                            <div class="product-placeholder">
                                                <i class="fas fa-file-alt"></i>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-12 col-md-7">
                                    <div class="product-info">
                                        <h2 class="product-name">{{ $product->name }}</h2>
                                        <p class="product-description">
                                            {{ $product->description ?: 'Premium digital product designed to provide exceptional value for your business needs.' }}
                                        </p>
                                        <div class="product-meta">
                                            <span class="category-badge">{{ ucfirst(str_replace('-', ' ', $product->category)) }}</span>
                                            <span class="product-price">
                                                @if($product->hasDiscount())
                                                    <span style="text-decoration: line-through; color: #999; margin-right: 8px;">{{ $product->formatted_price }}</span>
                                                    <span style="color: #e74c3c; font-weight: bold;">KES {{ number_format($product->sale_price, 2) }}</span>
                                                @else
                                                    {{ $product->formatted_price }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Checkout Form -->
                        <form action="{{ route('orders.store', $product) }}" method="POST" id="checkoutForm" autocomplete="off">
                            @csrf
                            <div class="row g-2">
                                <div class="col-12 col-lg-7">
                                    <!-- Customer Info -->
                                    <div class="form-section fade-in-up">
                                        <h3 class="section-title">
                                            <i class="fas fa-user me-1"></i>
                                            @auth
                                                Customer Information
                                            @else
                                                Order Information
                                            @endauth
                                        </h3>
                                        @auth
                                            {{-- Authenticated User: Show full form with name --}}
                                            <div class="row">
                                                <div class="col-12 col-md-6 mb-2">
                                                    <label for="customer_name" class="form-label">Full Name</label>
                                                    <input type="text" class="form-control" id="customer_name" name="customer_name"
                                                           value="{{ Auth::user()->name }}" required readonly>
                                                </div>
                                                <div class="col-12 col-md-6 mb-2">
                                                    <label for="customer_email" class="form-label">Email Address</label>
                                                    <input type="email" class="form-control" id="customer_email" name="customer_email"
                                                           value="{{ Auth::user()->email }}" required readonly>
                                                </div>
                                            </div>
                                        @else
                                            {{-- Guest: Show only email field --}}
                                            <div class="mb-2">
                                                <label for="customer_email" class="form-label">
                                                    <i class="fas fa-envelope me-1"></i>
                                                    Email Address (for download link)
                                                </label>
                                                <input type="email" class="form-control" id="customer_email" name="customer_email"
                                                       value="{{ old('customer_email') }}" required placeholder="your@email.com">
                                                <div class="form-text">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    We'll send your download link to this email
                                                </div>
                                            </div>
                                        @endauth
                                        <div class="mb-2">
                                            <label for="customer_phone" class="form-label">
                                                <i class="fas fa-mobile-alt me-1"></i>
                                                M-Pesa Phone Number
                                            </label>
                          <input type="tel" class="form-control @error('customer_phone') is-invalid @enderror"
                              id="customer_phone" name="customer_phone"
                              value="{{ old('customer_phone', optional(Auth::user())->phone) }}"
                              placeholder="enter phone number" required pattern="^(?:0[0-9]{9}|254[0-9]{9}|7[0-9]{8})$">
                                            @error('customer_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Enter your M-Pesa phone number
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Payment Method (smaller and below customer info) -->
                                    <div class="payment-method-card fade-in-up">
                                        <div class="payment-content">
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/1/15/M-PESA_LOGO-01.svg"
                                                 alt="M-Pesa" class="mpesa-logo">
                                            <div class="payment-title">M-Pesa STK Push</div>
                                            <div class="payment-description">Secure and instant payment via M-Pesa</div>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-shield-alt me-1"></i>
                                                    SSL encryption • PCI DSS compliant
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-5">
                                    <!-- Order Summary -->
                                    <div class="form-section fade-in-up">
                                        <div class="order-summary-card">
                                            <div class="order-summary-header">
                                                <h3 class="mb-0">
                                                    <i class="fas fa-receipt me-1"></i>
                                                    Order Summary
                                                </h3>
                                            </div>
                                            <div class="order-summary-body">
                                                <div class="summary-line">
                                                    @if($product->hasDiscount())
                                                        <span>Original Price:</span>
                                                        <span style="text-decoration: line-through; color: #999;">KES {{ number_format($product->price, 2) }}</span>
                                                    @else
                                                        <span>Product Price:</span>
                                                        <span>KES {{ number_format($product->price, 2) }}</span>
                                                    @endif
                                                </div>
                                                @if($product->hasDiscount())
                                                    <div class="summary-line" style="background: #fff3cd; padding: 8px; border-radius: 6px; margin: 8px 0;">
                                                        <span><strong>Sale Price:</strong></span>
                                                        <span style="color: #e74c3c; font-weight: bold;">KES {{ number_format($product->sale_price, 2) }}</span>
                                                    </div>
                                                    <div class="summary-line">
                                                        <span><strong>You Save:</strong></span>
                                                        <span style="color: #27ae60; font-weight: bold;">KES {{ number_format($product->price - $product->sale_price, 2) }} ({{ $product->getDiscountPercentage() }}%)</span>
                                                    </div>
                                                @endif
                                                <div class="summary-line">
                                                    <span>Processing Fee:</span>
                                                    <span class="text-success">FREE</span>
                                                </div>
                                                <div class="summary-line">
                                                    <span>Total Amount:</span>
                                                    <span class="text-primary">KES {{ number_format($product->getEffectivePrice(), 2) }}</span>
                                                </div>
                                                <ul class="benefits-list">
                                                    <li>
                                                        <span class="benefit-icon"><i class="fas fa-bolt"></i></span>
                                                        Instant download access
                                                    </li>
                                                    <li>
                                                        <span class="benefit-icon"><i class="fas fa-envelope"></i></span>
                                                        Email download link
                                                    </li>
                                                    <li>
                                                        <span class="benefit-icon"><i class="fas fa-clock"></i></span>
                                                        24/7 access to purchase
                                                    </li>
                                                    <li>
                                                        <span class="benefit-icon"><i class="fas fa-headset"></i></span>
                                                        Customer support
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Terms and Submit -->
                            <div class="terms-section fade-in-up">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="terms" required checked>
                                    <label class="form-check-label" for="terms">
                                        <i class="fas fa-check-circle me-1" style="color:#28a745;"></i>
                                        <span style="font-weight:bold;">I agree to the</span>
                                        <a href="{{ route('terms') }}" target="_blank" class="text-primary" style="font-weight:bold;">Terms of Service</a>
                                        <span style="font-weight:bold;">and</span>
                                        <a href="#" target="_blank" class="text-primary" style="font-weight:bold;">Privacy Policy</a>
                                    </label>
                                </div>
                                <div class="action-buttons">
                                    <a href="{{ route('products.show', $product) }}" class="btn-back">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Back to Product
                                    </a>
                                    <button type="submit" class="btn-pay" id="payButton">
                                        <i class="fas fa-mobile-alt me-2"></i>
                                        Pay with M-Pesa
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div><!-- checkout-body -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate elements on scroll
    const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, observerOptions);
    document.querySelectorAll('.fade-in-up').forEach(el => observer.observe(el));

        // Phone validation and normalization to 254XXXXXXXXX
    const phoneInput = document.getElementById('customer_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            // Allow digits and optionally a leading +
            let value = e.target.value.replace(/[^0-9+]/g, '');
            // Trim leading + if pasted
            if (value.startsWith('+')) value = value.substring(1);
            // Do not force normalization on each keystroke; just update the field visually
            e.target.value = value;
            // Set basic validity styling
            if (/^(?:0[0-9]{9}|254[0-9]{9}|7[0-9]{8})$/.test(value)) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            } else {
                e.target.classList.remove('is-valid');
                if (value.length > 0) e.target.classList.add('is-invalid');
            }
        });

        phoneInput.addEventListener('blur', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            if (/^0[0-9]{9}$/.test(value)) {
                value = '254' + value.substring(1);
            } else if (/^[7][0-9]{8}$/.test(value)) {
                value = '254' + value;
            }
            e.target.value = value;
            if (value && (!value.startsWith('254') || value.length !== 12)) {
                this.setCustomValidity('Please enter a valid M-Pesa number (e.g. 0723... or 254723...)');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Terms checkbox
    const payButton = document.getElementById('payButton');
    const termsCheckbox = document.getElementById('terms');
    if (payButton && termsCheckbox) {
        // Ensure the button reflects the checkbox state on load. We default to checked.
        payButton.disabled = !termsCheckbox.checked;
        payButton.style.opacity = termsCheckbox.checked ? '1' : '0.6';
        termsCheckbox.addEventListener('change', function() {
            payButton.disabled = !this.checked;
            payButton.style.opacity = this.checked ? '1' : '0.6';
        });
    }

    // Form loading overlay
    const checkoutForm = document.getElementById('checkoutForm');
            if (checkoutForm && payButton) {
        checkoutForm.addEventListener('submit', function(e) {
            let phone = phoneInput.value.replace(/[^0-9]/g, '');
            // Normalize before submitting
            if (/^0[0-9]{9}$/.test(phone)) {
                phone = '254' + phone.substring(1);
            } else if (/^[7][0-9]{8}$/.test(phone)) {
                phone = '254' + phone;
            }
            phoneInput.value = phone;
            if (!phone || phone.length !== 12 || !phone.startsWith('254')) {
                e.preventDefault();
                phoneInput.focus();
                showAlert('Please enter a valid M-Pesa phone number (e.g. 0723... or 254723...)', 'error');
                return;
            }
            payButton.disabled = true;
            payButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing Payment...';
            const loadingOverlay = document.createElement('div');
            loadingOverlay.style.cssText = `
                position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7);
                z-index:9999; display:flex; align-items:center; justify-content:center; backdrop-filter: blur(5px);
            `;
            const loadingContent = document.createElement('div');
            loadingContent.style.cssText = `
                background:white; padding:2rem; border-radius:15px; text-align:center; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                max-width:90%; width:320px;
            `;
            loadingContent.innerHTML = `
                <div style="font-size:2rem; color:#764ba2; margin-bottom:1rem;"><i class="fas fa-mobile-alt"></i></div>
                <h4 style="color:#2c3e50; margin-bottom:0.4rem;">Processing Payment</h4>
                <p style="color:#6c757d; margin-bottom:1.1rem;">Please check your phone for the M-Pesa prompt</p>
                <div style="display:flex; justify-content:center; align-items:center; gap:0.5rem;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <span style="color:#495057;">Initiating STK Push...</span>
                </div>
            `;
            loadingOverlay.appendChild(loadingContent);
            document.body.appendChild(loadingOverlay);
        });
    }

    // Alert function
    function showAlert(message, type = 'info') {
        const alert = document.createElement('div');
        alert.style.cssText = `
            position: fixed; top:20px; right:20px;
            background:${type === 'error' ? '#dc3545' : type === 'success' ? '#28a745' : '#764ba2'};
            color:white; padding:0.85rem 1.2rem; border-radius:9px; box-shadow:0 6px 20px rgba(0,0,0,0.17);
            z-index:10000; opacity:0; transform:translateX(100%); transition:all 0.3s ease; max-width:300px;
        `;
        alert.innerHTML = `<div style="display:flex; align-items:center; gap:0.5rem;">
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>`;
        document.body.appendChild(alert);
        setTimeout(() => { alert.style.opacity = '1'; alert.style.transform = 'translateX(0)'; }, 100);
        setTimeout(() => {
            alert.style.opacity = '0'; alert.style.transform = 'translateX(100%)';
            setTimeout(() => { if (alert.parentNode) alert.parentNode.removeChild(alert); }, 300);
        }, 4000);
    }
});
</script>
@endpush