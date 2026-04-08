<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Issues') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#0d0d0f] text-gray-300">
        <div class="h-screen flex overflow-hidden">
            {{-- Sidebar --}}
            <aside class="w-56 flex-shrink-0 bg-surface-600 border-r border-white/[0.06] flex flex-col">
                {{-- Brand --}}
                <div class="h-14 flex items-center px-4 border-b border-white/[0.06]">
                    <a href="/projects" class="flex items-center gap-2.5 group">
                        <div class="w-7 h-7 rounded-lg bg-white/[0.08] flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"/></svg>
                        </div>
                        <span class="text-[13px] font-semibold text-white tracking-tight">Issues</span>
                    </a>
                </div>

                <nav class="flex-1 px-3 py-3 overflow-y-auto space-y-5 text-[13px]">
                    {{-- Navigate section --}}
                    <div>
                        <p class="px-2 mb-1.5 text-[10px] font-semibold uppercase tracking-[0.08em] text-gray-400">Navigate</p>
                        <div class="space-y-0.5">
                            <a href="{{ route('projects.index') }}" class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition {{ request()->routeIs('projects.index') || request()->routeIs('projects.create') ? 'bg-white/[0.08] text-white' : 'text-gray-400 hover:text-white hover:bg-white/[0.06]' }}">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25a2.25 2.25 0 012.25 2.25v2.25a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 8.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25A2.25 2.25 0 0113.5 8.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25a2.25 2.25 0 01-2.25-2.25v-2.25z"/></svg>
                                Projects
                            </a>
                        </div>
                    </div>

                    {{-- Your Projects section --}}
                    @if(isset($sidebarProjects) && $sidebarProjects->count() > 0)
                    <div>
                        <p class="px-2 mb-1.5 text-[10px] font-semibold uppercase tracking-[0.08em] text-gray-400">Your Projects</p>
                        <div class="space-y-0.5">
                            @foreach($sidebarProjects as $sidebarProject)
                                @php
                                    $isCurrentProject = isset($project) && $project instanceof \App\Models\Project && $project->id === $sidebarProject->id;
                                @endphp
                                <div class="group flex items-center gap-1 rounded-lg transition {{ $isCurrentProject ? 'bg-white/[0.08]' : 'hover:bg-white/[0.06]' }}">
                                    <a href="{{ route('projects.board', $sidebarProject) }}" class="flex-1 flex items-center gap-2.5 px-2 py-1.5 {{ $isCurrentProject ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                                        <div class="w-4 h-4 rounded bg-white/[0.08] flex items-center justify-center text-[9px] font-semibold text-gray-300 flex-shrink-0">
                                            {{ substr($sidebarProject->key, 0, 1) }}
                                        </div>
                                        {{ $sidebarProject->name }}
                                    </a>
                                    <a href="{{ route('projects.show', $sidebarProject) }}" class="p-1.5 mr-1 text-gray-500 opacity-0 group-hover:opacity-100 hover:text-white transition" title="Settings">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Project section (contextual) --}}
                    @if(isset($project) && $project instanceof \App\Models\Project)
                    <div>
                        <p class="px-2 mb-1.5 text-[10px] font-semibold uppercase tracking-[0.08em] text-gray-400">{{ $project->key }}</p>
                        <div class="space-y-0.5">
                            <a href="{{ route('projects.board', $project) }}" class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition {{ request()->routeIs('projects.board') ? 'bg-white/[0.08] text-white' : 'text-gray-400 hover:text-white hover:bg-white/[0.06]' }}">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125z"/></svg>
                                Board
                            </a>
                            <a href="{{ route('projects.show', $project) }}" class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition {{ request()->routeIs('projects.show') ? 'bg-white/[0.08] text-white' : 'text-gray-400 hover:text-white hover:bg-white/[0.06]' }}">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Settings
                            </a>
                        </div>
                    </div>
                    @endif
                </nav>

                {{-- User footer with popup menu --}}
                <div class="border-t border-white/[0.06] p-3 relative" x-data="{ open: false }">
                    <button @click="open = !open" @mouseenter="open = true" class="w-full flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-white/[0.06] transition group">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-gray-500 to-gray-600 flex items-center justify-center text-[11px] font-semibold text-white ring-1 ring-white/[0.08]">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0 text-left">
                            <p class="text-[13px] text-gray-300 truncate leading-tight">{{ auth()->user()->name }}</p>
                        </div>
                        <svg class="w-3.5 h-3.5 text-gray-500 group-hover:text-gray-300 transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
                    </button>

                    {{-- Popup menu --}}
                    <div x-show="open"
                         @click.outside="open = false"
                         @mouseleave="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute bottom-full left-3 right-3 mb-1 bg-surface-300 border border-white/[0.1] rounded-lg shadow-xl overflow-hidden"
                         style="display: none;">
                        <div class="px-3 py-2.5 border-b border-white/[0.06]">
                            <p class="text-[12px] text-gray-300 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="py-1">
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-[13px] text-gray-300 hover:text-white hover:bg-white/[0.06] transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                Profile
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 text-[13px] text-gray-300 hover:text-white hover:bg-white/[0.06] transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Main content --}}
            <div class="flex-1 flex flex-col min-w-0 bg-surface-500">
                @isset($header)
                <header class="h-14 flex items-center justify-between px-6 border-b border-white/[0.06] bg-surface-500/80 backdrop-blur-md flex-shrink-0">
                    {{ $header }}
                </header>
                @endisset

                <main class="flex-1 overflow-auto">
                    @if(session('success'))
                        <div class="mx-6 mt-4 px-4 py-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded-lg text-emerald-400 text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mx-6 mt-4 px-4 py-2.5 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 text-sm">
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
