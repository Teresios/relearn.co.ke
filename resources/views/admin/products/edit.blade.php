@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Edit Product</h1>
                    <p class="text-muted">Update product information</p>
                </div>
                <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Product
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="ebook" {{ old('category', $product->category) == 'ebook' ? 'selected' : '' }}>eBook</option>
                                        <option value="business-plan" {{ old('category', $product->category) == 'business-plan' ? 'selected' : '' }}>Business Plan</option>
                                        <option value="template" {{ old('category', $product->category) == 'template' ? 'selected' : '' }}>Template</option>
                                        <option value="course" {{ old('category', $product->category) == 'course' ? 'selected' : '' }}>Course</option>
                                        <option value="software" {{ old('category', $product->category) == 'software' ? 'selected' : '' }}>Software</option>
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
                                           id="price" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sale_price" class="form-label">Sale Price (KES) <span class="badge bg-warning text-dark">Optional</span></label>
                                    <input type="number" class="form-control @error('sale_price') is-invalid @enderror" 
                                           id="sale_price" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" step="0.01" min="0">
                                    @error('sale_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text">Lower than regular price to show discount</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <div class="input-group mb-2">
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" required>{{ old('description', $product->description) }}</textarea>
                                <button type="button" class="btn btn-outline-info" id="generate-ai-btn" title="Generate with AI">
                                    <i class="bi bi-stars"></i> Generate with AI
                                </button>
                            </div>
                            <div id="ai-generate-loading" style="display:none;">
                                <span class="spinner-border spinner-border-sm text-info" role="status" aria-hidden="true"></span>
                                <span class="text-info ms-2">Generating description with AI...</span>
                            </div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control @error('tags') is-invalid @enderror" 
                                       id="tags" name="tags"
                                       value="{{ old('tags', is_array($product->tags) ? implode(', ', $product->tags) : $product->tags) }}" 
                                       placeholder="Enter tags separated by commas">
                                <button type="button" class="btn btn-outline-info" id="generate-ai-tags-btn" title="Generate tags with AI">
                                    <i class="bi bi-stars"></i> AI Tags
                                </button>
                            </div>
                            <div id="ai-generate-tags-loading" style="display:none;">
                                <span class="spinner-border spinner-border-sm text-info" role="status" aria-hidden="true"></span>
                                <span class="text-info ms-2">Generating tags with AI...</span>
                            </div>
                            @error('tags')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Separate tags with commas (e.g., business, marketing, finance)</div>
                        </div>
                        
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="show_discount" name="show_discount" value="1"
                                   {{ old('show_discount', $product->show_discount) ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_discount">
                                <strong>Show discount pricing</strong> - Display strikethrough original price with sale price
                            </label>
                        </div>

                <!-- Current Files Display -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Current Files</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Current Image</h6>
                                @if($product->image)
                                    <div class="mb-3">
                                        <img src="{{ asset('storage/' . $product->image) }}" 
                                             alt="{{ $product->name }}" 
                                             class="img-thumbnail" 
                                             style="max-width: 200px;">
                                        <div class="mt-2">
                                            <small class="text-muted">{{ basename($product->image) }}</small>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-muted">No image uploaded</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6>Current Product File</h6>
                                @if($product->file_path)
                                    <div class="mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-earmark-text me-2"></i>
                                            <div>
                                                <div>{{ basename($product->file_path) }}</div>
                                                <small class="text-success">File uploaded</small>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-danger">No file uploaded</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Update Files -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Update Files</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Product Image</label>
                                    <div class="image-upload-zone" id="imageUploadZone">
                                        <input type="file" class="form-control @error('image') is-invalid @enderror image-input" 
                                               id="image" name="image" accept="image/*" style="display: none;">
                                        <div class="upload-box" style="border: 3px dashed #667eea; border-radius: 15px; padding: 1.5rem; text-align: center; cursor: pointer; background: linear-gradient(135deg, rgba(102,126,234,0.05) 0%, rgba(118,75,162,0.05) 100%); transition: all 0.3s ease; min-height: 120px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                            <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #667eea; margin-bottom: 0.5rem;"></i>
                                            <p class="mb-1" style="font-weight: 600; color: #495057; font-size: 0.95rem;">Drag or click to change</p>
                                            <p class="text-muted" style="font-size: 0.85rem; margin: 0;">JPG, PNG (max 2MB)</p>
                                        </div>
                                    </div>
                                    @error('image')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @if($product->image)
                                        <div id="current-image" style="margin-top: 1rem;">
                                            <p class="text-muted mb-2" style="font-size: 0.9rem;"><strong>Current Image:</strong></p>
                                            <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid rounded" style="max-width: 200px; box-shadow: 0 4px 20px rgba(102,126,234,0.15);" alt="{{ $product->name }}">
                                        </div>
                                    @endif
                                    <div id="image-preview" style="display:none; margin-top: 1.5rem;">
                                        <p class="text-muted mb-2" style="font-size: 0.9rem;"><strong>New Image Preview:</strong></p>
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
                                    <label for="file" class="form-label">New Product File (ePub)</label>
                                    <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                           id="file" name="file">
                                    @error('file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Leave empty to keep current file</div>
                                    @if($product->file_path)
                                        <div class="mt-2">
                                            <p class="text-muted mb-1" style="font-size: 0.9rem;"><strong>Current File:</strong></p>
                                            <small class="text-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                {{ $product->file_name }} ({{ $product->formatted_file_size }})
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pdf_file" class="form-label">New Product File (PDF) <span class="badge bg-info text-dark">Optional</span></label>
                                    <input type="file" class="form-control @error('pdf_file') is-invalid @enderror" 
                                           id="pdf_file" name="pdf_file" accept=".pdf">
                                    @error('pdf_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Leave empty to keep current PDF or upload new one</div>
                                    @if($product->pdf_file_path)
                                        <div class="mt-2">
                                            <p class="text-muted mb-1" style="font-size: 0.9rem;"><strong>Current PDF:</strong></p>
                                            <small class="text-danger">
                                                <i class="fas fa-check-circle me-1"></i>
                                                {{ $product->pdf_file_name }} ({{ $product->formatted_pdf_file_size }})
                                            </small>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="remove_pdf" name="remove_pdf" value="1">
                                                <label class="form-check-label" for="remove_pdf">
                                                    Remove current PDF version
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="zip_file" class="form-label">New Product File (ZIP) <span class="badge bg-success text-white">Optional</span></label>
                                    <input type="file" class="form-control @error('zip_file') is-invalid @enderror" 
                                           id="zip_file" name="zip_file" accept=".zip">
                                    @error('zip_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Leave empty to keep current ZIP or upload new one</div>
                                    @if($product->zip_file_path)
                                        <div class="mt-2">
                                            <p class="text-muted mb-1" style="font-size: 0.9rem;"><strong>Current ZIP:</strong></p>
                                            <small class="text-warning">
                                                <i class="fas fa-check-circle me-1"></i>
                                                {{ $product->zip_file_name }} ({{ $product->formatted_zip_file_size }})
                                            </small>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="remove_zip" name="remove_zip" value="1">
                                                <label class="form-check-label" for="remove_zip">
                                                    Remove current ZIP version
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="preview_url" class="form-label">Preview URL</label>
                            <input type="url" class="form-control @error('preview_url') is-invalid @enderror" 
                                   id="preview_url" name="preview_url" value="{{ old('preview_url', $product->preview_url) }}" 
                                   placeholder="https://example.com/preview">
                            @error('preview_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Optional: Link to preview or demo of the product</div>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Product Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active (customers can purchase this product)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>
                        Update Product
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Product Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Product Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-primary">{{ $product->orders()->count() }}</h4>
                            <small class="text-muted">Total Sales</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">KES {{ number_format($product->orders()->sum('amount'), 2) }}</h4>
                            <small class="text-muted">Revenue</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <small class="text-muted">Created: {{ $product->created_at->format('M d, Y') }}</small>
                    </div>
                </div>
            </div>

            <!-- Warning -->
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-1"></i> Important Notes</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-info-circle text-info me-2"></i>
                            Changing files will not affect previous purchases
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-info-circle text-info me-2"></i>
                            Customers with existing downloads can still access old files
                        </li>
                        <li class="mb-0">
                            <i class="bi bi-info-circle text-info me-2"></i>
                            Price changes only affect new purchases
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Image upload with drag and drop
    const imageInput = document.getElementById('image');
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                previewImage(file);
            } else if (file) {
                alert('Please select a valid image file');
                imageInput.value = '';
            }
        });

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
            }
        });

        uploadBox.addEventListener('click', () => imageInput.click());

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

    // AI generation
    const aiBtn = document.getElementById('generate-ai-btn');
    const aiTagsBtn = document.getElementById('generate-ai-tags-btn');
    const descField = document.getElementById('description');
    const tagsField = document.getElementById('tags');
    const nameField = document.getElementById('name');
    const categoryField = document.getElementById('category');
    const priceField = document.getElementById('price');
    const loading = document.getElementById('ai-generate-loading');
    const tagsLoading = document.getElementById('ai-generate-tags-loading');

    if (aiBtn) {
        aiBtn.addEventListener('click', function () {
            loading.style.display = 'inline-block';
            aiBtn.disabled = true;

            fetch("{{ route('admin.products.ai-generate-description') }}", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: nameField.value,
                    category: categoryField.value,
                    price: priceField.value
                })
            })
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                aiBtn.disabled = false;
                if (data.description) {
                    descField.value = data.description;
                } else {
                    alert('AI could not generate a description.');
                }
            })
            .catch(() => {
                loading.style.display = 'none';
                aiBtn.disabled = false;
                alert('AI generation failed.');
            });
        });
    }

    if (aiTagsBtn) {
        aiTagsBtn.addEventListener('click', function () {
            tagsLoading.style.display = 'inline-block';
            aiTagsBtn.disabled = true;

            fetch("{{ route('admin.products.ai-generate-description') }}", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: nameField.value,
                    category: categoryField.value,
                    price: priceField.value,
                    tags_only: true
                })
            })
            .then(response => response.json())
            .then(data => {
                tagsLoading.style.display = 'none';
                aiTagsBtn.disabled = false;
                if (data.tags) {
                    tagsField.value = data.tags;
                } else {
                    alert('AI could not generate tags.');
                }
            })
            .catch(() => {
                tagsLoading.style.display = 'none';
                aiTagsBtn.disabled = false;
                alert('AI tag generation failed.');
            });
        });
    }
});
</script>
@endpush
@endsection