<?php

namespace App\Mcp\Tools;

use App\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List all projects with their ticket, column, and sprint counts')]
class ListProjects extends Tool
{
    public function handle(Request $request): Response
    {
        $projects = Project::with('owner')
            ->withCount(['tickets', 'columns', 'sprints'])
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'key' => $p->key,
                'description' => $p->description,
                'owner' => $p->owner->name,
                'tickets_count' => $p->tickets_count,
                'columns_count' => $p->columns_count,
                'sprints_count' => $p->sprints_count,
            ]);

        if ($projects->isEmpty()) {
            return Response::text('No projects found.');
        }

        return Response::json($projects);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
