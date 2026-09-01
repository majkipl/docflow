<?php

namespace App\Domain\States;

use App\Domain\Enums\DocumentStatus;

class RejectedState extends DocumentState
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return DocumentStatus::REJECTED->value;
    }
}
