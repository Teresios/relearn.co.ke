@component('mail::message')
# Thank you, {{ $user->name ?? 'Valued Customer' }}!

We appreciate your purchase of **{{ $product->name }}**.

@if($hasZip && !$hasEpub && !$hasPdf)
## Download Your Bundle

Your bundle is available for download in the following format:
@else
## Download Your Book

Your book is available for download in the following formats:
@endif

@if($hasEpub)
@component('mail::button', ['url' => $epubDownloadUrl, 'color' => 'primary'])
📖 Download ePub Version ({{ $epubFileSize }})
@endcomponent
@endif

@if($hasPdf)
@component('mail::button', ['url' => $pdfDownloadUrl, 'color' => 'success'])
📄 Download PDF Version ({{ $pdfFileSize }})
@endcomponent
@endif

@if($hasZip)
@component('mail::button', ['url' => $zipDownloadUrl, 'color' => 'warning'])
📦 Download ZIP File ({{ $zipFileSize }})
@endcomponent
@endif

@if(!$hasEpub && !$hasPdf && !$hasZip)
We apologize, but no download file is currently available for this product. Please contact support.
@endif

@if($user->id ?? false)
---

**Download Details:**

- Product: {{ $product->name }}
- Maximum downloads: 3 times per link
- Link valid for: 7 days
- After expiration, you can generate a new link from your account
@endif

If you have any issues or questions, please contact our support team.

Thanks again,

{{ config('app.name') }} Team

Contact Support: 0755772424
@endcomponent