<?php

namespace App\Http\Controllers;

use App\Models\Doc;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class AiController extends Controller
{
    /** Summarise a set of messages and optionally suggest tickets/actions */
    public function summarise(Project $project, Request $request)
    {
        $this->authorize('view', $project);

        $data = $request->validate([
            'messages' => 'required|array|min:1',
            'messages.*.user'    => 'required|string',
            'messages.*.text'    => 'required|string',
        ]);

        $apiKey = $project->getRawOriginal('openai_api_key')
            ? Crypt::decryptString($project->getRawOriginal('openai_api_key'))
            : null;

        if (! $apiKey) {
            return response()->json(['error' => 'No OpenAI API key configured for this project. Add one in Project Settings.'], 422);
        }

        $conversation = collect($data['messages'])
            ->map(fn($m) => "{$m['user']}: {$m['text']}")
            ->implode("\n");

        $prompt = <<<PROMPT
You are a helpful project management assistant. Below is a conversation excerpt from a project team.

{$conversation}

Please:
1. Write a concise summary (3-5 sentences).
2. List any action items or decisions as bullet points.
3. If it makes sense, suggest whether any tickets should be created and what their titles/descriptions might be.
4. If the content would make a good knowledge-base document, note that.

Respond in JSON with keys: summary (string), actions (array of strings), suggested_tickets (array of {title, description}), suggest_doc (boolean).
PROMPT;

        $response = Http::withToken($apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'    => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

        if ($response->failed()) {
            return response()->json(['error' => 'OpenAI request failed.'], 502);
        }

        $content = $response->json('choices.0.message.content');
        return response()->json(json_decode($content, true));
    }

    /** Quick-create a ticket from an AI suggestion */
    public function quickCreateTicket(Project $project, Request $request)
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type'        => ['nullable', 'in:task,bug,feature,improvement'],
            'priority'    => ['nullable', 'in:none,low,medium,high,urgent'],
        ]);

        $column = $project->columns()->orderBy('position')->first();

        if (! $column) {
            return response()->json(['error' => 'No columns found in this project.'], 422);
        }

        $maxPosition = \App\Models\Ticket::where('column_id', $column->id)->max('position') ?? -1;

        $ticket = $project->tickets()->create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'type'        => $data['type'] ?? 'task',
            'priority'    => $data['priority'] ?? 'none',
            'column_id'   => $column->id,
            'reporter_id' => auth()->id(),
            'position'    => $maxPosition + 1,
        ]);

        $ticket->load('column:id,name');

        return response()->json([
            'id'         => $ticket->id,
            'identifier' => $ticket->identifier,
            'title'      => $ticket->title,
            'status'     => $ticket->column?->name,
            'priority'   => $ticket->priority,
            'type'       => $ticket->type,
        ]);
    }

    /** Create a new doc from an AI-generated summary */
    public function startDoc(Project $project, Request $request)
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'title'     => ['required', 'string', 'max:255'],
            'body_text' => ['nullable', 'string'],
        ]);

        // Convert plain text to Tiptap JSON (one paragraph per non-empty line)
        $paragraphs = [];
        foreach (explode("\n", $data['body_text'] ?? '') as $line) {
            $line = trim($line);
            if ($line !== '') {
                $paragraphs[] = [
                    'type'    => 'paragraph',
                    'content' => [['type' => 'text', 'text' => $line]],
                ];
            }
        }
        if (empty($paragraphs)) {
            $paragraphs[] = ['type' => 'paragraph'];
        }

        $body = ['type' => 'doc', 'content' => $paragraphs];

        $doc = Doc::create([
            'project_id'  => $project->id,
            'folder_id'   => null,
            'created_by'  => auth()->id(),
            'updated_by'  => auth()->id(),
            'title'       => $data['title'],
            'slug'        => Doc::uniqueSlug($project->id, $data['title']),
            'body'        => $body,
            'body_text'   => $data['body_text'] ?? null,
        ]);

        return response()->json([
            'id'    => $doc->id,
            'title' => $doc->title,
            'url'   => route('docs.show', [$project, $doc]),
        ]);
    }
}
