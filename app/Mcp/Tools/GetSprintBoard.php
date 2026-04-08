<?php

namespace App\Mcp\Tools;

use App\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Get the current sprint board showing columns and their tickets for a project')]
class GetSprintBoard extends Tool
{
    public function handle(Request $request): Response
    {
        $project = Project::where('key', strtoupper($request->get('project_key', '')))
            ->with(['columns.tickets' => function ($q) {
                $q->whereHas('sprint', fn ($sq) => $sq->where('is_active', true))
                    ->with(['assignee'])
                    ->orderBy('position');
            }, 'sprints' => fn ($q) => $q->where('is_active', true)])
            ->first();

        if (!$project) {
            return Response::error("Project '{$request->get('project_key')}' not found.");
        }

        $activeSprint = $project->sprints->first();

        return Response::json([
            'project' => $project->name,
            'sprint' => $activeSprint?->name ?? 'No active sprint',
            'columns' => $project->columns->map(fn ($col) => [
                'name' => $col->name,
                'tickets' => $col->tickets->map(fn ($t) => [
                    'identifier' => $t->identifier,
                    'title' => $t->title,
                    'assignee' => $t->assignee?->name,
                    'priority' => $t->priority,
                    'type' => $t->type,
                ]),
            ]),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'project_key' => $schema->string()->description('Project key (e.g. PROJ)')->required(),
        ];
    }
}
