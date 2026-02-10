@extends('layouts.app')

@push('styles')
<style>
    .payment-status-page {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }
    
    .payment-card {
        background: white;
        border-radius: 25px;
        box-shadow: 0 15px 50px rgba(0,0,0,0.1);
        border: none;
        overflow: hidden;
    }
    
    .payment-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        text-align: center;
    }
    
    .payment-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.9;
    }
    
    .payment-title {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .payment-subtitle {
        opacity: 0.9;
        font-size: 1rem;
    }
    
    .payment-body {
        padding: 2.5rem;
    }
    
    .status-pending {
        color: #ffc107;
    }
    
    .status-completed {
        color: #28a745;
    }
    
    .status-failed {
        color: #dc3545;
    }
    
    .status-cancelled {
        color: #6c757d;
    }
    
    .order-details {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 1.5rem;
        margin: 1.5rem 0;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .detail-row:last-child {
        border-bottom: none;
    }
    
    .detail-label {
        font-weight: 600;
        color: #495057;
    }
    
    .detail-value {
        color: #6c757d;
        font-weight: 500;
    }
    
    .action-buttons {
        text-align: center;
        margin-top: 2rem;
    }
    
    .btn-relearn {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: none;
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 25px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        margin: 0.5rem;
    }
    
    .btn-relearn:hover {
        background: linear-gradient(135deg, #764ba2, #667eea);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        color: white;
    }
    
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .instructions {
        background: #e7f3ff;
        border: 1px solid #b8daff;
        border-radius: 10px;
        padding: 1rem;
        margin: 1rem 0;
    }
    
    .instructions h6 {
        color: #004085;
        margin-bottom: 0.5rem;
    }
    
    .instructions ol {
        margin-bottom: 0;
        color: #004085;
    }
</style>
@endpush

@section('content')
<div class="payment-status-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="payment-card">
                    <div class="payment-header">
                        @if($order->status === 'pending')
                            <div class="payment-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h2 class="payment-title">Payment Pending</h2>
                            <p class="payment-subtitle">Waiting for payment confirmation...</p>
                        @elseif($order->status === 'completed')
                            <div class="payment-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h2 class="payment-title">Payment Successful</h2>
                            <p class="payment-subtitle">Your payment has been confirmed!</p>
                        @elseif($order->status === 'failed')
                            <div class="payment-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <h2 class="payment-title">Payment Failed</h2>
                            <p class="payment-subtitle">Your payment was not successful</p>
                        @else
                            <div class="payment-icon">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <h2 class="payment-title">Payment {{ ucfirst($order->status) }}</h2>
                            <p class="payment-subtitle">Current payment status</p>
                        @endif
                    </div>
                    
                    <div class="payment-body">
                        <div class="order-details">
                            <div class="detail-row">
                                <span class="detail-label">Order ID:</span>
                                <span class="detail-value">#{{ $order->id }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Product:</span>
                                <span class="detail-value">{{ $order->product->name }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Amount:</span>
                                <span class="detail-value">{{ $order->formatted_amount }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Phone Number:</span>
                                <span class="detail-value">{{ $order->payment_phone }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status:</span>
                                <span class="detail-value">
                                    <span class="status-{{ $order->status }}">
                                        <i class="fas fa-circle me-1"></i>
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </span>
                            </div>
                            @if($order->mpesa_receipt_number)
                                <div class="detail-row">
                                    <span class="detail-label">M-Pesa Receipt:</span>
                                    <span class="detail-value">{{ $order->mpesa_receipt_number }}</span>
                                </div>
                            @endif
                            <div class="detail-row">
                                <span class="detail-label">Created:</span>
                                <span class="detail-value">{{ $order->created_at->format('M d, Y H:i') }}</span>
                            </div>
                        </div>
                        
                        @if($order->status === 'pending')
                            <div class="instructions">
                                <h6><i class="fas fa-mobile-alt me-2"></i>Complete Payment on Your Phone</h6>
                                <ol>
                                    <li>Check your phone for an M-Pesa payment request</li>
                                    <li>Enter your M-Pesa PIN to confirm payment</li>
                                    <li>Wait for payment confirmation</li>
                                    <li>This page will update automatically</li>
                                </ol>
                            </div>
                            
                            <div class="text-center">
                                <p class="mb-2">
                                    <span class="loading-spinner me-2"></span>
                                    Checking payment status...
                                </p>
                                <small class="text-muted">This page will refresh automatically every 5 seconds</small>
                            </div>
                        @endif
                        
                        <div class="action-buttons">
                            @if($order->status === 'completed')
                                @php
                                    $download = $order->downloads()
                                        ->where('user_id', auth()->id())
                                        ->latest()
                                        ->first();
                                @endphp

                                @if($download && !$download->isExpired() && $download->download_count < $download->max_downloads)
                                    <a href="{{ route('downloads.file', $download->token) }}" class="btn btn-relearn">
                                        <i class="fas fa-download me-2"></i>
                                        Download Product
                                    </a>
                                @else
                                    <form action="{{ route('downloads.generate') }}" method="POST" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                                        <button type="submit" class="btn btn-relearn">
                                            <i class="fas fa-download me-2"></i>
                                            Generate Download Link
                                        </button>
                                    </form>
                                @endif
                            @elseif($order->status === 'failed')
                                <a href="{{ route('orders.checkout', $order->product) }}" class="btn btn-relearn">
                                    <i class="fas fa-redo me-2"></i>
                                    Try Again
                                </a>
                            @endif
                            
                            @if(auth()->check())
                                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-list me-2"></i>
                                    My Orders
                                </a>
                            @endif
                            
                            <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if($order->status === 'completed')
@push('scripts')
<script>
    setTimeout(function() {
        window.location.href = "{{ route('orders.show', ['order' => $order->id]) }}";
    }, 4000); // 4 seconds delay
</script>
@endpush
@endif

@if($order->status === 'pending')
@push('scripts')
<script>
// Auto-refresh for pending payments
let checkCount = 0;
const maxChecks = 60; // Check for 5 minutes (60 * 5 seconds)

function checkPaymentStatus() {
    if (checkCount >= maxChecks) {
        clearInterval(statusCheck);
        location.reload(); // Reload after 5 minutes
        return;
    }
    
    // Preserve query string (token) so guest polling works when accessed via token
    const checkUrl = '{{ route("orders.check.status", $order) }}' + window.location.search;
    console.log('Polling payment status:', checkUrl);
    
    fetch(checkUrl, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Payment status data:', data);
        
        if (data.error) {
            console.error('Error in response:', data.error);
            checkCount++;
            return;
        }
        
        if (data.status !== 'pending') {
            console.log('Payment status changed to:', data.status);
            clearInterval(statusCheck);
            
            // Update the status badge in order details
            const statusBadge = document.querySelector('.status-pending');
            if (statusBadge) {
                statusBadge.classList.remove('status-pending');
                statusBadge.classList.add(`status-${data.status}`);
                statusBadge.innerHTML = `<i class="fas fa-circle me-1"></i>${data.status.charAt(0).toUpperCase() + data.status.slice(1)}`;
            }
            
            // Hide the checking status message and instructions
            const checkingMessage = document.querySelector('.text-center p:has(.loading-spinner)');
            if (checkingMessage) {
                checkingMessage.parentElement.style.display = 'none';
            }
            
            const instructions = document.querySelector('.instructions');
            if (instructions) {
                instructions.style.display = 'none';
            }
            
            // Update the header
            const header = document.querySelector('.payment-header');
            if (header) {
                if (data.status === 'completed') {
                    header.style.background = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';
                    header.innerHTML = `
                        <div class="payment-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2 class="payment-title">Payment Successful</h2>
                        <p class="payment-subtitle">Your payment has been confirmed!</p>
                    `;
                } else if (data.status === 'failed') {
                    header.style.background = 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)';
                    header.innerHTML = `
                        <div class="payment-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h2 class="payment-title">Payment Failed</h2>
                        <p class="payment-subtitle">Your payment was not successful</p>
                    `;
                }
            }
            
            // Show action buttons based on status
            const actionButtons = document.querySelector('.action-buttons');
            const token = new URLSearchParams(window.location.search).get('token');
            const isGuest = token !== null; // If token parameter exists, it's a guest
            
            if (actionButtons) {
                if (data.status === 'completed') {
                    // Show download button for guests
                    let actionButtonsHTML = `
                        <a href="{{ route('orders.download-guest', ['order' => $order->id, 'token' => '']) }}" class="btn btn-relearn" id="download-btn">
                            <i class="fas fa-download me-2"></i>
                            Download Product
                        </a>
                    `;
                    
                    // Only show "My Orders" for authenticated users (not for guests)
                    if (!isGuest) {
                        actionButtonsHTML += `
                            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-list me-2"></i>
                                My Orders
                            </a>
                        `;
                    }
                    
                    actionButtonsHTML += `
                        <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Continue Shopping
                        </a>
                    `;
                    
                    actionButtons.innerHTML = actionButtonsHTML;
                    
                    // Update download button with actual token if guest
                    if (isGuest) {
                        document.getElementById('download-btn').href = "{{ route('orders.download-guest', ['order' => $order->id]) }}" + "?token=" + token;
                    }
                } else if (data.status === 'failed') {
                    let actionButtonsHTML = `
                        <a href="{{ route('orders.checkout', $order->product) }}" class="btn btn-relearn">
                            <i class="fas fa-redo me-2"></i>
                            Try Again
                        </a>
                    `;
                    
                    // Only show "My Orders" for authenticated users (not for guests)
                    if (!isGuest) {
                        actionButtonsHTML += `
                            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-list me-2"></i>
                                My Orders
                            </a>
                        `;
                    }
                    
                    actionButtonsHTML += `
                        <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Continue Shopping
                        </a>
                    `;
                    
                    actionButtons.innerHTML = actionButtonsHTML;
                }
            }
            
            // Show redirect message
            const body = document.querySelector('.payment-body');
            if (body) {
                const redirectDiv = document.createElement('div');
                redirectDiv.className = 'alert alert-info mt-3';
                redirectDiv.innerHTML = 'Redirecting to download page in 3 seconds...';
                body.appendChild(redirectDiv);
            }
            
            // Redirect after 3 seconds to show the success message
            if (data.redirect_url) {
                console.log('Will redirect to:', data.redirect_url);
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 3000);
            } else {
                console.log('Reloading page');
                setTimeout(() => {
                    location.reload();
                }, 3000);
            }
        } else {
            console.log('Still pending, will check again');
        }
        checkCount++;
    })
    .catch(error => {
        console.error('Error checking payment status:', error);
        checkCount++;
    });
}

// Check status every 5 seconds
const statusCheck = setInterval(checkPaymentStatus, 5000);

// Stop checking when user leaves the page
window.addEventListener('beforeunload', function() {
    clearInterval(statusCheck);
});
</script>
@endpush
@endif