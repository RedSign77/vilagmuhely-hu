<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Világműhely') }} - Change Log</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gradient-to-br from-gray-900 via-purple-900 to-indigo-900 text-white">
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-black/20 backdrop-blur-lg border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-2">
                    <a href="/" class="flex items-center space-x-2">
                        <span class="text-2xl">💎</span>
                        <span class="text-xl font-bold">Világműhely</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/library" class="hover:text-purple-300 transition">Content Library</a>
                    <a href="/crystals" class="hover:text-purple-300 transition">Crystal Gallery</a>
                    <a href="/changelog" class="text-purple-300">Change Log</a>
                    @auth
                        <a href="/admin" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 rounded-lg transition">Dashboard</a>
                    @else
                        <a href="/admin/login" class="hover:text-purple-300 transition">Login</a>
                        <a href="/admin/register" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 rounded-lg transition">Join Now</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 px-4 sm:px-6 lg:px-8 overflow-hidden">
        <!-- Background Effects -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-cyan-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-pink-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
        </div>

        <div class="relative max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h1 class="text-5xl md:text-7xl font-bold mb-6 bg-gradient-to-r from-cyan-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
                    Change Log
                </h1>
                <p class="text-xl md:text-2xl text-gray-300 max-w-3xl mx-auto">
                    Track all updates, improvements, and new features added to Világműhely
                </p>
            </div>
        </div>
    </section>

    <!-- Changelog Content -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-black/20">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white/5 backdrop-blur-lg rounded-2xl p-8 md:p-12 border border-white/10 shadow-2xl">
                <div class="changelog-content">
                    {!! $content !!}
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/10 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <span class="text-2xl">💎</span>
                        <span class="text-xl font-bold">Világműhely</span>
                    </div>
                    <p class="text-gray-400 text-sm">
                        Where creativity crystallizes into something beautiful.
                    </p>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Explore</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="/crystals" class="hover:text-white transition">Crystal Gallery</a></li>
                        <li><a href="/library" class="hover:text-white transition">Content Library</a></li>
                        <li><a href="/admin" class="hover:text-white transition">Dashboard</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Resources</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="/changelog" class="hover:text-white transition">Change Log</a></li>
                        <li><a href="#" class="hover:text-white transition">Documentation</a></li>
                        <li><a href="#" class="hover:text-white transition">Help Center</a></li>
                        <li><a href="#" class="hover:text-white transition">Community</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Connect</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="#" class="hover:text-white transition">GitHub</a></li>
                        <li><a href="#" class="hover:text-white transition">Discord</a></li>
                        <li><a href="#" class="hover:text-white transition">Twitter</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10 pt-8 text-center text-sm text-gray-400">
                <p>&copy; {{ date('Y') }} Világműhely. Operated by Webtech Solutions</p>
            </div>
        </div>
    </footer>

    <style>
        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        .animation-delay-4000 {
            animation-delay: 4s;
        }

        /* Changelog specific styles */
        .changelog-content {
            color: #e5e7eb;
            line-height: 1.75;
        }

        .changelog-content h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 2rem;
            margin-top: 0;
            color: #ffffff;
            line-height: 1.2;
        }

        .changelog-content h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-top: 3rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            color: #c084fc;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            line-height: 1.3;
        }

        .changelog-content h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: #22d3ee;
            line-height: 1.4;
        }

        .changelog-content h4 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            color: #d1d5db;
            line-height: 1.5;
        }

        .changelog-content p {
            margin-bottom: 1.25rem;
            color: #d1d5db;
            line-height: 1.75;
        }

        .changelog-content ul {
            margin-bottom: 1.5rem;
            margin-top: 0.5rem;
            padding-left: 1.5rem;
        }

        .changelog-content ul li {
            margin-bottom: 0.5rem;
            color: #d1d5db;
            position: relative;
            padding-left: 0.5rem;
        }

        .changelog-content ul li::marker {
            color: #c084fc;
        }

        .changelog-content a {
            color: #c084fc;
            text-decoration: underline;
            transition: color 0.2s;
        }

        .changelog-content a:hover {
            color: #e9d5ff;
        }

        .changelog-content code {
            background-color: rgba(0, 0, 0, 0.3);
            color: #22d3ee;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            font-family: 'Courier New', monospace;
        }

        .changelog-content pre {
            background-color: rgba(0, 0, 0, 0.4);
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin-bottom: 1.5rem;
        }

        .changelog-content pre code {
            background-color: transparent;
            padding: 0;
        }

        .changelog-content blockquote {
            border-left: 4px solid #a855f7;
            padding-left: 1rem;
            font-style: italic;
            color: #9ca3af;
            margin: 1.5rem 0;
        }

        .changelog-content strong {
            color: #ffffff;
            font-weight: 600;
        }

        .changelog-content em {
            font-style: italic;
            color: #d1d5db;
        }

        .changelog-content hr {
            border: 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin: 2rem 0;
        }
    </style>
</body>
</html>
