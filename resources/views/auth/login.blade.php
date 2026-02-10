@extends('layouts.app')

@section('content')
<style>
.auth-page {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
    padding: 1rem;
}

.auth-page::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="rgba(255,255,255,0.09)"><polygon points="1000,100 1000,0 0,100"/></svg>');
    background-size: cover;
    z-index: 1;
}

.floating-elements {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 1;
}

.floating-element {
    position: absolute;
    background: rgba(255,255,255,0.11);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}
.floating-element:nth-child(1) { width: 70px; height: 70px; top: 18%; left: 14%; animation-delay: 0s;}
.floating-element:nth-child(2) { width: 110px; height: 110px; top: 60%; right: 8%; animation-delay: 2s;}
.floating-element:nth-child(3) { width: 58px; height: 58px; bottom: 15%; left: 28%; animation-delay: 4s;}

@keyframes float {
    0%, 100% {transform: translateY(0) rotate(0deg);}
    50% {transform: translateY(-18px) rotate(180deg);}
}

.auth-container {
    background: rgba(255,255,255,0.97);
    border-radius: 20px;
    box-shadow: 0 12px 32px rgba(0,0,0,0.12);
    backdrop-filter: blur(8px);
    overflow: hidden;
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 420px;
    margin: 2rem auto;
}

.auth-header {
    background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
    color: white;
    padding: 2.1rem 1.7rem 1.5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.auth-header::before {
    content: '';
    position: absolute;
    top: -38px; right: -38px;
    width: 80px; height: 80px;
    background: rgba(255,255,255,0.09);
    border-radius: 50%;
}
.auth-header::after {
    content: '';
    position: absolute;
    bottom: -24px; left: -24px;
    width: 46px; height: 46px;
    background: rgba(255,255,255,0.09);
    border-radius: 50%;
}

.auth-logo {
    font-size: 2.5rem;
    margin-bottom: 0.8rem;
    position: relative;
    z-index: 3;
}
.auth-title {
    font-size: 1.65rem;
    font-weight: 700;
    margin-bottom: 0.3rem;
    position: relative;
    z-index: 3;
}
.auth-subtitle {
    opacity: 0.93;
    margin-bottom: 0;
    position: relative;
    z-index: 3;
    font-size: 1.08rem;
}

.auth-body {
    padding: 1.7rem 1.3rem;
}

.form-group {
    margin-bottom: 1.2rem;
    position: relative;
}
.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.4rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.01rem;
}
.form-control {
    border: 2px solid #e9ecef;
    border-radius: 13px;
    padding: 0.95rem 1.2rem;
    font-size: 1rem;
    transition: all 0.2s;
    background: #f8f9fa;
    position: relative;
}
.form-control:focus {
    border-color: #4a90e2;
    box-shadow: 0 0 0 3px rgba(74,144,226,0.09);
    background: white;
    transform: translateY(-2px);
}
.form-control.is-invalid {
    border-color: #dc3545;
    background-color: #fff5f5;
}
.form-control.is-valid {
    border-color: #28a745;
    background-color: #f0fff4;
}
.input-icon {
    position: absolute;
    right: 1.1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    pointer-events: none;
    z-index: 3;
    transition: all 0.3s;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.6rem;
    background: #f8f9fa;
    border-radius: 9px;
    border: 1px solid #e9ecef;
    transition: background 0.2s;
}
.form-check:hover { background: #e9ecef; }
.form-check-input:checked { background-color: #4a90e2; border-color: #4a90e2;}
.form-check-label {
    margin-bottom: 0;
    color: #495057;
    font-weight: 500;
}

.btn-auth {
    background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
    border: none;
    color: white;
    padding: 0.85rem 1.6rem;
    border-radius: 11px;
    font-weight: 700;
    font-size: 1.09rem;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
    width: 100%;
    margin-bottom: 1rem;
}
.btn-auth:hover { transform: translateY(-2.5px); box-shadow: 0 10px 25px rgba(74,144,226,0.23); color: white;}
.btn-auth:active { transform: translateY(0); }

.btn-link-auth {
    background: transparent;
    border: 2px solid #6c757d;
    color: #6c757d;
    padding: 0.7rem 1.2rem;
    border-radius: 11px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    display: inline-block;
    width: 100%;
    text-align: center;
}
.btn-link-auth:hover {
    background: #6c757d;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(108,117,125,0.21);
}

.auth-footer {
    text-align: center;
    padding: 1.15rem;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}
.auth-footer p { margin-bottom: 1rem; color: #6c757d; }

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.87rem;
    margin-top: 0.45rem;
    padding: 0.45rem;
    background: #fff5f5;
    border-radius: 6px;
    border-left: 3px solid #dc3545;
}

.fade-in-up {
    opacity: 0;
    transform: translateY(24px);
    animation: fadeInUp 0.6s ease-out forwards;
}
@keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }

@media (max-width: 768px) {
    .auth-page { padding: 0.5rem; align-items: flex-start; padding-top: 2.1rem; }
    .auth-container { margin: 0; max-width: 99vw;}
    .auth-body { padding: 1rem 0.7rem;}
    .auth-header { padding: 1.3rem 0.7rem 1.1rem;}
    .auth-title { font-size: 1.18rem;}
    .auth-logo { font-size: 2rem;}
}
@media (max-width: 400px) {
    .auth-container { border-radius: 14px; }
    .form-control { font-size: 0.97rem; }
}
</style>

<div class="auth-page">
    <div class="floating-elements">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="auth-container fade-in-up">
                    <div class="auth-header">
                        <div class="auth-logo">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h1 class="auth-title">Welcome Back!</h1>
                        <p class="auth-subtitle">Sign in to access your account</p>
                    </div>
                    <div class="auth-body">
                        <form method="POST" action="{{ route('login') }}" id="loginForm" autocomplete="on">
                            @csrf
                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> {{ __('Email Address') }}
                                </label>
                                <input id="email" type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       name="email" value="{{ old('email') }}"
                                       required autocomplete="email" autofocus
                                       placeholder="Enter your email address">
                                <i class="fas fa-user input-icon"></i>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> {{ __('Password') }}
                                </label>
                                <div style="position: relative;">
                                    <input id="password" type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           name="password" required autocomplete="current-password"
                                           placeholder="Enter your password">
                                    <button type="button" class="password-toggle" id="passwordToggle" 
                                            style="position: absolute; right: 1.1rem; top: 50%; transform: translateY(-50%); 
                                                   background: none; border: none; color: #6c757d; cursor: pointer; 
                                                   padding: 0.5rem; z-index: 10; transition: all 0.3s;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                           {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                        <i class="fas fa-heart me-1"></i> {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn-auth" id="loginButton">
                                <i class="fas fa-sign-in-alt me-2"></i> {{ __('Sign In to Relearn') }}
                            </button>
                            @if (Route::has('password.request'))
                                <a class="btn-link-auth" href="{{ route('password.request') }}">
                                    <i class="fas fa-question-circle me-2"></i>
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            @endif
                        </form>
                    </div>
                    <div class="auth-footer">
                        <p>Don't have an account yet?</p>
                        <a href="{{ route('register') }}" class="btn-link-auth">
                            <i class="fas fa-user-plus me-2"></i> {{ __('Create Account') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    const passwordToggle = document.getElementById('passwordToggle');
    const passwordInput = document.getElementById('password');
    
    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            
            // Update icon
            const icon = this.querySelector('i');
            if (isPassword) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Update icon color on hover
        passwordToggle.addEventListener('mouseenter', function() {
            this.style.color = '#4a90e2';
        });
        passwordToggle.addEventListener('mouseleave', function() {
            this.style.color = '#6c757d';
        });
    }

    // Fade-in animation
    document.querySelectorAll('.fade-in-up').forEach(el => {
        el.classList.add('animate');
    });

    // Form control effects
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
            const icon = this.parentElement.querySelector('.input-icon');
            if (icon) {
                icon.style.color = '#4a90e2';
                icon.style.transform = 'translateY(-50%) scale(1.08)';
            }
        });
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
            const icon = this.parentElement.querySelector('.input-icon');
            if (icon) {
                icon.style.color = '#6c757d';
                icon.style.transform = 'translateY(-50%) scale(1)';
            }
        });
        // Real-time validation
        input.addEventListener('input', function() {
            if (this.type === 'email') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (this.value && emailRegex.test(this.value)) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else if (this.value) {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-valid', 'is-invalid');
                }
            }
            if (this.type === 'password') {
                if (this.value.length >= 6) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else if (this.value.length > 0) {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-valid', 'is-invalid');
                }
            }
        });
    });

    // Button ripple effect
    document.querySelectorAll('.btn-auth').forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255,255,255,0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s ease-out;
                pointer-events: none;
            `;
            this.appendChild(ripple);
            setTimeout(() => { if (ripple.parentNode) ripple.parentNode.removeChild(ripple); }, 650);
        });
    });

    // Form submission loading
    const loginForm = document.getElementById('loginForm');
    const loginButton = document.getElementById('loginButton');
    if (loginForm && loginButton) {
        loginForm.addEventListener('submit', function(e) {
            loginButton.disabled = true;
            loginButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
        });
    }
});
</script>
<style>
@keyframes ripple {
    to { transform: scale(2); opacity: 0; }
}
.spinner-border { width: 1.25rem; height: 1.25rem; border-width: 0.18em; }
.form-group.focused .form-control { border-color: #4a90e2; box-shadow: 0 0 0 3px rgba(74,144,226,0.09);}
.input-icon { transition: all 0.3s;}
.fade-in-up.animate { animation-play-state: running;}
@media (prefers-reduced-motion: reduce) {
    .fade-in-up, .btn-auth, .btn-link-auth, .form-control, .floating-element { animation: none !important; transition: none !important; }
}
</style>
@endpush