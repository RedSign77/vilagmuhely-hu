@extends('layouts.app')

@section('title', 'Műhely Világ - Workshop World')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
            Műhely Világ
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            A collaborative world built by our community of creators
        </p>
    </div>

    <!-- Zone Progress -->
    @if(!$zoneProgress['all_unlocked'])
    <div class="zone-progress mb-6">
        <div class="zone-progress-header">
            <span class="zone-progress-label">
                Next zone: {{ $zoneProgress['next_zone']['name'] }}
            </span>
            <span class="zone-progress-percentage">
                {{ number_format($zoneProgress['progress_percentage'], 1) }}%
            </span>
        </div>
        <div class="zone-progress-bar">
            <div class="zone-progress-fill" style="width: {{ $zoneProgress['progress_percentage'] }}%"></div>
        </div>
        <p class="text-xs text-gray-400 mt-1">
            {{ $zoneProgress['remaining'] }} more structures needed
        </p>
    </div>
    @endif

    <!-- Resource Bar (if authenticated) -->
    @auth
    @include('world.partials.resource-bar', ['resources' => $userResources])
    @endauth

    <!-- World Stats -->
    <div class="world-stats mb-6">
        <div class="stat-card">
            <div class="stat-label">Total Structures</div>
            <div class="stat-value">{{ number_format($totalStructures) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Unlocked Zones</div>
            <div class="stat-value">{{ $zoneProgress['all_unlocked'] ? 'All' : 'Central' }}</div>
        </div>
    </div>

    <!-- World Viewer -->
    <div id="world-viewer"
         class="world-viewer-container"
         data-world-viewer
         data-chunk-size="30"
         data-enable-building="{{ auth()->check() ? 'true' : 'false' }}"
         data-show-mini-map="true">
    </div>

    <!-- Instructions -->
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            How to Build
        </h3>
        <div class="grid md:grid-cols-3 gap-6 text-sm">
            <div>
                <h4 class="font-semibold text-indigo-600 dark:text-indigo-400 mb-2">1. Earn Resources</h4>
                <p class="text-gray-600 dark:text-gray-400">
                    Create content to earn Stone, Wood, Crystal Shards, and Magic Essence.
                    Different content types award different resources.
                </p>
            </div>
            <div>
                <h4 class="font-semibold text-indigo-600 dark:text-indigo-400 mb-2">2. Place Structures</h4>
                <p class="text-gray-600 dark:text-gray-400">
                    Use your resources to build structures. You must build adjacent to existing structures
                    to help the world grow organically.
                </p>
            </div>
            <div>
                <h4 class="font-semibold text-indigo-600 dark:text-indigo-400 mb-2">3. Unlock Zones</h4>
                <p class="text-gray-600 dark:text-gray-400">
                    As the community builds more structures, new zones unlock with unique themes
                    and opportunities.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
