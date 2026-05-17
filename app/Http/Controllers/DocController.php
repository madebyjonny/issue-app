<?php

namespace App\Http\Controllers;

use App\Models\Doc;
use App\Models\DocFolder;
use App\Models\Project;
use Illuminate\Http\Request;

class DocController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $folders = $project->docFolders()
            ->with(['children.docs', 'docs'])
            ->whereNull('parent_id')
            ->orderBy('position')
            ->get();

        $recent = $project->docs()
            ->with(['folder', 'editor'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        return view('docs.index', compact('project', 'folders', 'recent'));
    }

    public function show(Project $project, Doc $doc)
    {
        $this->authorize('view', $project);
        abort_unless($doc->project_id === $project->id, 404);

        $folders = $project->docFolders()
            ->with(['children.docs', 'docs'])
            ->whereNull('parent_id')
            ->orderBy('position')
            ->get();

        $doc->load(['folder', 'author', 'editor']);

        if ($doc->type === 'whiteboard') {
            return view('docs.whiteboard', compact('project', 'doc', 'folders'));
        }

        return view('docs.show', compact('project', 'doc', 'folders'));
    }

    public function create(Project $project)
    {
        $this->authorize('update', $project);

        $folders = $project->docFolders()->orderBy('position')->get();

        return view('docs.create', compact('project', 'folders'));
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'title'     => ['required', 'string', 'max:255'],
            'folder_id' => ['nullable', 'exists:doc_folders,id'],
            'body'      => ['nullable', 'array'],
            'type'      => ['nullable', 'in:text,whiteboard'],
        ]);

        $type = $data['type'] ?? 'text';
        $slug = Doc::uniqueSlug($project->id, $data['title']);
        $bodyText = isset($data['body']) ? Doc::extractText($data['body']) : null;

        $doc = $project->docs()->create([
            'type'       => $type,
            'title'      => $data['title'],
            'folder_id'  => $data['folder_id'] ?? null,
            'body'       => $data['body'] ?? null,
            'body_text'  => $bodyText,
            'slug'       => $slug,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $url = route('docs.show', [$project, $doc]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'id'    => $doc->id,
                'title' => $doc->title,
                'type'  => $doc->type,
                'url'   => $url,
            ]);
        }

        return redirect($url)->with('success', 'Document created.');
    }

    public function edit(Project $project, Doc $doc)
    {
        $this->authorize('update', $project);
        abort_unless($doc->project_id === $project->id, 404);

        $folders = $project->docFolders()->orderBy('position')->get();

        return view('docs.edit', compact('project', 'doc', 'folders'));
    }

    public function update(Request $request, Project $project, Doc $doc)
    {
        $this->authorize('update', $project);
        abort_unless($doc->project_id === $project->id, 404);

        $data = $request->validate([
            'title'     => ['required', 'string', 'max:255'],
            'folder_id' => ['nullable', 'exists:doc_folders,id'],
            'body'      => ['nullable', 'array'],
        ]);

        $slug = Doc::uniqueSlug($project->id, $data['title'], $doc->id);
        $bodyText = isset($data['body']) ? Doc::extractText($data['body']) : null;

        $doc->update([
            'title'      => $data['title'],
            'folder_id'  => $data['folder_id'] ?? null,
            'body'       => $data['body'] ?? null,
            'body_text'  => $bodyText,
            'slug'       => $slug,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('docs.show', [$project, $doc])
            ->with('success', 'Document updated.');
    }

    public function destroy(Project $project, Doc $doc)
    {
        $this->authorize('update', $project);
        abort_unless($doc->project_id === $project->id, 404);

        $doc->delete();

        return redirect()->route('docs.index', $project)
            ->with('success', 'Document deleted.');
    }

    /** JSON search endpoint — used by the chat /search command */
    public function search(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $q = trim($request->string('q'));
        if ($q === '') {
            return response()->json([]);
        }

        $results = $project->docs()
            ->where(function ($query) use ($q) {
                $query->where('title', 'LIKE', "%{$q}%")
                      ->orWhere('body_text', 'LIKE', "%{$q}%");
            })
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get(['id', 'title', 'slug', 'folder_id', 'updated_at', 'body_text']);

        return response()->json($results->map(fn($d) => [
            'id'     => $d->id,
            'title'  => $d->title,
            'url'    => route('docs.show', [$project, $d]),
            'excerpt' => $d->body_text ? mb_substr(strip_tags($d->body_text), 0, 120) : null,
        ]));
    }
}
