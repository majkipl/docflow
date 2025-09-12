<?php

namespace App\Jobs;

use App\Mail\DocumentStatusChangedMail;
use App\Models\Document;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDocumentStatusNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param Document $document
     * @param string $oldState
     * @param string $newState
     */
    public function __construct(
        public Document $document,
        public string   $oldState,
        public string   $newState
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Wysłanie maila do autora dokumentu.
        // W pełnej wersji biznesowej powiadomienia mogłyby trafiać też do managerów (gdy status = pending).
        if ($this->document->user) {
            Mail::to($this->document->user->email)->send(
                new DocumentStatusChangedMail($this->document, $this->oldState, $this->newState)
            );
        }
    }
}
