<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = auth()->user()->projects()->withCount('tickets')->latest()->get()
            ->merge(auth()->user()->ownedProjects()->withCount('tickets')->latest()->get())
            ->unique('id');

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:10', 'unique:projects,key', 'alpha_num', 'uppercase'],
            'description' => ['nullable', 'string'],
        ]);

        $project = auth()->user()->ownedProjects()->create($validated);
        $project->members()->attach(auth()->id(), ['role' => 'owner']);

        // Create default columns
        $defaults = ['Backlog', 'Todo', 'In Progress', 'In Review', 'Done'];
        foreach ($defaults as $i => $name) {
            $project->columns()->create(['name' => $name, 'position' => $i]);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Project created.');
    }

    public function show(Project $project)
    {
        $this->authorizeProject($project);
        $project->load(['columns.tickets.assignee', 'members', 'sprints']);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $this->authorizeProject($project);
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorizeProject($project);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)->with('success', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $this->authorizeProject($project);
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }

    private function authorizeProject(Project $project): void
    {
        abort_unless(
            $project->owner_id === auth()->id() || $project->members->contains(auth()->id()),
            403
        );
    }
}
