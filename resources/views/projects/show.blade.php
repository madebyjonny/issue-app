<x-app-layout>
    <x-slot name="project">{{ $project }}</x-slot>

    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="text-[15px] font-semibold text-white">Settings</h2>
        </div>
        <a href="{{ route('projects.edit', $project) }}" class="btn-ghost flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
            Edit Project
        </a>
    </x-slot>

    <div class="p-6 py-10 max-w-3xl mx-auto space-y-6">
        {{-- General Info --}}
        <div class="flex gap-8">
            <div class="w-48 flex-shrink-0 pt-5">
                <p class="section-title">General</p>
                <p class="section-desc">Project details and identification.</p>
            </div>
            <div class="flex-1 card-padded space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] text-gray-500 mb-1">Name</label>
                        <p class="text-[13px] text-white">{{ $project->name }}</p>
                    </div>
                    <div>
                        <label class="block text-[12px] text-gray-500 mb-1">Key</label>
                        <p class="text-[13px] text-white font-mono">{{ $project->key }}</p>
                    </div>
                </div>
                @if($project->description)
                <div>
                    <label class="block text-[12px] text-gray-500 mb-1">Description</label>
                    <p class="text-[13px] text-gray-300">{{ $project->description }}</p>
                </div>
                @endif
                <div>
                    <label class="block text-[12px] text-gray-500 mb-1">Owner</label>
                    <p class="text-[13px] text-gray-300">{{ $project->owner->name }}</p>
                </div>
            </div>
        </div>

        {{-- Columns --}}
        <div class="flex gap-8">
            <div class="w-48 flex-shrink-0 pt-5">
                <p class="section-title">Columns</p>
                <p class="section-desc">Board workflow stages.</p>
            </div>
            <div class="flex-1 card overflow-hidden">
                <div class="divide-y divide-white/[0.04]">
                    @foreach($project->columns as $column)
                        <div class="flex items-center gap-3 px-5 py-3 group">
                            <form method="POST" action="{{ route('columns.update', [$project, $column]) }}" class="flex items-center gap-3 flex-1">
                                @csrf
                                @method('PUT')
                                <div class="w-2 h-2 rounded-full bg-gray-600 flex-shrink-0"></div>
                                <input type="text" name="name" value="{{ $column->name }}" class="flex-1 bg-transparent border-0 text-[13px] text-gray-300 p-0 focus:ring-0 focus:text-white" />
                                <button type="submit" class="text-[12px] text-gray-600 hover:text-white transition opacity-0 group-hover:opacity-100">Save</button>
                            </form>
                            <form method="POST" action="{{ route('columns.destroy', [$project, $column]) }}" class="opacity-0 group-hover:opacity-100 transition">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[12px] text-red-500/60 hover:text-red-400 transition" onclick="return confirm('Delete this column?')">Remove</button>
                            </form>
                        </div>
                    @endforeach
                </div>
                <form method="POST" action="{{ route('columns.store', $project) }}" class="flex items-center gap-3 px-5 py-3 border-t border-white/[0.04]">
                    @csrf
                    <div class="w-2 h-2 rounded-full bg-gray-700/50 flex-shrink-0"></div>
                    <input type="text" name="name" placeholder="Add column..." required class="flex-1 bg-transparent border-0 text-[13px] text-gray-300 placeholder-gray-600 p-0 focus:ring-0" />
                    <button type="submit" class="text-[12px] text-accent hover:text-accent-hover transition">Add</button>
                </form>
            </div>
        </div>

        {{-- Sprints --}}
        <div class="flex gap-8">
            <div class="w-48 flex-shrink-0 pt-5">
                <p class="section-title">Sprints</p>
                <p class="section-desc">Iteration management.</p>
            </div>
            <div class="flex-1 space-y-2">
                @foreach($project->sprints as $sprint)
                    <div class="card flex items-center gap-3 px-5 py-3 group">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-[13px] text-white">{{ $sprint->name }}</span>
                                @if($sprint->is_active)
                                    <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-emerald-500/15 text-emerald-400 rounded border border-emerald-500/20">Active</span>
                                @endif
                            </div>
                            @if($sprint->start_date && $sprint->end_date)
                                <span class="text-[12px] text-gray-600">{{ $sprint->start_date->format('M j') }} &ndash; {{ $sprint->end_date->format('M j, Y') }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition">
                            @if(!$sprint->is_active)
                                <form method="POST" action="{{ route('sprints.update', [$project, $sprint]) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="is_active" value="1">
                                    <button type="submit" class="text-[12px] text-emerald-400/70 hover:text-emerald-400 transition">Activate</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('sprints.destroy', [$project, $sprint]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[12px] text-red-500/60 hover:text-red-400 transition" onclick="return confirm('Delete this sprint?')">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
                <form method="POST" action="{{ route('sprints.store', $project) }}" class="card flex items-center gap-3 px-5 py-3">
                    @csrf
                    <input type="text" name="name" placeholder="Sprint name..." required class="flex-1 bg-transparent border-0 text-[13px] text-gray-300 placeholder-gray-600 p-0 focus:ring-0" />
                    <input type="date" name="start_date" class="input-dark text-[12px] px-2 py-1" />
                    <input type="date" name="end_date" class="input-dark text-[12px] px-2 py-1" />
                    <button type="submit" class="text-[12px] text-accent hover:text-accent-hover transition whitespace-nowrap">Add Sprint</button>
                </form>
            </div>
        </div>

        {{-- Members --}}
        <div class="flex gap-8">
            <div class="w-48 flex-shrink-0 pt-5">
                <p class="section-title">Members</p>
                <p class="section-desc">People on this project.</p>
            </div>
            <div class="flex-1 card overflow-hidden divide-y divide-white/[0.04]">
                @foreach($project->members as $member)
                    <div class="flex items-center gap-3 px-5 py-3">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-accent/30 to-accent/10 flex items-center justify-center text-[11px] font-semibold text-accent ring-1 ring-white/[0.06]">
                            {{ strtoupper(substr($member->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-[13px] text-gray-300">{{ $member->name }}</span>
                        </div>
                        <span class="text-[12px] text-gray-600 capitalize">{{ $member->pivot->role }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Danger Zone --}}
        <div class="flex gap-8">
            <div class="w-48 flex-shrink-0 pt-5">
                <p class="section-title text-red-400/80">Danger Zone</p>
                <p class="section-desc">Irreversible actions.</p>
            </div>
            <div class="flex-1 card border-red-500/10 p-5">
                <p class="text-[13px] text-gray-500 mb-4">Deleting this project will permanently remove all tickets, columns, and sprints. This cannot be undone.</p>
                <form method="POST" action="{{ route('projects.destroy', $project) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3.5 py-2 bg-red-500/10 border border-red-500/15 text-red-400 text-[13px] font-medium rounded-lg hover:bg-red-500/20 transition" onclick="return confirm('Are you sure you want to delete this project? This cannot be undone.')">
                        Delete Project
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
