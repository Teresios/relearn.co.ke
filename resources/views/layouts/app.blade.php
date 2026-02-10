<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ !empty($title) ? $title . ' - ' : '' }}{{ config('app.name', 'Relearn') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Figtree', Arial, sans-serif;
            background: #f7fafc;
            color: #2c3e50;
        }
        .navbar {
            box-shadow: 0 2px 16px rgba(102,126,234,0.07);
            border-bottom: 1px solid #e9ecef;
            z-index: 1002;
        }
        .navbar-brand {
            font-weight: 800;
            color: #0d6efd !important;
            font-size: 1.5rem;
            letter-spacing: 1px;
        }
        .navbar-nav .nav-link {
            font-weight: 500;
            color: #495057 !important;
            margin-right: 0.5rem;
            transition: color 0.2s, background 0.2s;
            border-radius: 8px;
        }
        .navbar-nav .nav-link.active, .navbar-nav .nav-link:hover, .navbar-nav .nav-link:focus {
            color: #764ba2 !important;
            background: rgba(102,126,234,0.09);
        }
        .navbar-toggler {
            border: none;
            padding: 0.25rem 0.45rem;
            outline: none;
            box-shadow: none;
            background: #e0e4ea;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .navbar-toggler:focus {
            background: #d7dbec;
        }
        .navbar-toggler-icon {
            background-color: #0d6efd;
            border-radius: 8px;
            padding: 0.25rem;
        }
        .dropdown-menu {
            border-radius: 16px;
            box-shadow: 0 3px 18px rgba(102,126,234,0.08);
        }
        .dropdown-item {
            font-weight: 500;
        }
        /* Responsive mobile nav */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                position: fixed;
                top: 61px;
                left: 0;
                width: 100vw;
                height: calc(100vh - 61px);
                background: rgba(255,255,255,0.98);
                box-shadow: 0 5px 32px rgba(102,126,234,0.17);
                z-index: 1001;
                overflow-y: auto;
                padding: 2.5rem 1.3rem 1.2rem 1.3rem;
                transition: all 0.3s;
            }
            .navbar-nav {
                flex-direction: column;
                gap: 0.5rem;
            }
            .navbar-nav .nav-link {
                font-size: 1.15rem;
                padding: 0.7rem 1rem;
                margin: 0.15rem 0;
            }
            .navbar-nav .nav-link.active {
                font-weight: 700;
                background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
                color: #fff !important;
                box-shadow: 0 2px 12px rgba(102,126,234,0.11);
            }
            .navbar-nav .nav-link i {
                margin-right: 0.7rem;
            }
            .navbar-nav .dropdown-menu {
                width: 95vw;
                margin: 0.4rem auto;
                box-shadow: 0 2px 18px rgba(102,126,234,0.16);
            }
        }
        #mobile-nav-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(102,126,234,0.12);
            z-index: 1000;
            transition: opacity 0.2s;
        }
        #mobile-nav-overlay.active {
            display: block;
            opacity: 1;
        }
        .alert {
            border-radius: 12px;
            margin: 0.7rem auto;
            max-width: 600px;
            box-shadow: 0 2px 10px rgba(102,126,234,0.09);
        }
        @media (max-width: 600px) {
            .alert { max-width: 98vw; }
        }
        main {
            min-height: 60vh;
            padding-top: 2rem;
        }
        @media (max-width: 768px) {
            main { padding-top: 1rem; }
        }
        .footer {
            background: linear-gradient(120deg, #f8f9fa 70%, #e9ecef 100%);
            border-top: 1px solid #e0e4ea;
            font-size: 1rem;
        }
        .footer h5, .footer h6 {
            font-weight: 700;
            color: #764ba2;
            margin-bottom: 0.7rem;
        }
        .footer ul li {
            margin-bottom: 0.35rem;
        }
        .footer ul li a {
            color: #495057;
            text-decoration: none;
            transition: color 0.2s;
            font-weight: 500;
        }
        .footer ul li a:hover, .footer ul li a:focus {
            color: #0d6efd;
            text-decoration: underline;
        }
        .footer .row > div {
            margin-bottom: 1rem;
        }
        .footer hr {
            margin: 1.5rem 0 1rem 0;
        }
        .footer .text-end { text-align: right; }
        @media (max-width: 767px) {
            .footer .text-end { text-align: left; margin-top: 0.5rem; }
            .footer .row > div { margin-bottom: 1.2rem; }
        }
        .btn-primary {
            background: linear-gradient(120deg, #0d6efd 80%, #764ba2 100%);
            border: none;
            font-weight: 600;
            border-radius: 12px;
            letter-spacing: 0.3px;
        }
        .btn-primary:hover, .btn-primary:focus {
            background: linear-gradient(120deg, #764ba2 70%, #0d6efd 100%);
            color: #fff;
        }
        .btn-success {
            background: linear-gradient(120deg, #198754 80%, #42e695 100%);
            border: none;
            font-weight: 600;
            border-radius: 12px;
        }
        .btn-success:hover, .btn-success:focus {
            background: linear-gradient(120deg, #42e695 70%, #198754 100%);
            color: #fff;
        }
        @media (max-width: 575px) {
            .navbar-brand { font-size: 1.2rem; }
            .footer h5, .footer h6 { font-size: 1rem; }
            .footer .row { flex-direction: column; }
        }
        @media (min-width: 992px) {
            ::-webkit-scrollbar {
                width: 10px;
                background: #f8f9fa;
            }
            ::-webkit-scrollbar-thumb {
                background: #e9ecef;
                border-radius: 10px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #d7dbec;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Mobile Nav Overlay -->
    <div id="mobile-nav-overlay"></div>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                <i class="fas fa-graduation-cap"></i> Relearn
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation" aria-controls="navbarNav" aria-expanded="false">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav" tabindex="-1">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}"><i class="bi bi-house"></i> Home</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    @auth
                        {{-- Dashboard Links (Role-Based - Allow Multiple) --}}
                        @if(Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('admin'))
                            <li class="nav-item">
                                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                    <i class="bi bi-speedometer2 me-1"></i> Admin Dashboard
                                </a>
                            </li>
                        @endif

                        @if(Auth::user()->hasRole('product_admin'))
                            <li class="nav-item">
                                <a href="{{ route('products-admin.dashboard') }}" class="nav-link {{ request()->routeIs('products-admin.dashboard') ? 'active' : '' }}">
                                    <i class="bi bi-box me-1"></i> Products
                                </a>
                            </li>
                        @endif

                        @if(Auth::user()->hasRole('affiliate'))
                            <li class="nav-item">
                                <a href="{{ route('affiliate.dashboard') }}" class="nav-link {{ request()->routeIs('affiliate.dashboard') ? 'active' : '' }}">
                                    <i class="fa-solid fa-network-wired me-1"></i> Affiliate Dashboard
                                </a>
                            </li>
                        @endif

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('orders.index') }}">
                                        <i class="bi bi-bag-check me-2"></i>My Orders
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('downloads.index') }}">
                                        <i class="bi bi-cloud-arrow-down me-2"></i>My Downloads
                                    </a>
                                </li>
                                @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('super_admin'))
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                            <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.affiliate.applications.index') }}">
                                            <i class="fa-solid fa-users-viewfinder me-2"></i>Affiliate Applications
                                        </a>
                                    </li>
                                @endif
                                @if(Auth::user()->hasRole('product_admin'))
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('products-admin.dashboard') }}">
                                            <i class="bi bi-box me-2"></i>Products Dashboard
                                        </a>
                                    </li>
                                @endif
                                @if(Auth::user()->hasRole('affiliate'))
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('affiliate.dashboard') }}">
                                            <i class="fa-solid fa-network-wired me-2"></i>Affiliate Dashboard
                                        </a>
                                    </li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="bi bi-person-plus me-1"></i>Register
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-x-circle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer mt-5 py-4">
        <div class="container">
            <div class="row gy-3">
                <div class="col-md-6">
                    <h5>Relearn</h5>
                    <p class="text-muted">Your trusted platform for digital learning resources and business growth.</p>
                </div>
                <div class="col-md-3">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('home') }}">Home</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="{{ route('terms') }}">Terms of Service</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; {{ date('Y') }} Relearn. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="text-muted mb-0">Powered by Relearn</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
    // Seamless Mobile Navbar UX/UI & Dropdowns
    document.addEventListener('DOMContentLoaded', function() {
        const navCollapse = document.getElementById('navbarNav');
        const navOverlay = document.getElementById('mobile-nav-overlay');
        const navToggler = document.querySelector('.navbar-toggler');

        function openNav() {
            navOverlay.classList.add('active');
            navCollapse.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function closeNav() {
            navOverlay.classList.remove('active');
            navCollapse.classList.remove('show');
            document.body.style.overflow = '';
        }
        navToggler.addEventListener('click', function() {
            if (!navCollapse.classList.contains('show')) {
                setTimeout(openNav, 50);
            } else {
                closeNav();
            }
        });
        navOverlay.addEventListener('click', function(e) {
            if (!navCollapse.contains(e.target)) {
                closeNav();
            }
        });
        document.querySelectorAll('.navbar-nav .nav-link:not(.dropdown-toggle)').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    closeNav();
                }
            });
        });
        document.querySelectorAll('.dropdown-item').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    closeNav();
                }
            });
        });
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                closeNav();
            }
        });
    });
    </script>
    @stack('scripts')
</body>
</html>