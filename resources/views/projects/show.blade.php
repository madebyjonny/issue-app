<x-app-layout :project="$project">

    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="text-[15px] font-semibold text-gray-900">Settings</h2>
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
                        <p class="text-[13px] text-gray-900">{{ $project->name }}</p>
                    </div>
                    <div>
                        <label class="block text-[12px] text-gray-500 mb-1">Key</label>
                        <p class="text-[13px] text-gray-900 font-mono">{{ $project->key }}</p>
                    </div>
                </div>
                @if($project->description)
                <div>
                    <label class="block text-[12px] text-gray-500 mb-1">Description</label>
                    <p class="text-[13px] text-gray-700">{{ $project->description }}</p>
                </div>
                @endif
                <div>
                    <label class="block text-[12px] text-gray-500 mb-1">Owner</label>
                    <p class="text-[13px] text-gray-700">{{ $project->owner->name }}</p>
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
                <div class="divide-y divide-gray-100">
                    @foreach($project->columns as $column)
                        <div class="flex items-center gap-3 px-5 py-3 group">
                            <form method="POST" action="{{ route('columns.update', [$project, $column]) }}" class="flex items-center gap-3 flex-1">
                                @csrf
                                @method('PUT')
                                <div class="w-2 h-2 rounded-full bg-gray-400 flex-shrink-0"></div>
                                <input type="text" name="name" value="{{ $column->name }}" class="flex-1 bg-transparent border-0 text-[13px] text-gray-700 p-0 focus:ring-0 focus:text-gray-900" />
                                <button type="submit" class="text-[12px] text-gray-400 hover:text-gray-700 transition opacity-0 group-hover:opacity-100">Save</button>
                            </form>
                            <form method="POST" action="{{ route('columns.destroy', [$project, $column]) }}" class="opacity-0 group-hover:opacity-100 transition">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[12px] text-red-500/60 hover:text-red-400 transition" onclick="return confirm('Delete this column?')">Remove</button>
                            </form>
                        </div>
                    @endforeach
                </div>
                <form method="POST" action="{{ route('columns.store', $project) }}" class="flex items-center gap-3 px-5 py-3 border-t border-gray-100">
                    @csrf
                    <div class="w-2 h-2 rounded-full bg-gray-300 flex-shrink-0"></div>
                    <input type="text" name="name" placeholder="Add column..." required class="flex-1 bg-transparent border-0 text-[13px] text-gray-700 placeholder-gray-400 p-0 focus:ring-0" />
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
                                <span class="text-[13px] text-gray-900">{{ $sprint->name }}</span>
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
                    <input type="text" name="name" placeholder="Sprint name..." required class="flex-1 bg-transparent border-0 text-[13px] text-gray-700 placeholder-gray-400 p-0 focus:ring-0" />
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
            <div class="flex-1 card overflow-hidden">
                <div class="divide-y divide-gray-100">
                    @foreach($project->members as $member)
                        <div class="flex items-center gap-3 px-5 py-3 group">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-accent/30 to-accent/10 flex items-center justify-center text-[11px] font-semibold text-accent ring-1 ring-white/[0.06]">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-[13px] text-gray-700">{{ $member->name }}</span>
                                <span class="text-[12px] text-gray-600 ml-2">{{ $member->email }}</span>
                            </div>
                            <span class="text-[12px] text-gray-600 capitalize">{{ $member->pivot->role }}</span>
                            @if($project->owner_id === auth()->id() && $member->pivot->role !== 'owner')
                                <form method="POST" action="{{ route('projects.members.destroy', [$project, $member]) }}" class="opacity-0 group-hover:opacity-100 transition">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-[12px] text-red-500/60 hover:text-red-400 transition" onclick="return confirm('Remove {{ $member->name }} from this project?')">Remove</button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($project->owner_id === auth()->id())
                    <form method="POST" action="{{ route('projects.members.store', $project) }}" class="border-t border-gray-100 px-5 py-4 space-y-3" x-data="{ expanded: {{ $errors->has('email') || $errors->has('name') || $errors->has('password') ? 'true' : 'false' }} }">
                        @csrf
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            <input type="email" name="email" placeholder="Add by email address..." value="{{ old('email') }}" required class="flex-1 bg-transparent border-0 text-[13px] text-gray-700 placeholder-gray-400 p-0 focus:ring-0" @focus="expanded = true" />
                            <button type="submit" class="text-[12px] text-accent hover:text-accent-hover transition whitespace-nowrap">Add Member</button>
                        </div>
                        <div x-show="expanded" x-cloak class="grid grid-cols-2 gap-3 pl-7">
                            <div>
                                <input type="text" name="name" placeholder="Full name (if new user)" value="{{ old('name') }}" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-[13px] text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-accent/40" />
                                @error('name')<p class="mt-1 text-[11px] text-red-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <input type="password" name="password" placeholder="Password (if new user)" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-[13px] text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-accent/40" />
                                @error('password')<p class="mt-1 text-[11px] text-red-400">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        @error('email')<p class="pl-7 text-[12px] text-red-400">{{ $message }}</p>@enderror
                    </form>
                @endif
            </div>
        </div>

        {{-- Resources --}}
        <div class="flex gap-8" x-data="{ editing: null, creating: false }">
            <div class="w-48 flex-shrink-0 pt-5">
                <p class="section-title">Resources</p>
                <p class="section-desc">Skill cards given to AI agents working on tickets in this project.</p>
            </div>
            <div class="flex-1 space-y-2">
                @foreach($project->resources as $resource)
                    <div class="card overflow-hidden" x-data="{ open: false }">
                        <div class="flex items-center gap-3 px-5 py-3 cursor-pointer group" @click="open = !open">
                            <span class="text-[10px] font-semibold uppercase tracking-wider px-1.5 py-0.5 rounded
                                @if($resource->type === 'design') bg-purple-500/15 text-purple-400
                                @elseif($resource->type === 'development') bg-blue-500/15 text-blue-400
                                @elseif($resource->type === 'api') bg-amber-500/15 text-amber-400
                                @else bg-gray-500/15 text-gray-400 @endif">
                                {{ $resource->type }}
                            </span>
                            <span class="flex-1 text-[13px] text-gray-900">{{ $resource->name }}</span>
                            <svg class="w-3.5 h-3.5 text-gray-600 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                        </div>

                        <div x-show="open" x-cloak class="border-t border-gray-100" x-data="{ editing: false }">
                            {{-- View mode --}}
                            <div x-show="!editing" class="px-5 py-4 space-y-3">
                                <pre class="text-[12px] text-gray-400 whitespace-pre-wrap font-mono leading-relaxed">{{ $resource->content }}</pre>
                                <div class="flex items-center gap-3 pt-1">
                                    <button @click="editing = true" class="text-[12px] text-gray-500 hover:text-gray-900 transition">Edit</button>
                                    <form method="POST" action="{{ route('projects.resources.destroy', [$project, $resource]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[12px] text-red-500/60 hover:text-red-400 transition" onclick="return confirm('Delete this resource?')">Delete</button>
                                    </form>
                                </div>
                            </div>

                            {{-- Edit mode --}}
                            <form method="POST" action="{{ route('projects.resources.update', [$project, $resource]) }}" x-show="editing" class="px-5 py-4 space-y-3">
                                @csrf
                                @method('PUT')
                                <div>
                                    <input type="text" name="name" value="{{ $resource->name }}" required class="w-full input-dark text-[13px] px-3 py-2" placeholder="Resource name" />
                                </div>
                                <div>
                                    <select name="type" class="w-full input-dark text-[13px] px-3 py-2">
                                        @foreach(['design', 'development', 'api', 'process'] as $t)
                                            <option value="{{ $t }}" {{ $resource->type === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <textarea name="content" rows="10" required class="w-full input-dark text-[12px] px-3 py-2 font-mono resize-y">{{ $resource->content }}</textarea>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button type="submit" class="text-[12px] text-accent hover:text-accent-hover transition">Save</button>
                                    <button type="button" @click="editing = false" class="text-[12px] text-gray-500 hover:text-gray-900 transition">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach

                {{-- Create new resource --}}
                <div class="card overflow-hidden">
                    <div class="flex items-center gap-3 px-5 py-3 cursor-pointer" @click="creating = !creating">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span class="text-[13px] text-gray-500">Add resource...</span>
                    </div>
                    <form method="POST" action="{{ route('projects.resources.store', $project) }}" x-show="creating" x-cloak class="border-t border-gray-100 px-5 py-4 space-y-3">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <input type="text" name="name" required placeholder="Resource name" class="input-dark text-[13px] px-3 py-2" />
                            <select name="type" class="input-dark text-[13px] px-3 py-2">
                                <option value="design">Design</option>
                                <option value="development">Development</option>
                                <option value="api">API</option>
                                <option value="process">Process</option>
                            </select>
                        </div>
                        <div>
                            <textarea name="content" rows="8" required placeholder="Write the resource content in plain text or markdown..." class="w-full input-dark text-[12px] px-3 py-2 font-mono resize-y"></textarea>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="submit" class="text-[12px] text-accent hover:text-accent-hover transition">Create Resource</button>
                            <button type="button" @click="creating = false" class="text-[12px] text-gray-500 hover:text-gray-900 transition">Cancel</button>
                        </div>
                    </form>
                </div>
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
