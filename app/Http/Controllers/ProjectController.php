<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

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

        // Create default #general channel
        $channel = $project->channels()->create([
            'name'       => 'general',
            'slug'       => 'general',
            'description' => 'General project discussion',
            'is_private' => false,
            'created_by' => auth()->id(),
        ]);
        $channel->members()->attach(auth()->id());

        return redirect()->route('projects.show', $project)->with('success', 'Project created.');
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);
        $project->load(['columns.tickets.assignee', 'members', 'sprints', 'epics.tickets', 'resources']);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name'           => ['sometimes', 'required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'openai_api_key' => ['nullable', 'string', 'max:255'],
        ]);

        $flashMessage = 'Project updated.';

        if (array_key_exists('openai_api_key', $validated) && $validated['openai_api_key'] !== null) {
            $validated['openai_api_key'] = Crypt::encryptString($validated['openai_api_key']);
            $flashMessage = 'API key saved.';
        } elseif (array_key_exists('openai_api_key', $validated) && $validated['openai_api_key'] === null) {
            // Empty string submitted — don't overwrite existing key
            unset($validated['openai_api_key']);
        }

        $project->update($validated);

        $with = ['success' => $flashMessage];
        if (str_contains($flashMessage, 'API key')) {
            $with['openai_success'] = 'OpenAI API key saved successfully.';
        }

        return redirect()->route('projects.show', $project)->with($with);
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }
}
