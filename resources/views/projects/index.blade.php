<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[15px] font-semibold text-gray-900">Projects</h2>
        <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-accent hover:bg-accent-hover text-white text-[13px] font-medium rounded-lg shadow-sm shadow-accent/10 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New Project
        </a>
    </x-slot>

    <div class="p-6">
        @if($projects->isEmpty())
            <div class="text-center py-24">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-white/[0.06] border border-gray-200 dark:border-white/[0.08] flex items-center justify-center mx-auto mb-5">
                    <svg class="w-7 h-7 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25a2.25 2.25 0 012.25 2.25v2.25a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 8.25V6z"/></svg>
                </div>
                <p class="text-gray-500 text-sm mb-1">No projects yet</p>
                <p class="text-gray-600 text-[13px] mb-6">Create your first project to get started</p>
                <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-accent hover:bg-accent-hover text-white text-[13px] font-medium rounded-lg shadow-sm shadow-accent/10 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    New Project
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach($projects as $project)
                    <a href="{{ route('projects.board', $project) }}" class="group block p-4 card hover:border-gray-300 dark:hover:border-white/[0.15] transition-all duration-200">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-white/[0.08] flex items-center justify-center text-[11px] font-bold text-gray-500 dark:text-gray-300 ring-1 ring-gray-200 dark:ring-white/[0.08] flex-shrink-0">
                                {{ $project->key }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-[13px] font-semibold text-gray-900 dark:text-gray-100 group-hover:text-gray-700 dark:group-hover:text-white transition truncate">{{ $project->name }}</h3>
                                @if($project->description)
                                    <p class="text-[12px] text-gray-600 dark:text-gray-400 line-clamp-1 mt-0.5">{{ $project->description }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-4 text-[12px] text-gray-600 dark:text-gray-500">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/></svg>
                                {{ $project->tickets_count }} {{ Str::plural('ticket', $project->tickets_count) }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
