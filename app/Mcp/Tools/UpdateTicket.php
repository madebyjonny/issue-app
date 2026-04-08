<?php

namespace App\Mcp\Tools;

use App\Models\Ticket;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Update an existing ticket. Pass only the fields you want to change.')]
class UpdateTicket extends Tool
{
    public function handle(Request $request): Response
    {
        $ticket = Ticket::with('project.columns')
            ->where('identifier', strtoupper($request->get('identifier', '')))
            ->first();

        if (!$ticket) {
            return Response::error("Ticket '{$request->get('identifier')}' not found.");
        }

        $updates = [];

        if ($request->get('title') !== null) $updates['title'] = $request->get('title');
        if ($request->get('description') !== null) $updates['description'] = $request->get('description');
        if ($request->get('priority') !== null) $updates['priority'] = $request->get('priority');
        if ($request->get('type') !== null) $updates['type'] = $request->get('type');
        if ($request->get('estimate') !== null) $updates['estimate'] = $request->get('estimate');

        if ($request->get('assignee_id') !== null) {
            $updates['assignee_id'] = $request->get('assignee_id') === 0 ? null : $request->get('assignee_id');
        }

        if ($request->get('sprint_id') !== null) {
            $updates['sprint_id'] = $request->get('sprint_id') === 0 ? null : $request->get('sprint_id');
        }

        if ($columnName = $request->get('column_name')) {
            $column = $ticket->project->columns->firstWhere('name', $columnName);
            if ($column) {
                $updates['column_id'] = $column->id;
            } else {
                return Response::error("Column '{$columnName}' not found in project.");
            }
        }

        $ticket->update($updates);
        $ticket->refresh()->load(['column', 'assignee', 'reporter', 'sprint']);

        return Response::json([
            'success' => true,
            'ticket' => [
                'identifier' => $ticket->identifier,
                'title' => $ticket->title,
                'column' => $ticket->column->name,
                'assignee' => $ticket->assignee?->name,
                'priority' => $ticket->priority,
                'type' => $ticket->type,
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'identifier' => $schema->string()->description('The ticket identifier, e.g. PROJ-1')->required(),
            'title' => $schema->string()->description('New title')->nullable(),
            'description' => $schema->string()->description('New description')->nullable(),
            'column_name' => $schema->string()->description('Move to column by name')->nullable(),
            'assignee_id' => $schema->integer()->description('Assign to user ID (0 to unassign)')->nullable(),
            'sprint_id' => $schema->integer()->description('Set sprint ID (0 to remove)')->nullable(),
            'priority' => $schema->string()->description('Priority level')->enum(['none', 'low', 'medium', 'high', 'urgent'])->nullable(),
            'type' => $schema->string()->description('Ticket type')->enum(['task', 'bug', 'feature', 'improvement'])->nullable(),
            'estimate' => $schema->integer()->description('Estimate points')->nullable(),
        ];
    }
}
