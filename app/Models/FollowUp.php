<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FollowUp extends Model
{
    protected $fillable = [
        'followable_id',
        'followable_type',
        'user_id',
        'type',
        'content',
    ];

    const TYPE_NOTE = 'note';
    const TYPE_RESPONSE = 'response';
    const TYPE_STATUS_CHANGE = 'status_change';

    public static function getTypes(): array
    {
        return [
            self::TYPE_NOTE => 'Note',
            self::TYPE_RESPONSE => 'Response',
            self::TYPE_STATUS_CHANGE => 'Status Change',
        ];
    }

    public function followable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
