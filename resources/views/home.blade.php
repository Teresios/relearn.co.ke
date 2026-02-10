@extends('layouts.app')

@push('styles')
<style>
    /* Hero Section Styling */
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 80vh;
        position: relative;
        overflow: hidden;
    }
    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="rgba(255,255,255,0.1)"><polygon points="1000,100 1000,0 0,100"/></svg>');
        background-size: cover;
        z-index: 1;
    }
    .hero-content {
        position: relative;
        z-index: 2;
    }
    .hero-title {
        font-size: 2.4rem;
        font-weight: 700;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.22);
        margin-bottom: 1.1rem;
        line-height: 1.2;
    }
    .hero-subtitle {
        font-size: 1.08rem;
        margin-bottom: 1.6rem;
        opacity: 0.94;
    }
    .hero-cta {
        padding: 12px 32px;
        font-size: 1rem;
        border-radius: 50px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        transition: all 0.25s;
    }
    .hero-cta-white {
        background: #fff !important;
        color: #764ba2 !important;
        border: none !important;
    }
    .hero-cta-white:hover {
        background: #f7f7fb !important;
        color: #5a369d !important;
    }
    .hero-cta-outline {
        background: transparent;
        border: 2px solid #fff;
        color: #fff;
    }
    .hero-cta:hover, .hero-cta-outline:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 35px rgba(0,0,0,0.18);
    }
    .hero-animation {
        animation: float 6s ease-in-out infinite;
    }
    @keyframes float {
        0%, 100% { transform: translateY(0px);}
        50% { transform: translateY(-20px);}
    }
    .hero-whatsapp-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4em;
        background: #25d366;
        color: #fff !important;
        font-weight: 700;
        font-size: 1.02rem;
        border-radius: 2em;
        padding: 0.55em 1.2em;
        margin: 1.1em 0 0.3em 0;
        box-shadow: 0 2px 12px rgba(37,211,102,0.10);
        border: none;
        text-decoration: none !important;
        transition: background 0.16s, transform 0.16s;
    }
    .hero-whatsapp-btn:hover, .hero-whatsapp-btn:focus {
        background: #128c7e;
        color: #fff !important;
        transform: scale(1.04);
        outline: none;
        text-decoration: none !important;
    }
    .hero-whatsapp-btn i {
        font-size: 1.22em;
    }
    /* Feature Cards */
    .feature-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem 1rem 1.2rem 1rem;
        text-align: center;
        box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        transition: all 0.28s;
        border: none;
        height: 100%;
        min-height: 280px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }
    .feature-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 18px 42px rgba(0,0,0,0.13);
    }
    .feature-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.7rem;
        color: white;
        position: relative;
    }
    .feature-icon.icon-1 { background: linear-gradient(135deg, #667eea, #764ba2);}
    .feature-icon.icon-2 { background: linear-gradient(135deg, #f093fb, #f5576c);}
    .feature-icon.icon-3 { background: linear-gradient(135deg, #4facfe, #00f2fe);}
    .feature-icon.icon-4 { background: linear-gradient(135deg, #43e97b, #38f9d7);}
    /* Product and Masterclass Cards */
    .product-card {
        border: none;
        border-radius: 13px;
        overflow: hidden;
        box-shadow: 0 2px 14px rgba(102,126,234,0.08);
        transition: box-shadow .21s, transform .18s;
        background: white;
        height: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
        min-width: 0;
        max-width: 320px;
        margin: 0 auto;
    }
    .product-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 18px 45px rgba(102,126,234,0.15);
    }
    .product-image-container {
        height: 120px;
        overflow: hidden;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f7f7fb;
    }
    .product-card .card-img-top {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
        border-radius: 0;
    }
    .product-card:hover .card-img-top {
        transform: scale(1.08);
    }
    .product-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .product-placeholder i {
        font-size: 2.2rem;
        color: white;
        opacity: 0.8;
        z-index: 2;
        position: relative;
    }
    .product-card-body {
        padding: 1.1rem 1rem 1rem 1rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        position: relative;
        z-index: 2;
    }
    .product-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.6rem;
        gap: 0.6rem;
    }
    .product-badge {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: none;
        color: white;
        padding: 0.23rem 0.7rem;
        border-radius: 13px;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
        box-shadow: 0 2px 7px rgba(102, 126, 234, 0.18);
    }
    .product-price {
        font-size: 1.04rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        white-space: nowrap;
        margin-left: auto;
    }
    .product-title {
        font-size: 1.01rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.6rem;
        line-height: 1.4;
        min-height: 2.1rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-description {
        color: #6c757d;
        font-size: 0.92rem;
        line-height: 1.6;
        margin-bottom: 1rem;
        flex-grow: 1;
        min-height: 2.3rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-action {
        margin-top: auto;
    }
    .product-btn {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: none;
        color: white;
        padding: 0.5rem 1.3rem;
        border-radius: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.2s;
        box-shadow: 0 4px 14px rgba(102, 126, 234, 0.19);
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        text-decoration: none;
        font-size: 0.96rem;
    }
    .product-btn:hover {
        background: linear-gradient(135deg, #764ba2, #667eea);
        transform: translateY(-1px);
        box-shadow: 0 7px 22px rgba(102, 126, 234, 0.23);
        color: white;
        text-decoration: none;
    }
    /* Stats Section */
    .stats-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 0;
    }
    .stat-item {
        text-align: center;
        padding: 1.4rem 0.7rem;
    }
    .stat-number {
        font-size: 2.3rem;
        font-weight: 700;
        display: block;
        margin-bottom: 0.34rem;
    }
    .stat-label {
        font-size: 0.95rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    /* CTA Section */
    .cta-section {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        padding: 65px 0;
        color: white;
        position: relative;
        overflow: hidden;
    }
    .cta-section::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 30px 30px;
        animation: pulse 4s ease-in-out infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 0.5; }
        50% { opacity: 1; }
    }
    .cta-content {
        position: relative;
        z-index: 2;
    }
    /* Animations */
    .fade-in-up {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s;
    }
    .fade-in-up.animated {
        opacity: 1;
        transform: translateY(0);
    }
    /* Responsive Design */
    @media (max-width: 992px) {
        .product-card { min-width: 0; max-width: 100%; }
        .hero-title { font-size: 2rem; }
        .stat-number { font-size: 1.7rem; }
    }
    @media (max-width: 768px) {
        .feature-card { margin-bottom: 1.2rem; }
        .product-card { min-width: 0; max-width: 100%; }
        .hero-title { font-size: 1.4rem;}
        .stats-section { padding: 40px 0;}
    }
    @media (max-width: 600px) {
        .product-card-body { padding: 0.8rem 0.7rem;}
        .product-title { font-size: 1rem;}
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <!-- Hero Section -->
    <section class="hero-section d-flex align-items-center">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-lg-6 hero-content">
                    <div class="fade-in-up">
                        <h1 class="hero-title text-white">Learn, Grow, and Earn with Relearn</h1>
                        <p class="hero-subtitle text-white">Elevate your skills and supercharge your trajectory. Explore and discover a curated selection of premium digital assets—including exclusive eBooks, market-ready business plans, expert-led courses, and high-impact templates—all engineered to dramatically accelerate your personal and professional growth.</p>
                        <div class="d-flex flex-wrap gap-3 align-items-center">
                            @guest
                                <a href="{{ route('register') }}" class="btn btn-light hero-cta hero-cta-white">Get Started</a>
                                <a href="{{ route('products.index') }}" class="btn btn-outline-gradient hero-cta hero-cta-outline">Browse Products</a>
                                <a href="{{ route('register') }}" class="btn btn-primary hero-cta">
                                    <i class="fa-solid fa-network-wired me-2"></i>
                                    Register to Apply as Affiliate
                                </a>
                            @else
                                <a href="{{ route('products.index') }}" class="btn btn-light hero-cta hero-cta-white">Shop Now</a>
                                <a href="{{ route('downloads.index') }}" class="btn btn-light hero-cta hero-cta-white">My Downloads</a>
                                @php
                                    $userId = auth()->id();
                                    $affiliate = \App\Models\Affiliate::where('user_id', $userId)->first();
                                    $application = \App\Models\AffiliateApplication::where('user_id', $userId)->latest()->first();
                                @endphp
                                @if(!$affiliate)
                                    @if($application && $application->status === 'pending')
                                        <a class="btn btn-warning hero-cta" href="{{ route('affiliate.status') }}">
                                            <i class="fa-solid fa-hourglass-half me-2"></i>
                                            View My Application Status
                                        </a>
                                    @elseif(!$application || $application->status === 'rejected')
                                        <a class="btn btn-primary hero-cta" href="{{ route('affiliate.apply') }}">
                                            <i class="fa-solid fa-network-wired me-2"></i>
                                            Apply to be an Affiliate
                                        </a>
                                    @endif
                                @endif
                            @endguest
                            <!-- WhatsApp Us Button -->
                            <a href="https://wa.me/254723071290?text={{ urlencode('Hello, I would like to know more about Relearn\'s digital resources and masterclasses.') }}"
                               target="_blank"
                               rel="noopener"
                               class="hero-whatsapp-btn"
                               aria-label="WhatsApp us for more information">
                                <i class="fab fa-whatsapp"></i> WhatsApp Us
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="hero-animation">
                        <i class="fas fa-brain" style="font-size: 6rem; color: rgba(255,255,255,0.82);"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5" style="background: #f8f9ff;">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <h2 class="display-5 font-weight-bold mb-2 fade-in-up">Why Choose Relearn?</h2>
                    <p class="lead text-muted fade-in-up">Experience the best learning marketplace with secure payments and instant access to knowledge</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon icon-1">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4 class="font-weight-bold mb-2">Secure Payments</h4>
                        <p class="text-muted">Safe and secure M-Pesa STK Push payments with instant confirmation and receipt generation.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon icon-2">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h4 class="font-weight-bold mb-2">Instant Download</h4>
                        <p class="text-muted">Get immediate access to your purchases with secure tokenized download links sent to your email.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon icon-3">
                            <i class="fas fa-star"></i>
                        </div>
                        <h4 class="font-weight-bold mb-2">Premium Quality</h4>
                        <p class="text-muted">Carefully curated digital products from verified creators ensuring top-notch quality and value.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon icon-4">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4 class="font-weight-bold mb-2">24/7 Support</h4>
                        <p class="text-muted">Round-the-clock customer support to help you with any questions or download issues.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item fade-in-up">
                        <span class="stat-number">{{ number_format($stats['total_products']) }}+</span>
                        <span class="stat-label">Digital Products</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item fade-in-up">
                        <span class="stat-number">{{ number_format($stats['total_customers']) }}+</span>
                        <span class="stat-label">Happy Customers</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item fade-in-up">
                        <span class="stat-number">{{ number_format($stats['total_orders']) }}+</span>
                        <span class="stat-label">Orders Completed</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item fade-in-up">
                        <span class="stat-number">{{ number_format($stats['total_downloads']) }}+</span>
                        <span class="stat-label">Downloads</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Masterclasses Section -->
    <section class="py-5" style="background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <h2 class="display-5 font-weight-bold mb-2 fade-in-up">Upcoming Masterclasses</h2>
                    <p class="lead text-muted fade-in-up">Level up your skills — Register for our upcoming live masterclasses!</p>
                </div>
            </div>
            <div class="row g-4 justify-content-center">
                @forelse($masterClasses->take(3) as $class)
                <div class="col-lg-4 col-md-6 mb-4 d-flex">
                    <div class="product-card fade-in-up w-100">
                        <div class="product-image-container">
                            @if($class->image)
                                <img src="{{ Storage::url($class->image) }}" class="card-img-top" alt="{{ $class->title }}">
                            @else
                                <div class="product-placeholder">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                            @endif
                        </div>
                        <div class="product-card-body d-flex flex-column">
                            <div class="product-header">
                                <span class="product-badge">Masterclass</span>
                                <span class="product-price">
                                    {{ \Carbon\Carbon::parse($class->start_time)->format('M d, Y H:i') }}
                                </span>
                            </div>
                            <h5 class="product-title">{{ $class->title }}</h5>
                            <p class="product-description">{{ Str::limit($class->description, 85) }}</p>
                            <div class="product-action">
                                <a href="{{ route('master-classes.show', $class->id) }}" class="product-btn">
                                    <i class="fas fa-sign-in-alt"></i>
                                    Register Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center fade-in-up">
                    <p class="text-muted">No upcoming masterclasses at the moment. Check back soon!</p>
                </div>
                @endforelse
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('master-classes.index') }}" class="btn btn-outline-gradient btn-lg">
                    <i class="fas fa-chalkboard"></i>
                    View All Masterclasses
                </a>
            </div>
        </div>
    </section>
    <!-- End Masterclasses Section -->

    <!-- Featured Products -->
    @if($featuredProducts->count() > 0)
    <section class="py-5" style="background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <h2 class="display-5 font-weight-bold mb-2 fade-in-up">Featured Products</h2>
                    <p class="lead text-muted fade-in-up">Discover our most popular and trending digital products</p>
                </div>
            </div>
            <div class="row g-4 justify-content-center">
                @foreach($featuredProducts->take(4) as $product)
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4 d-flex">
                    <div class="product-card fade-in-up w-100">
                        <div class="product-image-container">
                            @if($product->image)
                                <img src="{{ Storage::url($product->image) }}" class="card-img-top" alt="{{ $product->name }}">
                            @else
                                <div class="product-placeholder">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            @endif
                        </div>
                        <div class="product-card-body d-flex flex-column">
                            <div class="product-header">
                                <span class="product-badge">{{ ucfirst(str_replace('-', ' ', $product->category)) }}</span>
                                <span class="product-price">{{ $product->formatted_price }}</span>
                            </div>
                            <h5 class="product-title">{{ $product->name }}</h5>
                            <p class="product-description">{{ $product->description ?? 'High-quality digital product designed to help you achieve your business goals and maximize your potential.' }}</p>
                            <div class="product-action">
                                <a href="{{ route('products.show', $product) }}" class="product-btn">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('products.index') }}" class="btn btn-outline-gradient btn-lg">
                    <i class="fas fa-th-large me-2"></i>View All Products
                </a>
            </div>
        </div>
    </section>
    @endif

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center cta-content">
                    <div class="fade-in-up">
                        <h2 class="display-5 font-weight-bold mb-3">Ready to Transform Your Business?</h2>
                        <p class="lead mb-4">Join thousands of entrepreneurs who have accelerated their success with our premium digital resources. Start your journey today!</p>
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            @guest
                                <a href="{{ route('register') }}" class="btn btn-light btn-lg px-5 py-3">
                                    <i class="fas fa-rocket me-2"></i>Get Started Now
                                </a>
                                <a href="{{ route('products.index') }}" class="btn btn-outline-light btn-lg px-5 py-3">
                                    <i class="fas fa-search me-2"></i>Explore Products
                                </a>
                                <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-5 py-3">
                                    <i class="bi bi-person-plus me-2"></i>Register to Apply as Affiliate
                                </a>
                            @else
                                <a href="{{ route('products.index') }}" class="btn btn-light btn-lg px-5 py-3">
                                    <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                                </a>
                                <a href="{{ route('downloads.index') }}" class="btn btn-light btn-lg px-5 py-3">
                                    <i class="fas fa-download me-2"></i>My Downloads
                                </a>
                                @php
                                    $userId = auth()->id();
                                    $affiliate = \App\Models\Affiliate::where('user_id', $userId)->first();
                                    $application = \App\Models\AffiliateApplication::where('user_id', $userId)->latest()->first();
                                @endphp
                                @if(!$affiliate && (!$application || $application->status === 'rejected'))
                                    <a href="{{ route('affiliate.apply') }}" class="btn btn-primary btn-lg px-5 py-3">
                                        <i class="bi bi-person-plus me-2"></i>Apply to Become an Affiliate
                                    </a>
                                @endif
                            @endguest
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    // Intersection Observer for fade-in animations
    const observeElements = () => {
        const elements = document.querySelectorAll('.fade-in-up');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, {
            threshold: 0.07,
            rootMargin: '0px 0px -40px 0px'
        });
        elements.forEach(element => {
            observer.observe(element);
        });
    };
    document.addEventListener('DOMContentLoaded', function() {
        observeElements();
        // Smooth scrolling for anchors
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    });
</script>
@endpush