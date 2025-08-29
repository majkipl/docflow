<?php

namespace App\Domain\States;

use App\Events\DocumentStateChanged;
use App\Models\Document;
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
     * @return void
     */
    public function submit(): void
    {
        throw new InvalidArgumentException("Nie można przesłać dokumentu ze statusu: {$this->getName()}");
    }

    /**
     * @return void
     */
    public function approve(): void
    {
        throw new InvalidArgumentException("Nie można zaakceptować dokumentu ze statusu: {$this->getName()}");
    }

    /**
     * @return void
     */
    public function reject(): void
    {
        throw new InvalidArgumentException("Nie można odrzucić dokumentu ze statusu: {$this->getName()}");
    }

    /**
     * @param string $newState
     * @return void
     */
    protected function transitionTo(string $newState): void
    {
        $oldState = $this->document->state->getName();
        $this->document->setAttribute('state', $newState);
        $this->document->save();

        // Wypuszczenie eventu dla Audit Loga (Zrealizowane w kroku 17)
        event(new DocumentStateChanged($this->document, $oldState, $newState, auth()->id()));
    }
}
