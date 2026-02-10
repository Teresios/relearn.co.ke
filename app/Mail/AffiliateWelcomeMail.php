<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AffiliateWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $fullName;
    public $setupLink;

    /**
     * Create a new message instance.
     */
    public function __construct($email, $fullName, $setupLink)
    {
        $this->email = $email;
        $this->fullName = $fullName;
        $this->setupLink = $setupLink;
    }

    /**
     * Get the message envelope.
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Welcome to Relearn Affiliate Program! Set Up Your Login',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content()
    {
        return new Content(
            markdown: 'emails.affiliate.welcome',
            with: [
                'email' => $this->email,
                'fullName' => $this->fullName,
                'setupLink' => $this->setupLink,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments()
    {
        return [];
    }
}
