<?php

namespace App\Domain\States;

use App\Events\DocumentStateChanged;
use App\Models\Document;
use App\Models\User;
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
     * @param string $newState
     * @param User|null $actor
     * @return void
     */
    protected function transitionTo(string $newState, ?User $actor = null): void
    {
        /** @var DocumentState $state */
        $state = $this->document->state;
        $oldState = $state->getName();

        $this->document->setAttribute('state', $newState);
        $this->document->save();

        $eventUserId = $actor?->id;

        event(new DocumentStateChanged($this->document, $oldState, $newState, $eventUserId));
    }
}
