<x-app-layout :project="$project">
    <x-slot name="header">
        <div class="flex items-center gap-3 text-gray-900 dark:text-white">
            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-[11px] font-semibold text-white">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <span class="font-semibold">{{ $user->name }}</span>
        </div>
    </x-slot>

    <div class="flex flex-col h-full" id="dm-root"
         data-conversation-id="{{ $conversation->id }}"
         data-project-id="{{ $project->id }}"
         data-user-id="{{ auth()->id() }}"
         data-user-name="{{ auth()->user()->name }}"
         data-send-url="{{ route('dm.store', [$project, $user]) }}">

        {{-- Messages list --}}
        <div id="dm-messages-list" class="flex-1 overflow-y-auto px-6 py-4 space-y-1 flex flex-col-reverse">
            @forelse($messages as $message)
            @php $isMine = $message->user_id === auth()->id(); @endphp
            <div class="flex gap-3 px-2 py-1 rounded-lg hover:bg-gray-50 dark:hover:bg-white/[0.03] transition {{ $isMine ? 'flex-row-reverse' : '' }}">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-[11px] font-semibold text-white flex-shrink-0">
                    {{ strtoupper(substr($message->user->name, 0, 1)) }}
                </div>
                <div class="max-w-[70%]">
                    <div class="flex items-baseline gap-2 mb-0.5 {{ $isMine ? 'flex-row-reverse' : '' }}">
                        <span class="font-semibold text-sm text-gray-900 dark:text-white">{{ $message->user->name }}</span>
                        <span class="text-xs text-gray-400">{{ $message->created_at->format('h:i A') }}</span>
                    </div>
                    <div class="prose prose-sm dark:prose-invert max-w-none message-body
                                {{ $isMine ? 'bg-indigo-600 text-white rounded-2xl rounded-tr-sm px-3 py-2' : 'text-gray-700 dark:text-gray-300' }}"
                         data-json="{{ json_encode($message->body) }}">
                        {{-- Rendered client-side --}}
                    </div>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center h-full text-center py-16">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center mb-3">
                    <span class="text-lg font-bold text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                </div>
                <p class="font-semibold text-gray-900 dark:text-white">Start a conversation with {{ $user->name }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Messages are private between you.</p>
            </div>
            @endforelse
        </div>

        {{-- Compose --}}
        <div class="border-t border-gray-200 dark:border-white/[0.06] px-4 py-3 bg-white dark:bg-[#111114]">
            <div class="bg-gray-50 dark:bg-white/[0.04] rounded-xl border border-gray-200 dark:border-white/[0.1] focus-within:ring-2 focus-within:ring-indigo-500 transition">
                <div id="dm-editor" class="px-3 pt-3 pb-1 min-h-[60px] text-sm text-gray-800 dark:text-gray-200"></div>
                <div class="flex items-center justify-end px-3 pb-2">
                    <button id="dm-send"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 transition disabled:opacity-50">
                        <svg class="w-3.5 h-3.5 rotate-45" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                        Send
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    @vite('resources/js/dm.js')
    @endpush
</x-app-layout>
