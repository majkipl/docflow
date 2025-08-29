<?php

namespace App\Models;

use App\Casts\StateCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = ['user_id', 'title', 'file_path', 'state'];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \class-string[]
     */
    protected function casts(): array
    {
        return [
            'state' => StateCast::class,
        ];
    }
}
