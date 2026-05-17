<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Issues') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        {{-- Flash-free dark mode: apply class before paint --}}
        <script>
            (function() {
                var saved = localStorage.theme;
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (saved === 'dark' || (!saved && prefersDark)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-800">
        <div class="h-screen flex overflow-hidden">
            {{-- Sidebar --}}
            <aside class="w-60 flex-shrink-0 bg-white border-r border-gray-200 dark:bg-[#111114] dark:border-white/[0.06] flex flex-col text-[13px]">

                {{-- Project switcher --}}
                @if(isset($project) && $project instanceof \App\Models\Project)
                <div class="px-3 pt-3 pb-2 border-b border-gray-200 dark:border-white/[0.06]"
                     x-data="{ open: false }">
                    <button @click="open = !open"
                            class="w-full flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/[0.06] transition focus:outline-none group">
                        <div class="w-6 h-6 rounded-md bg-indigo-600 flex items-center justify-center text-[10px] font-bold text-white flex-shrink-0">
                            {{ substr($project->key, 0, 2) }}
                        </div>
                        <div class="flex-1 min-w-0 text-left">
                            <p class="font-semibold text-gray-900 dark:text-white truncate leading-tight">{{ $project->name }}</p>
                        </div>
                        <svg class="w-3.5 h-3.5 text-gray-400 transition-transform group-hover:text-gray-600 dark:group-hover:text-gray-300"
                             :class="open ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Project switcher dropdown --}}
                    <div x-show="open" @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         class="mt-1 bg-white dark:bg-[#1f1f23] border border-gray-200 dark:border-white/[0.08] rounded-xl shadow-lg dark:shadow-black/40 overflow-hidden z-50"
                         style="display:none;">
                        @if(isset($sidebarProjects))
                        @foreach($sidebarProjects as $sp)
                        <a href="{{ route('projects.board', $sp) }}"
                           class="flex items-center gap-2.5 px-3 py-2 hover:bg-gray-50 dark:hover:bg-white/[0.05] transition
                                  {{ $sp->id === $project->id ? 'bg-gray-50 dark:bg-white/[0.05]' : '' }}">
                            <div class="w-5 h-5 rounded bg-indigo-600 flex items-center justify-center text-[9px] font-bold text-white flex-shrink-0">
                                {{ substr($sp->key, 0, 2) }}
                            </div>
                            <span class="text-gray-700 dark:text-gray-300 truncate">{{ $sp->name }}</span>
                            @if($sp->id === $project->id)
                            <svg class="w-3 h-3 ml-auto text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            @endif
                        </a>
                        @endforeach
                        @endif
                        <div class="border-t border-gray-100 dark:border-white/[0.06]">
                            <a href="{{ route('projects.index') }}"
                               class="flex items-center gap-2 px-3 py-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/[0.05] transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                All projects
                            </a>
                        </div>
                    </div>
                </div>

                <nav class="flex-1 px-2 py-2 overflow-y-auto space-y-4">

                    {{-- Project nav --}}
                    <div class="space-y-0.5">
                        <a href="{{ route('projects.board', $project) }}"
                           class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition {{ request()->routeIs('projects.board') ? 'bg-gray-100 text-gray-900 dark:bg-white/[0.08] dark:text-white' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/[0.06]' }}">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125z"/></svg>
                            Board
                        </a>
                        <a href="{{ route('epics.index', $project) }}"
                           class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition {{ request()->routeIs('epics.*') ? 'bg-gray-100 text-gray-900 dark:bg-white/[0.08] dark:text-white' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/[0.06]' }}">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
                            Epics
                        </a>
                        <a href="{{ route('docs.index', $project) }}"
                           class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition {{ request()->routeIs('docs.*') ? 'bg-gray-100 text-gray-900 dark:bg-white/[0.08] dark:text-white' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/[0.06]' }}">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                            Docs
                        </a>
                    </div>

                    {{-- Channels --}}
                    <div x-data="{ open: true }">
                        <div class="flex items-center gap-1 px-2 mb-0.5 group">
                            <button @click="open = !open" class="flex items-center gap-1 flex-1 min-w-0">
                                <svg class="w-3 h-3 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                <span class="text-[10px] font-semibold uppercase tracking-[0.08em] text-gray-400 ml-0.5">Channels</span>
                            </button>
                            <button onclick="document.getElementById('new-channel-modal').classList.remove('hidden')"
                                    title="New channel"
                                    class="p-0.5 rounded text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-100 dark:hover:bg-white/[0.06] transition flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            </button>
                        </div>
                        <div x-show="open" class="space-y-0.5">
                            @if(isset($sidebarChannels) && $sidebarChannels->isNotEmpty())
                            @foreach($sidebarChannels as $ch)
                            <a href="{{ route('channels.show', [$project, $ch]) }}"
                               class="flex items-center gap-2 px-2 py-1.5 rounded-lg transition {{ request()->is('*/channels/'.$ch->id) ? 'bg-gray-100 text-gray-900 dark:bg-white/[0.08] dark:text-white' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/[0.06]' }}">
                                <span class="text-gray-400 font-medium">#</span>
                                <span class="truncate text-[13px]">{{ $ch->name }}</span>
                            </a>
                            @endforeach
                            @else
                            <button onclick="document.getElementById('new-channel-modal').classList.remove('hidden')"
                                    class="w-full flex items-center gap-2 px-2 py-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/[0.04] transition text-[12px]">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                Add a channel
                            </button>
                            @endif
                        </div>
                    </div>

                    {{-- Direct Messages --}}
                    @if(isset($sidebarMembers))
                    <div x-data="{ open: true }">
                        <button @click="open = !open"
                                class="w-full flex items-center gap-1 px-2 mb-0.5">
                            <svg class="w-3 h-3 text-gray-400 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            <span class="text-[10px] font-semibold uppercase tracking-[0.08em] text-gray-400 flex-1 text-left">Direct Messages</span>
                        </button>
                        <div x-show="open" class="space-y-0.5">
                            @foreach($sidebarMembers as $member)
                            @if($member->id !== auth()->id())
                            <a href="{{ route('dm.show', [$project, $member]) }}"
                               class="flex items-center gap-2 px-2 py-1.5 rounded-lg transition text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/[0.06]">
                                <div class="w-5 h-5 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-[9px] font-semibold text-white flex-shrink-0">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                                <span class="truncate">{{ $member->name }}</span>
                            </a>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                </nav>

                {{-- Project settings + user footer --}}
                <div class="border-t border-gray-200 dark:border-white/[0.06] px-2 py-2 space-y-0.5">

                    {{-- Huddle --}}
                    @php
                        $activeHuddle = $project->huddleSessions()->where('is_active', true)->first();
                        $initialParticipants = $activeHuddle
                            ? \App\Models\User::whereIn('id', $activeHuddle->participants ?? [])->get(['id','name'])->toArray()
                            : [];
                    @endphp
                    <div id="huddle-section"
                         data-huddle-start="{{ route('huddle.start', $project) }}"
                         data-huddle-signal="{{ route('huddle.signal', $project) }}"
                         data-huddle-leave-base="{{ url('projects/' . $project->id . '/huddle') }}"
                         data-user-id="{{ auth()->id() }}"
                         data-project-id="{{ $project->id }}"
                         data-active-huddle-id="{{ $activeHuddle?->id ?? '' }}"
                         data-active-participants="{{ json_encode($initialParticipants) }}">

                        {{-- Idle: no active huddle --}}
                        <button id="huddle-start-btn"
                                class="w-full flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition text-gray-500 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:text-emerald-400 dark:hover:bg-emerald-900/20">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                            <span class="text-[13px]">Start Huddle</span>
                        </button>

                        {{-- Active huddle card (shown for everyone while huddle is live) --}}
                        <div id="huddle-active-card" class="hidden rounded-xl border p-2.5 bg-emerald-50 border-emerald-200 dark:bg-emerald-900/20 dark:border-emerald-700/40">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="relative flex h-2 w-2 flex-shrink-0">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                    </span>
                                    <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">Huddle</span>
                                </div>
                                {{-- Leave (only when participating) --}}
                                <button id="huddle-leave-btn"
                                        class="hidden text-[11px] font-medium text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition">
                                    Leave
                                </button>
                            </div>
                            <div id="huddle-participants-list" class="space-y-1 mb-2"></div>
                            {{-- Join (when not yet participating) --}}
                            <button id="huddle-join-btn"
                                    class="w-full px-2 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-medium transition">
                                Join
                            </button>
                            {{-- Mute (when participating) --}}
                            <button id="huddle-mute-btn"
                                    class="hidden w-full flex items-center justify-center gap-1.5 px-2 py-1 rounded-lg bg-emerald-100 hover:bg-emerald-200 dark:bg-emerald-800/40 dark:hover:bg-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-[11px] font-medium transition">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/></svg>
                                <span>Mute</span>
                            </button>
                        </div>
                    </div>
                    <a href="{{ route('projects.show', $project) }}"
                       class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition {{ request()->routeIs('projects.show') ? 'bg-gray-100 text-gray-900 dark:bg-white/[0.08] dark:text-white' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/[0.06]' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Project Settings
                    </a>

                    {{-- User --}}
                    <div class="mt-1 pt-1 border-t border-gray-100 dark:border-white/[0.04]" x-data="{ open: false }">
                        <button @click="open = !open" @mouseenter="open = true"
                                class="w-full flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/[0.06] focus:outline-none transition group">
                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-gray-500 to-gray-600 flex items-center justify-center text-[10px] font-semibold text-white flex-shrink-0">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <span class="flex-1 text-left text-gray-700 dark:text-gray-300 truncate">{{ auth()->user()->name }}</span>
                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
                        </button>
                        <div x-show="open" @click.outside="open = false" @mouseleave="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-75"
                             class="absolute bottom-16 left-3 right-3 bg-white border border-gray-200 dark:bg-[#1f1f23] dark:border-white/[0.08] rounded-lg shadow-xl overflow-hidden z-50"
                             style="display:none;">
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.05] transition">Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center gap-2 px-3 py-2 text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.05] transition">Sign out</button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- New channel modal --}}
                <div id="new-channel-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div class="bg-white dark:bg-[#1f1f23] rounded-xl shadow-2xl p-6 w-full max-w-sm mx-4">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Create channel</h2>
                        <form method="POST" action="{{ route('channels.store', $project) }}">
                            @csrf
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Name</label>
                                    <input type="text" name="name" placeholder="e.g. design"
                                           class="w-full rounded-lg border border-gray-300 dark:border-white/[0.1] bg-white dark:bg-white/[0.04] text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Description (optional)</label>
                                    <input type="text" name="description"
                                           class="w-full rounded-lg border border-gray-300 dark:border-white/[0.1] bg-white dark:bg-white/[0.04] text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input type="checkbox" name="is_private" value="1" class="rounded border-gray-300">
                                    Private channel
                                </label>
                            </div>
                            <div class="flex gap-2 mt-5">
                                <button type="button" onclick="document.getElementById('new-channel-modal').classList.add('hidden')"
                                        class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-white/[0.1] text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/[0.04] transition">Cancel</button>
                                <button type="submit"
                                        class="flex-1 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">Create</button>
                            </div>
                        </form>
                    </div>
                </div>

                @else
                {{-- No project context – simple nav --}}
                <div class="h-14 flex items-center px-4 border-b border-gray-200 dark:border-white/[0.06]">
                    <a href="/projects" class="flex items-center gap-2.5 group">
                        <div class="w-7 h-7 rounded-lg bg-gray-100 dark:bg-white/[0.08] flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"/></svg>
                        </div>
                        <span class="text-[13px] font-semibold text-gray-900 dark:text-white tracking-tight">Issues</span>
                    </a>
                </div>
                <nav class="flex-1 px-3 py-3 overflow-y-auto">
                    <div class="space-y-0.5">
                        <a href="{{ route('projects.index') }}" class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition {{ request()->routeIs('projects.*') ? 'bg-gray-100 text-gray-900 dark:bg-white/[0.08] dark:text-white' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/[0.06]' }}">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25a2.25 2.25 0 012.25 2.25v2.25a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 8.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25A2.25 2.25 0 0113.5 8.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25a2.25 2.25 0 01-2.25-2.25v-2.25z"/></svg>
                            Projects
                        </a>
                    </div>
                </nav>
                <div class="border-t border-gray-200 dark:border-white/[0.06] p-3" x-data="{ open: false }">
                    <button @click="open = !open" @mouseenter="open = true"
                            class="w-full flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/[0.06] focus:outline-none transition">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-gray-500 to-gray-600 flex items-center justify-center text-[11px] font-semibold text-white">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span class="flex-1 text-left text-[13px] text-gray-700 dark:text-gray-300 truncate">{{ auth()->user()->name }}</span>
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
                    </button>
                    <div x-show="open" @click.outside="open = false" @mouseleave="open = false"
                         class="absolute bottom-full left-3 right-3 mb-1 bg-white border border-gray-200 dark:bg-[#1f1f23] dark:border-white/[0.08] rounded-lg shadow-xl overflow-hidden z-50"
                         style="display:none;">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.05] transition">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.05] transition">Sign out</button>
                        </form>
                    </div>
                </div>
                @endif
            </aside>

            {{-- Main content --}}
            <div class="flex-1 flex flex-col min-w-0 bg-gray-50">
                @isset($header)
                <header class="h-14 flex items-center px-6 border-b border-gray-200 bg-white/90 backdrop-blur-md flex-shrink-0 gap-3">
                    <div class="flex-1 flex items-center justify-between min-w-0">
                        {{ $header }}
                    </div>
                    <button id="theme-toggle" title="Toggle light/dark mode" class="flex-shrink-0 p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-500 dark:hover:text-gray-200 dark:hover:bg-white/[0.06] transition">
                        {{-- Sun icon (shown in dark mode) --}}
                        <svg id="theme-icon-sun" class="w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/></svg>
                        {{-- Moon icon (shown in light mode) --}}
                        <svg id="theme-icon-moon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/></svg>
                    </button>
                </header>
                @endisset

                <main class="flex-1 overflow-auto">
                    @if(session('success'))
                        <div class="mx-6 mt-4 px-4 py-2.5 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mx-6 mt-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mx-6 mt-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>

        <script>
            (function () {
                const html = document.documentElement;
                const toggle = document.getElementById('theme-toggle');
                const sunIcon = document.getElementById('theme-icon-sun');
                const moonIcon = document.getElementById('theme-icon-moon');

                function applyTheme(dark) {
                    html.classList.toggle('dark', dark);
                    if (sunIcon && moonIcon) {
                        sunIcon.classList.toggle('hidden', !dark);
                        moonIcon.classList.toggle('hidden', dark);
                    }
                }

                // Sync icons on load
                applyTheme(html.classList.contains('dark'));

                if (toggle) {
                    toggle.addEventListener('click', function () {
                        const isDark = !html.classList.contains('dark');
                        applyTheme(isDark);
                        localStorage.theme = isDark ? 'dark' : 'light';
                    });
                }
            })();
        </script>
        @stack('scripts')
    </body>
</html>
