<?php

namespace App\Mcp\Tools;

use App\Mcp\Resources\TicketBoardApp;
use App\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\RendersApp;
use Laravel\Mcp\Server\Tool;

#[Description('Show an interactive visual Kanban board for a project')]
#[RendersApp(resource: TicketBoardApp::class)]
class ShowTicketBoard extends Tool
{
    public function handle(Request $request): Response
    {
        $user = $request->user();
        $key = strtoupper($request->get('project_key', ''));

        $project = Project::where('key', $key)
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('members', fn ($mq) => $mq->where('users.id', $user->id));
            })
            ->first();

        if (!$project) {
            return Response::error("Project '{$key}' not found or you don't have access.");
        }

        return Response::text("Opening board for {$project->name} ({$project->key}).");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'project_key' => $schema->string()->description('Project key (e.g. PROJ)')->required(),
        ];
    }
}
