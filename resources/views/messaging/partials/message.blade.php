<div class="group flex gap-3 px-2 py-1 rounded-lg hover:bg-gray-50 dark:hover:bg-white/[0.03] transition message-item"
     data-message-id="{{ $message->id }}"
     data-user="{{ $message->user->name }}"
     data-text="{{ \App\Models\Doc::extractText($message->body ?? []) }}"
     data-thread-url="{{ $message->thread?->reply_count > 0 ? route('messages.thread', [$project, $channel ?? $message->channel, $message]) : '' }}">

    {{-- Avatar --}}
    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-[11px] font-semibold text-white flex-shrink-0">
        {{ strtoupper(substr($message->user->name, 0, 1)) }}
    </div>

    <div class="flex-1 min-w-0">
        <div class="flex items-baseline gap-2 mb-0.5">
            <span class="font-semibold text-sm text-gray-900 dark:text-white">{{ $message->user->name }}</span>
            <span class="text-xs text-gray-400">{{ $message->created_at->format('h:i A') }}</span>
        </div>

        {{-- Message body (rendered from Tiptap JSON) --}}
        <div class="prose prose-sm dark:prose-invert max-w-none message-body text-gray-700 dark:text-gray-300"
             data-json="{{ json_encode($message->body) }}">
            {{-- Rendered client-side by messaging.js --}}
        </div>

        {{-- Ticket mention cards --}}
        @if($message->mentioned_tickets)
        <div class="flex flex-col gap-1.5 mt-2">
            @foreach($message->mentioned_tickets as $ticketId)
            @php $t = $project->tickets()->with('column:id,name')->find($ticketId); @endphp
            @if($t)
            <a href="{{ route('tickets.show', [$project, $t]) }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.07] transition no-underline">
                <span class="shrink-0 font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-1.5 py-0.5 rounded">{{ $t->identifier }}</span>
                <span class="flex-1 min-w-0 text-sm text-gray-800 dark:text-gray-200 font-medium truncate">{{ $t->title }}</span>
                @if($t->column)
                <span class="shrink-0 text-xs text-gray-400 dark:text-gray-500">{{ $t->column->name }}</span>
                @endif
                @if($t->priority)
                <span class="shrink-0 text-xs capitalize {{ $t->priority === 'high' ? 'text-red-500' : ($t->priority === 'medium' ? 'text-amber-500' : 'text-blue-400') }}">{{ $t->priority }}</span>
                @endif
            </a>
            @endif
            @endforeach
        </div>
        @endif

        {{-- Thread info --}}
        @if($message->thread && $message->thread->reply_count > 0)
        <button class="thread-open-btn mt-1 flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 hover:underline"
                data-message-id="{{ $message->id }}"
                data-thread-url="{{ route('messages.thread', [$project, $channel ?? $message->channel, $message]) }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z"/></svg>
            {{ $message->thread->reply_count }} {{ Str::plural('reply', $message->thread->reply_count) }}
            <span class="text-gray-400">· Last reply {{ $message->thread->last_reply_at?->diffForHumans() }}</span>
        </button>
        @else
        <button class="thread-open-btn flex mt-1 items-center gap-1.5 text-xs text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition"
                data-message-id="{{ $message->id }}"
                data-thread-url="{{ route('messages.thread', [$project, $channel ?? $message->channel, $message]) }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z"/></svg>
            Reply in thread
        </button>
        @endif
    </div>

    {{-- Per-message actions (right side, appears on hover) --}}
    <div class="flex-shrink-0 flex items-center gap-1.5 mt-1 opacity-0 group-hover:opacity-100 transition-opacity">
        <button class="msg-analyse-btn flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-[11px] font-medium hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
            Analyse
        </button>
        <input type="checkbox" class="message-select w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
    </div>
</div>
