<!DOCTYPE html>
<html lang="id" data-theme="corporate">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sistem Penjadwalan Manpower Line Operator' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-base-200 min-h-screen text-base-content font-sans antialiased flex flex-col">
    <!-- Top Navigation Header -->
    <header class="bg-base-100 border-b border-base-300 sticky top-0 z-30 shadow-xs">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">
                <!-- Left: Top Navigation Menu (Tabs Border Bottom) -->
                <nav class="hidden md:flex items-center h-full">
                    <div class="tabs tabs-border h-full items-end">
                        <a 
                            href="{{ url('/') }}" 
                            class="tab h-14 text-xs gap-1.5 {{ request()->is('/') ? 'tab-active font-bold text-neutral' : 'text-base-content/70 hover:text-base-content' }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Penjadwalan Roster
                        </a>

                        <a 
                            href="{{ url('/manpower') }}" 
                            class="tab h-14 text-xs gap-1.5 {{ request()->is('manpower*') ? 'tab-active font-bold text-neutral' : 'text-base-content/70 hover:text-base-content' }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Master Manpower
                        </a>

                        <a 
                            href="{{ url('/lines') }}" 
                            class="tab h-14 text-xs gap-1.5 {{ request()->is('lines*') ? 'tab-active font-bold text-neutral' : 'text-base-content/70 hover:text-base-content' }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Master Line & Station
                        </a>

                        <a 
                            href="{{ url('/leaders') }}" 
                            class="tab h-14 text-xs gap-1.5 {{ request()->is('leaders*') ? 'tab-active font-bold text-neutral' : 'text-base-content/70 hover:text-base-content' }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Master Leader
                        </a>
                    </div>
                </nav>

                <!-- Mobile Navigation Dropdown (Left side on mobile) -->
                <div class="flex items-center md:hidden">
                    <div class="dropdown dropdown-start">
                        <button tabindex="0" class="btn btn-square btn-ghost btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-5 h-5 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <ul tabindex="0" class="dropdown-content menu p-2 shadow-lg bg-base-100 rounded-box w-52 text-xs z-50 border border-base-200 mt-2">
                            <li><a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active font-bold' : '' }}">📅 Penjadwalan Roster</a></li>
                            <li><a href="{{ url('/manpower') }}" class="{{ request()->is('manpower*') ? 'active font-bold' : '' }}">👥 Master Manpower</a></li>
                            <li><a href="{{ url('/lines') }}" class="{{ request()->is('lines*') ? 'active font-bold' : '' }}">🏭 Master Line & Station</a></li>
                            <li><a href="{{ url('/leaders') }}" class="{{ request()->is('leaders*') ? 'active font-bold' : '' }}">👔 Master Leader</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Right: Date -->
                <div class="flex items-center gap-2.5">
                    <span class="text-xs text-base-content/60 font-mono hidden sm:inline-block">
                        {{ now()->translatedFormat('l, d M Y') }}
                    </span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area (Full Width Maximized) -->
    <main class="flex-1 w-full px-4 sm:px-6 lg:px-8 py-4 md:py-6 overflow-x-auto">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Minimal Footer -->
    <footer class="border-t border-base-300 bg-base-100/40 py-2 px-4 sm:px-6 lg:px-8 text-[11px] text-base-content/40 text-center sm:text-left">
        <span>Sistem Penjadwalan Manpower Line Operator</span>
    </footer>

    @livewireScripts
</body>
</html>
