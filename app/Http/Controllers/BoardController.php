<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function show(Project $project, Request $request)
    {
        $project->load(['columns.tickets.assignee', 'members', 'sprints', 'epics']);

        $sprintId = $request->query('sprint');
        $assigneeId = $request->query('assignee');
        $priority = $request->query('priority');
        $type = $request->query('type');
        $epicId = $request->query('epic');

        if ($sprintId || $assigneeId || $priority || $type || $epicId) {
            $project->columns->each(function ($column) use ($sprintId, $assigneeId, $priority, $type, $epicId) {
                $column->setRelation('tickets', $column->tickets->filter(function ($ticket) use ($sprintId, $assigneeId, $priority, $type, $epicId) {
                    if ($sprintId && $ticket->sprint_id != $sprintId) return false;
                    if ($assigneeId && $ticket->assignee_id != $assigneeId) return false;
                    if ($priority && $ticket->priority !== $priority) return false;
                    if ($type && $ticket->type !== $type) return false;
                    if ($epicId && $ticket->epic_id != $epicId) return false;
                    return true;
                })->values());
            });
        }

        return view('board.show', compact('project'));
    }
}
