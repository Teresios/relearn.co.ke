@extends('layouts.guest')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 text-primary fw-bold">
        <i class="bi bi-rocket-takeoff me-2"></i>
        Apply to Become an Affiliate
    </h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @elseif(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @elseif(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('affiliate.apply.submit') }}" method="POST" class="card shadow p-4 mx-auto" style="max-width: 600px;">
        @csrf

        {{-- Full Legal Name --}}
        <div class="mb-3">
            <label for="full_name" class="form-label">Full Legal Name <span class="text-danger">*</span></label>
            <input type="text" id="full_name" name="full_name" class="form-control @error('full_name') is-invalid @enderror" 
                value="{{ auth()->user()?->name ?? old('full_name') }}" 
                placeholder="Enter your full name"
                @if(auth()->check()) readonly tabindex="-1" @endif
                required>
            @error('full_name')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        {{-- Email Address --}}
        <div class="mb-3">
            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                value="{{ auth()->user()?->email ?? old('email') }}" 
                placeholder="Enter your email address"
                @if(auth()->check()) readonly tabindex="-1" @endif
                required>
            @error('email')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        {{-- County Dropdown --}}
        <div class="mb-3">
            <label for="county" class="form-label">Current County <span class="text-danger">*</span></label>
            <select id="county" name="county" class="form-select @error('county') is-invalid @enderror" required>
                <option value="">-- Select County --</option>
                @php
                    $counties = [
                        'Baringo', 'Bomet', 'Bungoma', 'Busia', 'Elgeyo Marakwet', 'Embu', 'Garissa', 'Homa Bay', 'Isiolo', 'Kajiado',
                        'Kakamega', 'Kericho', 'Kiambu', 'Kilifi', 'Kirinyaga', 'Kisii', 'Kisumu', 'Kitui', 'Kwale', 'Laikipia', 'Lamu',
                        'Machakos', 'Makueni', 'Mandera', 'Marsabit', 'Meru', 'Migori', 'Mombasa', 'Murang\'a', 'Nairobi', 'Nakuru',
                        'Nandi', 'Narok', 'Nyamira', 'Nyandarua', 'Nyeri', 'Samburu', 'Siaya', 'Taita Taveta', 'Tana River', 'Tharaka Nithi',
                        'Trans Nzoia', 'Turkana', 'Uasin Gishu', 'Vihiga', 'Wajir', 'West Pokot'
                    ];
                @endphp
                @foreach($counties as $county)
                    <option value="{{ $county }}" @if(old('county') == $county) selected @endif>{{ $county }}</option>
                @endforeach
            </select>
            @error('county')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        {{-- MPESA Payment Details --}}
        <div class="mb-3">
            <label for="payment_details" class="form-label">MPESA Phone Number for Payouts <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text">+254</span>
                <input type="text" id="payment_details" name="payment_details" class="form-control @error('payment_details') is-invalid @enderror" 
                    value="{{ auth()->user()?->phone ? ltrim(auth()->user()?->phone, '254') : old('payment_details') }}" 
                    placeholder="723071290"
                    @if(auth()->check()) readonly tabindex="-1" @endif
                    required
                    maxlength="9"
                    pattern="[0-9]{9}">
            </div>
            <small class="text-muted d-block mt-2">Enter without the country code. Commission payouts are made to this MPESA number.</small>
            @error('payment_details')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        {{-- Privacy Notice --}}
        <div class="mb-3 small text-muted">
            By submitting this form, you consent to the processing of your data for the purpose of affiliate onboarding and administration.
            All personal data is handled securely and in accordance with our <a href="{{ route('privacy.policy') }}" target="_blank">Privacy Policy</a>.
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-bold">Submit Application</button>
    </form>
</div>
@endsection