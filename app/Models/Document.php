<?php

namespace App\Models;

use App\Casts\StateCast;
use App\Domain\States\DocumentState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property DocumentState $state
 */
class Document extends Model
{
    protected $fillable = ['user_id', 'title', 'file_path', 'state'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'state' => StateCast::class,
        ];
    }
}
