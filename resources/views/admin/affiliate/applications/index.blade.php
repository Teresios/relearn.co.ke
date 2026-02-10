@extends('layouts.admin')

@section('content')
<div class="container-fluid px-1 px-md-3" style="max-width: 100vw;">
    <h1 class="mb-4 fw-bold text-primary" style="font-size:2.2rem;">Affiliate Applications</h1>

    {{-- Add Affiliate Bonus and Withdrawal Requests Buttons --}}
    <div class="mb-4 d-flex flex-wrap gap-2 justify-content-end">
        <form action="{{ route('admin.affiliate.applications.send-weekly-stats') }}" method="POST" class="d-inline" id="sendWeeklyStatsForm">
            @csrf
            <button type="submit" class="btn btn-primary fw-semibold" id="sendWeeklyStatsBtn" onclick="return confirm('Send weekly stats emails to all active affiliates? This will include commission details and encourage those with zero earnings.')">
                <i class="bi bi-envelope-check me-1"></i> Send Weekly Stats Emails
            </button>
        </form>
        <a href="{{ route('admin.affiliate.withdrawals.index') }}" class="btn btn-info fw-semibold">
            <i class="bi bi-cash-stack me-1"></i> Withdrawal Requests
        </a>
        <a href="{{ route('admin.affiliate-bonus.form') }}" class="btn btn-warning fw-semibold">
            <i class="bi bi-plus-circle me-1"></i> Add Affiliate Bonus
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-1 mb-4" style="flex-wrap: wrap;">
        @php
            $cardStats = [
                [
                    'value' => $stats['total_affiliates'] ?? 0,
                    'label' => 'Total Affiliates',
                    'icon'  => 'bi-people-fill',
                    'class' => 'bg-gradient-blue'
                ],
                [
                    'value' => $stats['pending_applications'] ?? 0,
                    'label' => 'Pending Applications',
                    'icon'  => 'bi-hourglass-split',
                    'class' => 'bg-gradient-orange'
                ],
                [
                    'value' => $stats['total_referrals'] ?? 0,
                    'label' => 'Total Referrals',
                    'icon'  => 'bi-person-check-fill',
                    'class' => 'bg-gradient-green',
                    'link'  => '#referral-stats'
                ],
                [
                    'value' => $stats['total_purchases'] ?? 0,
                    'label' => 'Total Purchases',
                    'icon'  => 'bi-cart-check',
                    'class' => 'bg-gradient-purple'
                ],
                [
                    'value' => 'KES ' . number_format($stats['available_earnings'] ?? 0, 2),
                    'label' => 'Available Earnings',
                    'icon'  => 'bi-cash-coin',
                    'class' => 'bg-gradient-success',
                    'minw'  => '150px'
                ],
                [
                    'value' => 'KES ' . number_format($stats['total_earnings'] ?? 0, 2),
                    'label' => 'Total Earnings',
                    'icon'  => 'bi-currency-exchange',
                    'class' => 'bg-gradient-gold',
                    'minw'  => '150px'
                ],
                [
                    'value' => 'KES ' . number_format($stats['weekly_payout_total'] ?? 0, 2),
                    'label' => "This Week's Pay Out",
                    'icon'  => 'bi-cash-stack',
                    'class' => 'bg-gradient-info',
                    'link'  => '#weekly-payouts',
                    'minw'  => '150px'
                ],
                [
                    'value' => 'KES ' . number_format($stats['last_week_payout_total'] ?? 0, 2),
                    'label' => "Last Week's Pay Out",
                    'icon'  => 'bi-calendar-check',
                    'class' => 'bg-gradient-info',
                    'link'  => '#last-week-payouts',
                    'minw'  => '150px'
                ]
            ];
        @endphp
        @foreach($cardStats as $i => $stat)
            <div style="flex: 1 1 {{ $stat['minw'] ?? '140px' }}; min-width: {{ $stat['minw'] ?? '140px' }};">
                @if(isset($stat['link']))
                <a href="{{ $stat['link'] }}" class="text-decoration-none">
                @endif
                <div class="card stat-card {{ $stat['class'] }} text-white text-center h-100 shadow border-0 {{ isset($stat['link']) ? 'clickable-card' : '' }}" style="{{ isset($stat['link']) ? 'cursor:pointer;' : '' }}">
                    <div class="card-body py-2 px-1">
                        <div class="fw-bold mb-1" style="font-size:1.3rem;">
                            <i class="bi {{ $stat['icon'] }} me-1"></i>{{ $stat['value'] }}
                        </div>
                        <div class="text-uppercase letter-spacing" style="font-size:0.75rem;">{{ $stat['label'] }}</div>
                        @if(isset($stat['link']) && $stat['link'] === '#referral-stats')
                            <div class="small mt-1" style="text-decoration:underline; font-size:0.7rem;">Click for detailed stats</div>
                        @endif
                    </div>
                </div>
                @if(isset($stat['link']))
                </a>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Pending Affiliate Applications Table --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-column flex-md-row">
            <h4 class="fw-semibold mb-2 mb-md-0 text-secondary" style="font-size:1.5rem;">
                <i class="bi bi-hourglass-split me-2"></i>Pending Applications
            </h4>
            <span class="text-muted small">{{ $pendingApplications->total() }} found</span>
        </div>
        <div class="table-responsive-lg table-responsive-md shadow-sm rounded">
            <table class="table table-bordered table-hover align-middle bg-white mb-0" style="font-size:0.97rem;">
                <thead class="table-light">
                    <tr>
                        <th style="white-space:nowrap;">Applicant</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th style="white-space:nowrap;">Submitted At</th>
                        <th class="text-center" style="white-space:nowrap;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingApplications as $application)
                        @php
                            $appData = json_decode($application->application_data, true);
                            $name = $appData['full_name'] ?? ($application->user->name ?? '-');
                            $email = $appData['email'] ?? ($application->user->email ?? '-');
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex justify-content-center align-items-center" style="width:2.1rem;height:2.1rem;font-size:1.1rem;">
                                        {{ strtoupper(substr($name, 0, 1)) }}
                                    </div>
                                    <span class="fw-semibold text-truncate" style="max-width:110px;">{{ $name }}</span>
                                </div>
                            </td>
                            <td class="text-break" style="max-width:180px;">{{ $email }}</td>
                            <td>
                                <span class="badge bg-warning text-dark py-1 px-2 fs-6">
                                    Pending
                                </span>
                            </td>
                            <td>
                                <span class="text-nowrap">{{ $application->created_at->format('Y-m-d') }}</span>
                                <div class="text-muted small">{{ $application->created_at->format('H:i') }}</div>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.affiliate.applications.show', $application) }}" class="btn btn-outline-info btn-sm mb-1" style="min-width:80px;">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <form action="{{ route('admin.affiliate.applications.approve', $application) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-success btn-sm mb-1" style="min-width:80px;" type="submit" onclick="return confirm('Approve this application?')">
                                        <i class="bi bi-check-circle"></i> Approve
                                    </button>
                                </form>
                                <form action="{{ route('admin.affiliate.applications.reject', $application) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-danger btn-sm mb-1" style="min-width:80px;" type="submit" onclick="return confirm('Reject this application?')">
                                        <i class="bi bi-x-circle"></i> Reject
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-folder-x fs-2 mb-2 d-block"></i>
                                No pending applications found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="py-3 d-flex justify-content-center">
            {{ $pendingApplications->withQueryString()->links() }}
        </div>
    </div>

    <hr class="my-4">

    {{-- Referral Statistics Table (dynamically rendered from real data) --}}
    <div id="referral-stats" class="mb-5" style="display:none;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold text-success" style="font-size:1.4rem;">
                <i class="bi bi-graph-up-arrow me-2"></i>Referral Statistics Overview
            </h2>
            <button class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('referral-stats').style.display='none'; window.location.hash='';">Close</button>
        </div>
        <div class="table-responsive shadow rounded bg-white p-3">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Top Link</th>
                        <th>Top Product</th>
                        <th>Top Affiliate</th>
                        <th>Conversions</th>
                        <th>Conversion Rate</th>
                        <th>First Referral</th>
                        <th>Most Recent Conversion</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i = 0; $i < max(
                        count($referralStats['top_links'] ?? []),
                        count($referralStats['top_products'] ?? []),
                        count($referralStats['top_affiliates'] ?? [])
                    ); $i++)
                        <tr>
                            {{-- Top Link --}}
                            <td>
                                @if(isset($referralStats['top_links'][$i]))
                                    <a href="{{ $referralStats['top_links'][$i]->link }}" target="_blank">
                                        {{ \Illuminate\Support\Str::limit($referralStats['top_links'][$i]->link, 40) }}
                                    </a>
                                    <div class="text-muted small">{{ $referralStats['top_links'][$i]->total }} referrals</div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            {{-- Top Product --}}
                            <td>
                                @if(isset($referralStats['top_products'][$i]))
                                    {{ $referralStats['top_products'][$i]->product_name }}
                                    <div class="text-muted small">{{ $referralStats['top_products'][$i]->total }} purchases</div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            {{-- Top Affiliate --}}
                            <td>
                                @if(isset($referralStats['top_affiliates'][$i]) && $referralStats['top_affiliates'][$i]->affiliate)
                                    {{ $referralStats['top_affiliates'][$i]->affiliate->user->name ?? '-' }}
                                    <div class="text-muted small">{{ $referralStats['top_affiliates'][$i]->conversions }} conversions</div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            {{-- Conversions --}}
                            <td>
                                @if($i === 0)
                                    {{ $referralStats['total_conversions'] ?? 0 }}
                                @endif
                            </td>
                            {{-- Conversion Rate --}}
                            <td>
                                @if($i === 0)
                                    {{ $referralStats['conversion_rate'] ?? 0 }}%
                                @endif
                            </td>
                            {{-- First Referral --}}
                            <td>
                                @if($i === 0 && isset($referralStats['first_referral']))
                                    {{ $referralStats['first_referral']->created_at ? $referralStats['first_referral']->created_at->format('Y-m-d H:i') : '-' }}
                                @endif
                            </td>
                            {{-- Most Recent Conversion --}}
                            <td>
                                @if($i === 0 && isset($referralStats['latest_conversion']))
                                    {{ $referralStats['latest_conversion']->created_at ? $referralStats['latest_conversion']->created_at->format('Y-m-d H:i') : '-' }}
                                @endif
                            </td>
                        </tr>
                    @endfor
                    @if(
                        empty($referralStats['top_links']) &&
                        empty($referralStats['top_products']) &&
                        empty($referralStats['top_affiliates'])
                    )
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-folder-x fs-2 mb-2 d-block"></i>
                                No referral statistics found.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <hr class="my-4">

    {{-- Affiliate Stats Dashboard --}}
    <div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3 gap-3">
            <h2 class="fw-bold mb-0 text-success" style="font-size:1.6rem;">
                <i class="bi bi-people-fill me-2"></i>Affiliate Stats
            </h2>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                {{-- Search Filter --}}
                <input type="text" id="searchFilter" class="form-control form-control-sm" placeholder="Search name/email/phone" value="{{ request()->get('search_filter', '') }}" style="min-width:200px;">
                
                {{-- Status Filter --}}
                <select id="statusFilter" class="form-select form-select-sm" style="min-width:150px;">
                    <option value="all" {{ request()->get('status_filter') === 'all' || !request()->has('status_filter') ? 'selected' : '' }}>All Status</option>
                    <option value="Active" {{ request()->get('status_filter') === 'Active' ? 'selected' : '' }}>Active Only</option>
                    <option value="Inactive" {{ request()->get('status_filter') === 'Inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>
                
                {{-- Sort By --}}
                <select id="sortBy" class="form-select form-select-sm" style="min-width:150px;">
                    <option value="earnings" {{ request()->get('sort_by') === 'earnings' ? 'selected' : '' }}>Sort: Earnings</option>
                    <option value="name" {{ request()->get('sort_by') === 'name' ? 'selected' : '' }}>Sort: Name</option>
                    <option value="referrals" {{ request()->get('sort_by') === 'referrals' ? 'selected' : '' }}>Sort: Referrals</option>
                    <option value="purchases" {{ request()->get('sort_by') === 'purchases' ? 'selected' : '' }}>Sort: Purchases</option>
                    <option value="links" {{ request()->get('sort_by') === 'links' ? 'selected' : '' }}>Sort: Links</option>
                    <option value="joined" {{ request()->get('sort_by') === 'joined' ? 'selected' : '' }}>Sort: Joined</option>
                </select>
                
                {{-- Sort Order --}}
                <select id="sortOrder" class="form-select form-select-sm" style="min-width:120px;">
                    <option value="desc" {{ request()->get('sort_order') === 'desc' ? 'selected' : '' }}>Highest First</option>
                    <option value="asc" {{ request()->get('sort_order') === 'asc' ? 'selected' : '' }}>Lowest First</option>
                </select>
                
                <button id="filterBtn" class="btn btn-primary btn-sm fw-semibold">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('admin.affiliate.applications.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </a>
                <a href="{{ route('admin.affiliate.applications.export') }}" class="btn btn-outline-success btn-sm fw-semibold">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                </a>
            </div>
        </div>
        <div class="table-responsive-lg table-responsive-md shadow-sm rounded">
            <table class="table table-bordered table-hover align-middle bg-white mb-0" style="font-size:0.98rem;">
                <thead class="table-light">
                    <tr>
                        <th style="white-space:nowrap;">Affiliate Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Affiliate Code</th>
                        <th>Total Links</th>
                        <th>Total Referrals</th>
                        <th>Total Purchases</th>
                        <th>Available Earnings</th>
                        <th>Payment Status</th>
                        <th>Status</th>
                        <th style="white-space:nowrap;">Actions</th>
                        <th style="white-space:nowrap;">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($affiliateStats as $affiliate)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar bg-success text-white rounded-circle d-flex justify-content-center align-items-center" style="width:2.1rem;height:2.1rem;font-size:1.1rem;">
                                        {{ strtoupper(substr($affiliate['name'] ?? '-',0,1)) }}
                                    </div>
                                    <span class="fw-semibold text-truncate" style="max-width:110px;">{{ $affiliate['name'] ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="text-break" style="max-width:180px;">{{ $affiliate['email'] ?? '-' }}</td>
                            <td class="text-nowrap">{{ $affiliate['phone'] ?? '-' }}</td>
                            <td class="fw-bold">{{ $affiliate['code'] }}</td>
                            <td><span class="badge bg-info">{{ $affiliate['links'] }}</span></td>
                            <td>{{ $affiliate['referrals'] }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary purchases-btn" data-affiliate-id="{{ $affiliate['id'] }}" data-purchases-json="{{ json_encode($affiliate['purchases_data']) }}" data-bs-toggle="modal" data-bs-target="#purchasesModal">
                                    {{ $affiliate['purchases'] }}
                                    @if($affiliate['purchases'] > 0)
                                        <i class="bi bi-chevron-down ms-1"></i>
                                    @endif
                                </button>
                            </td>
                            <td>
                                <span class="badge bg-success fs-6">
                                    KES {{ number_format($affiliate['earnings'], 2) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $paymentStatus = $affiliate['payment_status'] ?? 'pending';
                                    $statusBadgeClass = $paymentStatus === 'paid' ? 'bg-success' : 'bg-warning';
                                    $statusIcon = $paymentStatus === 'paid' ? 'bi-check-circle' : 'bi-hourglass-split';
                                @endphp
                                <span class="badge {{ $statusBadgeClass }} fs-6">
                                    <i class="bi {{ $statusIcon }} me-1"></i>{{ ucfirst($paymentStatus) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $affiliate['status'] === 'Active' ? 'success' : ($affiliate['status'] === 'Pending Setup' ? 'warning' : 'secondary') }} fs-6">
                                    {{ $affiliate['status'] }}
                                </span>
                            </td>
                            <td style="white-space:nowrap;">
                                @if(($affiliate['earnings'] ?? 0) > 0 && ($affiliate['payment_status'] ?? 'pending') === 'pending')
                                    <button class="btn btn-sm btn-success pay-now-btn" 
                                        data-affiliate-id="{{ $affiliate['affiliate_id'] }}"
                                        data-affiliate-name="{{ $affiliate['name'] }}"
                                        data-affiliate-code="{{ $affiliate['code'] }}"
                                        data-available-earnings="{{ number_format($affiliate['earnings'], 2) }}"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#payNowModal"
                                        title="Click to process payment">
                                        <i class="bi bi-credit-card me-1"></i>Pay Now
                                    </button>
                                @elseif(($affiliate['payment_status'] ?? 'pending') === 'paid')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Paid
                                    </span>
                                @else
                                    <span class="text-muted small">No earnings</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-nowrap">{{ $affiliate['joined'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">
                                <i class="bi bi-folder-x fs-2 mb-2 d-block"></i>
                                No affiliates found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination Info and Links --}}
        <div class="py-3">
            <div class="mb-3 text-muted small">
                Showing <strong>{{ ($affiliateStats->currentPage() - 1) * $affiliateStats->perPage() + 1 }}</strong> 
                to <strong>{{ min($affiliateStats->currentPage() * $affiliateStats->perPage(), $affiliateStats->total()) }}</strong> 
                of <strong>{{ $affiliateStats->total() }}</strong> affiliates
            </div>
            <div class="d-flex justify-content-center">
                {{ $affiliateStats->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Weekly Payouts Modal --}}
<div class="modal fade" id="weeklyPayoutsModal" tabindex="-1" aria-labelledby="weeklyPayoutsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="weeklyPayoutsModalLabel">
                    <i class="bi bi-cash-stack me-2"></i>This Week's Pay Out
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 text-muted small">
                    <strong>Week:</strong> {{ $weekStart->format('Y-m-d') }} to {{ $weekEnd->format('Y-m-d') }} (Wednesday to Wednesday)
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Affiliate Name</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Amount to Pay</th>
                                <th class="text-center">Transactions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($weeklyPayouts as $payout)
                                <tr>
                                    <td><strong>{{ $payout['affiliate_name'] }}</strong></td>
                                    <td>{{ $payout['email'] }}</td>
                                    <td class="font-monospace">{{ $payout['phone'] }}</td>
                                    <td>
                                        <span class="badge bg-success fs-6">
                                            KES {{ number_format($payout['amount'], 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $payout['commission_count'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-2 mb-2 d-block"></i>
                                        No sales this week. Check back next week!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($weeklyPayouts->count() > 0)
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end">Total Week's Payout:</td>
                                    <td colspan="2">
                                        <span class="badge bg-success fs-6">
                                            KES {{ number_format($stats['weekly_payout_total'] ?? 0, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Last Week Payouts Modal --}}
<div class="modal fade" id="lastWeekPayoutsModal" tabindex="-1" aria-labelledby="lastWeekPayoutsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="lastWeekPayoutsModalLabel">
                    <i class="bi bi-calendar-check me-2"></i>Last Week's Pay Out
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 text-muted small">
                    <strong>Week:</strong> {{ $lastWeekStart->format('Y-m-d') }} to {{ $lastWeekEnd->format('Y-m-d') }} (Wednesday to Wednesday)
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Affiliate Name</th>
                                <th>Amount Paid</th>
                                <th class="text-center">Transactions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lastWeekPayouts as $payout)
                                <tr>
                                    <td><strong>{{ $payout['affiliate_name'] }}</strong></td>
                                    <td>
                                        <span class="badge bg-success fs-6">
                                            KES {{ number_format($payout['amount'], 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $payout['commission_count'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-2 mb-2 d-block"></i>
                                        No sales last week.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($lastWeekPayouts->count() > 0)
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td class="text-end">Total Last Week's Payout:</td>
                                    <td colspan="2">
                                        <span class="badge bg-success fs-6">
                                            KES {{ number_format($stats['last_week_payout_total'] ?? 0, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Purchases Details Modal --}}
<div class="modal fade" id="purchasesModal" tabindex="-1" aria-labelledby="purchasesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="purchasesModalLabel">
                    <i class="bi bi-bag-check me-2"></i>Purchase Orders - <span id="affiliateName">-</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="purchasesContent">
                    <div class="text-center text-muted py-4">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Pay Now Confirmation Modal --}}
<div class="modal fade" id="payNowModal" tabindex="-1" aria-labelledby="payNowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title fw-bold" id="payNowModalLabel">
                    <i class="bi bi-credit-card me-2"></i>Process Affiliate Payment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Payment Processing:</strong> This action will mark all pending commissions as paid and send a payment notification email to the affiliate with their weekly statistics.
                </div>

                <div class="card mb-4 border-0 bg-light">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="text-muted small mb-1">Affiliate Name</div>
                                <div class="fw-bold fs-5" id="modalAffiliateName">-</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small mb-1">Affiliate Code</div>
                                <div class="fw-bold fs-5" id="modalAffiliateCode">-</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="text-muted small mb-1">Available Earnings</div>
                                <div class="fw-bold fs-5 text-success" id="modalAvailableEarnings">Ksh 0.00</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small mb-1">Payment Status</div>
                                <span class="badge bg-warning fs-6">
                                    <i class="bi bi-hourglass-split me-1"></i>Pending
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Confirmation Required:</strong> Click "Process Payment" below to confirm. This action will:
                    <ul class="mt-2 mb-0 ms-3">
                        <li>Mark all pending commissions as <strong>Paid</strong></li>
                        <li>Send a detailed email with weekly affiliate statistics</li>
                        <li>Reset available earnings to zero until new sales are made</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancel
                </button>
                <form id="payNowForm" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-lg" id="payNowSubmitBtn">
                        <i class="bi bi-check-circle me-1"></i>Process Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Custom Colors --}}
<style>
    .avatar {
        font-size: 1.1rem;
        width: 2.1rem;
        height: 2.1rem;
        min-width: 2.1rem;
        min-height: 2.1rem;
    }
    .letter-spacing {
        letter-spacing: 0.08em;
    }
    .bg-gradient-blue {
        background: linear-gradient(135deg, #3b8beb 0%, #0d6efd 100%);
    }
    .bg-gradient-orange {
        background: linear-gradient(135deg, #ffb347 0%, #ff7f50 100%);
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
    .bg-gradient-success {
        background: linear-gradient(135deg, #84e4a5 0%, #28a745 100%);
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #67e8f9 0%, #0dcaf0 100%);
    }
    .stat-card {
        border-radius: 1.2rem !important;
        transition: transform 0.18s cubic-bezier(.19,1,.22,1);
    }
    .stat-card:hover, .clickable-card:hover {
        transform: translateY(-5px) scale(1.03);
        box-shadow: 0 10px 32px 0 rgba(0,0,0,0.08), 0 2px 4px 0 rgba(0,0,0,0.08);
        filter: brightness(0.96);
    }
    #referral-stats:target, #referral-stats.active {
        display: block !important;
    }
    @media (max-width: 576px) {
        .display-6, .h3 { font-size: 1.2rem; }
        .fw-bold, h1, h2, h4 { font-size: 1.05rem !important; }
        .table th, .table td { padding: 0.45rem 0.3rem; }
    }
    @media (max-width: 768px) {
        .fw-bold, h1, h2, h4 { font-size: 1.1rem !important; }
    }
</style>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if(window.location.hash === "#referral-stats") {
            document.getElementById('referral-stats').style.display = 'block';
            setTimeout(function() {
                document.getElementById('referral-stats').scrollIntoView({behavior: 'smooth'});
            }, 100);
        }
        document.querySelectorAll('a[href="#referral-stats"]').forEach(function(link){
            link.addEventListener('click', function(e){
                setTimeout(function() {
                    document.getElementById('referral-stats').style.display = 'block';
                    document.getElementById('referral-stats').scrollIntoView({behavior: 'smooth'});
                }, 100);
            });
        });
        
        // Handle weekly payouts card click
        document.querySelectorAll('a[href="#weekly-payouts"]').forEach(function(link){
            link.addEventListener('click', function(e){
                e.preventDefault();
                const weeklyPayoutsModal = new bootstrap.Modal(document.getElementById('weeklyPayoutsModal'));
                weeklyPayoutsModal.show();
            });
        });
        
        // Handle last week payouts card click
        document.querySelectorAll('a[href="#last-week-payouts"]').forEach(function(link){
            link.addEventListener('click', function(e){
                e.preventDefault();
                const lastWeekPayoutsModal = new bootstrap.Modal(document.getElementById('lastWeekPayoutsModal'));
                lastWeekPayoutsModal.show();
            });
        });
        
        // Handle filter and sort button
        document.getElementById('filterBtn').addEventListener('click', function() {
            const searchFilter = document.getElementById('searchFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const sortBy = document.getElementById('sortBy').value;
            const sortOrder = document.getElementById('sortOrder').value;
            
            // Build query string
            const params = new URLSearchParams();
            if (searchFilter) params.append('search_filter', searchFilter);
            if (statusFilter !== 'all') params.append('status_filter', statusFilter);
            if (sortBy !== 'earnings') params.append('sort_by', sortBy);
            if (sortOrder !== 'desc') params.append('sort_order', sortOrder);
            
            // Redirect with new parameters
            window.location.href = '{{ route("admin.affiliate.applications.index") }}?' + params.toString();
        });
        
        // Allow Enter key to trigger filter
        ['searchFilter'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        document.getElementById('filterBtn').click();
                    }
                });
            }
        });
        
        // Handle purchases modal - extract data from table rows
        document.querySelectorAll('.purchases-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const affiliateId = this.getAttribute('data-affiliate-id');
                const row = this.closest('tr');
                const affiliateName = row.querySelector('td:first-child .fw-semibold').textContent.trim();
                
                // Get purchases data from the button's data attribute (store it there from backend)
                const purchasesJson = this.getAttribute('data-purchases-json');
                
                document.getElementById('affiliateName').textContent = affiliateName;
                
                if (purchasesJson) {
                    try {
                        const purchases = JSON.parse(purchasesJson);
                        
                        if (purchases && purchases.length > 0) {
                            let html = '<div class="table-responsive"><table class="table table-sm table-hover"><thead class="table-light"><tr><th>Product</th><th>Amount</th><th>Purchased At</th></tr></thead><tbody>';
                            
                            purchases.forEach(purchase => {
                                html += '<tr><td>' + (purchase.product_name || 'Unknown') + '</td><td><span class="badge bg-success">KES ' + parseFloat(purchase.amount).toFixed(2) + '</span></td><td>' + purchase.purchased_at + '</td></tr>';
                            });
                            
                            html += '</tbody></table></div>';
                            document.getElementById('purchasesContent').innerHTML = html;
                        } else {
                            document.getElementById('purchasesContent').innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-inbox fs-2 mb-2 d-block"></i>No purchases found for this affiliate.</div>';
                        }
                    } catch (e) {
                        document.getElementById('purchasesContent').innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-inbox fs-2 mb-2 d-block"></i>No purchases found for this affiliate.</div>';
                    }
                } else {
                    document.getElementById('purchasesContent').innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-inbox fs-2 mb-2 d-block"></i>No purchases found for this affiliate.</div>';
                }
            });
        });

        // Handle Pay Now button - populate modal and set form action
        document.querySelectorAll('.pay-now-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const affiliateId = this.getAttribute('data-affiliate-id');
                const affiliateName = this.getAttribute('data-affiliate-name');
                const affiliateCode = this.getAttribute('data-affiliate-code');
                const availableEarnings = this.getAttribute('data-available-earnings');

                // Populate modal fields
                document.getElementById('modalAffiliateName').textContent = affiliateName;
                document.getElementById('modalAffiliateCode').textContent = affiliateCode;
                document.getElementById('modalAvailableEarnings').textContent = 'Ksh ' + availableEarnings;

                // Set form action to the pay-now route
                const form = document.getElementById('payNowForm');
                form.action = '{{ route("admin.affiliate.pay-now", ":affiliate_id") }}'.replace(':affiliate_id', affiliateId);
            });
        });

        // Handle Pay Now form submission
        document.getElementById('payNowForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('payNowSubmitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
        });
    });
</script>
@endsection