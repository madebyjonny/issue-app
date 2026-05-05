<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectResource;
use App\Models\Ticket;
use Illuminate\Http\Request;

class ProjectResourceController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'type'    => ['required', 'in:design,development,api,process'],
            'content' => ['required', 'string'],
        ]);

        $project->resources()->create($validated);

        return back()->with('success', 'Resource created.');
    }

    public function update(Request $request, Project $project, ProjectResource $resource)
    {
        abort_if($resource->project_id !== $project->id, 403);

        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'type'    => ['required', 'in:design,development,api,process'],
            'content' => ['required', 'string'],
        ]);

        $resource->update($validated);

        return back()->with('success', 'Resource updated.');
    }

    public function destroy(Project $project, ProjectResource $resource)
    {
        abort_if($resource->project_id !== $project->id, 403);

        $resource->delete();

        return back()->with('success', 'Resource deleted.');
    }

    public function attachTicket(Request $request, Project $project, ProjectResource $resource)
    {
        abort_if($resource->project_id !== $project->id, 403);

        $validated = $request->validate([
            'ticket_id' => ['required', 'exists:tickets,id'],
        ]);

        $ticket = Ticket::findOrFail($validated['ticket_id']);
        abort_if($ticket->project_id !== $project->id, 403);

        $resource->tickets()->syncWithoutDetaching([$validated['ticket_id']]);

        return back()->with('success', 'Resource attached.');
    }

    public function detachTicket(Project $project, ProjectResource $resource, Ticket $ticket)
    {
        abort_if($resource->project_id !== $project->id, 403);
        abort_if($ticket->project_id !== $project->id, 403);

        $resource->tickets()->detach($ticket->id);

        return back()->with('success', 'Resource detached.');
    }
}
