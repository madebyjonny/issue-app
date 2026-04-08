<?php

namespace App\Mcp\Tools;

use App\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create a new epic in a project')]
class CreateEpic extends Tool
{
    public function handle(Request $request): Response
    {
        $project = Project::where('key', strtoupper($request->get('project_key', '')))->first();

        if (! $project) {
            return Response::error("Project '{$request->get('project_key')}' not found.");
        }

        $epic = $project->epics()->create([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'color' => $request->get('color', '#8b5cf6'),
        ]);

        return Response::json([
            'success' => true,
            'epic' => [
                'id' => $epic->id,
                'name' => $epic->name,
                'description' => $epic->description,
                'color' => $epic->color,
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'project_key' => $schema->string()->description('Project key (e.g. PROJ)')->required(),
            'name' => $schema->string()->description('Epic name')->required(),
            'description' => $schema->string()->description('Epic description'),
            'color' => $schema->string()->description('Hex color (e.g. #8b5cf6). Defaults to purple.'),
        ];
    }
}
