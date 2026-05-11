<?php

namespace App\Mcp\Tools;

use App\Models\Ticket;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Delete a ticket by its identifier')]
class DeleteTicket extends Tool
{
    public function handle(Request $request): Response
    {
        $user = $request->user();
        $identifier = strtoupper($request->get('identifier', ''));

        $ticket = Ticket::where('identifier', $identifier)
            ->whereHas('project', function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('members', fn ($mq) => $mq->where('users.id', $user->id));
            })
            ->first();

        if (!$ticket) {
            return Response::error("Ticket '{$identifier}' not found.");
        }

        if ($user->cannot('delete', $ticket)) {
            return Response::error("Ticket '{$identifier}' not found.");
        }

        $ticket->delete();

        return Response::json(['success' => true, 'deleted' => $identifier]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'identifier' => $schema->string()->description('The ticket identifier, e.g. PROJ-1')->required(),
        ];
    }
}
