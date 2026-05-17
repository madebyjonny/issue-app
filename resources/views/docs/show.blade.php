<x-app-layout :project="$project">
    <x-slot name="header">
        <div class="flex items-center gap-2 text-gray-900 dark:text-white min-w-0">
            <a href="{{ route('docs.index', $project) }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                </svg>
            </a>
            <svg class="w-3 h-3 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            @if($doc->folder)
            <span class="text-gray-400 text-sm flex-shrink-0">{{ $doc->folder->name }}</span>
            <svg class="w-3 h-3 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            @endif
            <span class="font-semibold truncate">{{ $doc->title }}</span>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <a href="{{ route('docs.edit', [$project, $doc]) }}"
               class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/[0.1] text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/[0.06] transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                Edit
            </a>
            <form method="POST" action="{{ route('docs.destroy', [$project, $doc]) }}"
                  onsubmit="return confirm('Delete this document?')">
                @csrf @method('DELETE')
                <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                </button>
            </form>
        </div>
    </x-slot>

    <div class="flex h-full">
        {{-- Docs sidebar --}}
        <aside class="w-56 flex-shrink-0 border-r border-gray-200 dark:border-white/[0.06] bg-white dark:bg-[#111114] overflow-y-auto p-3 space-y-4">
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z"/>
                </svg>
                <input id="doc-search-input" type="search" placeholder="Search docs…"
                       class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/[0.1] bg-gray-50 dark:bg-white/[0.04] text-[12px] text-gray-800 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div id="doc-search-results" class="hidden space-y-0.5"></div>

            <div>
                <a href="{{ route('docs.create', $project) }}"
                   class="flex items-center gap-1.5 w-full px-1 py-1 rounded text-[11px] text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-50 dark:hover:bg-white/[0.04] transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    New doc
                </a>
            </div>

            @php $unfiled = $project->docs()->whereNull('folder_id')->orderBy('title')->get(); @endphp
            @if($unfiled->isNotEmpty())
            <div class="space-y-0.5">
                <p class="text-[10px] font-semibold uppercase tracking-[0.08em] text-gray-400 px-1 mb-1">Unfiled</p>
                @foreach($unfiled as $d)
                <a href="{{ route('docs.show', [$project, $d]) }}"
                   class="flex items-center gap-1.5 px-1 py-1 rounded text-[12px] transition
                          {{ $doc->id === $d->id ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/[0.06]' }}">
                    <svg class="w-3.5 h-3.5 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    <span class="truncate">{{ $d->title }}</span>
                </a>
                @endforeach
            </div>
            @endif

            @if($folders->isNotEmpty())
            <div class="space-y-0.5">
                @include('docs._tree', ['folders' => $folders, 'project' => $project, 'activeDoc' => $doc])
            </div>
            @endif
        </aside>

        {{-- Doc content --}}
        <main class="flex-1 overflow-y-auto">
            <div class="max-w-3xl mx-auto px-8 py-10">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $doc->title }}</h1>
                <div class="flex items-center gap-3 text-xs text-gray-400 mb-8 pb-6 border-b border-gray-100 dark:border-white/[0.06]">
                    <span>By <span class="text-gray-600 dark:text-gray-300">{{ $doc->author?->name }}</span></span>
                    <span>·</span>
                    <span>Updated {{ $doc->updated_at->diffForHumans() }}</span>
                    @if($doc->folder)
                    <span>·</span>
                    <span class="text-amber-500">{{ $doc->folder->name }}</span>
                    @endif
                </div>
                {{-- Body rendered by docs.js --}}
                <div id="doc-body" class="prose prose-gray dark:prose-invert max-w-none"
                     data-json="{{ json_encode($doc->body) }}"></div>
            </div>
        </main>
    </div>

    @vite('resources/js/docs.js')
    <script>
        window.__docsSearchUrl = "{{ route('docs.search', $project) }}";
    </script>
</x-app-layout>
