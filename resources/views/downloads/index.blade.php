@extends('layouts.app')

@push('styles')
<style>
.library-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 0.7rem;
    justify-content: flex-start;
}
.library-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 1px 10px rgba(102,126,234,0.08);
    overflow: hidden;
    border: none;
    transition: box-shadow .14s, transform .12s;
    width: 100%;
    max-width: 200px;
    min-width: 0;
    display: flex;
    flex-direction: column;
    position: relative;
    margin: 0 auto;
}
.library-card:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 8px 24px rgba(102,126,234,0.13);
}
.library-image-container {
    height: 65px;
    background: #f7f7fb;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}
.library-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
    border-radius: 0;
}
.library-card:hover .library-img {
    transform: scale(1.08);
}
.library-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
}
.library-placeholder i {
    font-size: 1.3rem;
    color: white;
    opacity: 0.8;
    z-index: 2;
    position: relative;
}
.library-card-body {
    padding: 0.7rem 0.7rem 0.7rem 0.7rem;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    position: relative;
    z-index: 2;
}
.library-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.35rem;
    gap: 0.3rem;
    flex-wrap: wrap;
}
.library-category-badge {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: none;
    color: white;
    padding: 0.14rem 0.45rem;
    border-radius: 10px;
    font-size: 0.59rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
    box-shadow: 0 1px 3px rgba(102, 126, 234, 0.11);
}
.library-filetype-badge {
    background: linear-gradient(135deg,#e55a4e 0%, #fd7e14 100%);
    color: #fff;
    border-radius: 7px;
    padding: 0.10rem 0.36rem;
    font-size: 0.64rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-left: 0.18rem;
    margin-right: 0.18rem;
    box-shadow: 0 1px 2px rgba(253,126,20,0.08);
    display: inline-block;
}
.library-download-badge {
    background: linear-gradient(135deg, #43e97b, #38f9d7);
    color: #fff;
    border-radius: 8px;
    padding: 0.1rem 0.40rem;
    font-size: 0.6rem;
    font-weight: 600;
    display: inline-block;
    margin-left: auto;
    white-space: nowrap;
}
.status-badge {
    position: absolute;
    top: 7px;
    left: 8px;
    font-size: 0.62rem;
    font-weight: 700;
    padding: 0.09rem 0.42rem;
    border-radius: 9px;
    color: #fff;
    z-index: 11;
    box-shadow: 0 1px 4px rgba(102,126,234,0.09);
    background: linear-gradient(135deg, #28a745 60%, #20c997 100%);
}
.status-expired {
    background: linear-gradient(135deg, #d9534f 70%, #fd7e14 100%);
}
.status-valid {
    background: linear-gradient(135deg, #43e97b 60%, #38f9d7 100%);
}
.library-title {
    font-size: 0.89rem;
    font-weight: 700;
    color: #22253f;
    margin-bottom: 0.39rem;
    line-height: 1.25;
    min-height: 1.3rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.library-description {
    color: #6c757d;
    font-size: 0.73rem;
    line-height: 1.4;
    margin-bottom: 0.5rem;
    flex-grow: 1;
    min-height: 1.1rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.library-meta-row {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.74rem;
    margin-bottom: 0.08rem;
    color: #707793;
}
.library-meta-row i {
    color: #667eea;
    margin-right: 0.07rem;
}
.library-action {
    margin-top: auto;
}
.library-download-btn {
    background: linear-gradient(135deg, #43e97b, #38f9d7);
    border: none;
    color: white;
    padding: 0.35rem 0.7rem;
    border-radius: 9px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    transition: all 0.2s;
    box-shadow: 0 2px 6px rgba(67,233,123,0.11);
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    text-decoration: none;
    font-size: 0.81rem;
}
.library-download-btn:hover {
    background: linear-gradient(135deg, #38f9d7, #43e97b);
    transform: translateY(-1px);
    box-shadow: 0 4px 11px rgba(67,233,123,0.16);
    color: white;
    text-decoration: none;
}
.library-date {
    font-size: 0.67rem;
    color: #8e99af;
    margin-top: 0.13rem;
    text-align: right;
}
.download-meta {
    margin-bottom: 0.3rem;
}
.downloads-used {
    display: flex;
    justify-content: space-between;
    font-size: 0.66em;
    color: #576377;
    font-weight: 500;
    margin-bottom: 0.07rem;
}
.progress-bar-custom {
    background: #ececfb;
    border-radius: 4px;
    height: 5px;
    width: 100%;
    overflow: hidden;
    margin-bottom: 0.1rem;
}
.progress-fill {
    height: 100%;
    border-radius: 4px;
    background: linear-gradient(90deg, #43e97b 0%, #38f9d7 100%);
    transition: width .3s;
}
.card-footer {
    background: #f8f9fa;
    border-top: 1px solid #f1f3f4;
    padding: 0.3rem 0.7rem;
}
.order-badge {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    border-radius: 7px;
    padding: 0.07rem 0.37rem;
    font-size: 0.68rem;
    font-weight: 600;
}
.price-badge {
    background: linear-gradient(135deg, #fd7e14 0%, #e55a4e 100%);
    color: white;
    padding: 0.06rem 0.40rem;
    border-radius: 7px;
    font-size: 0.7rem;
    font-weight: 700;
}
.pagination-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    gap: 0.7rem;
}
.custom-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.2rem;
    flex-wrap: wrap;
}
.custom-pagination .page-link {
    color: #764ba2;
    background: #fff;
    border: 1px solid #e4e1f5;
    border-radius: 5px;
    padding: 0.21rem 0.5rem;
    font-weight: 600;
    font-size: 0.89rem;
    transition: background 0.14s, color 0.14s;
    min-width: 28px;
    text-align: center;
}
.custom-pagination .page-item.active .page-link,
.custom-pagination .page-link:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    color: #fff;
    border: 1px solid #764ba2;
}
.custom-pagination .page-item.disabled .page-link {
    color: #b4b4b4;
    background: #f9f9fa;
    pointer-events: none;
}
.pagination-summary {
    font-size: 0.88rem;
    color: #444;
    opacity: 0.78;
    text-align: center;
    font-weight: 500;
}
@media (max-width: 1200px) {
    .library-card { max-width: 48vw; }
}
@media (max-width: 992px) {
    .library-card { min-width: 0; max-width: 98vw; }
}
@media (max-width: 768px) {
    .library-grid { gap: 0.4rem;}
    .library-card { min-width: 0; max-width: 99vw; }
}
@media (max-width: 600px) {
    .library-card-body { padding: 0.5rem 0.4rem;}
    .library-title { font-size: 0.93rem;}
    .custom-pagination .page-link { font-size: 0.82rem; padding: 0.17rem 0.34rem;}
}
@media (max-width: 430px) {
    .library-card { max-width: 100vw;}
}
</style>
@endpush

@section('content')
<div class="container py-3">
    <h2 class="mb-3 text-primary fw-bold">My Digital Library</h2>
    @if($downloads->count() === 0)
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle me-2"></i>
            Your digital library is empty. <a href="{{ route('products.index') }}" class="text-primary text-decoration-underline">Browse the marketplace</a> to get started!
        </div>
    @else
        <div class="library-grid">
            @foreach($downloads as $download)
                @php
                    $product = optional(optional($download->order)->product);
                    // Guess file type from file_name or file_mime
                    $filetype = null;
                    if ($product && $product->file_name) {
                        $ext = strtolower(pathinfo($product->file_name, PATHINFO_EXTENSION));
                        $filetype = $ext ?: null;
                    }
                    if (!$filetype && isset($product->file_mime)) {
                        if (str_contains($product->file_mime, 'pdf')) $filetype = 'pdf';
                        elseif (str_contains($product->file_mime, 'epub')) $filetype = 'epub';
                        elseif (str_contains($product->file_mime, 'zip')) $filetype = 'zip';
                        elseif (str_contains($product->file_mime, 'doc')) $filetype = 'doc';
                        elseif (str_contains($product->file_mime, 'mp3')) $filetype = 'mp3';
                        elseif (str_contains($product->file_mime, 'mp4')) $filetype = 'mp4';
                    }
                @endphp
                <div class="library-card">
                    <!-- Status Badge -->
                    @if($download->expires_at->isPast())
                        <span class="status-badge status-expired">
                            <i class="bi bi-clock-history me-1"></i>Expired
                        </span>
                    @else
                        <span class="status-badge status-valid">
                            <i class="bi bi-check-circle me-1"></i>Active
                        </span>
                    @endif

                    <!-- Product Image -->
                    <div class="library-image-container">
                        @if($product && $product->image_path)
                            <img src="{{ asset('storage/' . $product->image_path) }}"
                                alt="Product Image"
                                class="library-img">
                        @else
                            <div class="library-placeholder">
                                <i class="fas fa-file-alt"></i>
                            </div>
                        @endif
                    </div>
                    <div class="library-card-body d-flex flex-column">
                        <div class="library-header">
                            <span class="library-category-badge">{{ ucfirst(str_replace('-', ' ', $product->category ?? '')) }}</span>
                            @if($filetype)
                                <span class="library-filetype-badge" title="File Type">{{ strtoupper($filetype) }}</span>
                            @endif
                            <span class="library-download-badge ms-auto" title="Download count">
                                <i class="fas fa-download me-1"></i>
                                {{ $product->downloads_count ?? $product->downloads ?? $download->downloads ?? '1+' }}
                            </span>
                        </div>
                        <div class="library-title">{{ $product->name ?? 'No Product' }}</div>
                        <div class="library-description">
                            {{ $product->description ? Str::limit($product->description, 48) : 'Your purchased digital product.' }}
                        </div>
                        <div class="library-meta-row mb-1">
                            @if($product && $product->file_size)
                                <span title="File size"><i class="fas fa-file-archive"></i> {{ $product->formatted_file_size }}</span>
                            @endif
                            @if($product && $product->created_at)
                                <span title="Date added"><i class="fas fa-clock"></i> {{ $product->created_at->format('M Y') }}</span>
                            @endif
                        </div>
                        <div class="download-meta">
                            <div class="downloads-used">
                                <span class="downloads-used-label">Used</span>
                                <span class="downloads-used-count">{{ $download->download_count }}/{{ $download->max_downloads }}</span>
                            </div>
                            <div class="progress-bar-custom">
                                <div class="progress-fill" style="width: {{ min(100, ($download->max_downloads ? ($download->download_count / $download->max_downloads) * 100 : 0)) }}%;"></div>
                            </div>
                        </div>
                        <div class="library-action">
                            @if(!$download->expires_at->isPast() && $download->download_count < $download->max_downloads)
                                @php
                                    $hasEpub = $product && !empty($product->file_path);
                                    $hasPdf = $product && $product->hasPdf();
                                    $hasZip = $product && $product->hasZip();
                                @endphp
                                
                                @if($hasEpub || $hasPdf || $hasZip)
                                    <!-- Multiple formats available -->
                                    @if($hasEpub)
                                        <a href="{{ route('downloads.serve-file', [$download->token, 'epub']) }}"
                                           class="library-download-btn mb-1" download>
                                            <i class="fas fa-download"></i> ePub
                                        </a>
                                    @endif
                                    @if($hasPdf)
                                        <a href="{{ route('downloads.serve-file', [$download->token, 'pdf']) }}"
                                           class="library-download-btn mb-1" download style="background: linear-gradient(135deg, #dc3545, #fd7e14);">
                                            <i class="fas fa-download"></i> PDF
                                        </a>
                                    @endif
                                    @if($hasZip)
                                        <a href="{{ route('downloads.serve-file', [$download->token, 'zip']) }}"
                                           class="library-download-btn mb-1" download style="background: linear-gradient(135deg, #fd7e14, #ffc107);">
                                            <i class="fas fa-download"></i> ZIP
                                        </a>
                                    @endif
                                @endif
                            @elseif($download->download_count >= $download->max_downloads)
                                <button class="btn btn-outline-secondary w-100 mb-1" disabled>
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    Limit Reached
                                </button>
                            @else
                                <form action="{{ route('downloads.generate') }}" method="POST" class="generate-link">
                                    @csrf
                                    <input type="hidden" name="order_id" value="{{ $download->order->id }}">
                                    <button type="submit" class="btn btn-outline-primary w-100 mb-1">
                                        <i class="fas fa-arrow-rotate-right me-1"></i>
                                        New Link
                                    </button>
                                </form>
                            @endif
                            @if($product && $product->preview_url)
                                <a href="{{ $product->preview_url }}"
                                   target="_blank"
                                   class="btn btn-outline-secondary w-100"
                                   style="font-size: 0.7rem; padding: 0.22rem;">
                                    <i class="fas fa-eye me-1"></i>
                                    Preview
                                </a>
                            @endif
                        </div>
                        <div class="library-date">
                            {{ $download->order->created_at->format('d M Y') }}
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="order-badge">#{{ $download->order->id }}</span>
                            <span class="price-badge">KES {{ number_format($download->order->amount, 0) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @if($downloads->hasPages())
        <div class="pagination-wrapper mt-3">
            <div class="pagination-summary">
                Page {{ $downloads->currentPage() }} of {{ $downloads->lastPage() }}
            </div>
            <nav>
                <ul class="pagination custom-pagination mb-0">
                    @php
                        $start = max(1, $downloads->currentPage() - 1);
                        $end = min($downloads->lastPage(), $downloads->currentPage() + 2);
                        if($downloads->currentPage() == 1) {
                            $end = min(4, $downloads->lastPage());
                        }
                    @endphp
                    @if($start > 1)
                        <li class="page-item"><a class="page-link" href="{{ $downloads->url(1) }}">1</a></li>
                        @if($start > 2)
                            <li class="page-item disabled"><span class="page-link">…</span></li>
                        @endif
                    @endif

                    @for($i = $start; $i <= $end; $i++)
                        <li class="page-item{{ $downloads->currentPage() == $i ? ' active' : '' }}">
                            <a class="page-link" href="{{ $downloads->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if($end < $downloads->lastPage())
                        @if($end < $downloads->lastPage() - 1)
                            <li class="page-item disabled"><span class="page-link">…</span></li>
                        @endif
                        <li class="page-item"><a class="page-link" href="{{ $downloads->url($downloads->lastPage()) }}">{{ $downloads->lastPage() }}</a></li>
                    @endif
                </ul>
            </nav>
        </div>
        @endif
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Download button feedback
    document.querySelectorAll('a.library-download-btn').forEach(link => {
        link.addEventListener('click', function() {
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-arrow-down-circle me-1"></i>Downloading...';
            btn.classList.add('disabled');
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-check-circle me-1"></i>Downloaded!';
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-success');
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('btn-success', 'disabled');
                    btn.classList.add('btn-primary');
                }, 2500);
            }, 1500);
        });
    });

    // AJAX handler for "New Link" form
    document.querySelectorAll('form.generate-link').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generating...';
            btn.disabled = true;
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            })
            .then(res => res.json())
            .then(response => {
                if (response.download_url) {
                    window.location = response.download_url;
                } else {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                    alert(response.error || 'Failed to generate a new download link.');
                }
            })
            .catch(() => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                alert('An unexpected error occurred. Please try again.');
            });
        });
    });

    // Smooth scroll for pagination
    document.querySelectorAll('.pagination a').forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href') !== '#') {
                setTimeout(() => {
                    window.scrollTo({top: 0, behavior: 'smooth'});
                }, 100);
            }
        });
    });
});
</script>
@endpush