<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Download;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct()
    {
        // Apply auth middleware only - route-level middleware handles role checking
        $this->middleware('auth');
    }

    /**
     * Product Admin Dashboard - Limited view for product_admin role
     */
    public function productAdminDashboard(Request $request)
    {
        // Check if user has product_admin role
        if (!auth()->user()->hasRole('product_admin')) {
            abort(403, 'Unauthorized access');
        }

        $query = Product::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        if ($request->has('status') && $request->status !== '') {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $products = $query->withCount('orders')->orderByDesc('created_at')->paginate(15);

        // Calculate stats
        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'inactive' => Product::where('is_active', false)->count(),
            'total_value' => Product::where('is_active', true)->sum('price'),
            'total_orders' => $products->sum('orders_count')
        ];

        return view('admin.products.product-admin-dashboard', compact('products', 'stats'));
    }

    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::with(['createdByUser', 'updatedByUser']);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        if ($request->has('status') && $request->status !== '') {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $products = $query->withCount('orders')->orderByDesc('created_at')->paginate(15);

        // Calculate stats
        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'inactive' => Product::where('is_active', false)->count(),
            'total_value' => Product::where('is_active', true)->sum('price')
        ];

        return view('admin.products.index', compact('products', 'stats'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Product::distinct()->pluck('category');
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'show_discount' => 'boolean',
            'category' => 'required|string|max:100',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'file' => 'nullable|file|max:51200', // ePub version (optional)
            'pdf_file' => 'nullable|file|mimes:pdf|max:51200', // 50MB max - PDF version (optional)
            'zip_file' => 'nullable|file|mimes:zip|max:102400', // 100MB max - ZIP version (optional)
            'preview_url' => 'nullable|url',
            'tags' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products/images', 'public');
            }

            // Handle ePub file upload
            $filePath = null;
            $fileName = null;
            $fileSize = null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $filePath = $file->store('products/files', 'public');
            }

            // Handle PDF file upload (optional)
            $pdfFilePath = null;
            $pdfFileName = null;
            $pdfFileSize = null;

            if ($request->hasFile('pdf_file')) {
                $pdfFile = $request->file('pdf_file');
                $pdfFileName = $pdfFile->getClientOriginalName();
                $pdfFileSize = $pdfFile->getSize();
                $pdfFilePath = $pdfFile->store('products/files/pdf', 'public');
            }

            // Handle ZIP file upload (optional)
            $zipFilePath = null;
            $zipFileName = null;
            $zipFileSize = null;

            if ($request->hasFile('zip_file')) {
                $zipFile = $request->file('zip_file');
                $zipFileName = $zipFile->getClientOriginalName();
                $zipFileSize = $zipFile->getSize();
                $zipFilePath = $zipFile->store('products/files/zip', 'public');
            }

            // Process tags
            $tags = $request->tags ? array_map('trim', explode(',', $request->tags)) : null;

            // Create product
            Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'sale_price' => $request->sale_price,
                'show_discount' => $request->has('show_discount'),
                'category' => $request->category,
                'image' => $imagePath,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'pdf_file_path' => $pdfFilePath,
                'pdf_file_name' => $pdfFileName,
                'pdf_file_size' => $pdfFileSize,
                'zip_file_path' => $zipFilePath,
                'zip_file_name' => $zipFileName,
                'zip_file_size' => $zipFileSize,
                'preview_url' => $request->preview_url,
                'tags' => $request->tags,
                'is_active' => $request->has('is_active'),
                'created_by_user_id' => auth()->id(),
                'updated_by_user_id' => auth()->id(),
            ]);

            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error creating product: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Generate description and tags using OpenAI.
     */
    public function aiGenerateDescription(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required',
        ]);

        $prompt = "Write a compelling product description and a set of up to 5 relevant tags for a digital product named '{$request->name}' priced at KES {$request->price}. 
        IMPORTANT: Do NOT present the product as being for sale or being bought. Instead, describe it as a digital resource that users can access as part of a library for a facilitation fee. Avoid words like 'buy', 'purchase', 'own', or 'for sale'. Respond in JSON, with 'description' and 'tags' (comma separated string).";

        $apiKey = config('services.openai.secret');
        $models = [
            'gpt-4.1-mini-2025-04-14',
            'gpt-4.1-mini',
            'gpt-4.1-nano-2025-04-14',
            'gpt-4.1-nano',
        ];

        $content = '';
        $lastError = null;

        foreach ($models as $model) {
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => "Bearer $apiKey",
                ])->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful assistant for digital product sellers.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens' => 350,
                    'temperature' => 0.7,
                ]);

                // If error, try next model
                if ($response->failed()) {
                    $lastError = $response->json('error.message') ?? $response->body();
                    continue;
                }

                $choices = $response->json('choices');
                $content = $choices[0]['message']['content'] ?? '';
                $json = json_decode($content, true);

                if ($json && isset($json['description'])) {
                    // Success!
                    return response()->json([
                        'description' => $json['description'],
                        'tags' => $json['tags'] ?? ''
                    ]);
                } else {
                    $lastError = "Invalid JSON in response for model $model";
                }
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                continue;
            }
        }

        return response()->json([
            'error' => "All models failed. Last error: $lastError"
        ], 500);
    }
    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        // Get recent orders for this product
        $recentOrders = $product->orders()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Calculate stats
        $totalOrders = $product->orders()->count();
        $totalRevenue = $product->orders()
            ->where('status', 'completed')
            ->sum('amount');

        $totalDownloads = Download::whereHas('order', function($query) use ($product) {
            $query->where('product_id', $product->id);
        })->count();

        $recentDownloads = Download::whereHas('order', function($query) use ($product) {
            $query->where('product_id', $product->id);
        })->whereMonth('created_at', now()->month)->count();

        // Get users with maxed out downloads for this product
        $maxedDownloads = Download::where('product_id', $product->id)
            ->whereColumn('download_count', '>=', 'max_downloads')
            ->with('user')
            ->get();

        return view('admin.products.show', compact(
            'product',
            'recentOrders',
            'totalOrders',
            'totalRevenue',
            'totalDownloads',
            'recentDownloads',
            'maxedDownloads'
        ));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = Product::distinct()->pluck('category');
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'show_discount' => 'boolean',
            'category' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'file' => 'nullable|file|max:51200',
            'pdf_file' => 'nullable|file|mimes:pdf|max:51200', // PDF version (optional)
            'zip_file' => 'nullable|file|mimes:zip|max:102400', // ZIP version (optional)
            'preview_url' => 'nullable|url',
            'tags' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $updateData = [
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'sale_price' => $request->sale_price,
                'show_discount' => $request->has('show_discount'),
                'category' => $request->category,
                'preview_url' => $request->preview_url,
                'tags' => $request->tags ? array_map('trim', explode(',', $request->tags)) : null,
                'is_active' => $request->has('is_active'),
            ];

            // Handle new image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                $updateData['image'] = $request->file('image')->store('products/images', 'public');
            }

            $fileWasUpdated = false;

            // Handle new ePub file upload
            if ($request->hasFile('file')) {
                // Delete old file
                if ($product->file_path && Storage::disk('public')->exists($product->file_path)) {
                    Storage::disk('public')->delete($product->file_path);
                }

                $file = $request->file('file');
                $updateData['file_name'] = $file->getClientOriginalName();
                $updateData['file_size'] = $file->getSize();
                $updateData['file_path'] = $file->store('products/files', 'public');
                $fileWasUpdated = true;
            }

            // Handle new PDF file upload
            if ($request->hasFile('pdf_file')) {
                // Delete old PDF file
                if ($product->pdf_file_path && Storage::disk('public')->exists($product->pdf_file_path)) {
                    Storage::disk('public')->delete($product->pdf_file_path);
                }

                $pdfFile = $request->file('pdf_file');
                $updateData['pdf_file_name'] = $pdfFile->getClientOriginalName();
                $updateData['pdf_file_size'] = $pdfFile->getSize();
                $updateData['pdf_file_path'] = $pdfFile->store('products/files/pdf', 'public');
                $fileWasUpdated = true;
            }

            // Allow removal of PDF file via checkbox
            if ($request->has('remove_pdf') && $request->remove_pdf) {
                if ($product->pdf_file_path && Storage::disk('public')->exists($product->pdf_file_path)) {
                    Storage::disk('public')->delete($product->pdf_file_path);
                }
                $updateData['pdf_file_path'] = null;
                $updateData['pdf_file_name'] = null;
                $updateData['pdf_file_size'] = null;
            }

            // Handle new ZIP file upload
            if ($request->hasFile('zip_file')) {
                // Delete old ZIP file
                if ($product->zip_file_path && Storage::disk('public')->exists($product->zip_file_path)) {
                    Storage::disk('public')->delete($product->zip_file_path);
                }

                $zipFile = $request->file('zip_file');
                $updateData['zip_file_name'] = $zipFile->getClientOriginalName();
                $updateData['zip_file_size'] = $zipFile->getSize();
                $updateData['zip_file_path'] = $zipFile->store('products/files/zip', 'public');
                $fileWasUpdated = true;
            }

            // Allow removal of ZIP file via checkbox
            if ($request->has('remove_zip') && $request->remove_zip) {
                if ($product->zip_file_path && Storage::disk('public')->exists($product->zip_file_path)) {
                    Storage::disk('public')->delete($product->zip_file_path);
                }
                $updateData['zip_file_path'] = null;
                $updateData['zip_file_name'] = null;
                $updateData['zip_file_size'] = null;
            }

            // Add editor tracking
            $updateData['updated_by_user_id'] = auth()->id();

            $product->update($updateData);

            // If a new file was uploaded, expire old downloads and create new ones for all users with completed orders
            if ($fileWasUpdated) {
                $activeDownloads = Download::where('product_id', $product->id)
                    ->where('expires_at', '>', now())
                    ->whereColumn('download_count', '<', 'max_downloads')
                    ->get();

                foreach ($activeDownloads as $oldDownload) {
                    // Expire the old download
                    $oldDownload->expires_at = now();
                    $oldDownload->save();

                    // Create or update a new download record for the same user/order
                    Download::updateOrCreate(
                        [
                            'user_id'    => $oldDownload->user_id,
                            'product_id' => $product->id,
                            'order_id'   => $oldDownload->order_id,
                        ],
                        [
                            'token'          => Str::random(40),
                            'expires_at'     => now()->addDays(7), // adjust as needed
                            'download_count' => 0,
                            'max_downloads'  => $oldDownload->max_downloads,
                        ]
                    );
                }
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully. Active download links have been regenerated for previous buyers if the file was changed.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error updating product: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        try {
            // Delete associated files
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            if ($product->file_path && Storage::disk('public')->exists($product->file_path)) {
                Storage::disk('public')->delete($product->file_path);
            }
            if ($product->pdf_file_path && Storage::disk('public')->exists($product->pdf_file_path)) {
                Storage::disk('public')->delete($product->pdf_file_path);
            }
            if ($product->zip_file_path && Storage::disk('public')->exists($product->zip_file_path)) {
                Storage::disk('public')->delete($product->zip_file_path);
            }

            $product->delete();

            return redirect()->route('admin.products.index')
                ->with('success', 'Product deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting product: ' . $e->getMessage());
        }
    }

    /**
     * Toggle product status.
     */
    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);

        $status = $product->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Product {$status} successfully.");
    }

    /**
     * Reset download limits for all users who have maxed out downloads for this product.
     */
    public function resetDownloadLimits(Product $product)
    {
        $downloads = Download::where('product_id', $product->id)
            ->whereColumn('download_count', '>=', 'max_downloads')
            ->get();

        $resetCount = 0;

        foreach ($downloads as $download) {
            $download->download_count = 0;
            $download->token = Str::random(40);
            $download->expires_at = now()->addDays(7); // or your preferred duration
            $download->save();
            $resetCount++;
        }

        return redirect()->back()->with('success', "$resetCount download limits have been reset for this product.");
    }

    /**
     * Reset download limits for a specific user who has maxed out downloads for this product.
     */
    public function resetUserDownloadLimit(Product $product, User $user)
    {
        $downloads = Download::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->whereColumn('download_count', '>=', 'max_downloads')
            ->get();

        $resetCount = 0;

        foreach ($downloads as $download) {
            $download->download_count = 0;
            $download->token = Str::random(40);
            $download->expires_at = now()->addDays(7);
            $download->save();
            $resetCount++;
        }

        return back()->with('success', "$resetCount download limits have been reset for {$user->name}.");
    }
}