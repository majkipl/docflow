<?php

namespace App\Domain\States;

use App\Domain\Enums\DocumentStatus;
use App\Models\User;

class PendingState extends DocumentState
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return DocumentStatus::PENDING->value;
    }

    /**
     * @param User|null $actor
     * @return void
     */
    public function approve(?User $actor = null): void
    {
        $this->transitionTo(DocumentStatus::APPROVED, $actor);
    }

    /**
     * @param User|null $actor
     * @return void
     */
    public function reject(?User $actor = null): void
    {
        $this->transitionTo(DocumentStatus::REJECTED, $actor);
    }
}
