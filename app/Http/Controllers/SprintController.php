<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Http\Request;

class SprintController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $project->sprints()->create($validated);

        return back()->with('success', 'Sprint created.');
    }

    public function update(Request $request, Project $project, Sprint $sprint)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (!empty($validated['is_active']) && $validated['is_active']) {
            $project->sprints()->where('id', '!=', $sprint->id)->update(['is_active' => false]);
        }

        $sprint->update($validated);

        return back()->with('success', 'Sprint updated.');
    }

    public function destroy(Project $project, Sprint $sprint)
    {
        $sprint->tickets()->update(['sprint_id' => null]);
        $sprint->delete();

        return back()->with('success', 'Sprint deleted.');
    }
}
