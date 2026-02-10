<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Affiliate;

class AffiliateWeeklyPaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $affiliate;
    public $paymentData;

    /**
     * Create a new message instance.
     */
    public function __construct(Affiliate $affiliate, array $paymentData)
    {
        $this->affiliate = $affiliate;
        $this->paymentData = $paymentData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->affiliate->user->email],
            subject: 'Your Weekly Affiliate Payment - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.affiliate-weekly-payment',
            with: [
                'affiliate' => $this->affiliate,
                'paymentData' => $this->paymentData,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments(): array
    {
        return [];
    }
}
