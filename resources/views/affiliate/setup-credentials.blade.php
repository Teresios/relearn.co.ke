@extends('layouts.guest')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-success mb-2">
                            <i class="bi bi-shield-lock"></i> Create Your Password
                        </h2>
                        <p class="text-muted">Set up your login credentials to access your affiliate dashboard</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Oops!</strong> Please fix the following errors:
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('affiliate.setup.store', $token) }}" novalidate>
                        @csrf

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">Create a Password <span class="text-danger">*</span></label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                                placeholder="Enter a strong password"
                                required
                                minlength="8"
                                autofocus>
                            <small class="text-muted d-block mt-2">
                                Password must be at least 8 characters long.
                            </small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label fw-bold">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                placeholder="Confirm your password"
                                required>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Info Box -->
                        <div class="alert alert-info mb-4" role="alert">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Save your password:</strong> You'll use {{ $email }} and this password to log in to your affiliate dashboard.
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-success w-100 fw-bold py-2 mb-3">
                            <i class="bi bi-check-circle me-2"></i>Create Password & Access Dashboard
                        </button>

                        <!-- Help Text -->
                        <div class="text-center">
                            <small class="text-muted">
                                Already have an account? <a href="{{ route('login') }}">Log in here</a>
                            </small>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Information -->
            <div class="card shadow-sm mt-4 border-0">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-shield-check text-success me-2"></i>Password Tips
                    </h6>
                    <ul class="small text-muted mb-0">
                        <li class="mb-2">Use uppercase, lowercase, numbers & symbols</li>
                        <li class="mb-2">Avoid personal information</li>
                        <li>Never share your password</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
