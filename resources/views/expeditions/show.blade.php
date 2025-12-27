@extends('layouts.app')

@section('title', $expedition->title . ' – Expedition')
@section('meta_description', \Illuminate\Support\Str::limit($expedition->description, 160))

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('expeditions.index') }}"
           class="inline-flex items-center text-gray-300 hover:text-purple-300 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Expeditions
        </a>
    </div>

    <!-- Header -->
    <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-lg shadow-lg p-8 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-400 via-violet-400 to-indigo-400 bg-clip-text text-transparent mb-2">
                    {{ $expedition->title }}
                </h1>
                <div class="flex items-center gap-3 text-sm text-gray-400">
                    <span>⏰ {{ $expedition->starts_at->format('M d') }} - {{ $expedition->ends_at->format('M d, Y') }}</span>
                    <span>•</span>
                    <span>👥 {{ $expedition->getParticipantCount() }}{{ $expedition->max_participants ? '/' . $expedition->max_participants : '' }} enrolled</span>
                </div>
            </div>
            @if($expedition->status === 'active')
                <span class="px-3 py-1 bg-green-600/20 text-green-300 rounded text-sm font-semibold">
                    Active
                </span>
            @endif
        </div>

        <p class="text-gray-300 text-lg mb-6">{{ $expedition->description }}</p>

        @if($canEnroll)
            <form action="{{ route('expeditions.enroll', $expedition) }}" method="POST">
                @csrf
                <button type="submit"
                        class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition font-bold text-lg">
                    🚀 Enroll Now
                </button>
            </form>
        @elseif($isEnrolled && $userEnrollment)
            <div class="px-4 py-3 bg-blue-600/20 text-blue-300 rounded-lg">
                ✓ You're enrolled! {{ $userEnrollment->isCompleted() ? 'Completed!' : 'Keep creating to complete the challenge.' }}
            </div>
        @elseif(!auth()->check())
            <a href="{{ route('login') }}"
               class="inline-block px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition font-bold">
                Login to Enroll
            </a>
        @endif
    </div>

    <div class="grid md:grid-cols-2 gap-6 mb-6">
        <!-- Requirements -->
        <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-white mb-4">📋 Requirements</h2>
            <ul class="space-y-2 text-gray-300">
                <li class="flex items-center gap-2">
                    <span class="text-purple-400">✓</span>
                    Create {{ $expedition->requirements['required_count'] ?? 3 }} blog posts
                </li>
                @if(isset($expedition->requirements['min_word_count']))
                    <li class="flex items-center gap-2">
                        <span class="text-purple-400">✓</span>
                        Minimum {{ $expedition->requirements['min_word_count'] }} words per post
                    </li>
                @endif
                <li class="flex items-center gap-2">
                    <span class="text-purple-400">✓</span>
                    Publish before {{ $expedition->ends_at->format('M d, Y') }}
                </li>
            </ul>
        </div>

        <!-- Rewards -->
        <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-white mb-4">🏆 Rewards</h2>
            <ul class="space-y-3 text-gray-300">
                <li class="flex items-center justify-between">
                    <span>Crystal Multiplier</span>
                    <span class="px-3 py-1 bg-purple-600/30 text-purple-300 rounded font-bold">
                        {{ $expedition->rewards['crystal_multiplier'] ?? 2 }}x
                    </span>
                </li>
                <li class="flex items-center justify-between">
                    <span>Engagement Bonus</span>
                    <span class="text-green-400 font-semibold">+{{ $expedition->rewards['engagement_bonus'] ?? 100 }}</span>
                </li>
                @if(isset($expedition->rewards['visual_effect']))
                    <li class="flex items-center justify-between">
                        <span>Visual Effect</span>
                        <span class="text-yellow-400">✨ {{ str_replace('_', ' ', ucwords($expedition->rewards['visual_effect'])) }}</span>
                    </li>
                @endif
            </ul>
        </div>
    </div>

    <!-- Your Progress -->
    @if($isEnrolled && $userEnrollment)
        <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-white mb-4">📊 Your Progress</h2>

            @php
                $progress = $userEnrollment->getProgress();
                $percentage = $progress['total_required'] > 0
                    ? ($progress['posts_created'] / $progress['total_required']) * 100
                    : 0;
            @endphp

            <div class="mb-4">
                <div class="flex items-center justify-between text-sm text-gray-400 mb-2">
                    <span>{{ $progress['posts_created'] }} / {{ $progress['total_required'] }} posts completed</span>
                    <span>{{ number_format($percentage, 0) }}%</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-3">
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 h-3 rounded-full transition-all duration-500"
                         style="width: {{ $percentage }}%"></div>
                </div>
            </div>

            @if($userEnrollment->qualifyingPosts->count() > 0)
                <h3 class="font-semibold text-white mb-2">Qualifying Posts:</h3>
                <ul class="space-y-2">
                    @foreach($userEnrollment->qualifyingPosts as $qualifyingPost)
                        <li class="flex items-center gap-2 text-gray-300 text-sm">
                            <span class="text-green-400">✓</span>
                            <a href="{{ route('blog.show', $qualifyingPost->post) }}"
                               class="hover:text-purple-300 transition">
                                {{ $qualifyingPost->post->title }}
                            </a>
                            <span class="text-gray-500">{{ $qualifyingPost->qualified_at->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <!-- Leaderboard -->
    @if($topCompleters->count() > 0)
        <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-white mb-4">🏅 Top Completers</h2>
            <div class="space-y-3">
                @foreach($topCompleters as $index => $completer)
                    <div class="flex items-center gap-3 text-gray-300">
                        <span class="text-2xl">{{ $index === 0 ? '🥇' : ($index === 1 ? '🥈' : ($index === 2 ? '🥉' : '🏅')) }}</span>
                        <span class="font-semibold">{{ $completer->user->getDisplayName() }}</span>
                        <span class="text-gray-500 text-sm ml-auto">{{ $completer->completed_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
