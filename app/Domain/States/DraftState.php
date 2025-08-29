<?php

namespace App\Domain\States;

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
     * @return void
     */
    public function submit(): void
    {
        $this->transitionTo('pending');
    }
}
