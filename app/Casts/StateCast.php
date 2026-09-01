<?php

namespace App\Casts;

use App\Domain\Enums\DocumentStatus;
use App\Domain\States\ApprovedState;
use App\Domain\States\DocumentState;
use App\Domain\States\DraftState;
use App\Domain\States\PendingState;
use App\Domain\States\RejectedState;
use App\Models\Document;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @implements CastsAttributes<DocumentState, DocumentState|DocumentStatus|string>
 */
class StateCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param array<string, mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): DocumentState
    {
        // Type guard - zapewniamy statyczną poprawność typów
        if (!$model instanceof Document) {
            throw new InvalidArgumentException('StateCast can only be used with the Document model.');
        }

        $status = is_string($value) ? DocumentStatus::tryFrom($value) : null;

        return match ($status) {
            DocumentStatus::DRAFT => new DraftState($model),
            DocumentStatus::PENDING => new PendingState($model),
            DocumentStatus::APPROVED => new ApprovedState($model),
            DocumentStatus::REJECTED => new RejectedState($model),
            default => throw new InvalidArgumentException("Nieznany status: " . (is_scalar($value) ? (string) $value : gettype($value))),
        };
    }

    /**
     * Prepare the given value for storage.
     *
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value instanceof DocumentState) {
            return $value->getName();
        }

        if ($value instanceof DocumentStatus) {
            return $value->value;
        }

        if (is_string($value)) {
            $status = DocumentStatus::tryFrom($value);
            if ($status !== null) {
                return $status->value;
            }
            throw new InvalidArgumentException("Nieznany status: $value");
        }

        throw new InvalidArgumentException('State value must be a string, DocumentStatus or instance of DocumentState.');
    }
}
