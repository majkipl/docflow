<?php

namespace App\Domain\States;

use App\Models\User;

class PendingState extends DocumentState
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'pending';
    }

    /**
     * @param User|null $actor
     * @return void
     */
    public function approve(?User $actor = null): void
    {
        $this->transitionTo('approved', $actor);
    }

    /**
     * @param User|null $actor
     * @return void
     */
    public function reject(?User $actor = null): void
    {
        $this->transitionTo('rejected', $actor);
    }
}
