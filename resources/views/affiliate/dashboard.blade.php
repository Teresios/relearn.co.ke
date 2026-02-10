@extends('layouts.app')

@section('content')
<div class="container py-3 px-1 px-md-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="fw-bold text-primary mb-0" style="font-size:1.5rem;">Affiliate Dashboard</h2>
        <a href="{{ route('affiliate.how_it_works') }}" class="btn btn-outline-info fw-semibold">
            <i class="bi bi-question-circle me-1"></i> How it works
        </a>
    </div>

    {{-- Affiliate Application/Status Logic --}}
    @if(!$affiliate && (!$application || $application->status === 'rejected'))
        <div class="alert alert-info mb-4 text-center">
            <a href="{{ route('affiliate.apply') }}" class="btn btn-primary btn-lg fw-semibold shadow w-100">
                Apply to become an Affiliate
            </a>
        </div>
    @elseif($application && $application->status === 'pending')
        <div class="alert alert-warning mb-4 text-center">
            <i class="bi bi-hourglass-split me-2"></i>
            Your affiliate application is <b>pending review</b>.
        </div>
    @elseif($affiliate)
        {{-- Show dashboard content only for approved affiliates --}}
        {{-- Top Stats --}}
        <div class="row g-2 mb-4">
            @php
                $userStats = [
                    [
                        'value' => 'KES ' . number_format($stats['available_earnings'], 2),
                        'label' => 'Available Earnings',
                        'icon'  => 'bi-currency-exchange',
                        'class' => 'bg-gradient-gold',
                        'is_earnings' => true
                    ],
                    [
                        'value' => $stats['total_clicks'],
                        'label' => 'Total Clicks',
                        'icon'  => 'bi-mouse',
                        'class' => 'bg-gradient-blue'
                    ],
                    [
                        'value' => $stats['total_referrals'],
                        'label' => 'Total Referrals',
                        'icon'  => 'bi-person-check-fill',
                        'class' => 'bg-gradient-green'
                    ],
                    [
                        'value' => $stats['total_conversions'],
                        'label' => 'Conversions',
                        'icon'  => 'bi-cart-check',
                        'class' => 'bg-gradient-purple'
                    ]
                ];
            @endphp
            @foreach($userStats as $i => $stat)
                <div class="col-3 col-sm-3 col-md-3 col-lg-3 px-1 mb-2">
                    <div class="card stat-card {{ $stat['class'] }} text-white text-center h-100 shadow border-0">
                        <div class="card-body py-2 px-1">
                            <div class="display-6 fw-bold mb-1 stat-value" style="font-size:1.03rem;">
                                <i class="bi {{ $stat['icon'] }} me-1"></i>{{ $stat['value'] }}
                            </div>
                            <div class="text-uppercase letter-spacing small stat-label" style="font-size:0.82rem;">{{ $stat['label'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Affiliate Info --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3 fw-semibold text-secondary"><i class="bi bi-person-badge me-2"></i>Your Affiliate Info</h5>
                <ul class="mb-0 ps-2">
                    <li><strong>Affiliate Code:</strong> <span class="badge bg-info text-dark px-2">{{ $affiliate->code ?? 'Not set' }}</span></li>
                    <li>
                        <strong>Status:</strong>
                        <span class="badge bg-{{ $affiliate->active ? 'success' : 'secondary' }} px-2">
                            {{ $affiliate->active ? 'Active' : 'Inactive' }}
                        </span>
                    </li>
                    <li>
                        <strong>Approved At:</strong>
                        <span class="badge bg-gradient-orange text-dark px-2">
                            {{ $affiliate->approved_at ? \Carbon\Carbon::parse($affiliate->approved_at)->format('Y-m-d') : 'Pending' }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Create new affiliate link --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3 fw-semibold text-secondary"><i class="bi bi-link-45deg me-2"></i>Create New Affiliate Link</h5>
                <form action="{{ route('affiliate.link.create') }}" method="POST" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-12 col-md-6">
                        <label for="product_id" class="form-label mb-0">Product</label>
                        <select name="product_id" id="product_id" class="form-select form-select-sm" required>
                            <option value="">Select a product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-primary btn-sm shadow w-100">Create Link</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Affiliate Links --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3 fw-semibold text-secondary"><i class="bi bi-link-45deg me-2"></i>Your Affiliate Links</h5>
                @if($links->count())
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Link</th>
                                    <th>Clicks</th>
                                    <th>Referrals</th>
                                    <th>Copy</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($links as $link)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">{{ $link->product->name ?? 'Product ID: '.$link->product_id }}</span>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm bg-light fw-semibold" readonly value="{{ url('/ref/' . $link->unique_code) }}">
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark px-2">{{ $link->referrals->count() }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-gradient-green text-white px-2">{{ $link->referrals->whereNotNull('user_id')->count() }}</span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm fw-semibold w-100" onclick="navigator.clipboard.writeText('{{ url('/ref/' . $link->unique_code) }}')">
                                                <i class="bi bi-clipboard"></i> Copy
                                            </button>
                                        </td>
                                        <td>
                                            <form action="{{ route('affiliate.link.delete', $link->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this link?');" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm shadow-sm w-100"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">You have no affiliate links yet.</p>
                @endif
            </div>
        </div>

        {{-- Recent Referrals --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3 fw-semibold text-secondary"><i class="bi bi-person-lines-fill me-2"></i>Recent Referrals</h5>
                @if($recent_referrals->count())
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Referral Code</th>
                                    <th>Status</th>
                                    <th>Clicked At</th>
                                    <th>Purchased At</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($recent_referrals as $referral)
                                <tr>
                                    <td>{{ $referral->referral_code ?? '-' }}</td>
                                    <td>
                                        @if($referral->purchased_at)
                                            <span class="badge bg-success">Purchased</span>
                                        @else
                                            <span class="badge bg-info text-dark">Clicked</span>
                                        @endif
                                    </td>
                                    <td>{{ $referral->clicked_at ? $referral->clicked_at->format('Y-m-d H:i') : '-' }}</td>
                                    <td>{{ $referral->purchased_at ? $referral->purchased_at->format('Y-m-d H:i') : '-' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No referrals yet.</p>
                @endif
            </div>
        </div>

        {{-- Commissions --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3 fw-semibold text-secondary"><i class="bi bi-cash-coin me-2"></i>Your Commissions</h5>
                @if($commissions->count())
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Paid At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($commissions as $commission)
                                <tr>
                                    <td>{{ $commission->order_id }}</td>
                                    <td>
                                        <span class="fw-semibold text-success">KES {{ number_format($commission->amount, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $commission->status == 'paid' ? 'success' : ($commission->status == 'approved' ? 'info text-dark' : 'secondary') }}">
                                            {{ ucfirst($commission->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $commission->paid_at ? \Carbon\Carbon::parse($commission->paid_at)->format('Y-m-d') : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No commissions yet.</p>
                @endif
            </div>
        </div>

        {{-- Payout Requests --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3 fw-semibold text-secondary"><i class="bi bi-wallet2 me-2"></i>Payout Requests</h5>
                @if($payouts->count())
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Requested At</th>
                                    <th>Paid At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payouts as $payout)
                                <tr>
                                    <td>
                                        <span class="fw-semibold text-success">KES {{ number_format($payout->amount, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $payout->status == 'paid' ? 'success' : ($payout->status == 'pending' ? 'warning text-dark' : 'danger') }}">
                                            {{ ucfirst($payout->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $payout->created_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ $payout->paid_at ? $payout->paid_at->format('Y-m-d H:i') : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No payout requests yet.</p>
                @endif
            </div>
        </div>

        {{-- 
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3 fw-semibold text-secondary"><i class="bi bi-trophy me-2"></i>Top Affiliates Leaderboard</h5>
                @if($leaderboard->count())
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Total Earnings</th>
                                    <th>Referrals</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaderboard as $i => $row)
                                <tr @if($row['user_id'] == auth()->id()) class="table-primary" @endif>
                                    <td>{{ $i + 1 }}</td>
                                    <td class="fw-semibold">{{ $row['name'] }}</td>
                                    <td>
                                        <span class="badge bg-gradient-gold text-dark px-2">KES {{ number_format($row['total'], 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-gradient-green text-white px-2">{{ $row['referrals'] }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No leaderboard data yet.</p>
                @endif
            </div>
        </div>
        --}}

    @endif
</div>

<style>
    .stat-card {
        border-radius: 0.7rem !important;
        min-width: 0;
        font-size: 0.91rem;
        padding: 0 !important;
        margin: 0 !important;
        box-shadow: 0 1.5px 4px rgba(0,0,0,0.08);
    }
    .stat-card .stat-value {
        font-size: 1.03rem !important;
        margin-bottom: 0.17rem;
    }
    .stat-card .stat-label {
        font-size: 0.82rem !important;
        margin-top: 0;
    }
    .letter-spacing {
        letter-spacing: 0.07em;
    }
    .badge-animate {
        border-radius: 0.7em;
        font-size: 0.93em;
        font-weight: 600;
    }
    .bg-gradient-blue {
        background: linear-gradient(135deg, #3b8beb 0%, #0d6efd 100%);
    }
    .bg-gradient-green {
        background: linear-gradient(135deg, #4fd69c 0%, #198754 100%);
    }
    .bg-gradient-purple {
        background: linear-gradient(135deg, #a685e2 0%, #6f42c1 100%);
    }
    .bg-gradient-gold {
        background: linear-gradient(135deg, #ffe259 0%, #ffa751 100%);
    }
    .bg-gradient-orange {
        background: linear-gradient(135deg, #ffb347 0%, #ff7f50 100%);
    }
    @media (max-width: 576px) {
        .container { padding-left: 2px !important; padding-right: 2px !important; }
        .row.g-2 > .col-3 { flex: 0 0 25%; max-width: 25%; }
        .stat-card { font-size: 0.83rem; }
        .stat-card .stat-value { font-size: 0.98rem !important; }
        .stat-card .stat-label { font-size: 0.68rem !important; }
        .display-6, .h4 { font-size: 0.93rem; }
        .fw-bold, h2, h5, th { font-size: 1rem !important; }
        .table th, .table td { padding: 0.37rem 0.13rem; }
        .btn-xs, .btn-group-xs>.btn { padding: .25rem .5rem; font-size: .82rem; border-radius: .2rem;}
    }
    @media (max-width: 400px) {
        .row.g-2 > .col-3 { flex: 0 0 50%; max-width: 50%; }
        .stat-card { font-size: 0.78rem; }
        .stat-card .stat-value { font-size: 0.89rem !important; }
        .stat-card .stat-label { font-size: 0.6rem !important; }
    }
</style>
<meta name="csrf-token" content="{{ csrf_token() }}">
</head>
@endsection