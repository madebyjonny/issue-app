<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class HuddleSession extends Model
{
    protected $fillable = ['project_id', 'room_id', 'initiated_by', 'participants', 'is_active'];

    protected $casts = [
        'participants' => 'array',
        'is_active'    => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (HuddleSession $session) {
            if (empty($session->room_id)) {
                $session->room_id = Str::uuid();
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }
}
