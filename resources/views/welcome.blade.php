<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Issues</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-surface-700 text-gray-200 flex items-center justify-center min-h-screen">
    <div class="text-center max-w-md px-6">
        <div class="flex items-center justify-center gap-3 mb-8">
            <svg class="w-10 h-10 text-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"/></svg>
            <h1 class="text-3xl font-bold text-white">Issues</h1>
        </div>
        <p class="text-gray-400 mb-8">AI-first issue tracking, designed for developers who live in their editor.</p>
        <div class="flex items-center justify-center gap-4">
            <a href="{{ route('login') }}" class="px-6 py-2.5 bg-accent hover:bg-accent-hover text-white font-medium rounded-lg transition">Sign in</a>
            <a href="{{ route('register') }}" class="px-6 py-2.5 bg-white/5 hover:bg-white/10 border border-white/10 text-white font-medium rounded-lg transition">Create account</a>
        </div>
    </div>
</body>
</html>
