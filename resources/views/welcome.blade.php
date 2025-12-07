<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Világműhely') }} - Build the World Together</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gradient-to-br from-gray-900 via-indigo-900 to-purple-900 text-white">
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-black/20 backdrop-blur-lg border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-2">
                    <span class="text-2xl">🌍</span>
                    <span class="text-xl font-bold">Világműhely</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('world.index') }}" class="hover:text-indigo-300 transition">Explore World</a>
                    @auth
                        <a href="{{ route('world.my-structures') }}" class="hover:text-indigo-300 transition">My Structures</a>
                        <a href="/admin" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">Dashboard</a>
                    @else
                        <a href="/admin/login" class="hover:text-indigo-300 transition">Login</a>
                        <a href="/admin/register" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">Join Now</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 px-4 sm:px-6 lg:px-8 overflow-hidden">
        <!-- Background Effects -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-pink-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
        </div>

        <div class="relative max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h1 class="text-5xl md:text-7xl font-bold mb-6 bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
                    Build the World<br>Together
                </h1>
                <p class="text-xl md:text-2xl text-gray-300 max-w-3xl mx-auto mb-8">
                    A collaborative world where every creator leaves their mark.
                    Earn resources through your work, place structures, and watch our shared world grow.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('world.index') }}" class="px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-lg text-lg font-semibold shadow-lg shadow-indigo-500/50 transition transform hover:scale-105">
                        Explore the World 🗺️
                    </a>
                    @guest
                    <a href="/admin/register" class="px-8 py-4 bg-white/10 hover:bg-white/20 backdrop-blur-lg rounded-lg text-lg font-semibold border border-white/20 transition">
                        Start Building
                    </a>
                    @else
                    <a href="{{ route('world.my-structures') }}" class="px-8 py-4 bg-white/10 hover:bg-white/20 backdrop-blur-lg rounded-lg text-lg font-semibold border border-white/20 transition">
                        My Structures 🏗️
                    </a>
                    @endguest
                </div>
            </div>

            <!-- Live Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto mt-16">
                <div class="bg-white/5 backdrop-blur-lg rounded-lg p-6 border border-white/10 text-center">
                    <div class="text-3xl font-bold text-indigo-400">{{ $worldStats['total_structures'] ?? 1 }}</div>
                    <div class="text-sm text-gray-400 mt-1">Structures Built</div>
                </div>
                <div class="bg-white/5 backdrop-blur-lg rounded-lg p-6 border border-white/10 text-center">
                    <div class="text-3xl font-bold text-purple-400">{{ $worldStats['total_builders'] ?? 1 }}</div>
                    <div class="text-sm text-gray-400 mt-1">Active Builders</div>
                </div>
                <div class="bg-white/5 backdrop-blur-lg rounded-lg p-6 border border-white/10 text-center">
                    <div class="text-3xl font-bold text-pink-400">{{ $worldStats['unlocked_zones'] ?? 1 }}</div>
                    <div class="text-sm text-gray-400 mt-1">Unlocked Zones</div>
                </div>
                <div class="bg-white/5 backdrop-blur-lg rounded-lg p-6 border border-white/10 text-center">
                    <div class="text-3xl font-bold text-blue-400">∞</div>
                    <div class="text-sm text-gray-400 mt-1">Possibilities</div>
                </div>
            </div>
        </div>
    </section>

    <!-- World Preview Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-black/20">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold mb-4">See the World Live</h2>
                <p class="text-xl text-gray-400">Watch our collaborative creation evolve in real-time</p>
            </div>

            <!-- Mini World Viewer -->
            <div class="bg-gradient-to-br from-indigo-900/50 to-purple-900/50 rounded-2xl p-8 border border-white/10 shadow-2xl">
                <div id="home-world-viewer"
                     class="world-viewer-container"
                     data-world-viewer
                     data-chunk-size="20"
                     data-enable-building="false"
                     data-show-mini-map="true"
                     style="height: 500px; border-radius: 1rem; overflow: hidden;">
                </div>
                <div class="text-center mt-6">
                    <a href="{{ route('world.index') }}" class="inline-flex items-center text-indigo-400 hover:text-indigo-300 transition">
                        <span>View Full Interactive Map</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">How It Works</h2>
                <p class="text-xl text-gray-400">Three simple steps to leave your mark</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="relative">
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center text-2xl font-bold shadow-lg">
                        1
                    </div>
                    <div class="bg-white/5 backdrop-blur-lg rounded-lg p-8 pt-12 border border-white/10 h-full">
                        <div class="text-4xl mb-4 text-center">✍️</div>
                        <h3 class="text-2xl font-bold mb-3 text-center">Create Content</h3>
                        <p class="text-gray-400 text-center">
                            Share your work - articles, images, files, or RPG modules.
                            Each piece earns you building resources.
                        </p>
                        <div class="mt-4 space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-400">Digital Files</span>
                                <span class="text-indigo-400">🪨 5 🪵 2 💎 3</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-400">Articles</span>
                                <span class="text-purple-400">🪨 5 🪵 5 💎 2 ✨ 1</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-400">RPG Modules</span>
                                <span class="text-pink-400">🪨 3 🪵 3 💎 5 ✨ 3</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="relative">
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-2xl font-bold shadow-lg">
                        2
                    </div>
                    <div class="bg-white/5 backdrop-blur-lg rounded-lg p-8 pt-12 border border-white/10 h-full">
                        <div class="text-4xl mb-4 text-center">🏗️</div>
                        <h3 class="text-2xl font-bold mb-3 text-center">Build Structures</h3>
                        <p class="text-gray-400 text-center">
                            Spend your resources to place buildings.
                            Each structure must connect to existing ones - creating organic growth.
                        </p>
                        <div class="mt-4 space-y-2">
                            <div class="flex items-center text-sm">
                                <span class="w-3 h-3 bg-amber-600 rounded mr-2"></span>
                                <span class="text-gray-400">Cottage, Workshop, Gallery</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <span class="w-3 h-3 bg-green-600 rounded mr-2"></span>
                                <span class="text-gray-400">Library, Academy</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <span class="w-3 h-3 bg-purple-600 rounded mr-2"></span>
                                <span class="text-gray-400">Tower, Monument</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="relative">
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-12 h-12 bg-gradient-to-br from-pink-500 to-blue-500 rounded-full flex items-center justify-center text-2xl font-bold shadow-lg">
                        3
                    </div>
                    <div class="bg-white/5 backdrop-blur-lg rounded-lg p-8 pt-12 border border-white/10 h-full">
                        <div class="text-4xl mb-4 text-center">🌍</div>
                        <h3 class="text-2xl font-bold mb-3 text-center">Unlock New Zones</h3>
                        <p class="text-gray-400 text-center">
                            As the community builds together, new zones unlock with unique themes and opportunities.
                        </p>
                        <div class="mt-4 space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-400">🟢 Origin Valley</span>
                                <span class="text-green-400">Unlocked</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-400">🔵 Crystal Plains</span>
                                <span class="text-gray-500">100 structures</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-400">🟣 Makers Marsh</span>
                                <span class="text-gray-500">250 structures</span>
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
                    <h2 class="text-4xl font-bold mb-6">Why Build With Us?</h2>
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-indigo-600 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Collaborative Creation</h3>
                                <p class="text-gray-400">Build alongside fellow creators. Every structure connects, creating a unified world we all share.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Earn Through Creating</h3>
                                <p class="text-gray-400">Your content has real value. Different types earn different resources - all stored permanently.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-pink-600 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Watch It Grow</h3>
                                <p class="text-gray-400">Explore the 3D isometric world. Watch zones unlock, structures appear, and the world evolve in real-time.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Leave Your Legacy</h3>
                                <p class="text-gray-400">Every structure carries your name. Your contributions are permanent marks in our shared world.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="bg-gradient-to-br from-indigo-600/20 to-purple-600/20 rounded-2xl p-8 border border-white/10 backdrop-blur-lg">
                        <div class="space-y-4">
                            <div class="bg-white/5 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-400">Zone Progress</span>
                                    <span class="text-sm text-indigo-400">{{ $zoneProgress['current_structures'] ?? 1 }} / {{ $zoneProgress['required_structures'] ?? 100 }}</span>
                                </div>
                                <div class="w-full bg-white/10 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-2 rounded-full" style="width: {{ $zoneProgress['progress_percentage'] ?? 1 }}%"></div>
                                </div>
                                @if(!($zoneProgress['all_unlocked'] ?? false))
                                <p class="text-xs text-gray-500 mt-2">{{ $zoneProgress['remaining'] ?? 99 }} structures until {{ $zoneProgress['next_zone']['name'] ?? 'next zone' }}</p>
                                @endif
                            </div>

                            <div class="bg-white/5 rounded-lg p-4">
                                <h4 class="font-bold mb-3">Recent Activity</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center text-gray-400">
                                        <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                                        Structure placed in Origin Valley
                                    </div>
                                    <div class="flex items-center text-gray-400">
                                        <span class="w-2 h-2 bg-blue-400 rounded-full mr-2"></span>
                                        New builder joined
                                    </div>
                                    <div class="flex items-center text-gray-400">
                                        <span class="w-2 h-2 bg-purple-400 rounded-full mr-2"></span>
                                        Content published
                                    </div>
                                </div>
                            </div>

                            <div class="text-center pt-4">
                                <p class="text-sm text-gray-400 mb-4">Ready to start building?</p>
                                <a href="{{ route('world.index') }}" class="inline-block px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-lg font-semibold transition transform hover:scale-105">
                                    Explore the World
                                </a>
                            </div>
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
                Your Story<br>
                <span class="bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
                    Shapes Our World
                </span>
            </h2>
            <p class="text-xl text-gray-400 mb-8">
                Join {{ $worldStats['total_builders'] ?? 1 }} builders who are already creating something incredible.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @guest
                <a href="/admin/register" class="px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-lg text-lg font-semibold shadow-lg shadow-indigo-500/50 transition transform hover:scale-105">
                    Create Your Account
                </a>
                <a href="{{ route('world.index') }}" class="px-8 py-4 bg-white/10 hover:bg-white/20 backdrop-blur-lg rounded-lg text-lg font-semibold border border-white/20 transition">
                    View World First
                </a>
                @else
                <a href="{{ route('world.my-structures') }}" class="px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-lg text-lg font-semibold shadow-lg shadow-indigo-500/50 transition transform hover:scale-105">
                    Start Building Now
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
                        <span class="text-2xl">🌍</span>
                        <span class="text-xl font-bold">Világműhely</span>
                    </div>
                    <p class="text-gray-400 text-sm">
                        Where stories are forged and worlds are built together.
                    </p>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Explore</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="{{ route('world.index') }}" class="hover:text-white transition">World Map</a></li>
                        @auth
                        <li><a href="{{ route('world.my-structures') }}" class="hover:text-white transition">My Structures</a></li>
                        @endauth
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
                <p>&copy; {{ date('Y') }} Világműhely. Operated by Webtech Solutions | Build: {{ config('app.version') }}</p>
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
