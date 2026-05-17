<x-app-layout :project="$project">

    <x-slot name="header">
        <div class="flex items-center gap-2.5">
            <h2 class="text-[15px] font-semibold text-gray-900">{{ $project->name }}</h2>
            <span class="text-[11px] text-gray-500 bg-gray-100 border border-gray-200 px-1.5 py-0.5 rounded font-mono">{{ $project->key }}</span>
            <a href="{{ route('projects.show', $project) }}" class="text-gray-400 hover:text-gray-700 transition p-1 rounded hover:bg-gray-100" title="Project settings">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </a>
        </div>
        <div class="flex items-center gap-1.5">
            <select id="sprint-filter" onchange="applyFilters()" class="bg-white border border-gray-200 text-gray-700 text-[11px] px-2 py-1 rounded-md focus:border-accent/50 focus:ring-0 transition">
                <option value="">All sprints</option>
                @foreach($project->sprints as $sprint)
                    <option value="{{ $sprint->id }}" {{ request('sprint') == $sprint->id ? 'selected' : '' }}>
                        {{ $sprint->name }}{{ $sprint->is_active ? ' ●' : '' }}
                    </option>
                @endforeach
            </select>
            <select id="assignee-filter" onchange="applyFilters()" class="bg-white border border-gray-200 text-gray-700 text-[11px] px-2 py-1 rounded-md focus:border-accent/50 focus:ring-0 transition">
                <option value="">All members</option>
                @foreach($project->members as $member)
                    <option value="{{ $member->id }}" {{ request('assignee') == $member->id ? 'selected' : '' }}>
                        {{ $member->name }}
                    </option>
                @endforeach
            </select>
            <select id="priority-filter" onchange="applyFilters()" class="bg-white border border-gray-200 text-gray-700 text-[11px] px-2 py-1 rounded-md focus:border-accent/50 focus:ring-0 transition">
                <option value="">All priorities</option>
                <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="none" {{ request('priority') == 'none' ? 'selected' : '' }}>None</option>
            </select>
            <select id="type-filter" onchange="applyFilters()" class="bg-white border border-gray-200 text-gray-700 text-[11px] px-2 py-1 rounded-md focus:border-accent/50 focus:ring-0 transition">
                <option value="">All types</option>
                <option value="task" {{ request('type') == 'task' ? 'selected' : '' }}>Task</option>
                <option value="bug" {{ request('type') == 'bug' ? 'selected' : '' }}>Bug</option>
                <option value="feature" {{ request('type') == 'feature' ? 'selected' : '' }}>Feature</option>
                <option value="improvement" {{ request('type') == 'improvement' ? 'selected' : '' }}>Improvement</option>
            </select>
            <select id="epic-filter" onchange="applyFilters()" class="bg-white border border-gray-200 text-gray-700 text-[11px] px-2 py-1 rounded-md focus:border-accent/50 focus:ring-0 transition">
                <option value="">All epics</option>
                @foreach($project->epics as $epic)
                    <option value="{{ $epic->id }}" {{ request('epic') == $epic->id ? 'selected' : '' }}>
                        {{ $epic->name }}
                    </option>
                @endforeach
            </select>
            <button onclick="openCreateDialog()" class="inline-flex items-center gap-1 px-2.5 py-1 bg-accent hover:bg-accent-hover text-white text-[12px] font-medium rounded-md transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Issue
            </button>
        </div>
    </x-slot>

    {{-- Board --}}
    <div class="flex-1 flex overflow-x-auto overflow-y-hidden p-4 gap-4" id="board">
        @foreach($project->columns as $column)
            <div class="column-wrapper flex-shrink-0 w-[280px] flex flex-col max-h-full group/col"
                 data-column-id="{{ $column->id }}">
                {{-- Column header --}}
                <div class="column-header flex items-center justify-between px-1 py-2 mb-2 cursor-grab active:cursor-grabbing select-none" draggable="true">
                    <div class="flex items-center gap-2">
                        <svg class="w-3 h-3 text-gray-300 opacity-0 group-hover/col:opacity-60 flex-shrink-0 transition" fill="currentColor" viewBox="0 0 16 16"><circle cx="5" cy="4" r="1.2"/><circle cx="5" cy="8" r="1.2"/><circle cx="5" cy="12" r="1.2"/><circle cx="11" cy="4" r="1.2"/><circle cx="11" cy="8" r="1.2"/><circle cx="11" cy="12" r="1.2"/></svg>
                        <h3 class="text-[13px] font-semibold text-gray-700">{{ $column->name }}</h3>
                        <span class="text-[12px] text-gray-400 tabular-nums column-count">{{ $column->tickets->count() }}</span>
                    </div>
                    <button onclick="openCreateDialog({{ $column->id }})" class="text-gray-400 hover:text-gray-700 transition p-1.5 rounded hover:bg-gray-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    </button>
                </div>

                {{-- Tickets drop zone --}}
                <div class="flex-1 space-y-2 min-h-[100px] overflow-y-auto pb-4 ticket-list" data-column-id="{{ $column->id }}">
                    @foreach($column->tickets as $ticket)
                        <div class="ticket-card group bg-white hover:bg-gray-50/80 border border-gray-200 hover:border-gray-300 rounded-lg p-3.5 cursor-grab active:cursor-grabbing transition-all duration-150 shadow-sm dark:bg-[#1f1f23] dark:border-white/[0.08] dark:hover:bg-white/[0.06] dark:hover:border-white/[0.15] dark:shadow-none"
                             draggable="true"
                             data-ticket-id="{{ $ticket->id }}"
                             onclick="openTicketDialog({{ $ticket->id }})">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-[11px] text-gray-500 dark:text-gray-500 font-mono">{{ $ticket->identifier }}</span>
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
                            </div>
                            <p class="text-[13px] text-gray-800 leading-snug group-hover:text-gray-900 transition font-medium dark:text-gray-200 dark:group-hover:text-white">{{ $ticket->title }}</p>
                            <div class="flex items-center justify-between mt-3">
                                <div class="flex items-center gap-1.5">
                                    @php
                                        $typeStyles = [
                                            'bug' => ['bg-red-500/20 text-red-400', '●'],
                                            'feature' => ['bg-purple-500/20 text-purple-400', '◆'],
                                            'improvement' => ['bg-blue-500/20 text-blue-400', '▲'],
                                            'task' => ['bg-gray-500/20 text-gray-400', '○'],
                                        ];
                                        $style = $typeStyles[$ticket->type] ?? $typeStyles['task'];
                                    @endphp
                                    <span class="text-[10px] px-1.5 py-0.5 rounded {{ $style[0] }} font-medium">{{ $style[1] }} {{ ucfirst($ticket->type) }}</span>
                                </div>
                                @if($ticket->assignee)
                                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-accent to-purple-600 flex items-center justify-center text-[10px] font-bold text-white" title="{{ $ticket->assignee->name }}">
                                        {{ strtoupper(substr($ticket->assignee->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Linear-inspired Dialog --}}
    <dialog id="ticket-dialog" class="backdrop:bg-black/80 backdrop:backdrop-blur-sm bg-transparent p-0 m-0 fixed inset-0 w-full h-full max-w-none max-h-none">
        <div class="fixed inset-0 flex items-start justify-center pt-[8vh] p-4 pointer-events-none">
            <div class="bg-white border border-gray-200 rounded-xl shadow-2xl shadow-gray-900/10 w-full max-w-3xl pointer-events-auto flex flex-col max-h-[80vh]">
                <form id="ticket-form" method="POST" action="" class="flex flex-col flex-1 min-h-0">
                    @csrf
                    <input type="hidden" name="_method" id="ticket-form-method" value="POST">

                    <div class="flex flex-1 min-h-0">
                        {{-- Left: Content --}}
                        <div class="flex-1 px-5 pt-5 pb-4 space-y-4 overflow-y-auto">
                            <input type="text" name="title" id="ticket-title" placeholder="Issue title" required
                                   class="w-full bg-transparent border-0 text-[18px] font-semibold text-gray-900 placeholder-gray-300 focus:ring-0 p-0 focus:outline-none" />

                            <textarea name="description" id="ticket-description" rows="14" placeholder="Add description..."
                                      class="w-full bg-transparent border-0 text-[14px] text-gray-700 placeholder-gray-300 focus:ring-0 p-0 resize-none focus:outline-none leading-relaxed"></textarea>
                        </div>

                        {{-- Right: Properties sidebar --}}
                        <div class="w-52 border-l border-gray-200 p-4 space-y-3 overflow-y-auto bg-gray-50 flex-shrink-0">
                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Status</label>
                                <select name="column_id" id="ticket-column" class="w-full input-dark text-[12px] px-2 py-1.5">
                                    @foreach($project->columns as $col)
                                        <option value="{{ $col->id }}">{{ $col->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Priority</label>
                                <select name="priority" id="ticket-priority" class="w-full input-dark text-[12px] px-2 py-1.5">
                                    <option value="none">None</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Assignee</label>
                                <select name="assignee_id" id="ticket-assignee" class="w-full input-dark text-[12px] px-2 py-1.5">
                                    <option value="">Unassigned</option>
                                    @foreach($project->members as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Type</label>
                                <select name="type" id="ticket-type" class="w-full input-dark text-[12px] px-2 py-1.5">
                                    <option value="task">Task</option>
                                    <option value="bug">Bug</option>
                                    <option value="feature">Feature</option>
                                    <option value="improvement">Improvement</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Sprint</label>
                                <select name="sprint_id" id="ticket-sprint" class="w-full input-dark text-[12px] px-2 py-1.5">
                                    <option value="">No sprint</option>
                                    @foreach($project->sprints as $sprint)
                                        <option value="{{ $sprint->id }}">{{ $sprint->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Epic</label>
                                <select name="epic_id" id="ticket-epic" class="w-full input-dark text-[12px] px-2 py-1.5">
                                    <option value="">No epic</option>
                                    @foreach($project->epics as $epic)
                                        <option value="{{ $epic->id }}">{{ $epic->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Estimate</label>
                                <input type="number" name="estimate" id="ticket-estimate" min="0" placeholder="Points"
                                       class="w-full input-dark text-[12px] px-2 py-1.5" />
                            </div>

                            @if($project->resources->isNotEmpty())
                            <div class="pt-3 border-t border-gray-100">
                                <label class="block text-[10px] font-semibold uppercase tracking-wider text-gray-600 mb-2">Resources</label>
                                <div class="space-y-1" id="ticket-resources">
                                    @foreach($project->resources as $resource)
                                    <button type="button"
                                            data-resource-id="{{ $resource->id }}"
                                            data-attach-url="{{ route('projects.resources.tickets.attach', [$project, $resource]) }}"
                                            data-attached="0"
                                            onclick="toggleResource(this)"
                                            class="resource-toggle flex items-center gap-2 w-full text-left px-2 py-1 rounded-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                                        <span class="resource-type-badge" style="font-size:10px;font-family:monospace;opacity:0.4">{{ substr($resource->type, 0, 3) }}</span>
                                        <span style="font-size:12px" class="truncate">{{ $resource->name }}</span>
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                        <div id="ticket-delete-btn" class="hidden">
                            <button type="button" onclick="deleteTicket()" class="text-[12px] text-red-500 hover:text-red-600 font-medium transition">Delete</button>
                        </div>
                        <div class="flex items-center gap-2 ml-auto">
                            <button type="button" onclick="document.getElementById('ticket-dialog').close()" class="px-3 py-1.5 text-[12px] font-medium text-gray-500 hover:text-gray-800 transition">
                                Cancel
                            </button>
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-accent hover:bg-accent-hover text-white text-[12px] font-medium rounded-md transition" id="ticket-submit-btn">
                                Create issue
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </dialog>

    <script>
        const projectId = {{ $project->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const tickets = @json($project->columns->pluck('tickets')->flatten()->map(fn($t) => array_merge($t->toArray(), ['resource_ids' => $t->resources->pluck('id')->all()]))->keyBy('id'));

        function applyFilters() {
            const sprint = document.getElementById('sprint-filter').value;
            const assignee = document.getElementById('assignee-filter').value;
            const priority = document.getElementById('priority-filter').value;
            const type = document.getElementById('type-filter').value;
            const epic = document.getElementById('epic-filter').value;
            const params = new URLSearchParams();
            if (sprint) params.set('sprint', sprint);
            if (assignee) params.set('assignee', assignee);
            if (priority) params.set('priority', priority);
            if (type) params.set('type', type);
            if (epic) params.set('epic', epic);
            window.location.href = `/projects/${projectId}/board` + (params.toString() ? '?' + params : '');
        }

        function openCreateDialog(columnId = null) {
            currentTicketId = null;
            const dialog = document.getElementById('ticket-dialog');
            const form = document.getElementById('ticket-form');
            form.action = `/projects/${projectId}/tickets`;
            document.getElementById('ticket-form-method').value = 'POST';
            document.getElementById('ticket-submit-btn').textContent = 'Create issue';
            document.getElementById('ticket-delete-btn').classList.add('hidden');
            document.getElementById('ticket-title').value = '';
            document.getElementById('ticket-description').value = '';
            document.getElementById('ticket-assignee').value = '';
            document.getElementById('ticket-priority').value = 'none';
            document.getElementById('ticket-type').value = 'task';
            document.getElementById('ticket-sprint').value = '';
            document.getElementById('ticket-epic').value = '';
            document.getElementById('ticket-estimate').value = '';
            if (columnId) document.getElementById('ticket-column').value = columnId;
            document.querySelectorAll('.resource-toggle').forEach(btn => setResourceActive(btn, false));
            dialog.showModal();
            document.getElementById('ticket-title').focus();
        }

        let currentTicketId = null;

        function openTicketDialog(ticketId) {
            event.stopPropagation();
            const ticket = tickets[ticketId];
            if (!ticket) return;
            currentTicketId = ticketId;
            const dialog = document.getElementById('ticket-dialog');
            const form = document.getElementById('ticket-form');
            form.action = `/projects/${projectId}/tickets/${ticketId}`;
            document.getElementById('ticket-form-method').value = 'PUT';
            document.getElementById('ticket-submit-btn').textContent = 'Save changes';
            document.getElementById('ticket-delete-btn').classList.remove('hidden');
            document.getElementById('ticket-title').value = ticket.title;
            document.getElementById('ticket-description').value = ticket.description || '';
            document.getElementById('ticket-column').value = ticket.column_id;
            document.getElementById('ticket-assignee').value = ticket.assignee_id || '';
            document.getElementById('ticket-priority').value = ticket.priority;
            document.getElementById('ticket-type').value = ticket.type;
            document.getElementById('ticket-sprint').value = ticket.sprint_id || '';
            document.getElementById('ticket-epic').value = ticket.epic_id || '';
            document.getElementById('ticket-estimate').value = ticket.estimate || '';

            // Sync resource toggle state
            const attachedIds = ticket.resource_ids || [];
            document.querySelectorAll('.resource-toggle').forEach(btn => {
                const rid = parseInt(btn.dataset.resourceId);
                setResourceActive(btn, attachedIds.includes(rid));
            });

            dialog.showModal();
        }

        function setResourceActive(btn, active) {
            const badge = btn.querySelector('.resource-type-badge');
            if (active) {
                btn.classList.remove('text-gray-500', 'hover:text-gray-700', 'hover:bg-gray-100');
                btn.classList.add('bg-accent/15', 'text-accent', 'hover:bg-accent/20');
                if (badge) badge.style.opacity = '0.8';
                btn.dataset.attached = '1';
            } else {
                btn.classList.add('text-gray-500', 'hover:text-gray-700', 'hover:bg-gray-100');
                btn.classList.remove('bg-accent/15', 'text-accent', 'hover:bg-accent/20');
                if (badge) badge.style.opacity = '0.4';
                btn.dataset.attached = '0';
            }
        }

        function toggleResource(btn) {
            if (!currentTicketId) return;
            const resourceId = btn.dataset.resourceId;
            const attached = btn.dataset.attached === '1';
            const ticket = tickets[currentTicketId];

            if (attached) {
                // Detach
                const url = `/projects/${projectId}/resources/${resourceId}/tickets/${currentTicketId}`;
                const body = new URLSearchParams({ _method: 'DELETE', _token: csrfToken });
                fetch(url, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString(),
                }).then(r => { if (r.ok || r.status === 302) {
                    setResourceActive(btn, false);
                    if (ticket) ticket.resource_ids = (ticket.resource_ids || []).filter(id => id !== parseInt(resourceId));
                }});
            } else {
                // Attach
                const body = new URLSearchParams({ _token: csrfToken, ticket_id: currentTicketId });
                fetch(btn.dataset.attachUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString(),
                }).then(r => { if (r.ok || r.status === 302) {
                    setResourceActive(btn, true);
                    if (ticket) ticket.resource_ids = [...(ticket.resource_ids || []), parseInt(resourceId)];
                }});
            }
        }

        function deleteTicket() {
            if (!confirm('Delete this issue?')) return;
            const form = document.getElementById('ticket-form');
            document.getElementById('ticket-form-method').value = 'DELETE';
            form.submit();
        }

        // Drag and drop
        let draggedCard = null;
        let draggedColumn = null;

        document.addEventListener('dragstart', (e) => {
            const columnHeader = e.target.closest('.column-header');
            if (columnHeader) {
                draggedColumn = columnHeader.closest('.column-wrapper');
                draggedColumn.classList.add('opacity-50');
                e.dataTransfer.effectAllowed = 'move';
                return;
            }
            const card = e.target.closest('.ticket-card');
            if (!card) return;
            draggedCard = card;
            card.classList.add('opacity-40', 'scale-[0.98]');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', card.dataset.ticketId);
        });

        document.addEventListener('dragend', () => {
            if (draggedColumn) { draggedColumn.classList.remove('opacity-50'); draggedColumn = null; }
            if (draggedCard) { draggedCard.classList.remove('opacity-40', 'scale-[0.98]'); draggedCard = null; }
            document.querySelectorAll('.ticket-list').forEach(el => el.classList.remove('bg-accent/10', 'ring-1', 'ring-accent/30', 'rounded-lg'));
        });

        document.querySelectorAll('.ticket-list').forEach(list => {
            list.addEventListener('dragover', (e) => {
                if (draggedColumn) return;
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                list.classList.add('bg-accent/10', 'ring-1', 'ring-accent/30', 'rounded-lg');
                const after = getDragAfterElement(list, e.clientY);
                after ? list.insertBefore(draggedCard, after) : list.appendChild(draggedCard);
            });
            list.addEventListener('dragleave', (e) => {
                if (!list.contains(e.relatedTarget)) list.classList.remove('bg-accent/10', 'ring-1', 'ring-accent/30', 'rounded-lg');
            });
            list.addEventListener('drop', (e) => {
                e.preventDefault();
                list.classList.remove('bg-accent/10', 'ring-1', 'ring-accent/30', 'rounded-lg');
                const ticketId = e.dataTransfer.getData('text/plain');
                const columnId = list.dataset.columnId;
                const cards = [...list.querySelectorAll('.ticket-card')];
                const position = cards.findIndex(c => c.dataset.ticketId === ticketId);
                fetch(`/projects/${projectId}/tickets/${ticketId}/move`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ column_id: parseInt(columnId), position: Math.max(0, position) }),
                });
                if (tickets[ticketId]) tickets[ticketId].column_id = parseInt(columnId);
                updateColumnCounts();
            });
        });

        function getDragAfterElement(container, y) {
            const elements = [...container.querySelectorAll('.ticket-card:not(.opacity-40)')];
            return elements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                return (offset < 0 && offset > closest.offset) ? { offset, element: child } : closest;
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        function updateColumnCounts() {
            document.querySelectorAll('[data-column-id]').forEach(col => {
                const list = col.querySelector('.ticket-list');
                const counter = col.querySelector('.column-count');
                if (list && counter) counter.textContent = list.querySelectorAll('.ticket-card').length;
            });
        }

        // Column reordering
        const board = document.getElementById('board');
        board.addEventListener('dragover', (e) => {
            if (!draggedColumn) return;
            e.preventDefault();
            const after = getColumnAfterElement(board, e.clientX);
            if (after) {
                board.insertBefore(draggedColumn, after);
            } else {
                board.appendChild(draggedColumn);
            }
        });

        board.addEventListener('drop', (e) => {
            if (!draggedColumn) return;
            e.preventDefault();
            const columns = [...board.querySelectorAll('.column-wrapper')];
            const order = columns.map(c => parseInt(c.dataset.columnId));
            fetch(`{{ route('columns.reorder', $project) }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ columns: order }),
            });
        });

        function getColumnAfterElement(container, x) {
            const columns = [...container.querySelectorAll('.column-wrapper')].filter(c => c !== draggedColumn);
            return columns.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = x - box.left - box.width / 2;
                return (offset < 0 && offset > closest.offset) ? { offset, element: child } : closest;
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        // ── Board presence cursors ─────────────────────────────────
        (function initPresence() {
            if (!window.Echo) return;
            const projectId = {{ $project->id }};
            const cursorUrl = '{{ route('presence.cursor', $project) }}';
            const csrfMeta  = document.querySelector('meta[name="csrf-token"]')?.content;
            const cursors   = {};

            function getCursorEl(userId, userName) {
                if (cursors[userId]) return cursors[userId];
                const el = document.createElement('div');
                el.className = 'pointer-events-none fixed z-50 flex flex-col items-start';
                el.innerHTML = `
                    <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0L0 16L4.5 12L7 18L9 17L6.5 11L12 11L0 0Z" fill="#6366f1"/>
                    </svg>
                    <span class="ml-3 -mt-1 px-1.5 py-0.5 rounded-md bg-indigo-600 text-white text-[10px] font-medium shadow whitespace-nowrap">${userName}</span>`;
                document.body.appendChild(el);
                cursors[userId] = el;
                return el;
            }

            window.Echo.join(`board.${projectId}`)
                .here(members => {
                    // Members already on the board; cursors appear on first mousemove
                })
                .joining(member => {})
                .leaving(member => {
                    if (cursors[member.id]) {
                        cursors[member.id].remove();
                        delete cursors[member.id];
                    }
                })
                .listen('CursorMoved', event => {
                    if (event.user_id == {{ auth()->id() }}) return;
                    const el = getCursorEl(event.user_id, event.user_name);
                    el.style.transform = `translate(${event.position.x}px, ${event.position.y}px)`;
                });

            let throttleTimer = null;
            document.addEventListener('mousemove', e => {
                if (throttleTimer) return;
                throttleTimer = setTimeout(() => {
                    throttleTimer = null;
                    fetch(cursorUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfMeta },
                        body: JSON.stringify({
                            position: { x: e.clientX, y: e.clientY },
                            ticket_id: null,
                            column_id: null,
                        }),
                    });
                }, 60); // ~16fps throttle
            });
        })();
    </script>
</x-app-layout>
