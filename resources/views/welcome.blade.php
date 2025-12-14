<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Világműhely') }} - Grow Your Crystal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                    <a href="/crystals" class="hover:text-purple-300 transition">Crystal Gallery</a>
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
                    <div class="absolute -top-4 -right-4 w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-400 rounded-full flex items-center justify-center text-2xl shadow-lg">
                        🏆
                    </div>
                    @endif
                    <div class="bg-gradient-to-br from-purple-900/50 to-cyan-900/50 rounded-2xl p-6 border border-white/10 shadow-2xl h-full">
                        <div class="text-center mb-4">
                            <div class="w-20 h-20 mx-auto mb-3 rounded-full bg-gradient-to-br from-purple-500 to-cyan-500 flex items-center justify-center overflow-hidden">
                                @if($metric->user->avatar)
                                <img src="{{ asset('storage/' . $metric->user->avatar) }}" alt="{{ $metric->user->name }}" class="w-full h-full rounded-full object-cover">
                                @else
                                <span class="text-3xl">💎</span>
                                @endif
                            </div>
                            <h3 class="text-xl font-bold">{{ $metric->user->name }}</h3>
                            <p class="text-sm text-gray-400">Rank #{{ $index + 1 }}</p>
                        </div>

                        <!-- Crystal Preview Container -->
                        <div class="crystal-preview-container mb-4"
                             data-crystal-viewer
                             data-user-id="{{ $metric->user->id }}"
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
                <div class="relative">
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-2xl font-bold shadow-lg">
                        ◆
                    </div>
                    <div class="bg-white/5 backdrop-blur-lg rounded-lg p-8 pt-12 border border-white/10 h-full">
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
                <div class="relative">
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-12 h-12 bg-gradient-to-br from-cyan-500 to-blue-500 rounded-full flex items-center justify-center text-2xl font-bold shadow-lg">
                        ✨
                    </div>
                    <div class="bg-white/5 backdrop-blur-lg rounded-lg p-8 pt-12 border border-white/10 h-full">
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
                <div class="relative">
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-12 h-12 bg-gradient-to-br from-pink-500 to-orange-500 rounded-full flex items-center justify-center text-2xl font-bold shadow-lg">
                        🎨
                    </div>
                    <div class="bg-white/5 backdrop-blur-lg rounded-lg p-8 pt-12 border border-white/10 h-full">
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

    <!-- Features Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-black/20">
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
                    <div class="bg-gradient-to-br from-purple-600/20 to-cyan-600/20 rounded-2xl p-8 border border-white/10 backdrop-blur-lg">
                        <h3 class="text-2xl font-bold mb-6 text-center">Crystal Metrics Breakdown</h3>
                        <div class="space-y-4">
                            <div class="bg-white/5 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-400">Complexity (Facets)</span>
                                    <span class="text-sm text-purple-400">4-50</span>
                                </div>
                                <div class="text-xs text-gray-500">Based on content count + diversity</div>
                            </div>

                            <div class="bg-white/5 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-400">Brightness (Glow)</span>
                                    <span class="text-sm text-cyan-400">0.00-1.00</span>
                                </div>
                                <div class="text-xs text-gray-500">Based on views, downloads, ratings</div>
                            </div>

                            <div class="bg-white/5 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-400">Clarity (Purity)</span>
                                    <span class="text-sm text-pink-400">0.30-1.00</span>
                                </div>
                                <div class="text-xs text-gray-500">Based on engagement score</div>
                            </div>

                            <div class="bg-white/5 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-400">Colors</span>
                                    <span class="text-sm text-orange-400">Up to 3</span>
                                </div>
                                <div class="text-xs text-gray-500">Top 3 content categories</div>
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
    </style>
</body>
</html>
