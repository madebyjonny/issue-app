<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirectMessage extends Model
{
    use SoftDeletes;

    protected $fillable = ['conversation_id', 'user_id', 'body', 'read_at'];

    protected $casts = [
        'body'    => 'array',
        'read_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(DirectConversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
