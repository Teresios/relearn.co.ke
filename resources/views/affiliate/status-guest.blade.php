@extends('layouts.guest')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Success Message -->
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-3" style="font-size: 1.5rem;"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Congratulations! Application Accepted!</h5>
                        <p class="mb-0">Your affiliate application has been instantly approved. You're now ready to start earning 30% commissions!</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <!-- What Happens Next -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>You're Ready to Go!</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <!-- Step 1 -->
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success text-white">
                                <i class="bi bi-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="fw-bold mb-2">Application Approved</h6>
                                <p class="text-muted mb-0">Your affiliate application for {{ $email ?? 'your email' }} has been instantly approved. Welcome to our affiliate program!</p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success text-white">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="fw-bold mb-2">Check Your Email</h6>
                                <p class="text-muted mb-0">We've sent your affiliate dashboard login details and unique affiliate links to {{ $email ?? 'your email' }}. Check your inbox (and spam folder) for the complete setup instructions.</p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success text-white">
                                <i class="bi bi-rocket-takeoff"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="fw-bold mb-2">Start Earning</h6>
                                <p class="text-muted mb-0">Log in to your affiliate dashboard to generate custom links, track clicks, and monitor your real-time earnings. Commissions are paid automatically every Wednesday.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Key Benefits -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-star me-2"></i>Affiliate Program Benefits</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="bi bi-percent text-success" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold">30% Commission</h6>
                                    <p class="text-muted small mb-0">Earn 30% on every sale made through your referral link.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="bi bi-link-45deg text-info" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Unlimited Links</h6>
                                    <p class="text-muted small mb-0">Generate unique affiliate links for any product in our catalog.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="bi bi-graph-up text-warning" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Real-Time Tracking</h6>
                                    <p class="text-muted small mb-0">Monitor clicks, sales, and earnings through your dashboard.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="bi bi-cash-coin text-success" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Weekly Payouts</h6>
                                    <p class="text-muted small mb-0">Automatic M-Pesa payouts every Wednesday to your account.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact & Support -->
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title fw-bold mb-3">Have Questions?</h5>
                    <p class="text-muted mb-3">If you have any questions about your application or need support, feel free to reach out to us.</p>
                    <a href="mailto:support@relearn.co.ke" class="btn btn-primary me-2">
                        <i class="bi bi-envelope me-2"></i>Email Support
                    </a>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Products
                    </a>
                </div>
            </div>

            <!-- Check Status Section -->
            <div class="alert alert-success mt-4" role="alert">
                <strong>Next Steps:</strong>
                <p class="mb-2">We've sent your affiliate dashboard credentials to <strong>{{ $email ?? 'your email' }}</strong>. Check your inbox and spam folder for the welcome email with your login details and first affiliate link.</p>
                <small><strong>Ready to start?</strong> Log in to your dashboard to generate custom affiliate links for any product and start tracking your earnings in real-time!</small>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 0;
}

.timeline-item {
    display: flex;
    margin-bottom: 2rem;
    position: relative;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 24px;
    top: 60px;
    width: 2px;
    height: 60px;
    background: #e9ecef;
}

.timeline-marker {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #e9ecef;
    margin-right: 1.5rem;
    flex-shrink: 0;
    font-size: 1.2rem;
}

.timeline-content {
    flex: 1;
}
</style>
@endsection
