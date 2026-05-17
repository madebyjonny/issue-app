<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageThread extends Model
{
    protected $fillable = ['message_id', 'reply_count', 'last_reply_at'];

    protected $casts = ['last_reply_at' => 'datetime'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
