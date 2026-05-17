<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = ['name', 'key', 'description', 'owner_id', 'openai_api_key'];

    protected $hidden = ['openai_api_key'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function columns(): HasMany
    {
        return $this->hasMany(Column::class)->orderBy('position');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function sprints(): HasMany
    {
        return $this->hasMany(Sprint::class);
    }

    public function epics(): HasMany
    {
        return $this->hasMany(Epic::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(ProjectResource::class)->orderBy('type')->orderBy('name');
    }

    public function activeSprint(): HasMany
    {
        return $this->hasMany(Sprint::class)->where('is_active', true);
    }

    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class)->orderBy('name');
    }

    public function huddleSessions(): HasMany
    {
        return $this->hasMany(HuddleSession::class)->where('is_active', true);
    }

    public function docFolders(): HasMany
    {
        return $this->hasMany(DocFolder::class)->orderBy('position');
    }

    public function docs(): HasMany
    {
        return $this->hasMany(Doc::class)->orderBy('title');
    }

    public function nextTicketNumber(): int
    {
        $lastIdentifier = $this->tickets()->orderBy('id', 'desc')->value('identifier');

        if (!$lastIdentifier) {
            return 1;
        }

        return ((int) explode('-', $lastIdentifier)[1]) + 1;
    }
}
