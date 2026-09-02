<?php

namespace App\Domain\States;

use App\Domain\Enums\DocumentStatus;
use App\Events\DocumentStateChanged;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

abstract class DocumentState
{
    /**
     * @param Document $document
     */
    public function __construct(protected Document $document)
    {
    }

    /**
     * @return string
     */
    abstract public function getName(): string;

    // Przejścia (BPMN Workflow) - domyślnie zablokowane

    /**
     * @param User|null $actor
     * @return void
     */
    public function submit(?User $actor = null): void
    {
        throw new InvalidArgumentException("Nie można przesłać dokumentu ze statusu: {$this->getName()}");
    }

    /**
     * @param User|null $actor
     * @return void
     */
    public function approve(?User $actor = null): void
    {
        throw new InvalidArgumentException("Nie można zaakceptować dokumentu ze statusu: {$this->getName()}");
    }

    /**
     * @param User|null $actor
     * @return void
     */
    public function reject(?User $actor = null): void
    {
        throw new InvalidArgumentException("Nie można odrzucić dokumentu ze statusu: {$this->getName()}");
    }

    /**
     * @param DocumentStatus|string $newState
     * @param User|null $actor
     * @return void
     */
    protected function transitionTo(DocumentStatus|string $newState, ?User $actor = null): void
    {
        DB::transaction(function () use ($newState, $actor) {
            /** @var DocumentState $state */
            $state = $this->document->state;
            $oldState = $state->getName();

            $newStateValue = $newState instanceof DocumentStatus ? $newState->value : $newState;

            $this->document->setAttribute('state', $newStateValue);
            $this->document->save();

            $eventUserId = $actor?->id;

            event(new DocumentStateChanged($this->document, $oldState, $newStateValue, $eventUserId));
        });
    }
}
