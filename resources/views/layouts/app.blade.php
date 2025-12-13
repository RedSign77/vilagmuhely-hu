<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Világműhely') }} - @yield('title', 'Workshop Crystals')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <nav class="bg-white dark:bg-gray-800 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-xl font-bold text-gray-900 dark:text-white">
                        Világműhely
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/admin" class="text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                        Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-8">
        @yield('content')
    </main>

    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center text-sm text-gray-600 dark:text-gray-400">
                <div class="flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-1">
                    <span class="font-semibold text-gray-900 dark:text-gray-100">Világműhely Admin</span>
                    <span class="hidden sm:inline">—</span>
                    <span class="italic">"Where stories are forged."</span>
                </div>
                <div class="mt-2 flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-1">
                    <span>&copy; {{ date('Y') }}</span>
                    <span class="hidden sm:inline">·</span>
                    <span>Operated by Webtech Solutions</span>
                    <span class="hidden sm:inline">|</span>
                    <span>Build: {{ config('app.version') }}</span>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
