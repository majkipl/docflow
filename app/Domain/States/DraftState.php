<?php

namespace App\Domain\States;

use App\Models\User;

class DraftState extends DocumentState
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'draft';
    }

    /**
     * @param User|null $actor
     * @return void
     */
    public function submit(?User $actor = null): void
    {
        $this->transitionTo('pending', $actor);
    }
}
