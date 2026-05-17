{{-- Recursive folder+doc tree. Call with $folders (top-level), $project, and optionally $activeDoc --}}
@foreach($folders as $folder)
<div x-data="{ open: true }" class="space-y-0.5">
    <div class="flex items-center gap-1 group">
        <button @click="open = !open"
                class="flex items-center gap-1.5 flex-1 min-w-0 px-1 py-1 rounded hover:bg-gray-100 dark:hover:bg-white/[0.06] text-left transition">
            <svg class="w-3 h-3 text-gray-400 transition-transform flex-shrink-0" :class="open ? 'rotate-90' : ''"
                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            <svg class="w-3.5 h-3.5 text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
            </svg>
            <span class="truncate text-[12px] font-medium text-gray-700 dark:text-gray-300">{{ $folder->name }}</span>
        </button>
        <div class="hidden group-hover:flex items-center gap-0.5 flex-shrink-0 pr-1">
            {{-- New doc in folder --}}
            <a href="{{ route('docs.create', $project) }}?folder={{ $folder->id }}"
               class="p-0.5 rounded text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition" title="New doc">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            </a>
        </div>
    </div>

    <div x-show="open" class="ml-4 space-y-0.5 border-l border-gray-100 dark:border-white/[0.06] pl-2">
        {{-- Docs directly in this folder --}}
        @foreach($folder->docs as $d)
        <a href="{{ route('docs.show', [$project, $d]) }}"
           class="flex items-center gap-1.5 px-1 py-1 rounded text-[12px] transition
                  {{ isset($activeDoc) && $activeDoc->id === $d->id
                     ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium'
                     : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/[0.06]' }}">
            @if($d->type === 'whiteboard')
            <svg class="w-3.5 h-3.5 flex-shrink-0 text-violet-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/>
            </svg>
            @else
            <svg class="w-3.5 h-3.5 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
            @endif
            <span class="truncate">{{ $d->title }}</span>
        </a>
        @endforeach

        {{-- Child folders --}}
        @if($folder->children->isNotEmpty())
            @include('docs._tree', ['folders' => $folder->children, 'project' => $project, 'activeDoc' => $activeDoc ?? null])
        @endif
    </div>
</div>
@endforeach
