<?php

namespace App\Http\Controllers;

use App\Models\DocFolder;
use App\Models\Project;
use Illuminate\Http\Request;

class DocFolderController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:doc_folders,id'],
        ]);

        $maxPos = $project->docFolders()
            ->where('parent_id', $data['parent_id'] ?? null)
            ->max('position') ?? -1;

        $project->docFolders()->create([
            'name'      => $data['name'],
            'parent_id' => $data['parent_id'] ?? null,
            'position'  => $maxPos + 1,
        ]);

        return back()->with('success', 'Folder created.');
    }

    public function update(Request $request, Project $project, DocFolder $folder)
    {
        $this->authorize('update', $project);
        abort_unless($folder->project_id === $project->id, 404);

        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $folder->update($data);

        return back()->with('success', 'Folder renamed.');
    }

    public function destroy(Project $project, DocFolder $folder)
    {
        $this->authorize('update', $project);
        abort_unless($folder->project_id === $project->id, 404);

        // Unparent any child folders and docs rather than cascade-delete
        $folder->children()->update(['parent_id' => $folder->parent_id]);
        $folder->docs()->update(['folder_id' => null]);
        $folder->delete();

        return back()->with('success', 'Folder deleted.');
    }
}
