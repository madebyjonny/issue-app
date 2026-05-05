<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProjectResource extends Model
{
    protected $fillable = ['project_id', 'name', 'type', 'content'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class);
    }
}
