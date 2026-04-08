<x-app-layout :project="$project">

    <x-slot name="header">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('epics.index', $project) }}" class="text-gray-500 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            </a>
            <div class="w-1.5 h-5 rounded-full flex-shrink-0" style="background: {{ $epic->color }}"></div>
            <h2 class="text-[15px] font-semibold text-white">{{ $epic->name }}</h2>
        </div>
        <div class="flex items-center gap-1.5">
            <button onclick="document.getElementById('edit-epic-dialog').showModal()" class="text-gray-500 hover:text-white transition p-1.5 rounded hover:bg-white/[0.08]" title="Edit epic">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"/></svg>
            </button>
            <button onclick="document.getElementById('create-ticket-dialog').showModal()" class="inline-flex items-center gap-1 px-2.5 py-1 bg-accent hover:bg-accent-hover text-white text-[12px] font-medium rounded-md transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Issue
            </button>
        </div>
    </x-slot>

    <div class="p-6 py-10 max-w-3xl mx-auto">
        {{-- Epic overview --}}
        <div class="flex gap-8 mb-8">
            <div class="w-48 flex-shrink-0 pt-5">
                <p class="section-title">Overview</p>
                <p class="section-desc">Epic progress and goal.</p>
            </div>
            <div class="flex-1 card-padded space-y-4">
                @if($epic->description)
                    <p class="text-[13px] text-gray-400 leading-relaxed">{{ $epic->description }}</p>
                @endif

                @php
                    $total = $epic->tickets->count();
                    $doneCol = $project->columns->last();
                    $done = $doneCol ? $epic->tickets->where('column_id', $doneCol->id)->count() : 0;
                    $pct = $total > 0 ? round(($done / $total) * 100) : 0;
                @endphp
                <div class="flex items-center gap-4">
                    <div class="flex-1 h-1.5 bg-white/[0.06] rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500" style="width: {{ $pct }}%; background: {{ $epic->color }}"></div>
                    </div>
                    <span class="text-[12px] text-gray-500 tabular-nums flex-shrink-0">{{ $done }}/{{ $total }} done</span>
                </div>
            </div>
        </div>

        {{-- Tickets list --}}
        <div class="flex gap-8">
            <div class="w-48 flex-shrink-0 pt-5">
                <p class="section-title">Issues</p>
                <p class="section-desc">Tickets in this epic.</p>
            </div>
            <div class="flex-1">
                @if($epic->tickets->isEmpty())
                    <div class="card text-center py-12">
                        <p class="text-gray-500 text-sm mb-1">No issues in this epic</p>
                        <p class="text-gray-600 text-[13px] mb-5">Create the first issue to start tracking work</p>
                        <button onclick="document.getElementById('create-ticket-dialog').showModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-accent hover:bg-accent-hover text-white text-[13px] font-medium rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            New Issue
                        </button>
                    </div>
                @else
                    <div class="card overflow-hidden divide-y divide-white/[0.04]">
                        @foreach($epic->tickets as $ticket)
                            <a href="{{ route('tickets.show', [$project, $ticket]) }}" class="group flex items-center gap-3 px-5 py-3 hover:bg-white/[0.04] transition">
                                @php
                                    $priorityColors = [
                                        'urgent' => 'bg-red-500',
                                        'high' => 'bg-orange-500',
                                        'medium' => 'bg-yellow-500',
                                        'low' => 'bg-blue-500',
                                        'none' => 'bg-gray-500',
                                    ];
                                @endphp
                                <span class="w-2 h-2 rounded-full {{ $priorityColors[$ticket->priority] ?? 'bg-gray-500' }} flex-shrink-0"></span>
                                <span class="text-[11px] text-gray-500 font-mono w-16 flex-shrink-0">{{ $ticket->identifier }}</span>
                                <span class="text-[13px] text-gray-200 group-hover:text-white transition flex-1 truncate">{{ $ticket->title }}</span>
                                <span class="text-[11px] text-gray-500 px-1.5 py-0.5 rounded bg-white/[0.04] flex-shrink-0">{{ $ticket->column->name }}</span>
                                @if($ticket->assignee)
                                    <div class="w-5 h-5 rounded-full bg-gradient-to-br from-accent to-purple-600 flex items-center justify-center text-[9px] font-bold text-white flex-shrink-0" title="{{ $ticket->assignee->name }}">
                                        {{ strtoupper(substr($ticket->assignee->name, 0, 1)) }}
                                    </div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create Ticket Dialog --}}
    <dialog id="create-ticket-dialog" class="backdrop:bg-black/80 backdrop:backdrop-blur-sm bg-transparent p-0 m-0 fixed inset-0 w-full h-full max-w-none max-h-none">
        <div class="fixed inset-0 flex items-start justify-center pt-[8vh] p-4 pointer-events-none">
            <div class="bg-[#1a1a1e] border border-white/[0.12] rounded-xl shadow-2xl shadow-black/60 w-full max-w-3xl pointer-events-auto flex flex-col max-h-[80vh]">
                <form method="POST" action="{{ route('epics.tickets.store', [$project, $epic]) }}" class="flex flex-col flex-1 min-h-0">
                    @csrf

                    <div class="flex flex-1 min-h-0">
                        {{-- Left: Content --}}
                        <div class="flex-1 px-5 pt-5 pb-4 space-y-4 overflow-y-auto">
                            <input type="text" name="title" placeholder="Issue title" required
                                   class="w-full bg-transparent border-0 text-[18px] font-semibold text-white placeholder-gray-500 focus:ring-0 p-0 focus:outline-none" />
                            <textarea name="description" rows="14" placeholder="Add description..."
                                      class="w-full bg-transparent border-0 text-[14px] text-gray-300 placeholder-gray-500 focus:ring-0 p-0 resize-none focus:outline-none leading-relaxed"></textarea>
                        </div>

                        {{-- Right: Properties sidebar --}}
                        <div class="w-52 border-l border-white/[0.08] p-4 space-y-3 overflow-y-auto bg-[#151518]/50 flex-shrink-0">
                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Status</label>
                                <select name="column_id" class="w-full input-dark text-[12px] px-2 py-1.5">
                                    @foreach($project->columns as $col)
                                        <option value="{{ $col->id }}">{{ $col->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Priority</label>
                                <select name="priority" class="w-full input-dark text-[12px] px-2 py-1.5">
                                    <option value="none">None</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Assignee</label>
                                <select name="assignee_id" class="w-full input-dark text-[12px] px-2 py-1.5">
                                    <option value="">Unassigned</option>
                                    @foreach($project->members as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Type</label>
                                <select name="type" class="w-full input-dark text-[12px] px-2 py-1.5">
                                    <option value="task">Task</option>
                                    <option value="bug">Bug</option>
                                    <option value="feature">Feature</option>
                                    <option value="improvement">Improvement</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Sprint</label>
                                <select name="sprint_id" class="w-full input-dark text-[12px] px-2 py-1.5">
                                    <option value="">No sprint</option>
                                    @foreach($project->sprints as $sprint)
                                        <option value="{{ $sprint->id }}">{{ $sprint->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Estimate</label>
                                <input type="number" name="estimate" min="0" placeholder="Points"
                                       class="w-full input-dark text-[12px] px-2 py-1.5" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 px-4 py-3 border-t border-white/[0.08] bg-[#151518] rounded-b-xl">
                        <button type="button" onclick="document.getElementById('create-ticket-dialog').close()" class="px-3 py-1.5 text-[12px] font-medium text-gray-400 hover:text-white transition">Cancel</button>
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-accent hover:bg-accent-hover text-white text-[12px] font-medium rounded-md transition">Create issue</button>
                    </div>
                </form>
            </div>
        </div>
    </dialog>

    {{-- Edit Epic Dialog --}}
    <dialog id="edit-epic-dialog" class="backdrop:bg-black/80 backdrop:backdrop-blur-sm bg-transparent p-0 m-0 fixed inset-0 w-full h-full max-w-none max-h-none">
        <div class="fixed inset-0 flex items-start justify-center pt-[10vh] p-4 pointer-events-none">
            <div class="bg-[#1a1a1e] border border-white/[0.12] rounded-xl shadow-2xl shadow-black/60 w-full max-w-lg pointer-events-auto">
                <form method="POST" action="{{ route('epics.update', [$project, $epic]) }}" class="flex flex-col">
                    @csrf
                    @method('PUT')
                    <div class="px-5 pt-5 pb-4 space-y-4">
                        <input type="text" name="name" value="{{ $epic->name }}" placeholder="Epic name" required
                               class="w-full bg-transparent border-0 text-[18px] font-semibold text-white placeholder-gray-500 focus:ring-0 p-0 focus:outline-none" />
                        <textarea name="description" rows="3" placeholder="Describe the goal of this epic..."
                                  class="w-full bg-transparent border-0 text-[14px] text-gray-300 placeholder-gray-500 focus:ring-0 p-0 resize-none focus:outline-none leading-relaxed">{{ $epic->description }}</textarea>
                        <div class="flex items-center gap-3 pt-3 border-t border-white/[0.08]">
                            <label class="text-[12px] text-gray-400">Color</label>
                            <div class="flex items-center gap-1.5">
                                @foreach(['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#6366f1'] as $color)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="color" value="{{ $color }}" class="sr-only peer" {{ $epic->color === $color ? 'checked' : '' }}>
                                        <div class="w-5 h-5 rounded-full border-2 border-transparent peer-checked:border-white/60 transition" style="background: {{ $color }}"></div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3 border-t border-white/[0.08] bg-[#151518] rounded-b-xl">
                        <button type="button" onclick="if(confirm('Delete this epic? Issues will be kept.')) document.getElementById('delete-epic-form').submit()" class="text-[12px] text-red-400 hover:text-red-300 font-medium transition">Delete</button>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="document.getElementById('edit-epic-dialog').close()" class="px-3 py-1.5 text-[12px] font-medium text-gray-400 hover:text-white transition">Cancel</button>
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-accent hover:bg-accent-hover text-white text-[12px] font-medium rounded-md transition">Save changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </dialog>

    <form id="delete-epic-form" method="POST" action="{{ route('epics.destroy', [$project, $epic]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-app-layout>
