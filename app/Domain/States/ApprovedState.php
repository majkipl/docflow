<?php

namespace App\Domain\States;

use App\Domain\Enums\DocumentStatus;

class ApprovedState extends DocumentState
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return DocumentStatus::APPROVED->value;
    }
}
