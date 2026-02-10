# PDF Upload and Multi-Format Download Feature - Implementation Guide

## Overview

This feature enhancement adds support for PDF book uploads and allows customers to download books in multiple formats (ePub and PDF). After payment, users receive download links via email and can choose their preferred format.

## Features Implemented

### 1. **Database Schema Updates**
- **Migration 1**: `2026_01_17_000001_add_pdf_fields_to_products_table.php`
  - Adds `pdf_file_path` column to store PDF file location
  - Adds `pdf_file_name` column to store original PDF filename
  - Adds `pdf_file_size` column to store PDF file size in bytes

- **Migration 2**: `2026_01_17_000002_add_file_format_to_downloads_table.php`
  - Adds `file_format` enum column (values: 'epub' or 'pdf')
  - Tracks which format was downloaded for each download record

### 2. **Product Model Enhancements**
**File**: `app/Models/Product.php`

**New Fillable Fields**:
```php
'pdf_file_path',
'pdf_file_name',
'pdf_file_size',
```

**New Methods**:
- `hasPdf()` - Returns boolean indicating if PDF version exists
- `getFormattedPdfFileSizeAttribute()` - Returns formatted PDF file size (e.g., "2.5 MB")

### 3. **Admin Product Controller Updates**
**File**: `app/Http/Controllers/Admin/ProductController.php`

#### store() Method Changes:
- Now accepts optional `pdf_file` input
- Validates PDF with mime type `pdf` and max 50MB
- Stores PDF in `products/files/pdf` directory
- Saves `pdf_file_path`, `pdf_file_name`, and `pdf_file_size` to database

#### update() Method Changes:
- Accepts optional `pdf_file` input for updating PDF
- Allows removal of PDF via `remove_pdf` checkbox
- Deletes old PDF if new one is uploaded
- Regenerates download links when file is changed

### 4. **Download Controller Enhancements**
**File**: `app/Http/Controllers/DownloadController.php`

**New Methods**:

#### `showFormatSelection($token)`
- Displays format selection page when both ePub and PDF are available
- Automatically redirects to download if only one format exists
- Shows file sizes and format information

#### `serveFile($token, $format = 'epub')`
- Handles file serving for specified format
- Validates download token and user authorization
- Updates download record with format selected
- Increments download count
- Returns file with proper MIME type

### 5. **Email Enhancement**
**File**: `app/Mail/ProductDownloadLinkMail.php`

**New Parameters**:
- `formatSelectionUrl` - URL to format selection page
- `hasEpub` - Boolean flag indicating ePub availability
- `hasPdf` - Boolean flag indicating PDF availability

**Behavior**:
- If both formats available: Shows format selection button
- If only one format: Shows direct download button for that format
- Email includes download details and link expiration info

### 6. **Email Template Update**
**File**: `resources/views/emails/product-download-link.blade.php`

Features:
- Conditional display based on available formats
- Shows format-specific download buttons
- Includes helpful information about download limits and expiration

### 7. **Order Completion Handler Update**
**File**: `app/Listeners/SendOrderThankYouEmail.php`

Changes:
- Retrieves download token from Download model
- Detects available formats from product
- Routes to format selection page if both formats available
- Passes appropriate URL and format information to email

### 8. **Routes Update**
**File**: `routes/web.php`

New routes in `downloads` prefix:
```php
GET  /downloads/select/{token}        → showFormatSelection()
GET  /downloads/serve/{token}/{format} → serveFile()
```

Existing routes remain for backward compatibility.

### 9. **Download Format Selection View**
**File**: `resources/views/downloads/select-format.blade.php`

Features:
- Clean card-based UI showing both format options
- Displays file sizes for each format
- Shows download count and expiration information
- Hover effects for better UX
- Direct download links to serve-file route

### 10. **Downloads Index View Update**
**File**: `resources/views/downloads/index.blade.php`

Changes:
- Detects available formats for each download
- Shows format selection link when both available
- Shows format-specific download button when only one available
- Maintains existing UI and download tracking

### 11. **Admin Product Forms Update**

#### Create Form (`resources/views/admin/products/create.blade.php`):
- ePub upload field labeled "Product File (ePub)" - Required
- PDF upload field labeled "Product File (PDF)" - Optional
- Separate badges indicating requirement status

#### Edit Form (`resources/views/admin/products/edit.blade.php`):
- Shows current ePub file with name and size
- Shows current PDF file with name and size
- Checkbox to remove current PDF
- Allows updating either or both files
- Clear indication of current file status

#### Show View (`resources/views/admin/products/show.blade.php`):
- Three-column file status display
- Shows image, ePub, and PDF status
- Displays file sizes and names for uploaded files
- Uses Bootstrap icons for visual clarity

### 12. **Download Model Update**
**File**: `app/Models/Download.php`

Updates:
- Added `file_format` to fillable array
- Model now tracks which format was downloaded

## Workflow

### For Admins - Uploading Books:

1. Navigate to Products > Create or Edit
2. Upload ePub version (required)
3. Optionally upload PDF version
4. Save product
5. Admin dashboard shows availability of both formats

### For Customers - Downloading:

1. Customer purchases a book
2. Receives email with:
   - Format selection button (if both available)
   - Direct download button (if one format only)
   - Download details and expiration info
3. Clicks download link
4. If multiple formats:
   - Sees format selection page with size info
   - Chooses ePub or PDF
5. File downloads with proper headers
6. Download count incremented
7. Download history shows in My Digital Library

## Database Migrations

To apply these changes:

```bash
php artisan migrate
```

This will:
1. Add PDF columns to products table
2. Add file_format column to downloads table

## File Storage

Files are organized as:
```
storage/app/public/
├── products/
│   ├── files/
│   │   ├── [epub-files]
│   │   └── pdf/
│   │       └── [pdf-files]
│   └── images/
│       └── [product-images]
```

## Download Limits

Each download record maintains:
- `download_count` - Current downloads
- `max_downloads` - Maximum allowed (default: 3)
- `expires_at` - Link expiration timestamp (default: 7 days)
- `file_format` - Format selected (epub or pdf)

## Email Example

When both formats are available, the email will show:
- Product name and thank you message
- "Choose Download Format" button
- Description of available formats:
  - **ePub** - Recommended for e-readers and mobile devices
  - **PDF** - For printing and viewing on any device
- Download details (limits, expiration)

## API/Route Responses

### Format Selection Page
```
GET /downloads/select/{token}
Returns: HTML view with format options
```

### File Download (ePub)
```
GET /downloads/serve/{token}/epub
Returns: ePub file or redirect based on validation
```

### File Download (PDF)
```
GET /downloads/serve/{token}/pdf
Returns: PDF file or redirect based on validation
```

## Error Handling

- Invalid token → 404 with "Download link not found"
- Unauthorized user → 403 with "Not authorized"
- Expired link → Redirect to downloads.index with error message
- Max downloads reached → Redirect with error message
- File not available → Redirect with error message
- Format not available → Redirect with error message

## Backward Compatibility

- Existing ePub-only products work without changes
- Customers can still download ePub format normally
- No breaking changes to existing download functionality
- All existing download links remain valid

## Configuration

No additional configuration needed. The feature works with default Laravel setup. Adjust download limits and expiration in:

```php
// In Download model boot() method
$download->max_downloads = 3; // Default max downloads
$download->expires_at = now()->addDays(7); // Default 7 days expiry
```

## Testing Checklist

- [ ] Upload product with ePub only - download works
- [ ] Upload product with PDF only - download works
- [ ] Upload product with both ePub and PDF - format selection shown
- [ ] Download link expires after 7 days
- [ ] Download count increments correctly
- [ ] Max 3 downloads limit enforced
- [ ] Email sent with correct download links
- [ ] Format selection page displays both options
- [ ] PDF and ePub files download correctly
- [ ] Admin can edit products and add/remove PDFs
- [ ] Admin product show view displays all file info
- [ ] User can generate new download links after expiration

## Troubleshooting

### PDF not appearing in downloads
- Verify migration ran: `php artisan migrate`
- Check PDF file size < 50MB
- Ensure PDF MIME type is correct

### Download link errors
- Clear application cache: `php artisan cache:clear`
- Check user authentication
- Verify download token exists and is valid

### Email not sending with correct links
- Check ProductDownloadLinkMail is being called
- Verify mail configuration
- Check SendOrderThankYouEmail listener is registered

### File format detection issues
- Clear browser cache
- Check correct route names are being used
- Verify database file_format column exists

## Future Enhancements

- Support for other formats (MOBI, AZW3)
- Batch PDF generation from ePub
- Format preference storage in user profile
- Download history filtering by format
- Analytics on format preferences
