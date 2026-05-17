<x-app-layout :project="$project">
    <x-slot name="header">
        <div class="flex items-center gap-2 text-gray-900 dark:text-white min-w-0">
            <a href="{{ route('docs.index', $project) }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0118 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                </svg>
            </a>
            <svg class="w-3 h-3 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <svg class="w-4 h-4 text-violet-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/>
            </svg>
            <span class="font-semibold truncate">{{ $doc->title }}</span>
            {{-- Live indicator --}}
            <span id="wb-live-badge" class="hidden flex-shrink-0 flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-[10px] font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Live
            </span>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <form method="POST" action="{{ route('docs.destroy', [$project, $doc]) }}"
                  onsubmit="return confirm('Delete this whiteboard?')">
                @csrf @method('DELETE')
                <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                </button>
            </form>
        </div>
    </x-slot>

    {{-- Full-height whiteboard canvas --}}
    <div id="whiteboard-container"
         class="w-full h-full"
         data-doc-id="{{ $doc->id }}"
         data-user-id="{{ auth()->id() }}"
         data-sync-url="{{ route('whiteboard.sync', [$project, $doc]) }}"
         data-elements="{{ json_encode($doc->body['elements'] ?? []) }}"
         data-theme="{{ request()->cookie('theme', 'light') }}">
    </div>

    @vite('resources/js/whiteboard.js')
</x-app-layout>
