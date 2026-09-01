<?php

namespace App\Domain\States;

use App\Domain\Enums\DocumentStatus;
use App\Models\User;

class DraftState extends DocumentState
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return DocumentStatus::DRAFT->value;
    }

    /**
     * @param User|null $actor
     * @return void
     */
    public function submit(?User $actor = null): void
    {
        $this->transitionTo(DocumentStatus::PENDING, $actor);
    }
}
