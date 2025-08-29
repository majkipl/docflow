<?php

namespace App\Domain\States;

class RejectedState extends DocumentState
{

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'rejected';
    }
}
