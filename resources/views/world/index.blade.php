@extends('layouts.app')

@section('title', 'Műhely Világ - Workshop World')

@push('head')
<meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('css/world-map.css') }}">
@endpush

@section('content')
<div class="world-page">
    <!-- Header -->
    <div class="world-header">
        <div class="container mx-auto px-4">
            <h1 class="world-title">Műhely Világ</h1>
            <p class="world-subtitle">
                Explore a vast world filled with natural wonders and hidden treasures
            </p>
        </div>
    </div>

    <!-- Resource Bar (if authenticated) -->
    @auth
    <div class="resource-bar-container">
        @include('world.partials.resource-bar', ['resources' => $userResources])
    </div>
    @endauth

    <!-- World Stats -->
    <div class="world-stats">
        <div class="container mx-auto px-4">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Elements</div>
                    <div class="stat-value">{{ number_format($totalElements) }}</div>
                </div>
                @auth
                <div class="stat-card">
                    <div class="stat-label">Your Discoveries</div>
                    <div class="stat-value">{{ number_format($userDiscoveries ?? 0) }}</div>
                </div>
                @endauth
                <div class="stat-card">
                    <div class="stat-label">Map Size</div>
                    <div class="stat-value">{{ $mapConfig->map_width }}×{{ $mapConfig->map_height }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- World Viewer (2D Top-Down Map) -->
    <div class="map-container">
        <div id="world-viewer"
             data-world-viewer
             data-tile-size="{{ $mapConfig->tile_size }}"
             data-enable-interaction="{{ auth()->check() ? 'true' : 'false' }}"
             data-show-grid="false">
        </div>

        <!-- Map Controls -->
        <div class="map-controls">
            <div class="control-hint">
                <span class="hint-icon">🖱️</span>
                <span class="hint-text">Drag to pan • Scroll to zoom • Click elements for details</span>
            </div>
        </div>
    </div>

    <!-- Instructions -->
    <div class="world-instructions">
        <div class="container mx-auto px-4">
            <h3 class="instructions-title">How to Explore</h3>
            <div class="instructions-grid">
                <div class="instruction-card">
                    <h4 class="instruction-title">1. Earn Resources</h4>
                    <p class="instruction-text">
                        Create and publish content to earn Stone, Wood, Crystal Shards, and Magic Essence.
                        Different content types award different resources.
                    </p>
                </div>
                <div class="instruction-card">
                    <h4 class="instruction-title">2. Explore the Map</h4>
                    <p class="instruction-text">
                        Pan and zoom around the map to discover elements across different biomes.
                        Click on trees, rocks, water features, and more to learn about them.
                    </p>
                </div>
                <div class="instruction-card">
                    <h4 class="instruction-title">3. Claim Bonuses</h4>
                    <p class="instruction-text">
                        Interact with elements to claim resource bonuses. Some bonuses are one-time,
                        while others can be claimed repeatedly after a cooldown period.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
    import { TopDownMapViewer } from '/js/components/TopDownMapViewer.js';
    import { ElementDetailModal } from '/js/components/ElementDetailModal.js';

    // Initialize map viewer
    const viewer = new TopDownMapViewer('world-viewer', {
        tileSize: {{ $mapConfig->tile_size }},
        enableInteraction: {{ auth()->check() ? 'true' : 'false' }},
        showGrid: false
    });

    // Initialize detail modal
    const modal = new ElementDetailModal();

    // Listen for element selection
    document.getElementById('world-viewer').addEventListener('element-selected', (e) => {
        modal.open(e.detail.element);
    });
</script>
@endpush
