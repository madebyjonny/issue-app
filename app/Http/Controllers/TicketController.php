<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'column_id' => ['required', 'exists:columns,id'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'sprint_id' => ['nullable', 'exists:sprints,id'],
            'priority' => ['required', 'in:none,low,medium,high,urgent'],
            'type' => ['required', 'in:task,bug,feature,improvement'],
            'estimate' => ['nullable', 'integer', 'min:0'],
        ]);

        $maxPosition = Ticket::where('column_id', $validated['column_id'])->max('position') ?? -1;

        $project->tickets()->create([
            ...$validated,
            'reporter_id' => auth()->id(),
            'position' => $maxPosition + 1,
        ]);

        return back()->with('success', 'Ticket created.');
    }

    public function show(Project $project, Ticket $ticket)
    {
        $ticket->load(['assignee', 'reporter', 'column', 'sprint']);
        $project->load(['columns', 'members', 'sprints']);

        return view('tickets.show', compact('project', 'ticket'));
    }

    public function update(Request $request, Project $project, Ticket $ticket)
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'column_id' => ['sometimes', 'required', 'exists:columns,id'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'sprint_id' => ['nullable', 'exists:sprints,id'],
            'priority' => ['sometimes', 'required', 'in:none,low,medium,high,urgent'],
            'type' => ['sometimes', 'required', 'in:task,bug,feature,improvement'],
            'estimate' => ['nullable', 'integer', 'min:0'],
        ]);

        $ticket->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'ticket' => $ticket->fresh()]);
        }

        return back()->with('success', 'Ticket updated.');
    }

    public function destroy(Project $project, Ticket $ticket)
    {
        $ticket->delete();

        return back()->with('success', 'Ticket deleted.');
    }

    public function move(Request $request, Project $project, Ticket $ticket)
    {
        $validated = $request->validate([
            'column_id' => ['required', 'exists:columns,id'],
            'position' => ['required', 'integer', 'min:0'],
        ]);

        // Reorder tickets in the target column
        Ticket::where('column_id', $validated['column_id'])
            ->where('position', '>=', $validated['position'])
            ->increment('position');

        $ticket->update($validated);

        return response()->json(['ok' => true]);
    }
}
