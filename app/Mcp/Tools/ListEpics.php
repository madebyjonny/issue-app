<?php

namespace App\Mcp\Tools;

use App\Models\Epic;
use App\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List epics for a project, including ticket counts and progress')]
class ListEpics extends Tool
{
    public function handle(Request $request): Response
    {
        $project = Project::where('key', strtoupper($request->get('project_key', '')))->first();

        if (!$project) {
            return Response::error("Project '{$request->get('project_key')}' not found.");
        }

        $epics = $project->epics()->withCount('tickets')->get()->map(fn ($e) => [
            'id' => $e->id,
            'name' => $e->name,
            'description' => $e->description,
            'color' => $e->color,
            'tickets_count' => $e->tickets_count,
        ]);

        if ($epics->isEmpty()) {
            return Response::text("No epics found for project {$project->key}.");
        }

        return Response::json($epics);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'project_key' => $schema->string()->description('Project key (e.g. PROJ)')->required(),
        ];
    }
}
