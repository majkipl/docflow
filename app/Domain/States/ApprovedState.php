<?php

namespace App\Domain\States;

class ApprovedState extends DocumentState
{

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'approved';
    }
}
