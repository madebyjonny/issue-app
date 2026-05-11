<?php

namespace App\Mcp\Tools;

use App\Models\ProjectResource;
use App\Models\Ticket;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Attach or detach a project resource (skill card) from a ticket. Use action "attach" or "detach".')]
class UpdateTicketResources extends Tool
{
    public function handle(Request $request): Response
    {
        $user = $request->user();
        $identifier = strtoupper($request->get('identifier', ''));
        $resourceId = (int) $request->get('resource_id', 0);
        $action     = strtolower($request->get('action', 'attach'));

        $ticket = Ticket::with('project')
            ->where('identifier', $identifier)
            ->whereHas('project', function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('members', fn ($mq) => $mq->where('id', $user->id));
            })
            ->first();
        if (!$ticket) {
            return Response::error("Ticket '{$identifier}' not found.");
        }

        if ($user->cannot('update', $ticket)) {
            return Response::error("Ticket '{$identifier}' not found.");
        }

        $resource = ProjectResource::find($resourceId);
        if (!$resource) {
            return Response::error("Resource #{$resourceId} not found.");
        }

        if ($resource->project_id !== $ticket->project_id) {
            return Response::error("Resource #{$resourceId} does not belong to the same project as ticket '{$identifier}'.");
        }

        if ($action === 'attach') {
            $ticket->resources()->syncWithoutDetaching([$resourceId]);
            return Response::text("Resource '{$resource->name}' attached to {$identifier}.");
        } elseif ($action === 'detach') {
            $ticket->resources()->detach($resourceId);
            return Response::text("Resource '{$resource->name}' detached from {$identifier}.");
        }

        return Response::error("'action' must be 'attach' or 'detach'.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'identifier'  => $schema->string()->description('Ticket identifier, e.g. CRUE-1')->required(),
            'resource_id' => $schema->integer()->description('The ID of the project resource to attach or detach')->required(),
            'action'      => $schema->string()->description('Either "attach" or "detach"')->required(),
        ];
    }
}
