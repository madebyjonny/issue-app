<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Channel;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Project;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $channels = $project->channels()
            ->with(['members' => fn($q) => $q->where('user_id', auth()->id())])
            ->get();

        return response()->json($channels);
    }

    public function store(Project $project, Request $request)
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'name'        => 'required|string|max:80',
            'description' => 'nullable|string|max:255',
            'is_private'  => 'boolean',
        ]);

        $channel = $project->channels()->create([
            ...$data,
            'created_by' => auth()->id(),
        ]);

        // Add creator as member
        $channel->members()->attach(auth()->id());

        // Auto-add all project members to public channels
        if (! ($data['is_private'] ?? false)) {
            $memberIds = $project->members()->pluck('users.id');
            $channel->members()->syncWithoutDetaching($memberIds);
        }

        if (request()->wantsJson()) {
            return response()->json($channel, 201);
        }

        return redirect()->route('channels.show', [$project, $channel]);
    }

    public function show(Project $project, Channel $channel)
    {
        $this->authorize('view', $project);

        $messages = $channel->messages()
            ->with(['user', 'thread'])
            ->latest()
            ->paginate(50);

        return view('messaging.channel', compact('project', 'channel', 'messages'));
    }

    public function destroy(Project $project, Channel $channel)
    {
        $this->authorize('update', $project);

        $channel->delete();

        return response()->json(['deleted' => true]);
    }
}
