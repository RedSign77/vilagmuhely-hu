@extends('layouts.app')

@section('title', $user->name . ' - Crystal Profile')
@section('meta_description', 'View ' . $user->name . '\'s unique 3D crystal grown through creative content. Explore their crystal\'s geometry, colors, and glow intensity.')
@section('meta_keywords', '3D crystal, ' . $user->name . ', creator profile, content visualization, gamification')
@section('og_title', $user->name . '\'s Crystal - Világműhely')
@section('og_description', 'View ' . $user->name . '\'s unique 3D crystal. Facets: ' . ($crystalMetrics->facet_count ?? '0') . ' | Glow: ' . number_format(($crystalMetrics->glow_intensity ?? 0) * 100, 1) . '%')
@section('twitter_title', $user->name . '\'s Crystal')
@section('twitter_description', 'View ' . $user->name . '\'s unique 3D crystal grown through creative content.')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('crystals.gallery') }}"
           class="inline-flex items-center text-gray-300 hover:text-purple-300 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Gallery
        </a>
    </div>

    <!-- Profile Header -->
    <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center gap-4">
            @if($user->avatar)
                <img src="{{ asset('storage/' . $user->avatar) }}"
                     alt="{{ $user->name }}"
                     class="w-20 h-20 rounded-full">
            @else
                <div class="w-20 h-20 rounded-full bg-purple-600 flex items-center justify-center text-white text-2xl font-bold">
                    {{ substr($user->name, 0, 1) }}
                </div>
            @endif
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-400 via-violet-400 to-indigo-400 bg-clip-text text-transparent">
                    {{ $user->name }}'s Crystal
                </h1>
                <p class="text-gray-300">
                    A visual representation of their creative journey
                </p>
            </div>
        </div>
    </div>

    <!-- Crystal Display -->
    <div class="profile-crystal-section">
        <h2 class="profile-crystal-title">
            Műhely Kristály
        </h2>
        <p class="profile-crystal-subtitle">
            This crystal evolves based on content creation, community engagement, and creative diversity
        </p>

        <!-- Crystal Viewer -->
        <div id="crystal-viewer"
             class="crystal-viewer-container size-large"
             data-crystal-viewer
             data-user-id="{{ $user->id }}"
             data-auto-rotate="true"
             data-rotation-speed="0.003"
             data-camera-distance="3"
             data-show-stats="true"
             data-size="large">
        </div>

        <!-- Metrics Grid -->
        <div class="profile-crystal-metrics">
            <div class="profile-metric-card">
                <span class="profile-metric-value">{{ $metric->total_content_count }}</span>
                <span class="profile-metric-label">Total Content</span>
            </div>
            <div class="profile-metric-card">
                <span class="profile-metric-value">{{ $metric->facet_count }}</span>
                <span class="profile-metric-label">Facets</span>
            </div>
            <div class="profile-metric-card">
                <span class="profile-metric-value">{{ number_format($metric->diversity_index * 100, 1) }}%</span>
                <span class="profile-metric-label">Diversity Index</span>
            </div>
            <div class="profile-metric-card">
                <span class="profile-metric-value">{{ number_format($metric->interaction_score, 0) }}</span>
                <span class="profile-metric-label">Interaction Score</span>
            </div>
            <div class="profile-metric-card">
                <span class="profile-metric-value">{{ number_format($metric->glow_intensity * 100, 0) }}%</span>
                <span class="profile-metric-label">Glow Intensity</span>
            </div>
            <div class="profile-metric-card">
                <span class="profile-metric-value">{{ number_format($metric->purity_level * 100, 0) }}%</span>
                <span class="profile-metric-label">Purity Level</span>
            </div>
        </div>

        <!-- Last Updated -->
        <div class="text-center mt-6 text-sm text-gray-400">
            Last updated: {{ $metric->last_calculated_at ? $metric->last_calculated_at->diffForHumans() : 'Never' }}
        </div>
    </div>

    <!-- Crystal Meaning -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mt-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            Understanding the Crystal
        </h3>
        <div class="grid md:grid-cols-3 gap-6 text-sm">
            <div>
                <h4 class="font-semibold text-indigo-600 dark:text-indigo-400 mb-2">Size & Geometry</h4>
                <p class="text-gray-600 dark:text-gray-400">
                    The number of facets ({{ $metric->facet_count }}) represents content quantity and diversity across different types.
                    More facets mean more varied creative output.
                </p>
            </div>
            <div>
                <h4 class="font-semibold text-indigo-600 dark:text-indigo-400 mb-2">Brightness & Glow</h4>
                <p class="text-gray-600 dark:text-gray-400">
                    Glow intensity ({{ number_format($metric->glow_intensity * 100, 0) }}%) reflects interaction quality.
                    Views, downloads, and helpful ratings make the crystal shine brighter.
                </p>
            </div>
            <div>
                <h4 class="font-semibold text-indigo-600 dark:text-indigo-400 mb-2">Clarity & Purity</h4>
                <p class="text-gray-600 dark:text-gray-400">
                    Purity level ({{ number_format($metric->purity_level * 100, 0) }}%) shows community engagement.
                    Active participation and helpful critiques increase transparency.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
