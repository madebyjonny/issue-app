<x-app-layout :project="$project">
    <x-slot name="header">
        <div class="flex items-center gap-2 text-gray-900 dark:text-white">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
            </svg>
            <span class="font-semibold">Docs</span>
        </div>
        <a href="{{ route('docs.create', $project) }}"
           class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New doc
        </a>
    </x-slot>

    <div class="flex h-full">
        {{-- Docs sidebar --}}
        <aside class="w-56 flex-shrink-0 border-r border-gray-200 dark:border-white/[0.06] bg-white dark:bg-[#111114] flex flex-col overflow-y-auto p-3 space-y-4">
            {{-- Search --}}
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z"/>
                </svg>
                <input id="doc-search-input" type="search" placeholder="Search docs…"
                       class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/[0.1] bg-gray-50 dark:bg-white/[0.04] text-[12px] text-gray-800 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            {{-- Search results (hidden until typing) --}}
            <div id="doc-search-results" class="hidden space-y-0.5"></div>

            {{-- New folder button --}}
            <div>
                <button onclick="document.getElementById('new-folder-modal').classList.remove('hidden')"
                        class="flex items-center gap-1.5 w-full px-1 py-1 rounded text-[11px] text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/[0.04] transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    New folder
                </button>
            </div>

            {{-- Unfiled docs --}}
            @php $unfiled = $project->docs()->whereNull('folder_id')->orderBy('title')->get(); @endphp
            @if($unfiled->isNotEmpty())
            <div class="space-y-0.5">
                <p class="text-[10px] font-semibold uppercase tracking-[0.08em] text-gray-400 px-1 mb-1">Unfiled</p>
                @foreach($unfiled as $d)
                <a href="{{ route('docs.show', [$project, $d]) }}"
                   class="flex items-center gap-1.5 px-1 py-1 rounded text-[12px] text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/[0.06] transition">
                    <svg class="w-3.5 h-3.5 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    <span class="truncate">{{ $d->title }}</span>
                </a>
                @endforeach
            </div>
            @endif

            {{-- Folder tree --}}
            @if($folders->isNotEmpty())
            <div class="space-y-0.5">
                @include('docs._tree', ['folders' => $folders, 'project' => $project])
            </div>
            @endif
        </aside>

        {{-- Main content --}}
        <main class="flex-1 overflow-y-auto">
            {{-- Hero --}}
            <div class="max-w-3xl mx-auto px-8 py-12">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Documentation</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">Knowledge base for <span class="font-medium text-gray-700 dark:text-gray-300">{{ $project->name }}</span></p>

                @if($recent->isNotEmpty())
                <div>
                    <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Recently updated</h2>
                    <div class="space-y-2">
                        @foreach($recent as $doc)
                        <a href="{{ route('docs.show', [$project, $doc]) }}"
                           class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-white/[0.03] hover:bg-gray-50 dark:hover:bg-white/[0.06] transition group">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                                        {{ $doc->type === 'whiteboard' ? 'bg-violet-50 dark:bg-violet-900/30' : 'bg-indigo-50 dark:bg-indigo-900/30' }}">
                                @if($doc->type === 'whiteboard')
                                <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/>
                                </svg>
                                @else
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-sm text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 truncate transition">{{ $doc->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    @if($doc->folder) <span class="text-amber-500">{{ $doc->folder->name }}</span> · @endif
                                    Updated {{ $doc->updated_at->diffForHumans() }}
                                    @if($doc->editor) by {{ $doc->editor->name }} @endif
                                </p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                        </svg>
                    </div>
                    <p class="font-semibold text-gray-900 dark:text-white mb-1">No docs yet</p>
                    <p class="text-sm text-gray-400 mb-4">Create your first document to start building the knowledge base.</p>
                    <a href="{{ route('docs.create', $project) }}"
                       class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                        Create first doc
                    </a>
                </div>
                @endif
            </div>
        </main>
    </div>

    {{-- New folder modal --}}
    <div id="new-folder-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-[#1c1c20] rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">New folder</h3>
            <form method="POST" action="{{ route('docs.folders.store', $project) }}">
                @csrf
                <input type="text" name="name" placeholder="Folder name" autofocus required
                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-white/[0.1] bg-gray-50 dark:bg-white/[0.04] text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-3">
                <div class="flex gap-2">
                    <button type="submit"
                            class="flex-1 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                        Create
                    </button>
                    <button type="button" onclick="document.getElementById('new-folder-modal').classList.add('hidden')"
                            class="px-4 py-2 rounded-lg border border-gray-200 dark:border-white/[0.1] text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/[0.04] transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    @vite('resources/js/docs.js')
    <script>
        window.__docsSearchUrl = "{{ route('docs.search', $project) }}";
    </script>
</x-app-layout>
