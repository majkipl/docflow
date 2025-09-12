<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param Document $document
     * @param string $oldState
     * @param string $newState
     */
    public function __construct(
        public Document $document,
        public string $oldState,
        public string $newState
    ) {}

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Zmiana statusu dokumentu: {$this->document->title}",
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content(): Content
    {
        // W realnej aplikacji użylibyśmy widoku blade, tutaj dla uproszczenia zwracamy prosty tekst (Markdown)
        return new Content(
            markdown: 'mail.document.status-changed',
            with: [
                'title' => $this->document->title,
                'oldState' => $this->oldState,
                'newState' => $this->newState,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
