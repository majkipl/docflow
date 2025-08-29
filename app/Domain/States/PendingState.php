<?php

namespace App\Domain\States;

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
     * @return void
     */
    public function approve(): void
    {
        $this->transitionTo('approved');
    }

    /**
     * @return void
     */
    public function reject(): void
    {
        $this->transitionTo('rejected');
    }
}
