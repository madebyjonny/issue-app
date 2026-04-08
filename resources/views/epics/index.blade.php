<x-app-layout :project="$project">

    <x-slot name="header">
        <div class="flex items-center gap-2.5">
            <h2 class="text-[15px] font-semibold text-white">Epics</h2>
            <span class="text-[11px] text-gray-500 tabular-nums">{{ $project->epics->count() }}</span>
        </div>
        <button onclick="document.getElementById('create-epic-dialog').showModal()" class="inline-flex items-center gap-1 px-2.5 py-1 bg-accent hover:bg-accent-hover text-white text-[12px] font-medium rounded-md transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New Epic
        </button>
    </x-slot>

    <div class="p-6 py-10 max-w-3xl mx-auto">
        @if($project->epics->isEmpty())
            <div class="text-center py-24">
                <div class="w-14 h-14 rounded-2xl bg-surface-300/60 border border-white/[0.06] flex items-center justify-center mx-auto mb-5">
                    <svg class="w-7 h-7 text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
                </div>
                <p class="text-gray-500 text-sm mb-1">No epics yet</p>
                <p class="text-gray-600 text-[13px] mb-6">Epics group related issues under a shared goal</p>
                <button onclick="document.getElementById('create-epic-dialog').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-accent hover:bg-accent-hover text-white text-[13px] font-medium rounded-lg shadow-sm shadow-accent/10 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    New Epic
                </button>
            </div>
        @else
            <div class="space-y-2">
                @foreach($project->epics as $epic)
                    <a href="{{ route('epics.show', [$project, $epic]) }}" class="group block card hover:border-white/[0.1] transition-all duration-200">
                        <div class="flex items-center gap-4 px-5 py-4">
                            <div class="w-1 h-8 rounded-full flex-shrink-0" style="background: {{ $epic->color }}"></div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-[13px] font-semibold text-white group-hover:text-gray-100 transition truncate">{{ $epic->name }}</h3>
                                @if($epic->description)
                                    <p class="text-[12px] text-gray-500 line-clamp-1 mt-0.5">{{ $epic->description }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-4 flex-shrink-0">
                                @php
                                    $total = $epic->tickets->count();
                                    $doneCol = $project->columns->last();
                                    $done = $doneCol ? $epic->tickets->where('column_id', $doneCol->id)->count() : 0;
                                    $pct = $total > 0 ? round(($done / $total) * 100) : 0;
                                @endphp
                                <div class="flex items-center gap-2.5">
                                    <div class="w-24 h-1.5 bg-white/[0.06] rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-300" style="width: {{ $pct }}%; background: {{ $epic->color }}"></div>
                                    </div>
                                    <span class="text-[11px] text-gray-500 tabular-nums w-8 text-right">{{ $pct }}%</span>
                                </div>
                                <span class="text-[11px] text-gray-500 tabular-nums">{{ $total }} {{ Str::plural('issue', $total) }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Create Epic Dialog --}}
    <dialog id="create-epic-dialog" class="backdrop:bg-black/80 backdrop:backdrop-blur-sm bg-transparent p-0 m-0 fixed inset-0 w-full h-full max-w-none max-h-none">
        <div class="fixed inset-0 flex items-start justify-center pt-[10vh] p-4 pointer-events-none">
            <div class="bg-[#1a1a1e] border border-white/[0.12] rounded-xl shadow-2xl shadow-black/60 w-full max-w-lg pointer-events-auto">
                <form method="POST" action="{{ route('epics.store', $project) }}" class="flex flex-col">
                    @csrf
                    <div class="px-5 pt-5 pb-4 space-y-4">
                        <input type="text" name="name" placeholder="Epic name" required
                               class="w-full bg-transparent border-0 text-[18px] font-semibold text-white placeholder-gray-500 focus:ring-0 p-0 focus:outline-none" />
                        <textarea name="description" rows="3" placeholder="Describe the goal of this epic..."
                                  class="w-full bg-transparent border-0 text-[14px] text-gray-300 placeholder-gray-500 focus:ring-0 p-0 resize-none focus:outline-none leading-relaxed"></textarea>
                        <div class="flex items-center gap-3 pt-3 border-t border-white/[0.08]">
                            <label class="text-[12px] text-gray-400">Color</label>
                            <div class="flex items-center gap-1.5">
                                @foreach(['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#6366f1'] as $color)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="color" value="{{ $color }}" class="sr-only peer" {{ $loop->first ? 'checked' : '' }}>
                                        <div class="w-5 h-5 rounded-full border-2 border-transparent peer-checked:border-white/60 transition" style="background: {{ $color }}"></div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 px-4 py-3 border-t border-white/[0.08] bg-[#151518] rounded-b-xl">
                        <button type="button" onclick="document.getElementById('create-epic-dialog').close()" class="px-3 py-1.5 text-[12px] font-medium text-gray-400 hover:text-white transition">Cancel</button>
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-accent hover:bg-accent-hover text-white text-[12px] font-medium rounded-md transition">Create epic</button>
                    </div>
                </form>
            </div>
        </div>
    </dialog>
</x-app-layout>
