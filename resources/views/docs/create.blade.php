<x-app-layout :project="$project">
    <x-slot name="header">
        <div class="flex items-center gap-2 text-gray-900 dark:text-white">
            <a href="{{ route('docs.index', $project) }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0118 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                </svg>
            </a>
            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="font-semibold">New</span>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto px-8 py-10" x-data="{ docType: '{{ old('type', request('type', 'text')) }}' }">

        {{-- Type picker --}}
        <div class="flex items-center gap-3 mb-8">
            <button type="button" @click="docType='text'"
                    :class="docType==='text' ? 'ring-2 ring-indigo-500 border-indigo-400 dark:border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-white/[0.1] hover:border-gray-300'"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl border bg-white dark:bg-white/[0.03] transition text-left w-44">
                <svg class="w-5 h-5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Document</p>
                    <p class="text-[11px] text-gray-400">Rich text</p>
                </div>
            </button>
            <button type="button" @click="docType='whiteboard'"
                    :class="docType==='whiteboard' ? 'ring-2 ring-violet-500 border-violet-400 dark:border-violet-500 bg-violet-50 dark:bg-violet-900/20' : 'border-gray-200 dark:border-white/[0.1] hover:border-gray-300'"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl border bg-white dark:bg-white/[0.03] transition text-left w-44">
                <svg class="w-5 h-5 text-violet-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Whiteboard</p>
                    <p class="text-[11px] text-gray-400">Live canvas</p>
                </div>
            </button>
        </div>

        <form method="POST" action="{{ route('docs.store', $project) }}" id="doc-form">
            @csrf
            <input type="hidden" name="type" id="doc-type-input" x-bind:value="docType">

            <div class="mb-4">
                <input type="text" name="title" id="doc-title"
                       value="{{ old('title') }}"
                       :placeholder="docType === 'whiteboard' ? 'Whiteboard title…' : 'Document title…'"
                       autofocus required
                       class="w-full text-3xl font-bold bg-transparent border-none outline-none text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-600 focus:ring-0">
            </div>

            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100 dark:border-white/[0.06]">
                <select name="folder_id" id="doc-folder"
                        class="text-sm bg-gray-50 dark:bg-white/[0.04] border border-gray-200 dark:border-white/[0.1] rounded-lg px-2 py-1 text-gray-600 dark:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">No folder</option>
                    @foreach($folders as $folder)
                    <option value="{{ $folder->id }}" {{ request('folder') == $folder->id ? 'selected' : '' }}>
                        {{ $folder->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Text editor (hidden when whiteboard type) --}}
            <input type="hidden" name="body" id="doc-body-input">
            <div id="doc-editor" x-show="docType === 'text'"
                 class="min-h-[300px] prose prose-gray dark:prose-invert max-w-none focus-within:outline-none">
            </div>

            {{-- Whiteboard placeholder (shown when whiteboard type) --}}
            <div x-show="docType === 'whiteboard'"
                 class="flex flex-col items-center justify-center h-64 rounded-xl border-2 border-dashed border-violet-200 dark:border-violet-800/50 bg-violet-50 dark:bg-violet-900/10">
                <svg class="w-10 h-10 text-violet-400 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/>
                </svg>
                <p class="text-sm text-violet-600 dark:text-violet-400 font-medium">Excalidraw canvas opens after creation</p>
                <p class="text-xs text-violet-400 dark:text-violet-500 mt-1">Shared in real-time with your team</p>
            </div>

            <div class="mt-8 flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-white/[0.06]">
                <button type="submit"
                        :class="docType === 'whiteboard' ? 'bg-violet-600 hover:bg-violet-700' : 'bg-indigo-600 hover:bg-indigo-700'"
                        class="px-5 py-2 rounded-lg text-white text-sm font-medium transition">
                    <span x-text="docType === 'whiteboard' ? 'Create whiteboard' : 'Save document'"></span>
                </button>
                <a href="{{ route('docs.index', $project) }}"
                   class="px-5 py-2 rounded-lg border border-gray-200 dark:border-white/[0.1] text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/[0.04] transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    @vite('resources/js/docs.js')
    <script>
        window.__docEditorMode = 'create';
        window.__docInitialContent = null;
    </script>
</x-app-layout>
