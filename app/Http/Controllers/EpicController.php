<?php

namespace App\Http\Controllers;

use App\Models\Epic;
use App\Models\Project;
use App\Models\Ticket;
use Illuminate\Http\Request;

class EpicController extends Controller
{
    public function index(Project $project)
    {
        $project->load(['epics.tickets', 'members', 'sprints', 'columns']);

        return view('epics.index', compact('project'));
    }

    public function show(Project $project, Epic $epic)
    {
        $epic->load(['tickets.assignee', 'tickets.column', 'tickets.sprint']);
        $project->load(['columns', 'members', 'sprints', 'epics']);

        return view('epics.show', compact('project', 'epic'));
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:7'],
        ]);

        $project->epics()->create($validated);

        return back()->with('success', 'Epic created.');
    }

    public function update(Request $request, Project $project, Epic $epic)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:7'],
        ]);

        $epic->update($validated);

        return back()->with('success', 'Epic updated.');
    }

    public function destroy(Project $project, Epic $epic)
    {
        $epic->tickets()->update(['epic_id' => null]);
        $epic->delete();

        return redirect()->route('epics.index', $project)->with('success', 'Epic deleted.');
    }

    public function createTicket(Request $request, Project $project, Epic $epic)
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
            'epic_id' => $epic->id,
            'reporter_id' => auth()->id(),
            'position' => $maxPosition + 1,
        ]);

        return back()->with('success', 'Ticket created.');
    }
}
