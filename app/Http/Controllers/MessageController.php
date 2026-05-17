<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Channel;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Project;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Project $project, Channel $channel, Request $request)
    {
        $this->authorize('view', $project);

        $data = $request->validate([
            'body'              => 'required|array',
            'parent_id'         => 'nullable|integer|exists:messages,id',
            'mentioned_tickets' => 'nullable|array',
        ]);

        $message = $channel->messages()->create([
            'user_id'           => auth()->id(),
            'body'              => $data['body'],
            'parent_id'         => $data['parent_id'] ?? null,
            'mentioned_tickets' => $data['mentioned_tickets'] ?? null,
        ]);

        // Maintain thread metadata on the parent
        if ($message->parent_id) {
            $thread = MessageThread::firstOrCreate(
                ['message_id' => $message->parent_id],
                ['reply_count' => 0]
            );
            $thread->increment('reply_count');
            $thread->update(['last_reply_at' => now()]);
        }

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message->load('user'), 201);
    }

    public function destroy(Project $project, Channel $channel, Message $message)
    {
        $this->authorize('view', $project);

        if ($message->user_id !== auth()->id()) {
            abort(403);
        }

        $message->delete();

        return response()->json(['deleted' => true]);
    }

    /** Return thread replies for a parent message */
    public function thread(Project $project, Channel $channel, Message $message)
    {
        $this->authorize('view', $project);

        $replies = $message->replies()->with('user')->get();

        return response()->json($replies);
    }
}
