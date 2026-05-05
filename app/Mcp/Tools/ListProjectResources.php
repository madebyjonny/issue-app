<?php

namespace App\Mcp\Tools;

use App\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List all resources (skill cards) for a project.')]
class ListProjectResources extends Tool
{
    public function handle(Request $request): Response
    {
        $projectKey = strtoupper($request->get('project_key', ''));
        $project = Project::where('key', $projectKey)->with('resources')->first();

        if (!$project) {
            return Response::error("Project '{$projectKey}' not found.");
        }

        return Response::json(
            $project->resources->map(fn ($r) => [
                'id'   => $r->id,
                'name' => $r->name,
                'type' => $r->type,
            ])->values()->all()
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'project_key' => $schema->string()->description('Project key, e.g. CRUE')->required(),
        ];
    }
}
