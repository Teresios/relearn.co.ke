@if(!isset($isSidebar)) @php $isSidebar = false; @endphp @endif
<div class="filter-section-wrapper">
<form method="GET" action="{{ route('products.index') }}" class="w-100" autocomplete="off">
    @if(request('search'))
        <input type="hidden" name="search" value="{{ request('search') }}">
    @endif

    {{-- Price Range Slider --}}
    <div class="filter-group">
        <label class="filter-label">
            <span class="filter-icon"><i class="fas fa-money-bill-wave"></i></span>Price Range
        </label>
        <div class="mb-2">
            <input type="text" id="priceRange{{ $isSidebar ? 'Sidebar' : 'Sheet' }}" readonly class="form-control-plaintext slider-label" style="font-weight:bold; color:#764ba2;" />
            <input type="hidden" name="price_min" id="slider_price_min{{ $isSidebar ? 'Sidebar' : 'Sheet' }}" value="{{ request('price_min', $minPrice) }}">
            <input type="hidden" name="price_max" id="slider_price_max{{ $isSidebar ? 'Sidebar' : 'Sheet' }}" value="{{ request('price_max', $maxPrice) }}">
            <div id="price-slider{{ $isSidebar ? 'Sidebar' : 'Sheet' }}" style="margin-top:10px;"></div>
        </div>
    </div>

    {{-- Sticky Top-down Category List --}}
    <div class="filter-group sticky-categories">
        <label class="filter-label"><span class="filter-icon"><i class="fas fa-tags"></i></span>Categories</label>
        <div class="category-list d-flex flex-column gap-1">
            @php
                $categoriesTopDown = [
                    'business-plan' => 'Business Plan',
                    'ebook' => 'Ebook',
                    'software' => 'Software',
                    'template' => 'Template',
                ];
                $currentCategory = request('category');
            @endphp
            @foreach($categoriesTopDown as $key => $label)
                <label class="cat-radio d-flex align-items-center" style="font-weight:500; cursor:pointer;">
                    <input type="radio" name="category" value="{{ $key }}" @checked($currentCategory == $key) style="margin-right:0.6em;">
                    <span>{{ $label }}</span>
                    <span class="badge bg-light text-primary ms-auto" style="font-size:0.95em;">
                        {{ $categoryCounts[$key] ?? 0 }}
                    </span>
                </label>
            @endforeach
            <label class="cat-radio d-flex align-items-center" style="font-weight:500; cursor:pointer;">
                <input type="radio" name="category" value="" @checked(!$currentCategory) style="margin-right:0.6em;">
                <span>All Categories</span>
            </label>
        </div>
    </div>

    <div class="filter-action-bar mt-3">
        <button type="submit" class="btn apply-btn">
            <i class="fas fa-search me-2"></i>Apply
        </button>
        @if(request()->hasAny(['category', 'price_min', 'price_max', 'search']))
        <a href="{{ route('products.index') }}" class="btn reset-btn">
            <i class="fas fa-times me-2"></i>Reset
        </a>
        @endif
    </div>

    @if(!$isSidebar && request()->hasAny(['category', 'price_min', 'price_max', 'search']))
        <div class="active-filters-section">
            <h6 class="filter-label mb-2"><i class="fas fa-check-circle me-1"></i>Active Filters:</h6>
            @if(request('search'))
                <span class="badge badge-primary"><i class="fas fa-search"></i> {{ request('search') }}</span>
            @endif
            @if(request('category'))
                <span class="badge badge-secondary"><i class="fas fa-tag"></i> {{ ucfirst(str_replace('-', ' ', request('category'))) }}</span>
            @endif
            @if(request('price_min') || request('price_max'))
                <span class="badge badge-success"><i class="fas fa-money-bill"></i> KES {{ request('price_min', $minPrice) }} - {{ request('price_max', $maxPrice) }}</span>
            @endif
        </div>
    @endif
</form>
</div>

@push('styles')
<style>
/* Enhanced Ecommerce Filter Section */
.filter-section-wrapper {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fc 100%);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 24px rgba(102, 126, 234, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.filter-section-wrapper:hover {
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.12);
}

.filter-group {
    margin-bottom: 1.8rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e9ecef;
    transition: all 0.2s;
}

.filter-group:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.filter-label {
    font-size: 1.05rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.8rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-icon {
    font-size: 1.2rem;
    background: linear-gradient(120deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: flex;
    align-items: center;
}

/* Price Slider Enhancement */
#price-sliderSidebar,
#price-sliderSheet {
    background: linear-gradient(90deg, #e9ecef 0%, #f8fafc 100%);
    border-radius: 12px;
    padding: 12px;
    box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.03);
}

.noUi-target {
    background: #e9ecef !important;
    border-radius: 12px !important;
    border: none !important;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.08) !important;
}

.noUi-connect {
    background: linear-gradient(90deg, #667eea, #764ba2) !important;
    box-shadow: 0 2px 12px rgba(102, 126, 234, 0.15) !important;
}

.noUi-horizontal .noUi-handle {
    border-radius: 50% !important;
    background: #fff !important;
    border: 3px solid #667eea !important;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2) !important;
    width: 20px !important;
    height: 20px !important;
    top: -5px !important;
    right: -10px !important;
    transition: all 0.2s !important;
}

.noUi-horizontal .noUi-handle:hover,
.noUi-horizontal .noUi-handle.noUi-active {
    border-color: #764ba2 !important;
    box-shadow: 0 4px 16px rgba(118, 75, 162, 0.3) !important;
    transform: scale(1.15) !important;
}

.noUi-tooltip {
    background: linear-gradient(120deg, #667eea, #764ba2) !important;
    border-radius: 8px !important;
    padding: 6px 12px !important;
    font-weight: 600 !important;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2) !important;
}

/* Category List */
.sticky-categories {
    position: sticky;
    top: 1.5em;
    z-index: 101;
    background: inherit;
    border-radius: 12px;
    padding: 1rem;
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border: 1px solid #e9ecef;
    transition: all 0.3s;
}

.sticky-categories:hover {
    border-color: #d7dbec;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.08);
}

.category-list {
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
}

.cat-radio {
    padding: 0.8rem 1rem;
    border-radius: 10px;
    background: #fff;
    border: 2px solid #e9ecef;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    gap: 0.7rem;
}

.cat-radio:hover {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.04);
    box-shadow: 0 2px 12px rgba(102, 126, 234, 0.09);
}

.cat-radio input[type="radio"] {
    accent-color: #667eea;
    cursor: pointer;
    width: 18px;
    height: 18px;
}

.cat-radio input[type="radio"]:checked + span {
    color: #764ba2;
    font-weight: 700;
}

.cat-radio input[type="radio"]:checked ~ .badge {
    background: linear-gradient(120deg, #667eea, #764ba2) !important;
    color: #fff !important;
    transform: scale(1.05);
}

.cat-radio .badge {
    font-size: 0.85rem;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-weight: 600;
    transition: all 0.2s;
}

/* Filter Action Bar */
.filter-action-bar {
    display: flex;
    gap: 0.8rem;
    justify-content: space-between;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid #e9ecef;
}

.filter-action-bar .btn {
    flex: 1 0 0;
    font-size: 1rem;
    font-weight: 700;
    border-radius: 12px;
    padding: 0.9rem 1rem;
    border: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.filter-action-bar .apply-btn {
    background: linear-gradient(120deg, #667eea, #764ba2);
    color: #fff;
}

.filter-action-bar .apply-btn:hover {
    background: linear-gradient(120deg, #764ba2, #667eea);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.25);
}

.filter-action-bar .apply-btn:active {
    transform: translateY(0);
}

.filter-action-bar .reset-btn {
    background: #e9ecef;
    color: #667eea;
    flex: 0.8;
}

.filter-action-bar .reset-btn:hover {
    background: #d7dbec;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.15);
}

/* Active Filters Display */
.active-filters-section {
    margin-top: 1.2rem;
    padding: 1rem;
    background: linear-gradient(135deg, #f0f4ff 0%, #fff5e6 100%);
    border-radius: 12px;
    border-left: 4px solid #667eea;
}

.active-filters-section h6 {
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #667eea;
    font-weight: 700;
    margin-bottom: 0.8rem;
}

.active-filters-section .badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0.3rem 0.3rem 0.3rem 0;
    animation: slideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.active-filters-section .badge-primary {
    background: linear-gradient(120deg, #667eea, #764ba2);
}

.active-filters-section .badge-secondary {
    background: linear-gradient(120deg, #a8b5e8, #c4a8d8);
}

.active-filters-section .badge-success {
    background: linear-gradient(120deg, #34d399, #10b981);
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Mobile Sheet Enhancements */
@media (max-width: 991.98px) {
    .filter-section-wrapper {
        padding: 1.2rem;
    }
    
    .sticky-categories {
        position: static;
        top: auto;
    }
}

/* Responsive adjustments */
@media (max-width: 575.98px) {
    .filter-action-bar {
        flex-direction: column;
        gap: 1rem;
    }
    
    .filter-action-bar .btn {
        width: 100%;
        padding: 0.8rem;
    }
    
    .filter-action-bar .reset-btn {
        flex: 1;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // For both sidebar and sheet sliders
    ['Sidebar', 'Sheet'].forEach(function(context){
        var sliderId = 'price-slider' + context;
        var minInputId = 'slider_price_min' + context;
        var maxInputId = 'slider_price_max' + context;
        var rangeDisplayId = 'priceRange' + context;

        var priceSlider = document.getElementById(sliderId);
        if(priceSlider){
            var min = {{ $minPrice }};
            var max = {{ $maxPrice }};
            var startMin = {{ request('price_min', $minPrice) }};
            var startMax = {{ request('price_max', $maxPrice) }};
            noUiSlider.create(priceSlider, {
                start: [startMin, startMax],
                connect: true,
                range: {'min': min, 'max': max},
                tooltips: [true, true],
                format: {
                    to: function (value) { return 'KES ' + Math.round(value); },
                    from: function (value) { return Number(value.replace('KES ','')); }
                }
            });
            var priceMinInput = document.getElementById(minInputId);
            var priceMaxInput = document.getElementById(maxInputId);
            var priceRangeDisplay = document.getElementById(rangeDisplayId);
            priceSlider.noUiSlider.on('update', function(values, handle){
                var minVal = Math.round(values[0].replace('KES ','')),
                    maxVal = Math.round(values[1].replace('KES ',''));
                priceMinInput.value = minVal;
                priceMaxInput.value = maxVal;
                priceRangeDisplay.value = 'KES ' + minVal + ' — KES ' + maxVal;
            });
        }
    });
});
</script>
@endpush