<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocFolder extends Model
{
    protected $fillable = ['project_id', 'parent_id', 'name', 'position'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DocFolder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(DocFolder::class, 'parent_id')->orderBy('position');
    }

    public function docs(): HasMany
    {
        return $this->hasMany(Doc::class, 'folder_id')->orderBy('title');
    }
}
