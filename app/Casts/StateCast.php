<?php

namespace App\Casts;

use App\Domain\States\ApprovedState;
use App\Domain\States\DocumentState;
use App\Domain\States\DraftState;
use App\Domain\States\PendingState;
use App\Domain\States\RejectedState;
use App\Models\Document;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class StateCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): DocumentState
    {
        // Type guard - zapewniamy statyczną poprawność typów
        if (! $model instanceof Document) {
            throw new InvalidArgumentException('StateCast can only be used with the Document model.');
        }

        return match ($value) {
            'draft' => new DraftState($model),
            'pending' => new PendingState($model),
            'approved' => new ApprovedState($model),
            'rejected' => new RejectedState($model),
            default => throw new InvalidArgumentException("Nieznany status: $value"),
        };
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return $value instanceof DocumentState ? $value->getName() : $value;
    }
}
