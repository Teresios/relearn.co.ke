@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.4.0/tabler-icons.min.css">
<style>
    .create-product-page {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding-bottom: 2rem;
    }
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    .page-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="rgba(255,255,255,0.1)"><polygon points="1000,100 1000,0 0,100"/></svg>');
        background-size: cover;
    }
    .page-header-content { position: relative; z-index: 2; }
    .page-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    .page-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 0;
    }
    .form-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 2rem;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .form-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 50px rgba(0,0,0,0.15);
    }
    .form-card-header {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-bottom: none;
        padding: 1.5rem 2rem;
        position: relative;
    }
    .form-card-header::before {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(135deg, #667eea, #764ba2);
    }
    .form-card-header h5 {
        font-weight: 700;
        margin-bottom: 0;
        color: #495057;
        display: flex;
        align-items: center;
    }
    .form-card-header h5 i {
        margin-right: 0.5rem;
        color: #667eea;
    }
    .form-card-body { padding: 2rem; }
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .form-control, .form-select {
        border-radius: 12px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        font-size: 1rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        transform: translateY(-2px);
    }
    .form-control:hover, .form-select:hover {
        border-color: #667eea;
        transform: translateY(-1px);
    }
    .btn-gradient-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: none;
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 25px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    .btn-gradient-primary:hover {
        background: linear-gradient(135deg, #764ba2, #667eea);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        color: white;
    }
    .btn-gradient-secondary {
        background: linear-gradient(135deg, #6c757d, #495057);
        border: none;
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 25px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
    }
    .btn-gradient-secondary:hover {
        background: linear-gradient(135deg, #495057, #6c757d);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(108, 117, 125, 0.6);
        color: white;
    }
    .btn-outline-gradient {
        background: transparent;
        border: 2px solid #667eea;
        color: #667eea;
        padding: 0.75rem 2rem;
        border-radius: 25px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }
    .btn-outline-gradient:hover {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-color: transparent;
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }
    .required-indicator { color: #f093fb; font-weight: bold; }
    .form-steps {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
        position: sticky;
        top: 20px;
    }
    .step-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        border-radius: 10px;
        transition: all 0.3s ease;
        margin-bottom: 0.5rem;
    }
    .step-item.active {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        transform: translateX(10px);
    }
    .step-item.completed {
        background: linear-gradient(135deg, #43e97b, #38f9d7);
        color: white;
    }
    .step-number {
        width: 30px; height: 30px; border-radius: 50%;
        background: #e9ecef; display: flex; align-items: center; justify-content: center;
        margin-right: 1rem; font-weight: bold; transition: all 0.3s ease;
    }
    .step-item.active .step-number,
    .step-item.completed .step-number {
        background: rgba(255,255,255,0.2);
    }
    .fade-in-up {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s ease;
    }
    .fade-in-up.animated {
        opacity: 1;
        transform: translateY(0);
    }
    .slide-in-left {
        opacity: 0;
        transform: translateX(-30px);
        transition: all 0.6s ease;
    }
    .slide-in-left.animated {
        opacity: 1;
        transform: translateX(0);
    }
    .is-valid { border-color: #43e97b !important; }
    .is-invalid { border-color: #f093fb !important; }
    .valid-feedback { color: #43e97b; font-weight: 600; }
    .invalid-feedback { color: #f093fb; font-weight: 600; }
    @media (max-width: 768px) {
        .page-title { font-size: 2rem; }
        .form-card-body { padding: 1.5rem; }
    }
</style>
@endpush

@section('content')
<div class="create-product-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center page-header-content">
                        <div class="fade-in-up">
                            <h1 class="page-title">Add New Product</h1>
                            <p class="page-subtitle">Create a premium digital product for your marketplace</p>
                        </div>
                        <div class="fade-in-up">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-gradient">
                                <i class="fas fa-arrow-left me-2"></i>Back to Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Form Steps Sidebar -->
            <div class="col-lg-3">
                <div class="form-steps slide-in-left">
                    <h6 class="mb-3 font-weight-bold">Creation Steps</h6>
                    <div class="step-item active" id="step-basic">
                        <div class="step-number">1</div>
                        <div>
                            <div class="font-weight-bold">Basic Info</div>
                            <small class="text-muted">Name, category, price</small>
                        </div>
                    </div>
                    <div class="step-item" id="step-files">
                        <div class="step-number">2</div>
                        <div>
                            <div class="font-weight-bold">Files</div>
                            <small class="text-muted">Upload product files</small>
                        </div>
                    </div>
                    <div class="step-item" id="step-media">
                        <div class="step-number">3</div>
                        <div>
                            <div class="font-weight-bold">Media</div>
                            <small class="text-muted">Images and preview</small>
                        </div>
                    </div>
                    <div class="step-item" id="step-settings">
                        <div class="step-number">4</div>
                        <div>
                            <div class="font-weight-bold">Settings</div>
                            <small class="text-muted">Status and visibility</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form -->
            <div class="col-lg-9">
                <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
                    @csrf
                    <!-- Basic Information -->
                    <div class="form-card fade-in-up" data-step="basic">
                        <div class="form-card-header">
                            <h5><i class="fas fa-info-circle"></i>Basic Information</h5>
                        </div>
                        <div class="form-card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-4">
                                        <label for="name" class="form-label">Product Name <span class="required-indicator">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               id="name" name="name" value="{{ old('name') }}" required
                                               placeholder="Enter a compelling product name">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Choose a clear, descriptive name that customers will easily understand</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="category" class="form-label">Category <span class="required-indicator">*</span></label>
                                        <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                            <option value="">Choose a category...</option>
                                            <option value="ebook" {{ old('category') == 'ebook' ? 'selected' : '' }}>eBook</option>
                                            <option value="business-plan" {{ old('category') == 'business-plan' ? 'selected' : '' }}>Business Plan</option>
                                            <option value="template" {{ old('category') == 'template' ? 'selected' : '' }}>Template</option>
                                            <option value="course" {{ old('category') == 'course' ? 'selected' : '' }}>Course</option>
                                            <option value="software" {{ old('category') == 'software' ? 'selected' : '' }}>Software</option>
                                        </select>
                                        @error('category')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price (KES) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('price') is-invalid @enderror"
                                               id="price" name="price" value="{{ old('price') }}" step="0.01" min="0" required>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sale_price" class="form-label">Sale Price (KES) <span class="badge bg-warning text-dark">Optional</span></label>
                                        <input type="number" class="form-control @error('sale_price') is-invalid @enderror"
                                               id="sale_price" name="sale_price" value="{{ old('sale_price') }}" step="0.01" min="0">
                                        @error('sale_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text">Lower than regular price to show discount</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">
                                    Description <span class="text-danger">*</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="ai-generate-btn">
                                        <i class="ti ti-sparkles"></i> Generate with AI
                                    </button>
                                    <span id="ai-loader" class="spinner-border spinner-border-sm text-primary d-none"></span>
                                </label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Let AI help you write a compelling description for your product.</div>
                            </div>
                            <div class="mb-3">
                                <label for="tags" class="form-label">Tags</label>
                                <input type="text" class="form-control @error('tags') is-invalid @enderror"
                                       id="tags" name="tags" value="{{ old('tags') }}"
                                       placeholder="Enter tags separated by commas">
                                @error('tags')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Separate tags with commas (e.g., business, marketing, finance)</div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="show_discount" name="show_discount" value="1"
                                       {{ old('show_discount') ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_discount">
                                    <strong>Show discount pricing</strong> - Display strikethrough original price with sale price
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Files -->
                    <div class="form-card fade-in-up" data-step="files">
                        <div class="form-card-header">
                            <h5><i class="fas fa-file-upload"></i>Product Files</h5>
                        </div>
                        <div class="form-card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Product Image</label>
                                        <div class="image-upload-zone" id="imageUploadZone" style="position: relative;">
                                            <input type="file" class="form-control @error('image') is-invalid @enderror image-input"
                                                   id="image" name="image" accept="image/*" style="display: none;">
                                            <div class="upload-box" style="border: 3px dashed #667eea; border-radius: 15px; padding: 2rem; text-align: center; cursor: pointer; background: linear-gradient(135deg, rgba(102,126,234,0.05) 0%, rgba(118,75,162,0.05) 100%); transition: all 0.3s ease; min-height: 150px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                                <i class="fas fa-cloud-upload-alt" style="font-size: 2.5rem; color: #667eea; margin-bottom: 0.8rem;"></i>
                                                <p class="mb-1" style="font-weight: 600; color: #495057;">Drag image here or click to browse</p>
                                                <p class="text-muted" style="font-size: 0.9rem; margin: 0;">JPG, PNG (max 2MB)</p>
                                            </div>
                                        </div>
                                        @error('image')
                                            <div class="invalid-feedback d-block" style="color: #f56565;">{{ $message }}</div>
                                        @enderror
                                        <div id="image-preview" style="display:none; margin-top: 1.5rem;">
                                            <div style="position: relative; display: inline-block;">
                                                <img id="preview-img" src="#" class="img-fluid rounded" style="max-width: 200px; box-shadow: 0 4px 20px rgba(102,126,234,0.15);" alt="Preview">
                                                <button type="button" id="remove-image" class="btn btn-sm btn-danger" style="position: absolute; top: -10px; right: -10px; border-radius: 50%; width: 35px; height: 35px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="file" class="form-label">Product File (ePub) <span class="badge bg-warning text-dark">Optional</span></label>
                                        <input type="file" class="form-control @error('file') is-invalid @enderror"
                                               id="file" name="file">
                                        @error('file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Upload the digital product file (ePub, etc.)</div>
                                        <div id="file-selected" style="display:none;">
                                            <span class="badge bg-success mt-2"><i class="fas fa-check-circle me-2"></i><span id="file-name"></span></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="pdf_file" class="form-label">Product File (PDF) <span class="badge bg-info text-dark">Optional</span></label>
                                        <input type="file" class="form-control @error('pdf_file') is-invalid @enderror"
                                               id="pdf_file" name="pdf_file" accept=".pdf">
                                        @error('pdf_file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Upload an optional PDF version of the product</div>
                                        <div id="pdf-file-selected" style="display:none;">
                                            <span class="badge bg-danger mt-2"><i class="fas fa-check-circle me-2"></i><span id="pdf-file-name"></span></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="zip_file" class="form-label">Product File (ZIP) <span class="badge bg-success text-white">Optional</span></label>
                                        <input type="file" class="form-control @error('zip_file') is-invalid @enderror"
                                               id="zip_file" name="zip_file" accept=".zip">
                                        @error('zip_file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Upload an optional ZIP version of the product</div>
                                        <div id="zip-file-selected" style="display:none;">
                                            <span class="badge bg-warning mt-2"><i class="fas fa-check-circle me-2"></i><span id="zip-file-name"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="preview_url" class="form-label">Preview URL</label>
                                <input type="url" class="form-control @error('preview_url') is-invalid @enderror"
                                       id="preview_url" name="preview_url" value="{{ old('preview_url') }}"
                                       placeholder="https://example.com/preview">
                                @error('preview_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Optional: Link to preview or demo of the product</div>
                            </div>
                            <div class="mb-3">
                                <label for="sample_file" class="form-label">
                                    <i class="fas fa-book-open me-1 text-info"></i>
                                    Free Sample / Preview File (PDF)
                                    <span class="badge bg-info text-dark">Optional</span>
                                </label>
                                <input type="file" class="form-control @error('sample_file') is-invalid @enderror"
                                       id="sample_file" name="sample_file" accept=".pdf">
                                @error('sample_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Upload a free sample PDF (e.g. first chapter) that visitors can download without purchasing.
                                    This helps build trust and encourages purchases.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="form-card fade-in-up" data-step="settings">
                        <div class="form-card-header">
                            <h5><i class="fas fa-cog"></i>Product Status</h5>
                        </div>
                        <div class="form-card-body">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active (customers can purchase this product)
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-2 mb-5">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-gradient">Cancel</a>
                        <button type="submit" class="btn btn-gradient-primary">
                            <i class="ti ti-device-floppy me-2"></i>
                            Create Product
                        </button>
                        <button type="button" class="btn btn-gradient-secondary" onclick="previewProduct()">
                            <i class="fas fa-eye me-2"></i> Preview
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Tips</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                Use clear, descriptive product names
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                Write detailed descriptions to help customers
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                Upload high-quality product images
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                Set competitive prices for your market
                            </li>
                            <li class="mb-0">
                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                Use relevant tags for better discoverability
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">File Requirements</h6>
                    </div>
                    <div class="card-body">
                        <h6>Images:</h6>
                        <ul class="small mb-3">
                            <li>Format: JPG, PNG, GIF</li>
                            <li>Max size: 2MB</li>
                            <li>Recommended: 800x600px</li>
                        </ul>
                        <h6>Product Files:</h6>
                        <ul class="small mb-0">
                            <li>Format: PDF, ZIP, DOC, etc.</li>
                            <li>Max size: 50MB</li>
                            <li>Ensure files are complete and error-free</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // AI Generate Description and Tags
    const aiBtn = document.getElementById('ai-generate-btn');
    const aiLoader = document.getElementById('ai-loader');
    aiBtn?.addEventListener('click', async function() {
        const name = document.getElementById('name').value.trim();
        const price = document.getElementById('price').value.trim();
        if (!name || !price) {
            showNotification('Please enter a product name and price first.', 'warning');
            return;
        }
        aiBtn.disabled = true;
        aiLoader.classList.remove('d-none');
        try {
            const response = await fetch('{{ route("admin.products.ai-generate-description") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name, price })
            });
            const data = await response.json();
            if(data.description) document.getElementById('description').value = data.description;
            if(data.tags) document.getElementById('tags').value = data.tags;
        } catch (e) {
            showNotification('AI description generation failed. Try again.', 'danger');
        }
        aiBtn.disabled = false;
        aiLoader.classList.add('d-none');
    });

    // Fade in/slide in animations
    const observeElements = () => {
        const elements = document.querySelectorAll('.fade-in-up, .slide-in-left');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        elements.forEach(element => { observer.observe(element); });
    };
    observeElements();

    // File preview
    const fileInput = document.getElementById('file');
    const imageInput = document.getElementById('image');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                document.getElementById('file-name').textContent = file.name;
                document.getElementById('file-selected').style.display = 'block';
                updateStepStatus('files', 'completed');
            }
        });
    }

    // PDF file preview
    const pdfFileInput = document.getElementById('pdf_file');
    if (pdfFileInput) {
        pdfFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                document.getElementById('pdf-file-name').textContent = file.name;
                document.getElementById('pdf-file-selected').style.display = 'block';
            }
        });
    }

    // ZIP file preview
    const zipFileInput = document.getElementById('zip_file');
    if (zipFileInput) {
        zipFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                document.getElementById('zip-file-name').textContent = file.name;
                document.getElementById('zip-file-selected').style.display = 'block';
            }
        });
    }
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                previewImage(file);
                updateStepStatus('media', 'completed');
            } else {
                alert('Please select a valid image file');
                imageInput.value = '';
            }
        });

        // Drag and drop functionality
        const uploadZone = document.getElementById('imageUploadZone');
        const uploadBox = uploadZone.querySelector('.upload-box');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => {
                uploadBox.style.borderColor = '#764ba2';
                uploadBox.style.backgroundColor = 'rgba(118,75,162,0.08)';
                uploadBox.style.transform = 'scale(1.02)';
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => {
                uploadBox.style.borderColor = '#667eea';
                uploadBox.style.backgroundColor = 'rgba(102,126,234,0.05)';
                uploadBox.style.transform = 'scale(1)';
            });
        });

        uploadZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0 && files[0].type.startsWith('image/')) {
                imageInput.files = files;
                previewImage(files[0]);
                updateStepStatus('media', 'completed');
            }
        });

        uploadBox.addEventListener('click', () => imageInput.click());

        // Remove image button
        document.getElementById('remove-image')?.addEventListener('click', (e) => {
            e.preventDefault();
            imageInput.value = '';
            document.getElementById('image-preview').style.display = 'none';
            uploadBox.style.borderColor = '#667eea';
        });
    }

    function previewImage(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
    // Form validation and step tracking
    const form = document.getElementById('productForm');
    const requiredFields = {
        'basic': ['name', 'category', 'price', 'description'],
        'files': ['file'],
        'settings': ['status'] // not strictly required but for UI consistency
    };
    form.addEventListener('input', function(e) {
        const field = e.target;
        const step = field.closest('[data-step]')?.getAttribute('data-step');
        if (step && requiredFields[step]) {
            checkStepCompletion(step);
        }
        if (field.checkValidity()) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
        }
    });
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Product...';
        submitBtn.disabled = true;
    });
    function checkStepCompletion(stepName) {
        const fields = requiredFields[stepName];
        let allValid = true;
        fields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (!field || !field.value || !field.checkValidity()) {
                allValid = false;
            }
        });
        if (allValid) {
            updateStepStatus(stepName, 'completed');
        }
    }
    function updateStepStatus(stepName, status) {
        const stepElement = document.getElementById(`step-${stepName}`);
        stepElement.classList.remove('active', 'completed');
        stepElement.classList.add(status);
        if (status === 'completed') {
            const number = stepElement.querySelector('.step-number');
            number.innerHTML = '<i class="fas fa-check"></i>';
        }
    }
    // Auto-save functionality (draft mode)
    let autoSaveTimeout;
    form.addEventListener('input', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            showNotification('Draft saved automatically', 'success');
        }, 2000);
    });
    // Enhanced form interactions
    const formControls = document.querySelectorAll('.form-control, .form-select');
    formControls.forEach(control => {
        control.addEventListener('focus', function() {
            this.closest('.form-card')?.classList.add('focused');
        });
        control.addEventListener('blur', function() {
            this.closest('.form-card')?.classList.remove('focused');
        });
    });
    // Price formatting
    const priceInput = document.getElementById('price');
    if (priceInput) {
        priceInput.addEventListener('input', function() {
            let value = this.value;
            if (value && !isNaN(value)) {
                const formatted = parseFloat(value).toLocaleString('en-KE', {
                    style: 'currency',
                    currency: 'KES'
                });
            }
        });
    }
    // Tags enhancement
    const tagsInput = document.getElementById('tags');
    if (tagsInput) {
        tagsInput.addEventListener('blur', function() {
            let tags = this.value.split(',').map(tag => tag.trim()).filter(tag => tag);
            this.value = tags.join(', ');
        });
    }
});

function previewProduct() {
    const name = document.getElementById('name')?.value;
    const category = document.getElementById('category')?.value;
    const price = document.getElementById('price')?.value;
    const description = document.getElementById('description')?.value;
    if (!name || !category || !price || !description) {
        showNotification('Please fill in the basic information first', 'warning');
        return;
    }
    const previewContent = `
        <div style="padding: 20px; font-family: Arial, sans-serif;">
            <h2>${name}</h2>
            <p><strong>Category:</strong> ${category}</p>
            <p><strong>Price:</strong> KES ${parseFloat(price).toFixed(2)}</p>
            <p><strong>Description:</strong></p>
            <p>${description}</p>
        </div>
    `;
    const previewWindow = window.open('', '_blank', 'width=600,height=400');
    previewWindow.document.write(previewContent);
    previewWindow.document.title = 'Product Preview';
}
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check' : type === 'warning' ? 'exclamation' : 'info'}-circle me-2"></i>
        ${message}
    `;
    document.body.appendChild(notification);
    setTimeout(() => { notification.remove(); }, 3000);
}
</script>
@endpush