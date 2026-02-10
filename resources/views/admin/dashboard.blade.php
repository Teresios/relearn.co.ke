@extends('layouts.app')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
/* -- Styles unchanged, omitted for brevity -- */
.admin-dashboard {
    background: linear-gradient(135deg, #f8fafc 0%, #e3f2fd 100%);
    min-height: 100vh;
    padding-bottom: 2rem;
}
.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem 0 1.2rem 0;
    margin-bottom: 1.2rem;
    position: relative;
}
.dashboard-title {
    font-size: 2.1rem;
    font-weight: 700;
    margin-bottom: 0.3rem;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1.7rem;
}
.stats-card {
    background: white;
    border-radius: 13px;
    padding: 1rem 1rem 0.8rem 1rem;
    box-shadow: 0 3px 14px rgba(0,0,0,0.07);
    transition: all 0.2s;
    min-width: 0;
}
.stats-card:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 8px 24px rgba(102,126,234,0.12);
}
.stats-header {
    display: flex;
    align-items: center;
    margin-bottom: 0.7rem;
}
.stats-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.11rem;
    color: white;
    margin-right: 0.7rem;
}
.stats-icon.revenue { background: linear-gradient(135deg, #1e3a8a, #3b82f6); }
.stats-icon.orders { background: linear-gradient(135deg, #059669, #10b981); }
.stats-icon.users { background: linear-gradient(135deg, #7c3aed, #a855f7); }
.stats-icon.downloads { background: linear-gradient(135deg, #dc2626, #ef4444); }
.stats-number {
    font-size: 1.38rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.08rem;
    line-height: 1.1;
}
.stats-label {
    font-size: 0.81rem;
    color: #6b7280;
    font-weight: 600;
    text-transform: uppercase;
}
.stats-card small {
    font-size: 0.75rem;
}
.quick-actions {
    background: white;
    border-radius: 13px;
    padding: 1rem 1rem 0.8rem 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.09);
    margin-bottom: 1.7rem;
}
.action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.7rem;
}
.quick-action-btn {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: none;
    color: white;
    padding: 0.7rem 1rem;
    border-radius: 9px;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    transition: all 0.2s;
    cursor: pointer;
    font-size: 0.93rem;
    min-width: 0;
}
.quick-action-btn:hover {
    background: linear-gradient(135deg, #764ba2, #667eea);
    transform: translateY(-2px);
    color: white;
    text-decoration: none;
}
.recent-card .card-header {
    font-size: 1.04rem;
    font-weight: 600;
    background: #f8fafc;
    border-bottom: 1px solid #edeef2;
}
.recent-card .card-body {
    padding: 0.7rem 1rem;
    font-size: 0.99rem;
}
.recent-card .badge {
    font-size: 0.76rem;
    padding: 0.30em 0.7em;
    border-radius: 7px;
}
@media (max-width: 768px) {
    .dashboard-title { font-size: 1.25rem; }
    .stats-grid { grid-template-columns: 1fr 1fr; gap: 0.6rem; }
    .quick-actions { padding: 0.7rem 0.4rem; }
    .action-grid { grid-template-columns: 1fr 1fr; gap: 0.5rem; }
    .recent-card .card-body { font-size: 0.92rem; }
}
@media (max-width: 500px) {
    .stats-grid { grid-template-columns: 1fr; }
    .action-grid { grid-template-columns: 1fr; }
    .dashboard-title { font-size: 1rem; }
    .stats-card, .quick-actions { padding: 0.7rem 0.4rem; }
    .recent-card .card-header { font-size: 0.97rem; }
}

/* Notification Bell Styles */
.notification-bell-container {
    position: absolute;
    top: 22px;
    right: 36px;
    z-index: 1111;
}
@media (max-width: 768px) {
    .notification-bell-container {
        top: 12px;
        right: 10px;
    }
}
#notificationBtn {
    background: none;
    border: none;
    position: relative;
    padding: 0;
    outline: none;
}
#notificationBell {
    font-size: 2rem;
    color: #fff;
}
#notificationCount {
    display: none;
    position: absolute;
    top: -0.3em;
    right: -0.2em;
    background: red;
    color: white;
    border-radius: 50%;
    padding: 2px 7px;
    font-size: 0.8rem;
    font-weight: bold;
    min-width: 1.3em;
    text-align: center;
    box-shadow: 0 1px 5px rgba(0,0,0,0.13);
    z-index: 2;
}
</style>
@endpush

@section('content')
<div class="admin-dashboard">
    <!-- Dashboard Header -->
    <div class="dashboard-header position-relative">
        <!-- Notification Bell for Admin -->
        <div class="notification-bell-container">
            <button id="notificationBtn" aria-label="Notifications">
                <span id="notificationBell"><i class="fas fa-bell"></i></span>
                <span id="notificationCount">0</span>
            </button>
        </div>
        <div class="container">
            <div class="row align-items-center gy-2">
                <div class="col-md-8">
                    <h1 class="dashboard-title mb-0">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Admin Dashboard
                    </h1>
                    <p class="dashboard-subtitle mb-0" style="font-size:0.97rem;">Welcome back! Here's what's happening with your platform.</p>
                </div>
                <div class="col-md-4">
                    <div class="text-end mt-2 mt-md-0">
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="bi bi-check-circle me-1"></i>
                            System Healthy
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistics Grid -->
        <div class="stats-grid">
            <!-- Revenue Card -->
            <div class="stats-card">
                <div class="stats-header">
                    <div class="stats-icon revenue">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div>
                        <div class="stats-number">KES {{ number_format($stats['total_revenue'] ?? 0, 0) }}</div>
                        <div class="stats-label">Revenue</div>
                    </div>
                </div>
                <div>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i>
                        This Month: KES {{ number_format($stats['revenue_this_month'] ?? 0, 0) }}
                    </small>
                </div>
            </div>

            <!-- Orders Card -->
            <div class="stats-card">
                <div class="stats-header">
                    <div class="stats-icon orders">
                        <i class="bi bi-cart"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ number_format($stats['total_orders'] ?? 0) }}</div>
                        <div class="stats-label">Orders</div>
                    </div>
                </div>
                <div>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i>
                        Today: {{ $stats['orders_today'] ?? 0 }}
                    </small>
                </div>
            </div>

            <!-- Users Card -->
            <div class="stats-card">
                <div class="stats-header">
                    <div class="stats-icon users">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ number_format($stats['total_users'] ?? 0) }}</div>
                        <div class="stats-label">Users</div>
                    </div>
                </div>
                <div>
                    <small class="text-info">
                        <i class="bi bi-person-plus"></i>
                        New: {{ $stats['users_this_month'] ?? 0 }}
                    </small>
                </div>
            </div>

            <!-- Downloads Card -->
            <div class="stats-card">
                <div class="stats-header">
                    <div class="stats-icon downloads">
                        <i class="bi bi-download"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ number_format($stats['total_downloads'] ?? 0) }}</div>
                        <div class="stats-label">Downloads</div>
                    </div>
                </div>
                <div>
                    <small class="text-warning">
                        <i class="bi bi-hourglass"></i>
                        Active: {{ $stats['active_downloads'] ?? 0 }}
                    </small>
                </div>
            </div>

            <!-- Masterclass Registrations Card (UPDATED) -->
            <div class="stats-card">
                <div class="stats-header">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #fb923c, #fbbf24);">
                        <i class="bi bi-easel"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ number_format($stats['successful_masterclass_registrations'] ?? 0) }}</div>
                        <div class="stats-label">Masterclass Registrations</div>
                    </div>
                </div>
                <div>
                    <small class="text-primary">
                        <i class="bi bi-easel"></i>
                        Masterclasses: {{ $stats['total_masterclasses'] ?? 0 }}
                    </small>
                </div>
            </div>

            <!-- Today's Earnings Card -->
            <a href="{{ route('admin.orders.index') }}" style="text-decoration: none; color: inherit;">
                <div class="stats-card">
                    <div class="stats-header">
                        <div class="stats-icon revenue">
                            <i class="bi bi-piggy-bank"></i>
                        </div>
                        <div>
                            <div class="stats-number">KES {{ number_format($stats['revenue_today'] ?? 0, 0) }}</div>
                            <div class="stats-label">Today's Earnings</div>
                        </div>
                    </div>
                    <div>
                        <small class="text-success">
                            <i class="bi bi-arrow-up"></i>
                            This Week: KES {{ number_format($stats['revenue_this_week'] ?? 0, 0) }}
                        </small>
                    </div>
                </div>
            </a>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="mb-2">
                <h5 style="font-size:1.07rem;"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
            </div>
            <div class="action-grid">
                <a href="{{ route('admin.products.index') }}" class="quick-action-btn">
                    <i class="bi bi-box"></i>
                    Products
                </a>
                <a href="{{ route('admin.orders.index') }}" class="quick-action-btn">
                    <i class="bi bi-cart"></i>
                    Orders
                </a>
                <a href="{{ route('admin.users.index') }}" class="quick-action-btn">
                    <i class="bi bi-people"></i>
                    Users
                </a>
                <a href="{{ route('admin.masterclasses.index') }}" class="quick-action-btn">
                    <i class="bi bi-easel"></i>
                    Masterclasses
                </a>
                <button class="quick-action-btn" onclick="alert('System health check')">
                    <i class="bi bi-shield-check"></i>
                    Health
                </button>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row g-2">
            <div class="col-md-6">
                <div class="card recent-card h-100">
                    <div class="card-header">
                        <i class="bi bi-clock-history me-2"></i>Recent Orders
                    </div>
                    <div class="card-body">
                        @if(isset($recentOrders) && $recentOrders->count() > 0)
                            @foreach($recentOrders as $order)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <strong>#{{ $order->id }}</strong><br>
                                    <small class="text-muted">{{ $order->created_at->format('M d, Y') }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $order->status === 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($order->status) }}
                                    </span><br>
                                    <small>KES {{ number_format($order->amount) }}</small>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <p class="text-muted mb-0">No recent orders found.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Masterclass Registrations (UPDATED) -->
            <div class="col-md-6">
                <div class="card recent-card h-100">
                    <div class="card-header">
                        <i class="bi bi-easel me-2"></i>Recent Masterclass Registrations
                    </div>
                    <div class="card-body">
                        @if(isset($recentRegistrations) && $recentRegistrations->count() > 0)
                            @foreach($recentRegistrations as $reg)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <strong>{{ $reg->masterClass->title ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $reg->created_at->format('M d, Y') }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $reg->paid ? 'success' : 'warning' }}">
                                        {{ $reg->paid ? 'Paid' : 'Pending' }}
                                    </span><br>
                                    <small>{{ $reg->email }}</small>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <p class="text-muted mb-0">No recent registrations found.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function checkNotifications() {
    fetch("{{ route('dashboard.notifications') }}", {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(data => {
        let notifCount = document.getElementById('notificationCount');
        if (data.count && data.count > 0) {
            notifCount.style.display = 'inline';
            notifCount.textContent = data.count;
        } else {
            notifCount.style.display = 'none';
        }
    });
}
setInterval(checkNotifications, 30000);
window.onload = checkNotifications;

document.addEventListener('DOMContentLoaded', function() {
    var bellBtn = document.getElementById('notificationBtn');
    if (bellBtn) {
        bellBtn.addEventListener('click', function() {
            fetch("{{ route('dashboard.notifications') }}", {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.notifications && data.notifications.length > 0) {
                    let details = '';
                    data.notifications.forEach(n => {
                        details += `${n.stat.replace('_',' ').toUpperCase()} changed: ${n.old} → ${n.new} (${n.time})\n`;
                    });
                    alert("Notifications:\n\n" + details);
                } else {
                    alert("No new notifications.");
                }
                // Now clear notifications
                fetch("{{ route('dashboard.notifications.clear') }}", {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'same-origin'
                }).then(() => checkNotifications());
            });
        });
    }
});
</script>
@endpush