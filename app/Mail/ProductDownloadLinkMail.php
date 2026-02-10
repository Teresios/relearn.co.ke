<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProductDownloadLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $product;
    public $epubDownloadUrl;
    public $pdfDownloadUrl;
    public $zipDownloadUrl;
    public $hasEpub;
    public $hasPdf;
    public $hasZip;
    public $epubFileSize;
    public $pdfFileSize;
    public $zipFileSize;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $product, $epubDownloadUrl = null, $pdfDownloadUrl = null, $zipDownloadUrl = null)
    {
        $this->user = $user;
        $this->product = $product;
        $this->epubDownloadUrl = $epubDownloadUrl;
        $this->pdfDownloadUrl = $pdfDownloadUrl;
        $this->zipDownloadUrl = $zipDownloadUrl;
        $this->hasEpub = !empty($product->file_path) && !empty($epubDownloadUrl);
        $this->hasPdf = $product->hasPdf() && !empty($pdfDownloadUrl);
        $this->hasZip = $product->hasZip() && !empty($zipDownloadUrl);
        $this->epubFileSize = $this->hasEpub ? $product->formatted_file_size : null;
        $this->pdfFileSize = $this->hasPdf ? $product->formatted_pdf_file_size : null;
        $this->zipFileSize = $this->hasZip ? $product->formatted_zip_file_size : null;

        // Log mailable creation
        Log::info('ProductDownloadLinkMail mailable initialized', [
            'user_id' => $user->id ?? 'guest',
            'email' => $user->email,
            'product_id' => $product->id,
            'has_epub' => $this->hasEpub,
            'has_pdf' => $this->hasPdf,
            'has_zip' => $this->hasZip
        ]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info('Building ProductDownloadLinkMail email', [
            'user_id' => $this->user->id ?? 'guest',
            'email' => $this->user->email,
            'has_epub' => $this->hasEpub,
            'has_pdf' => $this->hasPdf,
            'has_zip' => $this->hasZip
        ]);

        return $this->subject('Your Download Link is Ready')
                    ->markdown('emails.product-download-link')
                    ->with([
                        'user' => $this->user,
                        'product' => $this->product,
                        'epubDownloadUrl' => $this->epubDownloadUrl,
                        'pdfDownloadUrl' => $this->pdfDownloadUrl,
                        'zipDownloadUrl' => $this->zipDownloadUrl,
                        'hasEpub' => $this->hasEpub,
                        'hasPdf' => $this->hasPdf,
                        'hasZip' => $this->hasZip,
                        'epubFileSize' => $this->epubFileSize,
                        'pdfFileSize' => $this->pdfFileSize,
                        'zipFileSize' => $this->zipFileSize
                    ]);
    }
}
