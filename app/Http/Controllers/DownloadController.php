<?php

namespace App\Http\Controllers;

use App\Models\Download;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;

class DownloadController extends Controller
{
    public function __construct()
    {
        // Auth middleware only for index - downloads.serve-file is public with token verification
        $this->middleware('auth')->only('index');
    }

    /**
     * Display user's downloads.
     */
    public function index()
    {
        $downloads = Auth::user()->downloads()
            ->with(['order.product'])
            ->latest()
            ->paginate(12);

        return view('downloads.index', compact('downloads'));
    }

    /**
     * Handle file download with token verification - direct to selected format.
     */
    public function download($token)
    {
        // Redirect to format selection or direct download
        return $this->showFormatSelection($token);
    }

    /**
     * Show download format selection page if both formats available.
     */
    public function showFormatSelection($token)
    {
        $download = Download::where('token', $token)->first();

        if (!$download) {
            abort(404, 'Download link not found.');
        }

        // Allow access if: (1) it's a guest download (user_id is null), or (2) user is the owner
        if ($download->user_id !== null && $download->user_id != Auth::id()) {
            abort(403, 'You are not authorized to access this download.');
        }

        $product = $download->product;

        // Check if multiple formats available
        $hasEpub = !empty($product->file_path);
        $hasPdf = $product->hasPdf();
        $hasZip = $product->hasZip();

        // If only one format available, redirect directly to download
        if (!$hasEpub && !$hasPdf && !$hasZip) {
            return redirect()->route('downloads.index')
                ->with('error', 'No file available for download.');
        }

        if (!$hasEpub && !$hasZip && $hasPdf) {
            // Only PDF available, download directly
            return $this->serveFile($token, 'pdf');
        }

        if (!$hasPdf && !$hasZip && $hasEpub) {
            // Only ePub available, download directly
            return $this->serveFile($token, 'epub');
        }

        if (!$hasEpub && !$hasPdf && $hasZip) {
            // Only ZIP available, download directly
            return $this->serveFile($token, 'zip');
        }

        // Multiple formats available - show selection
        return view('downloads.select-format', compact('download', 'product', 'hasEpub', 'hasPdf', 'hasZip'));
    }

    /**
     * Download file in specified format.
     */
    public function serveFile($token, $format = 'epub')
    {
        \Log::info('=== FILE SERVE ATTEMPT ===', [
            'token' => $token,
            'format' => $format,
            'user_id' => Auth::id()
        ]);

        $download = Download::where('token', $token)->first();

        if (!$download) {
            \Log::error('Download not found', ['token' => $token]);
            abort(404, 'Download link not found.');
        }

        // Allow access if: (1) it's a guest download (user_id is null), or (2) user is the owner
        if ($download->user_id !== null && $download->user_id != Auth::id()) {
            \Log::error('Authorization failed', [
                'download_user_id' => $download->user_id,
                'current_user_id' => Auth::id()
            ]);
            abort(403, 'You are not authorized to download this file.');
        }

        if (!$download->isValid()) {
            if ($download->isExpired()) {
                return redirect()->route('downloads.index')
                    ->with('error', 'Download link has expired.');
            }
            
            if ($download->maxDownloadsReached()) {
                return redirect()->route('downloads.index')
                    ->with('error', 'Maximum download limit reached.');
            }
        }

        $product = $download->product;

        // Determine file path based on format
        if ($format === 'pdf') {
            if (!$product->hasPdf()) {
                return redirect()->route('downloads.index')
                    ->with('error', 'PDF version not available for this product.');
            }
            $filePath = $product->pdf_file_path;
            $filename = $product->pdf_file_name;
        } elseif ($format === 'zip') {
            if (!$product->hasZip()) {
                return redirect()->route('downloads.index')
                    ->with('error', 'ZIP file not available for this product.');
            }
            $filePath = $product->zip_file_path;
            $filename = $product->zip_file_name;
        } else {
            // Default to ePub
            if (empty($product->file_path)) {
                return redirect()->route('downloads.index')
                    ->with('error', 'ePub version not available for this product.');
            }
            $filePath = $product->file_path;
            $filename = $product->file_name;
        }

        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            \Log::error('Storage file not found', [
                'filePath' => $filePath,
                'format' => $format,
                'exists' => $filePath ? Storage::disk('public')->exists($filePath) : false
            ]);
            return redirect()->route('downloads.index')
                ->with('error', 'File not found. Please contact support.');
        }

        // Increment download count
        $download->incrementDownloadCount();

        // Get file info - check if file actually has content
        $filesize = Storage::disk('public')->size($filePath);
        $fileContent = Storage::disk('public')->get($filePath);
        $firstBytes = substr($fileContent, 0, 10);
        
        // Get file extension and set appropriate MIME type
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $mimeType = $this->getMimeType($extension);

        \Log::info('Serving file', [
            'filePath' => $filePath,
            'filename' => $filename,
            'mimeType' => $mimeType,
            'filesize' => $filesize,
            'extension' => $extension,
            'first_10_bytes' => bin2hex($firstBytes),
            'is_html' => stripos($fileContent, '<html') !== false || stripos($fileContent, '<!DOCTYPE') !== false
        ]);

        // Get the full file path
        $fullPath = Storage::disk('public')->path($filePath);
        
        // Verify the file actually exists on disk
        if (!file_exists($fullPath)) {
            \Log::error('File not found on disk', [
                'filePath' => $filePath,
                'fullPath' => $fullPath
            ]);
            return redirect()->route('downloads.index')
                ->with('error', 'File not found on server. Please contact support.');
        }

        // Check if file is actually HTML (corrupted upload)
        if (stripos($fileContent, '<html') !== false || stripos($fileContent, '<!DOCTYPE') !== false) {
            \Log::error('File appears to be HTML content', [
                'filePath' => $filePath,
                'filename' => $filename,
                'content_preview' => substr($fileContent, 0, 200)
            ]);
            return redirect()->route('downloads.index')
                ->with('error', 'File is corrupted. Please contact support to re-upload.');
        }

        // Use response()->download() with proper headers
        return response()->download(
            $fullPath,
            $filename,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => $filesize,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'Content-Transfer-Encoding' => 'binary',
                'Accept-Ranges' => 'bytes',
            ]
        );
    }

    /**
     * Get MIME type based on file extension.
     */
    private function getMimeType($extension)
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'epub' => 'application/epub+zip',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'txt' => 'text/plain',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
        ];

        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }

    /**
     * Generate a new download link for an order.
     */
    public function generateLink(Request $request)
    {
        $orderId = $request->input('order_id');
        $order = Auth::user()->orders()
            ->where('id', $orderId)
            ->where('status', 'completed')
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found or not completed.'], 404);
        }

        // Check if download already exists
        $existingDownload = Download::where('user_id', Auth::id())
            ->where('product_id', $order->product_id)
            ->where('order_id', $order->id)
            ->first();

        if ($existingDownload && $existingDownload->isValid()) {
            return response()->json([
                'download_url' => route('downloads.file', $existingDownload->token),
                'expires_at' => $existingDownload->expires_at->format('Y-m-d H:i:s'),
                'downloads_remaining' => $existingDownload->max_downloads - $existingDownload->download_count
            ]);
        }

        // Create or update download record
        $download = Download::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'product_id' => $order->product_id,
                'order_id' => $order->id,
            ],
            [
                'token' => Str::random(40),
                'expires_at' => now()->addDays(7),
                'download_count' => 0,
                'max_downloads' => 5, // or your preferred default
            ]
        );

        return response()->json([
            'download_url' => route('downloads.file', $download->token),
            'expires_at' => $download->expires_at->format('Y-m-d H:i:s'),
            'downloads_remaining' => $download->max_downloads - $download->download_count
        ]);
    }
}