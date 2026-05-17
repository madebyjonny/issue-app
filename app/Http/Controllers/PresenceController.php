<?php

namespace App\Http\Controllers;

use App\Events\CursorMoved;
use App\Models\Project;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    /** Broadcast cursor position to other board users */
    public function cursor(Project $project, Request $request)
    {
        $this->authorize('view', $project);

        $data = $request->validate([
            'ticket_id' => 'nullable|integer',
            'column_id' => 'nullable|string',
            'position'  => 'nullable|array',
        ]);

        broadcast(new CursorMoved(
            $project->id,
            auth()->id(),
            auth()->user()->name,
            $data['ticket_id'] ?? null,
            $data['column_id'] ?? null,
            $data['position'] ?? null,
        ))->toOthers();

        return response()->json(['ok' => true]);
    }
}
