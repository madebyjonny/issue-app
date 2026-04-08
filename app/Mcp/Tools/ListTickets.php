<?php

namespace App\Mcp\Tools;

use App\Models\Ticket;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List tickets with optional filters by project, assignee, sprint, column, or priority')]
class ListTickets extends Tool
{
    public function handle(Request $request): Response
    {
        $query = Ticket::with(['project', 'column', 'assignee', 'sprint', 'epic']);

        if ($key = $request->get('project_key')) {
            $query->whereHas('project', fn ($q) => $q->where('key', strtoupper($key)));
        }

        if ($assigneeId = $request->get('assignee_id')) {
            $query->where('assignee_id', $assigneeId);
        }

        if ($sprintId = $request->get('sprint_id')) {
            $query->where('sprint_id', $sprintId);
        }

        if ($columnId = $request->get('column_id')) {
            $query->where('column_id', $columnId);
        }

        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }

        if ($request->get('active_sprint')) {
            $query->whereHas('sprint', fn ($q) => $q->where('is_active', true));
        }

        if ($epicId = $request->get('epic_id')) {
            $query->where('epic_id', $epicId);
        }

        $tickets = $query->orderBy('position')->get()->map(fn ($t) => [
            'identifier' => $t->identifier,
            'title' => $t->title,
            'project' => $t->project->key,
            'column' => $t->column->name,
            'assignee' => $t->assignee?->name,
            'sprint' => $t->sprint?->name,
            'epic' => $t->epic?->name,
            'priority' => $t->priority,
            'type' => $t->type,
        ]);

        if ($tickets->isEmpty()) {
            return Response::text('No tickets found matching the given filters.');
        }

        return Response::json($tickets);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'project_key' => $schema->string()->description('Filter by project key (e.g. PROJ)')->nullable(),
            'assignee_id' => $schema->integer()->description('Filter by assignee user ID')->nullable(),
            'sprint_id' => $schema->integer()->description('Filter by sprint ID')->nullable(),
            'column_id' => $schema->integer()->description('Filter by column ID')->nullable(),
            'priority' => $schema->string()->description('Filter by priority')->enum(['none', 'low', 'medium', 'high', 'urgent'])->nullable(),
            'active_sprint' => $schema->boolean()->description('Only show tickets in the active sprint')->nullable(),
            'epic_id' => $schema->integer()->description('Filter by epic ID')->nullable(),
        ];
    }
}
