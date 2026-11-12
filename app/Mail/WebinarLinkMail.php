<?php

namespace App\Mail;

use App\Models\Webinar;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WebinarLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Webinar $webinar,
        public string $subjectLine,
        public string $body,
        public string $meetingLink,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.webinar-link',
            text: 'emails.webinar-link-text',
        );
    }
}
