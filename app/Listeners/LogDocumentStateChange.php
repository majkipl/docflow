<?php

namespace App\Listeners;

use App\Events\DocumentStateChanged;
use App\Models\DocumentAuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogDocumentStateChange
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param DocumentStateChanged $event
     * @return void
     */
    public function handle(DocumentStateChanged $event): void
    {
        DocumentAuditLog::create([
            'document_id' => $event->document->id,
            'user_id' => $event->userId,
            'old_state' => $event->oldState,
            'new_state' => $event->newState,
        ]);
    }
}
