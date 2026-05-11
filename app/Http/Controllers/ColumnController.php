<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Project;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $maxPosition = $project->columns()->max('position') ?? -1;
        $project->columns()->create([
            'name' => $validated['name'],
            'position' => $maxPosition + 1,
        ]);

        return back()->with('success', 'Column added.');
    }

    public function update(Request $request, Project $project, Column $column)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $column->update($validated);

        return back()->with('success', 'Column updated.');
    }

    public function destroy(Project $project, Column $column)
    {
        $this->authorize('update', $project);

        if ($column->tickets()->exists()) {
            return back()->with('error', 'Move all tickets out of "' . $column->name . '" before deleting it.');
        }

        $column->delete();

        return back()->with('success', 'Column deleted.');
    }

    public function reorder(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'columns' => ['required', 'array'],
            'columns.*' => ['integer', 'exists:columns,id'],
        ]);

        foreach ($validated['columns'] as $position => $id) {
            Column::where('id', $id)->where('project_id', $project->id)->update(['position' => $position]);
        }

        return response()->json(['ok' => true]);
    }
}
