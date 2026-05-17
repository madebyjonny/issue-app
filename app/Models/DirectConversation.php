<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DirectConversation extends Model
{
    protected $fillable = ['project_id', 'user_a_id', 'user_b_id'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function userA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_a_id');
    }

    public function userB(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_b_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DirectMessage::class, 'conversation_id')->oldest();
    }

    /** Return the other participant from the current user's perspective */
    public function otherUser(int $currentUserId): User
    {
        return $this->user_a_id === $currentUserId ? $this->userB : $this->userA;
    }
}
