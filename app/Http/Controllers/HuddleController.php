<?php

namespace App\Http\Controllers;

use App\Events\HuddleSignal;
use App\Events\HuddleUpdated;
use App\Models\HuddleSession;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class HuddleController extends Controller
{
    /** Start or retrieve the active huddle for a project */
    public function start(Project $project)
    {
        $this->authorize('view', $project);

        $huddle = HuddleSession::firstOrCreate(
            ['project_id' => $project->id, 'is_active' => true],
            ['initiated_by' => auth()->id(), 'participants' => [auth()->id()]]
        );

        // Join if not already a participant
        $participants = $huddle->participants ?? [];
        if (! in_array(auth()->id(), $participants)) {
            $participants = [...$participants, auth()->id()];
            $huddle->update(['participants' => $participants]);
        }

        $participantsData = User::whereIn('id', $participants)->get(['id', 'name'])->toArray();

        broadcast(new HuddleUpdated($project->id, $huddle->id, true, $participantsData))->toOthers();

        return response()->json([
            ...$huddle->toArray(),
            'participants_data' => $participantsData,
        ]);
    }

    /** Leave a huddle */
    public function leave(Project $project, HuddleSession $huddle)
    {
        $this->authorize('view', $project);

        $participants = array_values(array_filter(
            $huddle->participants ?? [],
            fn($id) => $id !== auth()->id()
        ));

        if (empty($participants)) {
            $huddle->update(['is_active' => false, 'participants' => []]);
            broadcast(new HuddleUpdated($project->id, $huddle->id, false, []))->toOthers();
        } else {
            $huddle->update(['participants' => $participants]);
            $participantsData = User::whereIn('id', $participants)->get(['id', 'name'])->toArray();
            broadcast(new HuddleUpdated($project->id, $huddle->id, true, $participantsData))->toOthers();
        }

        return response()->json([
            'ok'               => true,
            'is_active'        => ! empty($participants),
            'participants_data' => empty($participants) ? [] : User::whereIn('id', $participants)->get(['id', 'name'])->toArray(),
        ]);
    }

    /** Forward a WebRTC signal to the target peer */
    public function signal(Project $project, Request $request)
    {
        $this->authorize('view', $project);

        $data = $request->validate([
            'room_id'     => 'required|string',
            'to_user_id'  => 'required|integer|exists:users,id',
            'type'        => 'required|in:offer,answer,ice-candidate,hangup',
            'payload'     => 'nullable',
        ]);

        broadcast(new HuddleSignal(
            $data['room_id'],
            auth()->id(),
            $data['to_user_id'],
            $data['type'],
            $data['payload'] ?? null,
        ))->toOthers();

        return response()->json(['ok' => true]);
    }
}
