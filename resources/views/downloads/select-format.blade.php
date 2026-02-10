@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <!-- Header -->
            <div class="mb-4">
                <h1 class="h3 mb-2">Choose Download Format</h1>
                <p class="text-muted">Select which format you'd like to download</p>
            </div>

            <!-- Product Info Card -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    @if($product->image)
                        <div class="mb-3">
                            @php
                                $imageUrl = $product->getImageUrl();
                            @endphp
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 250px; width: 100%; object-fit: cover;" onerror="this.style.display='none';">
                            @endif
                        </div>
                    @else
                        <div class="mb-3 bg-light rounded d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="bi bi-book" style="font-size: 3rem; color: #ccc;"></i>
                        </div>
                    @endif
                    <h5 class="card-title">{{ $product->name }}</h5>
                    <p class="text-muted small">{{ Str::limit($product->description, 150) }}</p>
                    
                    @if($download->expires_at)
                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-clock"></i> 
                            Link expires: {{ $download->expires_at->format('F d, Y g:i A') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Format Selection Cards -->
            <div class="row g-3">
                @if($hasEpub)
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm hover-card" style="cursor: pointer;">
                            <a href="{{ route('downloads.serve-file', [$download->token, 'epub']) }}" class="text-decoration-none text-dark stretched-link">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-book" style="font-size: 2.5rem; color: #6c757d;"></i>
                                    </div>
                                    <h5 class="card-title">ePub Format</h5>
                                    <p class="text-muted small mb-2">
                                        @if($product->file_size)
                                            {{ $product->formatted_file_size }}
                                        @else
                                            Digital book format
                                        @endif
                                    </p>
                                    <div class="badge bg-primary">Recommended</div>
                                </div>
                            </a>
                        </div>
                    </div>
                @endif

                @if($hasPdf)
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm hover-card" style="cursor: pointer;">
                            <a href="{{ route('downloads.serve-file', [$download->token, 'pdf']) }}" class="text-decoration-none text-dark stretched-link">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-file-pdf" style="font-size: 2.5rem; color: #dc3545;"></i>
                                    </div>
                                    <h5 class="card-title">PDF Format</h5>
                                    <p class="text-muted small mb-2">
                                        @if($product->pdf_file_size)
                                            {{ $product->formatted_pdf_file_size }}
                                        @else
                                            Portable Document Format
                                        @endif
                                    </p>
                                </div>
                            </a>
                        </div>
                    </div>
                @endif

                @if($hasZip)
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm hover-card" style="cursor: pointer;">
                            <a href="{{ route('downloads.serve-file', [$download->token, 'zip']) }}" class="text-decoration-none text-dark stretched-link">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-file-zip" style="font-size: 2.5rem; color: #fd7e14;"></i>
                                    </div>
                                    <h5 class="card-title">ZIP Archive</h5>
                                    <p class="text-muted small mb-2">
                                        @if($product->zip_file_size)
                                            {{ $product->formatted_zip_file_size }}
                                        @else
                                            Compressed Archive
                                        @endif
                                    </p>
                                </div>
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Info -->
            <div class="mt-4 text-center">
                <small class="text-muted">
                Downloads remaining: <strong>{{ $download->max_downloads - $download->download_count }}</strong> of {{ $download->max_downloads }}
                </small>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>
@endsection
