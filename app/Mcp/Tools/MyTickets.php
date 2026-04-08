<?php

namespace App\Mcp\Tools;

use App\Models\Ticket;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Get tickets assigned to a specific user, optionally filtered to the active sprint')]
class MyTickets extends Tool
{
    public function handle(Request $request): Response
    {
        $query = Ticket::with(['project', 'column', 'sprint'])
            ->where('assignee_id', $request->get('user_id'));

        if ($request->get('active_sprint')) {
            $query->whereHas('sprint', fn ($q) => $q->where('is_active', true));
        }

        if ($key = $request->get('project_key')) {
            $query->whereHas('project', fn ($q) => $q->where('key', strtoupper($key)));
        }

        $tickets = $query->orderBy('priority', 'desc')->orderBy('position')->get()->map(fn ($t) => [
            'identifier' => $t->identifier,
            'title' => $t->title,
            'project' => $t->project->key,
            'column' => $t->column->name,
            'sprint' => $t->sprint?->name,
            'priority' => $t->priority,
            'type' => $t->type,
        ]);

        if ($tickets->isEmpty()) {
            return Response::text('No assigned tickets found.');
        }

        return Response::json($tickets);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'user_id' => $schema->integer()->description('The user ID to get assigned tickets for')->required(),
            'active_sprint' => $schema->boolean()->description('Only show tickets in the active sprint')->nullable(),
            'project_key' => $schema->string()->description('Filter by project key')->nullable(),
        ];
    }
}
