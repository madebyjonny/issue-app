<?php

namespace App\Mcp\Tools;

use App\Models\Project;
use App\Models\ProjectResource;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create a new resource (skill card) on a project. Resources are context given to AI agents when working on tickets.')]
class CreateProjectResource extends Tool
{
    public function handle(Request $request): Response
    {
        $user = $request->user();
        $projectKey = strtoupper($request->get('project_key', ''));

        $project = Project::where('key', $projectKey)
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('members', fn ($mq) => $mq->where('users.id', $user->id));
            })
            ->first();

        if (!$project) {
            return Response::error("Project '{$projectKey}' not found.");
        }

        if ($user->cannot('update', $project)) {
            return Response::error("Project '{$projectKey}' not found.");
        }

        $name    = trim($request->get('name', ''));
        $type    = strtolower(trim($request->get('type', '')));
        $content = trim($request->get('content', ''));

        if (!$name) return Response::error("'name' is required.");
        if (!in_array($type, ['design', 'development', 'api', 'process'])) {
            return Response::error("'type' must be one of: design, development, api, process.");
        }
        if (!$content) return Response::error("'content' is required.");

        $resource = $project->resources()->create([
            'name'    => $name,
            'type'    => $type,
            'content' => $content,
        ]);

        return Response::json([
            'id'          => $resource->id,
            'project_key' => $project->key,
            'name'        => $resource->name,
            'type'        => $resource->type,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'project_key' => $schema->string()->description('Project key, e.g. CRUE')->required(),
            'name'        => $schema->string()->description('Short name for the resource')->required(),
            'type'        => $schema->string()->description('One of: design, development, api, process')->required(),
            'content'     => $schema->string()->description('Full text content of the resource/skill card')->required(),
        ];
    }
}
