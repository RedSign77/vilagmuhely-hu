<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Világműhely') }} - Grow Your Crystal</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Create content and watch your unique 3D crystal evolve. Join our community of creators with gamified content management and visual progress tracking.">
    <meta name="keywords" content="crystal growth, content creation platform, gamification, 3D visualization, creative community, RPG content, digital content">
    <meta name="author" content="Webtech Solutions">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Világműhely - Grow Your Crystal Through Creation">
    <meta property="og:description" content="Every piece of content you create shapes your unique 3D crystal. More content, more diversity, more interaction - your crystal evolves.">
    <meta property="og:image" content="{{ asset('images/og-default.jpg') }}">
    <meta property="og:locale" content="en_US">
    <meta property="og:site_name" content="{{ config('app.name') }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="Világműhely - Grow Your Crystal Through Creation">
    <meta name="twitter:description" content="Every piece of content you create shapes your unique 3D crystal. More content, more diversity, more interaction - your crystal evolves.">
    <meta name="twitter:image" content="{{ asset('images/twitter-card.jpg') }}">

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

    <!-- Structured Data (JSON-LD) -->
    @php
        $websiteSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Világműhely',
            'url' => url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => url('/library') . '?q={search_term_string}',
                'query-input' => 'required name=search_term_string'
            ]
        ];
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
    {!! json_encode($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    <script type="application/ld+json">
    {!! json_encode($orgSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
</head>
<body class="antialiased bg-gradient-to-br from-gray-900 via-purple-900 to-indigo-900 text-white">
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-black/20 backdrop-blur-lg border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-2">
                    <span class="text-2xl">💎</span>
                    <span class="text-xl font-bold">Világműhely</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/library" class="hover:text-purple-300 transition">Content Library</a>
                    <a href="/crystals" class="hover:text-purple-300 transition">Crystal Gallery</a>
                    <a href="/blog" class="hover:text-purple-300 transition">Blog</a>
                    <a href="/changelog" class="hover:text-purple-300 transition">Change Log</a>
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
                    Grow Your Crystal<br>Through Creation
                </h1>
                <p class="text-xl md:text-2xl text-gray-300 max-w-3xl mx-auto mb-8">
                    Every piece of content you create shapes your unique 3D crystal.
                    More content, more diversity, more interaction - your crystal evolves.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @guest
                    <a href="/admin/register" class="px-8 py-4 bg-gradient-to-r from-purple-600 to-cyan-600 hover:from-purple-700 hover:to-cyan-700 rounded-lg text-lg font-semibold shadow-lg shadow-purple-500/50 transition transform hover:scale-105">
                        Start Growing
                    </a>
                    <a href="/crystals" class="px-8 py-4 bg-white/10 hover:bg-white/20 backdrop-blur-lg rounded-lg text-lg font-semibold border border-white/20 transition">
                        View Gallery
                    </a>
                    @else
                    <a href="/admin" class="px-8 py-4 bg-gradient-to-r from-purple-600 to-cyan-600 hover:from-purple-700 hover:to-cyan-700 rounded-lg text-lg font-semibold shadow-lg shadow-purple-500/50 transition transform hover:scale-105">
                        Dashboard 💎
                    </a>
                    <a href="/crystals" class="px-8 py-4 bg-white/10 hover:bg-white/20 backdrop-blur-lg rounded-lg text-lg font-semibold border border-white/20 transition">
                        Crystal Gallery
                    </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    <!-- Top Crystals Leaderboard -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-black/20">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold mb-4">Top Crystals</h2>
                <p class="text-xl text-gray-400">The most brilliant creators in our community</p>
            </div>

            @if(isset($topCrystals) && $topCrystals->count() > 0)
            <div class="grid md:grid-cols-3 gap-8">
                @foreach($topCrystals as $index => $metric)
                <div class="relative">
                    @if($index === 0)
                    <div class="absolute -top-4 -right-4 w-14 h-14 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center shadow-lg shadow-yellow-500/50 border-2 border-yellow-300">
                        <svg class="w-8 h-8 text-yellow-900" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                            <path d="M6 21H18V22C18 22.5523 17.5523 23 17 23H7C6.44772 23 6 22.5523 6 22V21Z"/>
                            <path d="M7 21V19H17V21H7Z"/>
                        </svg>
                    </div>
                    @elseif($index === 1)
                    <div class="absolute -top-4 -right-4 w-14 h-14 bg-gradient-to-br from-gray-300 to-gray-500 rounded-full flex items-center justify-center shadow-lg shadow-gray-400/50 border-2 border-gray-200">
                        <svg class="w-8 h-8 text-gray-700" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                            <path d="M6 21H18V22C18 22.5523 17.5523 23 17 23H7C6.44772 23 6 22.5523 6 22V21Z"/>
                            <path d="M7 21V19H17V21H7Z"/>
                        </svg>
                    </div>
                    @elseif($index === 2)
                    <div class="absolute -top-4 -right-4 w-14 h-14 bg-gradient-to-br from-orange-400 to-orange-600 rounded-full flex items-center justify-center shadow-lg shadow-orange-500/50 border-2 border-orange-300">
                        <svg class="w-8 h-8 text-orange-900" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                            <path d="M6 21H18V22C18 22.5523 17.5523 23 17 23H7C6.44772 23 6 22.5523 6 22V21Z"/>
                            <path d="M7 21V19H17V21H7Z"/>
                        </svg>
                    </div>
                    @endif
                    <div class="bg-gradient-to-br from-purple-900/50 to-cyan-900/50 rounded-2xl p-6 border border-white/10 shadow-2xl h-full">
                        <div class="text-center mb-4">
                            <div class="w-20 h-20 mx-auto mb-3 rounded-full bg-gradient-to-br from-purple-500 to-cyan-500 flex items-center justify-center overflow-hidden">
                                <span class="text-3xl">💎</span>
                            </div>
                            <h3 class="text-xl font-bold">{{ $metric->user->anonymized_name }}</h3>
                            <p class="text-sm text-gray-400">Rank #{{ $index + 1 }}</p>
                        </div>

                        <!-- Crystal Preview Container -->
                        <div id="crystal-viewer-{{ $metric->user->id }}"
                             class="crystal-preview-container mb-4"
                             data-crystal-viewer
                             data-user-id="{{ $metric->user->id }}"
                             data-size="small"
                             data-auto-rotate="true"
                             data-rotation-speed="0.005"
                             style="height: 200px; border-radius: 0.5rem; background: rgba(0,0,0,0.2);">
                        </div>

                        <!-- Crystal Stats -->
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Facets:</span>
                                <span class="font-bold text-purple-400">{{ $metric->facet_count }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Glow:</span>
                                <span class="font-bold text-cyan-400">{{ number_format($metric->glow_intensity * 100, 1) }}%</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Content:</span>
                                <span class="font-bold text-pink-400">{{ $metric->total_content_count }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Diversity:</span>
                                <span class="font-bold text-green-400">{{ number_format($metric->diversity_index * 100, 1) }}%</span>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-white/10">
                            <a href="/crystals/{{ $metric->user->id }}" class="block text-center text-purple-400 hover:text-purple-300 transition text-sm">
                                View Full Crystal →
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <div class="text-6xl mb-4">💎</div>
                <p class="text-xl text-gray-400">Be the first to grow a crystal!</p>
                <p class="text-gray-500 mt-2">Start creating content to see your crystal evolve</p>
            </div>
            @endif

            <div class="text-center mt-12">
                <a href="/crystals" class="inline-flex items-center text-purple-400 hover:text-purple-300 transition text-lg">
                    <span>View Full Leaderboard</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">How Crystals Evolve</h2>
                <p class="text-xl text-gray-400">Your crystal reflects your creative journey</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Facets -->
                <div class="relative pt-8">
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-2xl font-bold shadow-lg z-10">
                        ◆
                    </div>
                    <div class="bg-white/5 backdrop-blur-lg rounded-lg p-8 pt-12 border border-white/10 h-full relative z-0">
                        <h3 class="text-2xl font-bold mb-3 text-center">Facets (Complexity)</h3>
                        <p class="text-gray-400 text-center mb-4">
                            More content = more facets. Diversity across content types adds extra facets.
                        </p>
                        <div class="bg-black/20 rounded p-4 text-sm">
                            <div class="font-mono text-cyan-400">
                                facets = 4 + (content_count / 2) + (diversity × 20)
                            </div>
                            <div class="text-gray-500 mt-2">Range: 4-50 facets</div>
                        </div>
                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-purple-500 rounded-full mr-2"></span>
                                <span class="text-gray-400">Simple: 4-10 facets</span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-pink-500 rounded-full mr-2"></span>
                                <span class="text-gray-400">Complex: 30+ facets</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Glow -->
                <div class="relative pt-8">
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-12 h-12 bg-gradient-to-br from-cyan-500 to-blue-500 rounded-full flex items-center justify-center text-2xl font-bold shadow-lg z-10">
                        ✨
                    </div>
                    <div class="bg-white/5 backdrop-blur-lg rounded-lg p-8 pt-12 border border-white/10 h-full relative z-0">
                        <h3 class="text-2xl font-bold mb-3 text-center">Glow (Popularity)</h3>
                        <p class="text-gray-400 text-center mb-4">
                            Views, downloads, and helpful ratings make your crystal shine brighter.
                        </p>
                        <div class="bg-black/20 rounded p-4 text-sm">
                            <div class="font-mono text-cyan-400">
                                glow = log₁₀(score + 1) / 4
                            </div>
                            <div class="text-gray-500 mt-2">
                                score = views×0.3 + downloads×0.5 + helpful_ratings×1.0
                            </div>
                        </div>
                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-cyan-500 rounded-full mr-2 opacity-30"></span>
                                <span class="text-gray-400">Dim: 0-0.3 glow</span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-cyan-400 rounded-full mr-2 animate-pulse"></span>
                                <span class="text-gray-400">Radiant: 0.7+ glow</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Colors -->
                <div class="relative pt-8">
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-12 h-12 bg-gradient-to-br from-pink-500 to-orange-500 rounded-full flex items-center justify-center text-2xl font-bold shadow-lg z-10">
                        🎨
                    </div>
                    <div class="bg-white/5 backdrop-blur-lg rounded-lg p-8 pt-12 border border-white/10 h-full relative z-0">
                        <h3 class="text-2xl font-bold mb-3 text-center">Colors (Categories)</h3>
                        <p class="text-gray-400 text-center mb-4">
                            Your crystal shows colors based on your top 3 content categories.
                        </p>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Digital Files</span>
                                <span class="flex items-center">
                                    <span class="w-4 h-4 rounded" style="background: #6366f1"></span>
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Image Gallery</span>
                                <span class="flex items-center">
                                    <span class="w-4 h-4 rounded" style="background: #ec4899"></span>
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Articles</span>
                                <span class="flex items-center">
                                    <span class="w-4 h-4 rounded" style="background: #10b981"></span>
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">RPG Modules</span>
                                <span class="flex items-center">
                                    <span class="w-4 h-4 rounded" style="background: #f59e0b"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Content Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-black/20">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold mb-4">Featured Content</h2>
                <p class="text-xl text-gray-400">Explore the latest creations from our community</p>
            </div>

            @if(isset($featuredContent) && $featuredContent->count() > 0)
            <div class="grid md:grid-cols-3 gap-8 mb-8">
                @foreach($featuredContent as $content)
                <a href="/library" class="group">
                    <div class="bg-gradient-to-br from-purple-900/50 to-cyan-900/50 rounded-2xl overflow-hidden border border-white/10 shadow-2xl h-full transition transform group-hover:scale-105 group-hover:shadow-purple-500/20">
                        <div class="aspect-video bg-gradient-to-br from-purple-600/20 to-cyan-600/20 flex items-center justify-center overflow-hidden">
                            @if($content->featured_image)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($content->featured_image) }}"
                                     alt="{{ $content->title }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="text-white/40">
                                    @if($content->type === 'digital_file')
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                    @elseif($content->type === 'image_gallery')
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    @elseif($content->type === 'markdown_post')
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    @elseif($content->type === 'article')
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    @elseif($content->type === 'rpg_module')
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2 group-hover:text-purple-300 transition line-clamp-2">{{ $content->title }}</h3>

                            <div class="flex items-center gap-2 mb-3">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                    {{ $content->type_label }}
                                </span>
                                @if($content->category)
                                    <span class="text-sm text-gray-400">{{ $content->category->name }}</span>
                                @endif
                            </div>

                            @if($content->excerpt)
                                <p class="text-gray-400 text-sm line-clamp-2 mb-4">{{ Str::limit(strip_tags($content->excerpt), 100) }}</p>
                            @endif

                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>By {{ $content->creator->anonymized_name }}</span>
                                <span class="text-purple-400 group-hover:text-purple-300">View →</span>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            <div class="text-center">
                <a href="/library" class="inline-flex items-center text-purple-400 hover:text-purple-300 transition text-lg">
                    <span>Browse Full Library</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
            @else
            <div class="text-center py-12">
                <div class="text-6xl mb-4">📚</div>
                <p class="text-xl text-gray-400">Content library coming soon!</p>
                <p class="text-gray-500 mt-2">Be the first to publish content</p>
            </div>
            @endif
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-4xl font-bold mb-6">Why Grow Your Crystal?</h2>
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Visual Progress</h3>
                                <p class="text-gray-400">Watch your crystal evolve in real-time 3D. Every piece of content shapes its geometry, colors, and glow.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-cyan-600 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Gamified Creation</h3>
                                <p class="text-gray-400">Content creation becomes addictive. See immediate visual feedback for every article, image, or file you publish.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-pink-600 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Community Recognition</h3>
                                <p class="text-gray-400">Compete on the leaderboard. Get recognized for quality, diversity, and community engagement.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-orange-600 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Unique Identity</h3>
                                <p class="text-gray-400">No two crystals are alike. Your creation style creates a unique visual signature.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="bg-gradient-to-br from-purple-600/30 to-cyan-600/30 rounded-2xl p-8 border border-white/20 backdrop-blur-lg">
                        <h3 class="text-2xl font-bold mb-6 text-center text-white">Crystal Metrics Breakdown</h3>
                        <div class="space-y-3">
                            <div class="bg-white/10 rounded-lg p-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-gray-200">Complexity (Facets)</span>
                                    <span class="text-xs font-bold text-purple-300">4-50</span>
                                </div>
                                <div class="text-[10px] text-gray-300">Based on content count + diversity</div>
                            </div>

                            <div class="bg-white/10 rounded-lg p-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-gray-200">Brightness (Glow)</span>
                                    <span class="text-xs font-bold text-cyan-300">0.00-1.00</span>
                                </div>
                                <div class="text-[10px] text-gray-300">Based on views, downloads, ratings</div>
                            </div>

                            <div class="bg-white/10 rounded-lg p-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-gray-200">Clarity (Purity)</span>
                                    <span class="text-xs font-bold text-pink-300">0.30-1.00</span>
                                </div>
                                <div class="text-[10px] text-gray-300">Based on engagement score</div>
                            </div>

                            <div class="bg-white/10 rounded-lg p-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-gray-200">Colors</span>
                                    <span class="text-xs font-bold text-orange-300">Up to 3</span>
                                </div>
                                <div class="text-[10px] text-gray-300">Top 3 content categories</div>
                            </div>
                        </div>

                        <div class="text-center pt-6 mt-6 border-t border-white/10">
                            <p class="text-sm text-gray-400 mb-4">Updates automatically every 30 minutes</p>
                            @guest
                            <a href="/admin/register" class="inline-block px-6 py-3 bg-gradient-to-r from-purple-600 to-cyan-600 hover:from-purple-700 hover:to-cyan-700 rounded-lg font-semibold transition transform hover:scale-105">
                                Start Your Crystal
                            </a>
                            @else
                            <a href="/admin" class="inline-block px-6 py-3 bg-gradient-to-r from-purple-600 to-cyan-600 hover:from-purple-700 hover:to-cyan-700 rounded-lg font-semibold transition transform hover:scale-105">
                                View Your Crystal
                            </a>
                            @endguest
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">
                Every Creator<br>
                <span class="bg-gradient-to-r from-purple-400 to-cyan-400 bg-clip-text text-transparent">
                    Has a Unique Crystal
                </span>
            </h2>
            <p class="text-xl text-gray-400 mb-8">
                Join {{ $stats['total_users'] ?? 1 }} creators who are building their crystals through quality content.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @guest
                <a href="/admin/register" class="px-8 py-4 bg-gradient-to-r from-purple-600 to-cyan-600 hover:from-purple-700 hover:to-cyan-700 rounded-lg text-lg font-semibold shadow-lg shadow-purple-500/50 transition transform hover:scale-105">
                    Create Your Crystal
                </a>
                <a href="/crystals" class="px-8 py-4 bg-white/10 hover:bg-white/20 backdrop-blur-lg rounded-lg text-lg font-semibold border border-white/20 transition">
                    Explore Gallery
                </a>
                @else
                <a href="/admin" class="px-8 py-4 bg-gradient-to-r from-purple-600 to-cyan-600 hover:from-purple-700 hover:to-cyan-700 rounded-lg text-lg font-semibold shadow-lg shadow-purple-500/50 transition transform hover:scale-105">
                    View Your Crystal
                </a>
                <a href="/crystals" class="px-8 py-4 bg-white/10 hover:bg-white/20 backdrop-blur-lg rounded-lg text-lg font-semibold border border-white/20 transition">
                    Crystal Gallery
                </a>
                @endguest
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
                        <li><a href="/admin" class="hover:text-white transition">Dashboard</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Resources</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="/changelog" class="hover:text-white transition">Change Log</a></li>
                        <li><a href="mailto:info@webtech-solutions.hu" class="hover:text-white transition">Help Center</a></li>
                        <li><a href="https://discord.gg/QJAcDyjA" target="_blank" class="hover:text-white transition">Community</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Connect</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="https://www.facebook.com/profile.php?id=61575724097365" target="_blank" class="hover:text-white transition">Facebook</a></li>
                        <li><a href="https://discord.gg/QJAcDyjA" target="_blank" class="hover:text-white transition">Discord</a></li>
                        <li><a href="https://www.tiktok.com/@vilagmuhely" target="_blank" class="hover:text-white transition">TikTok</a></li>
                        <li><a href="https://www.instagram.com/vilagmuhely/" target="_blank" class="hover:text-white transition">Instagram</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10 pt-8 text-center text-sm text-gray-400">
                <p>&copy; {{ date('Y') }} Világműhely. Operated by Webtech Solutions</p>
            </div>
        </div>
    </footer>

    <style>
        @@keyframes blob {
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
    </style>
</body>
</html>
