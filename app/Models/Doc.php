<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Doc extends Model
{
    protected $fillable = [
        'type', 'project_id', 'folder_id', 'created_by', 'updated_by',
        'title', 'slug', 'body', 'body_text',
    ];

    protected $casts = [
        'body' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(DocFolder::class, 'folder_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** Generate a unique slug within the project. */
    public static function uniqueSlug(int $projectId, string $title, ?int $excludeId = null): string
    {
        $base = Str::slug($title) ?: 'doc';
        $slug = $base;
        $i = 1;
        while (
            static::where('project_id', $projectId)
                ->where('slug', $slug)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }

    /** Recursively extract plain text from a Tiptap JSON node. */
    public static function extractText(mixed $node): string
    {
        if (is_string($node)) {
            $decoded = json_decode($node, true);
            if ($decoded) $node = $decoded;
            else return '';
        }
        if (!is_array($node)) return '';
        if (($node['type'] ?? '') === 'text') return $node['text'] ?? '';
        $text = '';
        foreach ($node['content'] ?? [] as $child) {
            $text .= ' ' . static::extractText($child);
        }
        return trim($text);
    }
}
