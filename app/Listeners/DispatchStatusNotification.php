<?php

namespace App\Listeners;

use App\Events\DocumentStateChanged;
use App\Jobs\SendDocumentStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DispatchStatusNotification
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
        // Wrzucamy Job do kolejki. Dzięki konfiguracji w .env (QUEUE_CONNECTION=redis),
        // zadanie trafi do pamięci podręcznej i zostanie obsłużone przez workera.
        SendDocumentStatusNotification::dispatch(
            $event->document,
            $event->oldState,
            $event->newState
        );
    }
}
