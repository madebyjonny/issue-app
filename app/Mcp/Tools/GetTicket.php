<?php

namespace App\Mcp\Tools;

use App\Models\Ticket;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Get full details for a specific ticket by its identifier (e.g. PROJ-1)')]
class GetTicket extends Tool
{
    public function handle(Request $request): Response
    {
        $identifier = strtoupper($request->get('identifier', ''));

        $ticket = Ticket::with(['project', 'column', 'assignee', 'reporter', 'sprint'])
            ->where('identifier', $identifier)
            ->first();

        if (!$ticket) {
            return Response::error("Ticket '{$identifier}' not found.");
        }

        return Response::json([
            'id' => $ticket->id,
            'identifier' => $ticket->identifier,
            'title' => $ticket->title,
            'description' => $ticket->description,
            'project' => [
                'id' => $ticket->project->id,
                'name' => $ticket->project->name,
                'key' => $ticket->project->key,
            ],
            'column' => $ticket->column->name,
            'assignee' => $ticket->assignee?->name,
            'reporter' => $ticket->reporter->name,
            'sprint' => $ticket->sprint?->name,
            'priority' => $ticket->priority,
            'type' => $ticket->type,
            'estimate' => $ticket->estimate,
            'created_at' => $ticket->created_at->toIso8601String(),
            'updated_at' => $ticket->updated_at->toIso8601String(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'identifier' => $schema->string()->description('The ticket identifier, e.g. PROJ-1')->required(),
        ];
    }
}
