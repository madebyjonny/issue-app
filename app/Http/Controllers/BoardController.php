<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function show(Project $project, Request $request)
    {
        $project->load(['columns.tickets.assignee', 'members', 'sprints']);

        $sprintId = $request->query('sprint');
        $assigneeId = $request->query('assignee');

        if ($sprintId || $assigneeId) {
            $project->columns->each(function ($column) use ($sprintId, $assigneeId) {
                $column->setRelation('tickets', $column->tickets->filter(function ($ticket) use ($sprintId, $assigneeId) {
                    if ($sprintId && $ticket->sprint_id != $sprintId) return false;
                    if ($assigneeId && $ticket->assignee_id != $assigneeId) return false;
                    return true;
                })->values());
            });
        }

        return view('board.show', compact('project'));
    }
}
