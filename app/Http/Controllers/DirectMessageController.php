<?php

namespace App\Http\Controllers;

use App\Events\DirectMessageSent;
use App\Models\DirectConversation;
use App\Models\DirectMessage;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class DirectMessageController extends Controller
{
    public function show(Project $project, User $user)
    {
        $this->authorize('view', $project);

        [$a, $b] = [min(auth()->id(), $user->id), max(auth()->id(), $user->id)];

        $conversation = DirectConversation::firstOrCreate([
            'project_id' => $project->id,
            'user_a_id'  => $a,
            'user_b_id'  => $b,
        ]);

        $messages = $conversation->messages()->with('user')->get();

        return view('messaging.dm', compact('project', 'user', 'conversation', 'messages'));
    }

    public function store(Project $project, User $user, Request $request)
    {
        $this->authorize('view', $project);

        $data = $request->validate([
            'body' => 'required|array',
        ]);

        [$a, $b] = [min(auth()->id(), $user->id), max(auth()->id(), $user->id)];

        $conversation = DirectConversation::firstOrCreate([
            'project_id' => $project->id,
            'user_a_id'  => $a,
            'user_b_id'  => $b,
        ]);

        $message = $conversation->messages()->create([
            'user_id' => auth()->id(),
            'body'    => $data['body'],
        ]);

        broadcast(new DirectMessageSent($message))->toOthers();

        return response()->json($message->load('user'), 201);
    }
}
