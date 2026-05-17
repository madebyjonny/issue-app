<x-app-layout :project="$project">
    <x-slot name="header">
        <div class="flex items-center gap-2 text-gray-900 dark:text-white min-w-0">
            <a href="{{ route('docs.show', [$project, $doc]) }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                </svg>
            </a>
            <svg class="w-3 h-3 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="font-semibold truncate">Editing — {{ $doc->title }}</span>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto px-8 py-10">
        <form method="POST" action="{{ route('docs.update', [$project, $doc]) }}" id="doc-form">
            @csrf @method('PUT')

            <div class="mb-4">
                <input type="text" name="title" id="doc-title" value="{{ old('title', $doc->title) }}"
                       placeholder="Document title…" required
                       class="w-full text-3xl font-bold bg-transparent border-none outline-none text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-600 focus:ring-0">
            </div>

            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100 dark:border-white/[0.06]">
                <select name="folder_id" id="doc-folder"
                        class="text-sm bg-gray-50 dark:bg-white/[0.04] border border-gray-200 dark:border-white/[0.1] rounded-lg px-2 py-1 text-gray-600 dark:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">No folder</option>
                    @foreach($folders as $folder)
                    <option value="{{ $folder->id }}" {{ old('folder_id', $doc->folder_id) == $folder->id ? 'selected' : '' }}>
                        {{ $folder->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <input type="hidden" name="body" id="doc-body-input">

            <div id="doc-editor"
                 class="min-h-[400px] prose prose-gray dark:prose-invert max-w-none focus-within:outline-none">
            </div>

            <div class="mt-8 flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-white/[0.06]">
                <button type="submit"
                        class="px-5 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                    Save changes
                </button>
                <a href="{{ route('docs.show', [$project, $doc]) }}"
                   class="px-5 py-2 rounded-lg border border-gray-200 dark:border-white/[0.1] text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/[0.04] transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    @vite('resources/js/docs.js')
    <script>
        window.__docEditorMode = 'edit';
        window.__docInitialContent = {!! json_encode($doc->body) !!};
    </script>
</x-app-layout>
