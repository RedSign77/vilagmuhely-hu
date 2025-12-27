@extends('layouts.app')

@section('title', 'Active Expeditions')
@section('meta_description', 'Join timed content creation challenges and earn crystal growth multipliers. Explore active expeditions and forge your creator legacy.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-12">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-400 via-violet-400 to-indigo-400 bg-clip-text text-transparent mb-4">
            🚀 Forge Expeditions
        </h1>
        <p class="text-gray-300 text-lg">
            Join timed content creation challenges, earn crystal multipliers, and unlock unique visual effects
        </p>
    </div>

    @if($activeExpeditions->count() > 0)
        <div class="mb-16">
            <h2 class="text-2xl font-bold text-white mb-6">🔥 Active Expeditions</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($activeExpeditions as $expedition)
                    <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-lg shadow-lg p-6 hover:border-purple-400 transition">
                        <h3 class="text-xl font-bold text-white mb-2">{{ $expedition->title }}</h3>
                        <p class="text-gray-300 text-sm mb-4 line-clamp-3">{{ $expedition->description }}</p>

                        <div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
                            <span>⏰ Ends {{ $expedition->ends_at->diffForHumans() }}</span>
                        </div>

                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm text-gray-400">
                                👥 {{ $expedition->enrollments_count }}{{ $expedition->max_participants ? '/' . $expedition->max_participants : '' }} enrolled
                            </span>
                            <span class="px-2 py-1 bg-purple-600/20 text-purple-300 rounded text-xs font-semibold">
                                {{ $expedition->rewards['crystal_multiplier'] ?? 2 }}x Crystal
                            </span>
                        </div>

                        <a href="{{ route('expeditions.show', $expedition) }}"
                           class="block w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-center rounded-lg transition font-semibold">
                            View Details →
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($upcomingExpeditions->count() > 0)
        <div class="mb-16">
            <h2 class="text-2xl font-bold text-white mb-6">📅 Upcoming Expeditions</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($upcomingExpeditions as $expedition)
                    <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-lg shadow-lg p-6">
                        <div class="mb-4">
                            <span class="px-3 py-1 bg-gray-600/30 text-gray-300 rounded text-xs font-semibold">
                                Starts {{ $expedition->starts_at->diffForHumans() }}
                            </span>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">{{ $expedition->title }}</h3>
                        <p class="text-gray-300 text-sm mb-4 line-clamp-3">{{ $expedition->description }}</p>

                        <a href="{{ route('expeditions.show', $expedition) }}"
                           class="block w-full px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-center rounded-lg transition font-semibold">
                            View Details
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($completedExpeditions->count() > 0)
        <div>
            <h2 class="text-2xl font-bold text-white mb-6">✅ Completed Expeditions</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($completedExpeditions as $expedition)
                    <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-lg shadow-lg p-6 opacity-75">
                        <div class="mb-4">
                            <span class="px-3 py-1 bg-blue-600/30 text-blue-300 rounded text-xs font-semibold">
                                Completed
                            </span>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">{{ $expedition->title }}</h3>
                        <p class="text-gray-300 text-sm mb-4">
                            {{ $expedition->enrollments_count }} participants
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($activeExpeditions->count() === 0 && $upcomingExpeditions->count() === 0)
        <div class="text-center py-12">
            <div class="text-6xl mb-4">🗺️</div>
            <p class="text-xl text-gray-400">No active expeditions at the moment</p>
            <p class="text-gray-500 mt-2">Check back later for new challenges!</p>
        </div>
    @endif
</div>
@endsection
