<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Mail\ProductDownloadLinkMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendOrderThankYouEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param  OrderCompleted  $event
     * @return void
     */
    public function handle(OrderCompleted $event)
    {
        // Log that the listener is fired
        Log::info('SendOrderThankYouEmail listener fired', [
            'order_id' => $event->order->id
        ]);

        $order = $event->order;

        if ($order->thank_you_sent) {
            Log::info("Thank-you email already sent for order: {$order->id}");
            return;
        }

        $user = $order->user;
        $product = $order->product;

        // Get the download token - this should be the token from Download model
        $download = $order->downloads()->first();
        $token = $download ? $download->token : ($order->download_token ?? '');

        // Build download URL based on available formats
        $hasEpub = !empty($product->file_path);
        $hasPdf = $product->hasPdf();

        // If multiple formats available, show format selection page
        if ($hasEpub && $hasPdf) {
            $downloadUrl = route('downloads.select-format', $token);
        } else {
            // Single format - use the direct download or generic download route
            $downloadUrl = route('downloads.file', $token);
        }

        try {
            Mail::to($user->email)->send(new ProductDownloadLinkMail(
                $user,
                $product,
                $downloadUrl,
                $downloadUrl  // Pass format selection URL as well
            ));
            $order->update(['thank_you_sent' => true]);
            Log::info("Thank-you email successfully sent for order: {$order->id}");
        } catch (\Exception $e) {
            Log::error('Failed to send thank-you email', [
                'order_id' => $order->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
