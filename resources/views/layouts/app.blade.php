<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Világműhely') }} - @yield('title', 'Workshop Crystals')</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('meta_description', 'Világműhely - Grow your unique 3D crystal through content creation. Gamified content management with visual rewards.')">
    <meta name="keywords" content="@yield('meta_keywords', 'crystal visualization, content creation, gamification, 3D crystals, creative platform')">
    <meta name="author" content="Webtech Solutions">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('og_title', config('app.name') . ' - Grow Your Crystal')">
    <meta property="og:description" content="@yield('og_description', 'Grow your unique 3D crystal through content creation. Every piece of content shapes your crystal\'s geometry, colors, and glow.')">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
    <meta property="og:locale" content="en_US">
    <meta property="og:site_name" content="{{ config('app.name') }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="@yield('twitter_title', config('app.name') . ' - Grow Your Crystal')">
    <meta name="twitter:description" content="@yield('twitter_description', 'Grow your unique 3D crystal through content creation.')">
    <meta name="twitter:image" content="@yield('twitter_image', asset('images/twitter-card.jpg'))">

    <!-- Additional Meta -->
    <meta name="theme-color" content="#9333ea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XRJ6TXDHGC"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-XRJ6TXDHGC');
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')

    <!-- Structured Data (JSON-LD) -->
    @php
        $orgSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Világműhely',
            'url' => url('/'),
            'logo' => asset('images/logo.png'),
            'description' => 'Creative platform for growing unique 3D crystals through content creation',
            'sameAs' => [
                'https://www.facebook.com/profile.php?id=61575724097365',
                'https://discord.gg/QJAcDyjA',
                'https://www.tiktok.com/@vilagmuhely',
                'https://www.instagram.com/vilagmuhely/'
            ]
        ];
    @endphp
    <script type="application/ld+json">
    {!! json_encode($orgSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    @stack('structured_data')
</head>
<body class="bg-gradient-to-br from-gray-900 via-purple-900 to-indigo-900">
    <nav class="bg-black/20 backdrop-blur-lg border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-xl font-bold text-white">
                        Világműhely
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('library.index') }}" class="text-gray-200 hover:text-purple-300 transition">
                        Content Library
                    </a>
                    <a href="{{ route('crystals.gallery') }}" class="text-gray-200 hover:text-purple-300 transition">
                        Crystal Gallery
                    </a>
                    <a href="{{ route('blog.index') }}" class="text-gray-200 hover:text-purple-300 transition">
                        Blog
                    </a>
                    @auth
                        <a href="{{ route('crystals.show', auth()->user()) }}" class="text-gray-200 hover:text-purple-300 transition">
                            My Crystal
                        </a>
                    @endauth
                    @auth
                    <a href="/admin" class="text-gray-200 hover:text-purple-300 transition">
                        Dashboard
                    </a>
                    @else
                        <a href="/admin" class="text-gray-200 hover:text-purple-300 transition">
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="py-8">
        @yield('content')
    </main>

    <footer class="bg-black/20 backdrop-blur-lg border-t border-white/10 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center text-sm text-gray-300">
                <div class="flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-1">
                    <span class="font-semibold text-white">Világműhely Admin</span>
                    <span class="hidden sm:inline">—</span>
                    <span class="italic">"Where stories are forged."</span>
                </div>
                <div class="mt-2 flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-1">
                    <span>&copy; {{ date('Y') }}</span>
                    <span class="hidden sm:inline">·</span>
                    <span>Operated by <a href="https://webtech-solutions.hu" target="_blank" class="text-purple-400 hover:text-purple-300 hover:underline transition">Webtech Solutions</a></span>
                    <span class="hidden sm:inline">|</span>
                    <span>Build: {{ config('version.version') }}</span>
                </div>
                <div class="mt-2 flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-4">
                    <a href="https://webtech-solutions.hu/terms-and-conditions" target="_blank" class="text-purple-300 hover:text-purple-100 hover:underline transition">
                        Terms and Conditions
                    </a>
                    <span class="hidden sm:inline">|</span>
                    <a href="https://webtech-solutions.hu/privacy-policy" target="_blank" class="text-purple-300 hover:text-purple-100 hover:underline transition">
                        Privacy Policy
                    </a>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
