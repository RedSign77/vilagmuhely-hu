@extends('layouts.app')

@section('title', $pageTitle)
@section('meta_description', $pageDescription)
@section('meta_keywords', 'creator profile, ' . $displayName . ', ' . $colorName . ' crystal, level ' . $rpgStats['level'])
@section('og_title', $pageTitle)
@section('og_description', $pageDescription)
@section('twitter_title', $displayName . "'s Forge")
@section('twitter_description', $pageDescription)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Back Button --}}
    <div class="mb-6">
        <a href="{{ route('crystals.gallery') }}"
           class="inline-flex items-center text-gray-300 hover:text-purple-300 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Crystal Gallery
        </a>
    </div>

    {{-- TOP THIRD: Crystal + RPG Stats --}}
    <section class="forge-hero-section mb-12">
        <div class="grid lg:grid-cols-2 gap-8">

            {{-- Interactive 3D Crystal --}}
            <div class="forge-crystal-container">
                <div id="crystal-viewer"
                     data-crystal-viewer
                     data-user-id="{{ $user->id }}"
                     data-auto-rotate="true"
                     data-rotation-speed="0.004"
                     data-camera-distance="3.5"
                     data-size="large">
                </div>
            </div>

            {{-- RPG-Style Stats Panel --}}
            <div class="forge-stats-panel">
                <h1 class="forge-title">{{ $displayName }}'s Forge</h1>
                <div class="flex items-center gap-2 mb-2">
                    <p class="forge-subtitle">{{ $rpgStats['rank'] }}</p>
                    @if($user->hasPublicIdentity())
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-600/20 text-purple-300 rounded text-xs font-semibold">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            </svg>
                            Public
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-600/20 text-gray-400 rounded text-xs font-semibold">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            Anonymous
                        </span>
                    @endif
                </div>

                {{-- Stat Bars --}}
                <div class="forge-stats-grid">
                    <div class="stat-bar">
                        <div class="stat-label">Rank / Level</div>
                        <div class="stat-value-large">{{ $rpgStats['rank'] }} • Lv.{{ $rpgStats['level'] }}</div>
                        <div class="stat-progress">
                            <div class="stat-progress-fill" style="width: {{ ($rpgStats['level'] / 50) * 100 }}%"></div>
                        </div>
                        <div class="stat-description">Based on complexity ({{ $metric->facet_count }} facets)</div>
                    </div>

                    <div class="stat-bar">
                        <div class="stat-label">Aura / Resonance</div>
                        <div class="stat-value-large">{{ $rpgStats['aura'] }}%</div>
                        <div class="stat-progress">
                            <div class="stat-progress-fill aura" style="width: {{ $rpgStats['aura'] }}%"></div>
                        </div>
                        <div class="stat-description">Based on brightness (interaction score: {{ number_format($metric->interaction_score) }})</div>
                    </div>

                    <div class="stat-bar">
                        <div class="stat-label">Essence / Clarity</div>
                        <div class="stat-value-large">{{ $rpgStats['essence'] }}%</div>
                        <div class="stat-progress">
                            <div class="stat-progress-fill essence" style="width: {{ $rpgStats['essence'] }}%"></div>
                        </div>
                        <div class="stat-description">Based on purity (engagement: {{ number_format($metric->engagement_score) }})</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CREATOR'S PORTFOLIO SECTION --}}
    <section class="forge-portfolio-section mb-12">
        <h2 class="section-title">Creator's Portfolio</h2>

        {{-- Tab Navigation --}}
        <div class="portfolio-tabs" x-data="{ activeTab: 'authored' }">
            <div class="tab-buttons">
                <button @click="activeTab = 'authored'" :class="{ 'active': activeTab === 'authored' }">
                    📚 Authored Works ({{ $counts['authored'] }})
                </button>
                <button @click="activeTab = 'vault'" :class="{ 'active': activeTab === 'vault' }">
                    🗝️ The Vault ({{ $counts['purchased'] }})
                </button>
                <button @click="activeTab = 'echoes'" :class="{ 'active': activeTab === 'echoes' }">
                    💬 Echoes ({{ $counts['reviews'] }})
                </button>
            </div>

            {{-- Authored Works Tab --}}
            <div x-show="activeTab === 'authored'" class="tab-content">
                @if($user->contents->count() > 0)
                    <div class="content-grid">
                        @foreach($user->contents as $content)
                            @include('forge.partials.content-card', ['content' => $content])
                        @endforeach
                    </div>
                    @if($counts['authored'] > 6)
                        <div class="text-center mt-6">
                            <p class="text-gray-400 text-sm">Showing 6 of {{ $counts['authored'] }} works</p>
                        </div>
                    @endif
                @else
                    <p class="empty-state">No published content yet.</p>
                @endif
            </div>

            {{-- The Vault Tab --}}
            <div x-show="activeTab === 'vault'" class="tab-content">
                @if($downloads->count() > 0)
                    <div class="content-grid">
                        @foreach($downloads as $content)
                            @include('forge.partials.content-card', ['content' => $content])
                        @endforeach
                    </div>
                    @if($counts['purchased'] > 6)
                        <div class="text-center mt-6">
                            <p class="text-gray-400 text-sm">Showing 6 of {{ $counts['purchased'] }} items</p>
                        </div>
                    @endif
                @else
                    <p class="empty-state">No collected content yet.</p>
                @endif
            </div>

            {{-- Echoes Tab --}}
            <div x-show="activeTab === 'echoes'" class="tab-content">
                @if($user->reviews->count() > 0)
                    <div class="reviews-list">
                        @foreach($user->reviews as $review)
                            @include('forge.partials.review-card', ['review' => $review])
                        @endforeach
                    </div>
                    @if($counts['reviews'] > 5)
                        <div class="text-center mt-6">
                            <p class="text-gray-400 text-sm">Showing 5 of {{ $counts['reviews'] }} reviews</p>
                        </div>
                    @endif
                @else
                    <p class="empty-state">No reviews written yet.</p>
                @endif
            </div>
        </div>
    </section>

    {{-- THE FORGE LOG: Activity Feed --}}
    <section class="forge-log-section">
        <h2 class="section-title">The Forge Log</h2>
        <p class="section-subtitle">Recent milestones and activities</p>

        @if($activities->count() > 0)
            <div class="activity-timeline">
                @foreach($activities as $activity)
                    <div class="activity-item">
                        <span class="activity-icon">{{ $activity['icon'] }}</span>
                        <div class="activity-content">
                            <p class="activity-message">{{ $activity['message'] }}</p>
                            <time class="activity-time">{{ $activity['timestamp']->diffForHumans() }}</time>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="empty-state">Activity log is empty.</p>
        @endif
    </section>

</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
