<x-app-layout :project="$project">
    <x-slot name="header">
        <div class="flex items-center gap-2 text-gray-900 dark:text-white">
            <span class="text-gray-400 font-medium text-lg">#</span>
            <span class="font-semibold">{{ $channel->name }}</span>
            @if($channel->description)
            <span class="text-gray-400 text-sm font-normal ml-2 border-l border-gray-200 dark:border-white/[0.1] pl-2">{{ $channel->description }}</span>
            @endif
        </div>
        {{-- Huddle button --}}
        <button id="huddle-btn"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 text-sm font-medium hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition border border-emerald-200 dark:border-emerald-800/50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
            Huddle
        </button>
    </x-slot>

    {{-- Huddle UI (hidden by default) --}}
    <div id="huddle-panel" class="hidden fixed bottom-4 right-4 z-50 bg-gray-900 dark:bg-gray-800 text-white rounded-2xl shadow-2xl p-4 w-72">
        <div class="flex items-center justify-between mb-3">
            <span class="font-semibold text-sm">Huddle</span>
            <button id="huddle-end" class="text-red-400 hover:text-red-300 text-xs font-medium">End huddle</button>
        </div>
        <div id="huddle-participants" class="space-y-2 text-sm text-gray-300 mb-3">
            <p class="text-gray-500 text-xs">Connecting…</p>
        </div>
        <div class="flex gap-2">
            <button id="huddle-mute" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/></svg>
                Mute
            </button>
        </div>
    </div>

    {{-- Thread sidebar --}}
    <div id="thread-panel" class="hidden fixed top-0 right-0 h-full w-80 bg-white dark:bg-[#111114] border-l border-gray-200 dark:border-white/[0.06] flex flex-col shadow-2xl z-40">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-white/[0.06]">
            <span class="font-semibold text-sm text-gray-900 dark:text-white">Thread</span>
            <button id="thread-close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="thread-messages" class="flex-1 overflow-y-auto p-4 space-y-3 text-sm"></div>
        <div class="border-t border-gray-200 dark:border-white/[0.06] p-3">
            <div id="thread-editor" class="bg-gray-50 dark:bg-white/[0.04] rounded-xl border border-gray-200 dark:border-white/[0.1] p-3 min-h-[60px] focus-within:ring-2 focus-within:ring-indigo-500 text-sm text-gray-800 dark:text-gray-200"></div>
            <button id="thread-send" data-parent-id=""
                    class="mt-2 w-full px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                Reply
            </button>
        </div>
    </div>

    {{-- Main message area --}}
    <div class="flex flex-col h-full" id="channel-root"
         data-channel-id="{{ $channel->id }}"
         data-project-id="{{ $project->id }}"
         data-user-id="{{ auth()->id() }}"
         data-user-name="{{ auth()->user()->name }}"
         data-send-url="{{ route('messages.store', [$project, $channel]) }}"
         data-ai-url="{{ route('ai.summarise', $project) }}"
         data-tickets-create-url="{{ route('ai.ticket.create', $project) }}"
         data-ai-doc-url="{{ route('ai.doc.create', $project) }}"
         data-docs-search-url="{{ route('docs.search', $project) }}"
         data-docs-store-url="{{ route('docs.store', $project) }}"
         data-huddle-start-url="{{ route('huddle.start', $project) }}"
         data-huddle-signal-url="{{ route('huddle.signal', $project) }}"
         data-tickets-json="{{ $project->tickets()->with('column:id,name')->select(['id','identifier','title','priority','type','column_id'])->get()->map(fn($t) => ['id'=>$t->id,'identifier'=>$t->identifier,'title'=>$t->title,'priority'=>$t->priority,'type'=>$t->type,'status'=>$t->column?->name])->toJson() }}">

        {{-- Messages list --}}
        <div id="messages-list" class="flex-1 overflow-y-auto px-6 py-4 space-y-1 flex flex-col-reverse">
            @forelse($messages as $message)
                @include('messaging.partials.message', ['message' => $message])
            @empty
            <div class="flex flex-col items-center justify-center h-full text-center py-16">
                <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center mb-3">
                    <span class="text-xl font-bold text-indigo-500">#</span>
                </div>
                <p class="font-semibold text-gray-900 dark:text-white">This is the beginning of #{{ $channel->name }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Send a message to get things started.</p>
            </div>
            @endforelse
        </div>

        {{-- AI suggestion bar (hidden until relevant) --}}
        <div id="ai-suggestion" class="hidden mx-6 mb-2 p-3 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800/50 rounded-xl text-sm">
            <p class="font-medium text-indigo-800 dark:text-indigo-300 mb-1.5">AI Analysis</p>
            <div id="ai-summary" class="text-gray-700 dark:text-gray-300 text-xs mb-2"></div>
            <div id="ai-actions" class="space-y-1 mb-2"></div>
            <div id="ai-ticket-suggestions" class="space-y-1"></div>
            <button id="ai-dismiss" class="mt-2 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">Dismiss</button>
        </div>

        {{-- Message compose area --}}
        <div class="border-t border-gray-200 dark:border-white/[0.06] px-4 py-3 bg-white dark:bg-[#111114]">
            {{-- Selected message actions --}}
            <div id="selection-toolbar" class="hidden mb-2 flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400" id="selected-count">0 selected</span>
                <button id="ai-summarise-btn"
                        class="flex items-center gap-1.5 px-3 py-1 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    Analyse with AI
                </button>
                <button id="clear-selection" class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">Clear</button>
            </div>

            <div class="bg-gray-50 dark:bg-white/[0.04] rounded-xl border border-gray-200 dark:border-white/[0.1] focus-within:ring-2 focus-within:ring-indigo-500 transition cursor-text"
                 onclick="this.querySelector('.ProseMirror')?.focus()">
                <div id="message-editor" class="min-h-[60px] text-sm text-gray-800 dark:text-gray-200"></div>
                <div class="flex items-center justify-between px-3 pb-2">
                    <div class="flex items-center gap-1 text-gray-400">
                        <button class="p-1 rounded hover:bg-gray-200 dark:hover:bg-white/[0.08] transition" title="Bold" data-format="bold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.744h-.753v8.25h7.125a4.125 4.125 0 000-8.25H6.75zM6 16.5h6.75a4.125 4.125 0 010 8.25H6V16.5z"/></svg>
                        </button>
                        <button class="p-1 rounded hover:bg-gray-200 dark:hover:bg-white/[0.08] transition" title="Italic" data-format="italic">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.248 20.246H9.05m0 0h3.696m-3.696 0 5.893-16.502m0 0h-3.697m3.697 0H16.75"/></svg>
                        </button>
                        <button class="p-1 rounded hover:bg-gray-200 dark:hover:bg-white/[0.08] transition" title="Code" data-format="code">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9.75L16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z"/></svg>
                        </button>
                    </div>
                    <button id="send-message"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 transition disabled:opacity-50">
                        <svg class="w-3.5 h-3.5 rotate-45" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                        Send
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    @vite('resources/js/messaging.js')
    @endpush
</x-app-layout>
