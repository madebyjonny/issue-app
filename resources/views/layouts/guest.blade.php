<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Issues') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-surface-700 text-gray-200">
        <div class="min-h-screen flex flex-col justify-center items-center px-4">
            <div class="mb-8">
                <a href="/" class="flex items-center gap-2.5 text-xl font-bold text-white">
                    <svg class="w-8 h-8 text-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"/></svg>
                    Issues
                </a>
            </div>

            <div class="w-full sm:max-w-md px-8 py-8 bg-surface-600 border border-white/5 rounded-xl shadow-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
