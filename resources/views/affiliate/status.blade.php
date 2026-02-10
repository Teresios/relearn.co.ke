@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card shadow-lg border-0">
                <div class="card-body text-center py-5">
                    @if ($isAffiliate)
                        <div class="mb-4">
                            <span class="display-1 text-success">
                                <i class="bi bi-patch-check-fill"></i>
                            </span>
                            <h2 class="fw-bold text-success">Congratulations!</h2>
                            <p class="lead mb-4">You are now an approved affiliate. Welcome aboard!</p>
                            <p class="text-muted mb-4">Your affiliate dashboard is ready to use. Start generating unique affiliate links and earning 30% commissions on every sale.</p>
                        </div>
                        <a href="{{ route('affiliate.dashboard') }}" class="btn btn-primary btn-lg fw-bold">Go to Affiliate Dashboard</a>
                    @else
                        <div class="mb-4">
                            <span class="display-1 text-primary">
                                <i class="bi bi-rocket-takeoff"></i>
                            </span>
                            <h2 class="fw-bold text-primary">Get Started Today</h2>
                            <p class="lead mb-4">
                                Become an affiliate and start earning 30% commissions!
                            </p>
                        </div>
                        <a href="{{ route('affiliate.apply') }}" class="btn btn-primary btn-lg fw-bold">Apply as Affiliate</a>
                    @endif
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection