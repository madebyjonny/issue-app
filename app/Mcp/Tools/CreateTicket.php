<?php

namespace App\Mcp\Tools;

use App\Models\Project;
use App\Models\Ticket;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create a new ticket in a project')]
class CreateTicket extends Tool
{
    public function handle(Request $request): Response
    {
        $project = Project::where('key', strtoupper($request->get('project_key', '')))->first();

        if (!$project) {
            return Response::error("Project '{$request->get('project_key')}' not found.");
        }

        $columnName = $request->get('column_name');
        if ($columnName) {
            $column = $project->columns()->where('name', $columnName)->first();
        } else {
            $column = $project->columns()->orderBy('position')->first();
        }

        if (!$column) {
            return Response::error('No column found.');
        }

        $maxPosition = Ticket::where('column_id', $column->id)->max('position') ?? -1;

        $ticket = $project->tickets()->create([
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'column_id' => $column->id,
            'assignee_id' => $request->get('assignee_id'),
            'reporter_id' => $project->owner_id,
            'sprint_id' => $request->get('sprint_id'),
            'epic_id' => $request->get('epic_id'),
            'priority' => $request->get('priority', 'none'),
            'type' => $request->get('type', 'task'),
            'estimate' => $request->get('estimate'),
            'position' => $maxPosition + 1,
        ]);

        $ticket->load(['column', 'assignee', 'reporter', 'sprint']);

        return Response::json([
            'success' => true,
            'ticket' => [
                'id' => $ticket->id,
                'identifier' => $ticket->identifier,
                'title' => $ticket->title,
                'column' => $ticket->column->name,
                'priority' => $ticket->priority,
                'type' => $ticket->type,
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'project_key' => $schema->string()->description('Project key (e.g. PROJ)')->required(),
            'title' => $schema->string()->description('Ticket title')->required(),
            'description' => $schema->string()->description('Ticket description')->nullable(),
            'column_name' => $schema->string()->description('Column name to place the ticket in (default: first column)')->nullable(),
            'assignee_id' => $schema->integer()->description('Assignee user ID')->nullable(),
            'sprint_id' => $schema->integer()->description('Sprint ID')->nullable(),
            'epic_id' => $schema->integer()->description('Epic ID')->nullable(),
            'priority' => $schema->string()->description('Priority level')->enum(['none', 'low', 'medium', 'high', 'urgent'])->nullable(),
            'type' => $schema->string()->description('Ticket type')->enum(['task', 'bug', 'feature', 'improvement'])->nullable(),
            'estimate' => $schema->integer()->description('Estimate points')->nullable(),
        ];
    }
}
