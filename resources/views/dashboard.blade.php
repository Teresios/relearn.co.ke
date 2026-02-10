@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="fw-bold mb-2">Welcome back, {{ auth()->user()->name }}! 👋</h1>
            <p class="text-muted fs-5">Here's your dashboard overview</p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @auth
        @php
            $userId = auth()->id();
            $affiliate = \App\Models\Affiliate::where('user_id', $userId)->first();
            $application = \App\Models\AffiliateApplication::where('user_id', $userId)->latest()->first();
        @endphp

        @if($affiliate)
            <div class="card border-0 shadow-sm mb-4 bg-success bg-opacity-10">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title fw-bold text-success mb-1">
                                <i class="bi bi-star-fill me-2"></i>You're an Active Affiliate!
                            </h5>
                            <p class="text-muted mb-0">Start earning commissions by sharing your affiliate links. Check your dashboard for real-time statistics.</p>
                        </div>
                        <a href="{{ route('affiliate.dashboard') }}" class="btn btn-success btn-lg ms-3">
                            <i class="bi bi-speedometer2 me-2"></i>Go to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        @elseif($application && $application->status === 'pending')
            <div class="card border-0 shadow-sm mb-4 bg-warning bg-opacity-10">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold text-warning mb-1">
                        <i class="bi bi-hourglass-split me-2"></i>Application Under Review
                    </h5>
                    <p class="text-muted mb-0">Your affiliate application is being reviewed by our team. We'll notify you once it's approved!</p>
                </div>
            </div>
        @elseif($application && $application->status === 'approved')
            <div class="card border-0 shadow-sm mb-4 bg-info bg-opacity-10">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title fw-bold text-info mb-1">
                                <i class="bi bi-check-circle me-2"></i>Application Approved!
                            </h5>
                            <p class="text-muted mb-0">Your application has been approved. You should be activated shortly to start earning!</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm mb-4 bg-primary bg-opacity-10">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title fw-bold text-primary mb-1">
                                <i class="bi bi-briefcase me-2"></i>Ready to Earn?
                            </h5>
                            <p class="text-muted mb-0">Join our affiliate program and start earning commissions on every sale you refer.</p>
                        </div>
                        <a href="{{ route('affiliate.apply') }}" class="btn btn-primary btn-lg ms-3">
                            <i class="bi bi-plus-circle me-2"></i>Apply Now
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Notifications --}}
        @if(auth()->user()->notifications->count())
            <h4>Your Notifications</h4>
            <ul class="list-group mb-3">
                @foreach(auth()->user()->notifications as $notification)
                    <li class="list-group-item">
                        @if($notification->type === 'App\Notifications\AffiliateApplicationReviewed')
                            Your affiliate application was <strong>{{ ucfirst($notification->data['status']) }}</strong>.
                            @if(!empty($notification->data['admin_feedback']))
                                <br><em>Feedback: "{{ $notification->data['admin_feedback'] }}"</em>
                            @endif
                            <br>
                            <small>
                                {{ \Carbon\Carbon::parse($notification->data['reviewed_at'] ?? $notification->created_at)->diffForHumans() }}
                            </small>
                        @else
                            {{ $notification->data['message'] ?? 'You have a new notification.' }}
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

    @else
        <div class="alert alert-info mb-4">
            Please <a href="{{ route('login') }}">login</a> to view your dashboard.
        </div>
    @endauth

    {{-- Add more dashboard content here --}}
</div>
@endsection