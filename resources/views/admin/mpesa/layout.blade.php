@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block bg-dark sidebar py-4" style="min-height: 100vh;">
            <div class="position-sticky">
                <div class="text-center mb-4">
                    <h5 class="text-white">M-Pesa Admin</h5>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white{{ request()->routeIs('admin.mpesa.dashboard') ? ' active bg-success rounded' : '' }}" href="{{ route('admin.mpesa.dashboard') }}">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white{{ request()->routeIs('admin.mpesa.transactions.*') ? ' active bg-success rounded' : '' }}" href="{{ route('admin.mpesa.transactions.index') }}">
                            <i class="bi bi-arrow-left-right me-2"></i>Transactions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white{{ request()->routeIs('admin.mpesa.budgets.*') ? ' active bg-success rounded' : '' }}" href="{{ route('admin.mpesa.budgets.index') }}">
                            <i class="bi bi-wallet2 me-2"></i>Budgets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white{{ request()->routeIs('admin.mpesa.forwarding.*') ? ' active bg-success rounded' : '' }}" href="{{ route('admin.mpesa.forwarding.index') }}">
                            <i class="bi bi-arrow-repeat me-2"></i>Auto-Forwarding
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white{{ request()->routeIs('admin.mpesa.reports.*') ? ' active bg-success rounded' : '' }}" href="{{ route('admin.mpesa.reports.index') }}">
                            <i class="bi bi-file-earmark-bar-graph me-2"></i>Reports
                        </a>
                    </li>
                    <hr class="text-white">
                    <li class="nav-item">
                        <a class="nav-link text-white-50" href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-arrow-left me-2"></i>Back to Admin
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('mpesa-content')
        </main>
    </div>
</div>
@endsection

@push('styles')
<style>
    .sidebar .nav-link:hover {
        background-color: rgba(255,255,255,0.1);
        border-radius: 5px;
    }
    .stat-card {
        border-left: 4px solid;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
    }
    .stat-card.inbound { border-left-color: #28a745; }
    .stat-card.outbound { border-left-color: #dc3545; }
    .stat-card.pending { border-left-color: #ffc107; }
    .stat-card.total { border-left-color: #007bff; }
</style>
@endpush
