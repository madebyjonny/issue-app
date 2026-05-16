<?php

namespace App\Mcp\Resources;

use App\Models\Project;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\AppResource;
use Laravel\Mcp\Server\Attributes\AppMeta;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Support\UriTemplate;

#[Description('Interactive visual Kanban board showing project tickets grouped by column.')]
#[AppMeta(libraries: [])]
class TicketBoardApp extends AppResource implements HasUriTemplate
{
    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('ui://resources/ticket-board-app/{project_key}');
    }

    public function handle(Request $request): Response
    {
        $user = $request->user();
        $key = strtoupper($request->get('project_key', ''));

        $project = Project::where('key', $key)
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('members', fn ($mq) => $mq->where('users.id', $user->id));
            })
            ->with(['columns.tickets' => function ($q) {
                $q->whereHas('sprint', fn ($sq) => $sq->where('is_active', true))
                    ->with(['assignee'])
                    ->orderBy('position');
            }, 'sprints' => fn ($q) => $q->where('is_active', true)])
            ->first();

        if (! $project) {
            return Response::view('mcp.ticket-board-app', [
                'title' => $this->title(),
                'error' => "Project '{$key}' not found.",
                'project' => null,
                'sprint' => null,
                'columns' => collect(),
            ]);
        }

        $activeSprint = $project->sprints->first();

        $columns = $project->columns->map(fn ($col) => [
            'name' => $col->name,
            'tickets' => $col->tickets->map(fn ($t) => [
                'identifier' => $t->identifier,
                'title' => $t->title,
                'assignee' => $t->assignee?->name,
                'priority' => $t->priority,
                'type' => $t->type,
            ]),
        ]);

        return Response::view('mcp.ticket-board-app', [
            'title' => $this->title(),
            'error' => null,
            'project' => $project,
            'sprint' => $activeSprint?->name ?? 'No active sprint',
            'columns' => $columns,
        ]);
    }
}
