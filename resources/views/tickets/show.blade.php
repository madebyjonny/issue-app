<x-app-layout>
    <x-slot name="project">{{ $project }}</x-slot>

    <x-slot name="header">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('projects.board', $project) }}" class="text-gray-600 hover:text-gray-300 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            </a>
            <span class="text-[12px] font-mono text-gray-500">{{ $ticket->identifier }}</span>
        </div>
    </x-slot>

    <div class="flex flex-1 overflow-hidden">
        {{-- Main --}}
        <div class="flex-1 p-8 overflow-y-auto">
            <form method="POST" action="{{ route('tickets.update', [$project, $ticket]) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <input type="text" name="title" value="{{ $ticket->title }}"
                           class="w-full bg-transparent border-0 text-xl font-semibold text-white focus:ring-0 p-0 placeholder-gray-600" />
                </div>

                <div>
                    <textarea name="description" rows="20" placeholder="Add a description..."
                              class="w-full input-dark p-4 resize-none">{{ $ticket->description }}</textarea>
                </div>

                <x-primary-button>Save Changes</x-primary-button>
            </form>
        </div>

        {{-- Meta sidebar --}}
        <div class="w-56 border-l border-white/[0.06] p-5 space-y-4 overflow-y-auto bg-surface-300/30">
            <form method="POST" action="{{ route('tickets.update', [$project, $ticket]) }}" id="meta-form">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Status</label>
                        <select name="column_id" onchange="document.getElementById('meta-form').submit()" class="w-full input-dark text-[12px] px-2 py-1.5">
                            @foreach($project->columns as $col)
                                <option value="{{ $col->id }}" {{ $ticket->column_id == $col->id ? 'selected' : '' }}>{{ $col->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Assignee</label>
                        <select name="assignee_id" onchange="document.getElementById('meta-form').submit()" class="w-full input-dark text-[12px] px-2 py-1.5">
                            <option value="">Unassigned</option>
                            @foreach($project->members as $member)
                                <option value="{{ $member->id }}" {{ $ticket->assignee_id == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Priority</label>
                        <select name="priority" onchange="document.getElementById('meta-form').submit()" class="w-full input-dark text-[12px] px-2 py-1.5">
                            <option value="none" {{ $ticket->priority === 'none' ? 'selected' : '' }}>None</option>
                            <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ $ticket->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Type</label>
                        <select name="type" onchange="document.getElementById('meta-form').submit()" class="w-full input-dark text-[12px] px-2 py-1.5">
                            <option value="task" {{ $ticket->type === 'task' ? 'selected' : '' }}>Task</option>
                            <option value="bug" {{ $ticket->type === 'bug' ? 'selected' : '' }}>Bug</option>
                            <option value="feature" {{ $ticket->type === 'feature' ? 'selected' : '' }}>Feature</option>
                            <option value="improvement" {{ $ticket->type === 'improvement' ? 'selected' : '' }}>Improvement</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Sprint</label>
                        <select name="sprint_id" onchange="document.getElementById('meta-form').submit()" class="w-full input-dark text-[12px] px-2 py-1.5">
                            <option value="">No sprint</option>
                            @foreach($project->sprints as $sprint)
                                <option value="{{ $sprint->id }}" {{ $ticket->sprint_id == $sprint->id ? 'selected' : '' }}>{{ $sprint->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>

            <div class="pt-4 border-t border-white/[0.06] space-y-3">
                <div>
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-600">Reporter</span>
                    <p class="text-[13px] text-gray-400 mt-0.5">{{ $ticket->reporter->name }}</p>
                </div>
                <div>
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-600">Created</span>
                    <p class="text-[13px] text-gray-400 mt-0.5">{{ $ticket->created_at->diffForHumans() }}</p>
                </div>
                <div>
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-600">Updated</span>
                    <p class="text-[13px] text-gray-400 mt-0.5">{{ $ticket->updated_at->diffForHumans() }}</p>
                </div>
            </div>

            <div class="pt-4 border-t border-white/[0.06]">
                <form method="POST" action="{{ route('tickets.destroy', [$project, $ticket]) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-[12px] text-red-400/60 hover:text-red-400 transition" onclick="return confirm('Delete this ticket?')">
                        Delete ticket
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
