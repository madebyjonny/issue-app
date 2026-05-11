<x-mcp::app :title="$title">
    <x-slot:head>
        <style>
            [x-cloak] { display: none !important; }
            .ticket-list { min-height: 60px; }
            ::-webkit-scrollbar { width: 4px; height: 4px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 2px; }
        </style>
        <script type="module">
        createMcpApp(async (app) => {

            async function fetchProjects() {
                try {
                    const result = await app.callServerTool('list-projects', {});
                    if (result.isError) return [];
                    return JSON.parse(result.content[0]?.text ?? '[]');
                } catch (e) {
                    return [];
                }
            }

            async function fetchBoard(projectKey) {
                try {
                    const result = await app.callServerTool('get-sprint-board', { project_key: projectKey });
                    if (result.isError) return null;
                    return JSON.parse(result.content[0]?.text ?? 'null');
                } catch (e) {
                    return null;
                }
            }

            const projects = await fetchProjects();

            Alpine.store('board', {
                projects,
                selectedKey: projects[0]?.key ?? '',
                board: null,
                loading: false,
                error: null,

                get selectedProject() {
                    return this.projects.find(p => p.key === this.selectedKey) ?? null;
                },

                async load(key) {
                    if (!key) return;
                    this.loading = true;
                    this.error = null;
                    this.board = null;
                    const data = await fetchBoard(key);
                    if (!data) {
                        this.error = 'Could not load board data.';
                    } else {
                        this.board = data;
                    }
                    this.loading = false;
                },

                priorityColor(p) {
                    return {
                        urgent: 'bg-red-500',
                        high:   'bg-orange-400',
                        medium: 'bg-yellow-400',
                        low:    'bg-blue-400',
                        none:   'bg-gray-300',
                    }[p] ?? 'bg-gray-300';
                },

                typeStyle(t) {
                    return {
                        bug:         'bg-red-100 text-red-600',
                        feature:     'bg-purple-100 text-purple-600',
                        improvement: 'bg-blue-100 text-blue-600',
                        task:        'bg-gray-100 text-gray-500',
                    }[t] ?? 'bg-gray-100 text-gray-500';
                },

                typeIcon(t) {
                    return { bug: '●', feature: '◆', improvement: '▲', task: '○' }[t] ?? '○';
                },
            });

            if (projects[0]?.key) {
                Alpine.store('board').load(projects[0].key);
            }
        });
        </script>
    </x-slot:head>

    <div x-data class="h-screen flex flex-col bg-gray-50 text-gray-900 font-sans text-sm overflow-hidden select-none">

        {{-- Header --}}
        <div class="flex items-center gap-3 px-4 h-12 border-b border-gray-200 bg-white flex-shrink-0">
            <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125z"/>
            </svg>
            <span class="text-[13px] font-semibold text-gray-700">Board</span>

            <div class="h-4 w-px bg-gray-200 mx-1"></div>

            <select
                x-model="$store.board.selectedKey"
                @change="$store.board.load($store.board.selectedKey)"
                class="text-[12px] border border-gray-200 rounded-md px-2 py-1 bg-white text-gray-700 focus:outline-none focus:ring-1 focus:ring-indigo-400 max-w-[180px]">
                <template x-for="p in $store.board.projects" :key="p.key">
                    <option :value="p.key" x-text="p.name + ' · ' + p.key"></option>
                </template>
            </select>

            <span
                x-show="$store.board.board"
                x-cloak
                class="text-[11px] text-gray-400 truncate"
                x-text="'Sprint: ' + ($store.board.board?.sprint ?? '—')">
            </span>

            <button
                @click="$store.board.load($store.board.selectedKey)"
                :disabled="$store.board.loading"
                class="ml-auto flex items-center gap-1.5 text-[12px] px-2.5 py-1 rounded-md border border-gray-200 text-gray-500 hover:bg-gray-50 disabled:opacity-40 transition">
                <svg class="w-3 h-3" :class="{ 'animate-spin': $store.board.loading }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                </svg>
                Refresh
            </button>
        </div>

        {{-- Loading --}}
        <div x-show="$store.board.loading" x-cloak class="flex-1 flex items-center justify-center">
            <div class="flex items-center gap-2.5 text-[13px] text-gray-400">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Loading board…
            </div>
        </div>

        {{-- Error --}}
        <div x-show="!$store.board.loading && $store.board.error" x-cloak class="flex-1 flex items-center justify-center">
            <p class="text-[13px] text-red-400" x-text="$store.board.error"></p>
        </div>

        {{-- Empty: no projects --}}
        <div x-show="!$store.board.loading && $store.board.projects.length === 0" x-cloak class="flex-1 flex items-center justify-center">
            <p class="text-[13px] text-gray-400">No projects found.</p>
        </div>

        {{-- Board columns --}}
        <div
            x-show="!$store.board.loading && $store.board.board"
            x-cloak
            class="flex-1 flex gap-3 p-4 overflow-x-auto overflow-y-hidden items-start">

            <template x-for="col in ($store.board.board?.columns ?? [])" :key="col.name">
                <div class="flex-shrink-0 w-[240px] flex flex-col bg-white rounded-xl border border-gray-200 overflow-hidden max-h-full">

                    {{-- Column header --}}
                    <div class="flex items-center justify-between px-3 py-2 border-b border-gray-100 flex-shrink-0">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400" x-text="col.name"></span>
                        <span
                            class="text-[10px] font-semibold tabular-nums px-1.5 py-0.5 rounded-full"
                            :class="col.tickets.length > 0 ? 'bg-indigo-50 text-indigo-500' : 'bg-gray-100 text-gray-400'"
                            x-text="col.tickets.length">
                        </span>
                    </div>

                    {{-- Ticket list --}}
                    <div class="ticket-list flex-1 overflow-y-auto p-2 space-y-1.5">

                        <template x-for="ticket in col.tickets" :key="ticket.identifier">
                            <div class="bg-white rounded-lg border border-gray-200 p-3 hover:border-gray-300 hover:shadow-sm transition-all duration-100">

                                {{-- Identifier + priority --}}
                                <div class="flex items-center gap-1.5 mb-1.5">
                                    <span class="text-[10px] font-mono text-gray-400" x-text="ticket.identifier"></span>
                                    <span
                                        class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                        :class="$store.board.priorityColor(ticket.priority)">
                                    </span>
                                </div>

                                {{-- Title --}}
                                <p class="text-[12px] text-gray-800 font-medium leading-snug mb-2.5 line-clamp-2"
                                   x-text="ticket.title"></p>

                                {{-- Type + assignee --}}
                                <div class="flex items-center justify-between gap-2">
                                    <span
                                        class="text-[10px] px-1.5 py-0.5 rounded font-medium"
                                        :class="$store.board.typeStyle(ticket.type)"
                                        x-text="$store.board.typeIcon(ticket.type) + ' ' + (ticket.type ? ticket.type.charAt(0).toUpperCase() + ticket.type.slice(1) : 'Task')">
                                    </span>

                                    <div
                                        x-show="ticket.assignee"
                                        class="w-5 h-5 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-[9px] font-bold text-white flex-shrink-0"
                                        :title="ticket.assignee"
                                        x-text="ticket.assignee ? ticket.assignee.charAt(0).toUpperCase() : ''">
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Empty column --}}
                        <div x-show="col.tickets.length === 0"
                             class="flex items-center justify-center h-16 rounded-lg border border-dashed border-gray-200">
                            <span class="text-[11px] text-gray-300">Empty</span>
                        </div>

                    </div>
                </div>
            </template>
        </div>

    </div>
</x-mcp::app>
