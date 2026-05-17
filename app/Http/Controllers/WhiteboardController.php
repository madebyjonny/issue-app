<?php

namespace App\Http\Controllers;

use App\Events\WhiteboardUpdated;
use App\Models\Doc;
use App\Models\Project;
use Illuminate\Http\Request;

class WhiteboardController extends Controller
{
    /**
     * Persist Excalidraw state and broadcast to other collaborators.
     */
    public function sync(Request $request, Project $project, Doc $doc)
    {
        $this->authorize('update', $project);
        abort_unless($doc->project_id === $project->id, 404);
        abort_unless($doc->type === 'whiteboard', 422);

        $data = $request->validate([
            'elements'   => ['required', 'array'],
            'elements.*' => ['array'],
        ]);

        $body = ['elements' => $data['elements']];

        $doc->update([
            'body'       => $body,
            'updated_by' => auth()->id(),
        ]);

        broadcast(new WhiteboardUpdated($doc, $data['elements'], auth()->id()))->toOthers();

        return response()->json(['ok' => true]);
    }
}
