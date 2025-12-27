@extends('layouts.app')

@section('title', 'Crystal Gallery')
@section('meta_description', 'View the leaderboard of unique 3D crystals grown by our creator community. Each crystal reflects its owner\'s creative journey.')
@section('meta_keywords', 'crystal gallery, 3D crystals, creator leaderboard, visual progress, gamification leaderboard')
@section('og_title', 'Világműhely - Crystal Gallery')
@section('og_description', 'View the leaderboard of unique 3D crystals grown by our creator community. Each crystal reflects its owner\'s creative journey.')
@section('twitter_title', 'Világműhely - Crystal Gallery')
@section('twitter_description', 'View the leaderboard of unique 3D crystals grown by our creator community.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-400 via-violet-400 to-indigo-400 bg-clip-text text-transparent mb-2">
            Workshop Crystal Gallery
        </h1>
        <p class="text-gray-300">
            Explore the unique crystals of our creators, each representing their creative journey
        </p>
    </div>

    <!-- Sort Controls -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('crystals.gallery', ['sort' => 'interaction']) }}"
           class="px-4 py-2 rounded-lg {{ $sortBy === 'interaction' ? 'bg-purple-600 text-white' : 'bg-white/10 backdrop-blur-sm text-gray-300 border border-white/20' }} hover:bg-purple-500 hover:text-white transition">
            Most Interactive
        </a>
        <a href="{{ route('crystals.gallery', ['sort' => 'diversity']) }}"
           class="px-4 py-2 rounded-lg {{ $sortBy === 'diversity' ? 'bg-purple-600 text-white' : 'bg-white/10 backdrop-blur-sm text-gray-300 border border-white/20' }} hover:bg-purple-500 hover:text-white transition">
            Most Diverse
        </a>
        <a href="{{ route('crystals.gallery', ['sort' => 'engagement']) }}"
           class="px-4 py-2 rounded-lg {{ $sortBy === 'engagement' ? 'bg-purple-600 text-white' : 'bg-white/10 backdrop-blur-sm text-gray-300 border border-white/20' }} hover:bg-purple-500 hover:text-white transition">
            Most Engaged
        </a>
    </div>

    <!-- Crystal Grid -->
    <div class="crystal-gallery-grid">
        @forelse($metrics as $metric)
            <div class="crystal-gallery-item">
                <!-- Crystal Viewer -->
                <div id="crystal-{{ $metric->user_id }}"
                     class="crystal-gallery-viewer crystal-viewer-container size-medium"
                     data-crystal-viewer
                     data-user-id="{{ $metric->user_id }}"
                     data-auto-rotate="true"
                     data-rotation-speed="0.005"
                     data-camera-distance="3"
                     data-size="medium">
                </div>

                <!-- User Info -->
                <div class="crystal-gallery-info">
                    <div class="user-name">
                        <a href="{{ route('crystals.show', $metric->user) }}"
                           class="hover:text-indigo-400 transition">
                            {{ $metric->user->getDisplayName() }}
                        </a>
                    </div>

                    <div class="metrics">
                        <div class="metric">
                            <span class="metric-value">{{ $metric->total_content_count }}</span>
                            <span class="metric-label">Content</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value">{{ $metric->facet_count }}</span>
                            <span class="metric-label">Facets</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value">{{ number_format($metric->diversity_index * 100, 0) }}%</span>
                            <span class="metric-label">Diversity</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500 dark:text-gray-400 text-lg">
                    No crystals found. Start creating content to forge your crystal!
                </p>
            </div>
        @endforelse
    </div>
</div>
@endsection
