<?php

namespace App\Models;

use App\Casts\StateCast;
use App\Domain\States\DocumentState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $file_path
 * @property DocumentState $state
 * @property User $user
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
     * @return HasMany<DocumentAuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(DocumentAuditLog::class);
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
