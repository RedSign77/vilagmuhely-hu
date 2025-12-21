@extends('layouts.app')

@section('title', $post->meta_title ?? $post->title)
@section('meta_description', $post->meta_description)
@section('meta_keywords', $post->meta_keywords ?? '')
@section('og_title', $post->meta_title ?? $post->title)
@section('og_description', $post->meta_description)
@section('og_image', $post->featured_image ? asset('storage/' . $post->featured_image) : asset('images/og-default.jpg'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('blog.index') }}"
           class="inline-flex items-center text-gray-300 hover:text-purple-300 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Blog
        </a>
    </div>

    <article class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-lg shadow-lg overflow-hidden">
        @if($post->featured_image)
            <div class="aspect-video overflow-hidden">
                <img src="{{ asset('storage/' . $post->featured_image) }}"
                     alt="{{ $post->title }}"
                     class="w-full h-full object-cover">
            </div>
        @endif

        <div class="p-8">
            <div class="flex items-center gap-3 text-sm text-gray-400 mb-6">
                <span>By {{ $post->author->anonymized_name }}</span>
                <span>•</span>
                <time datetime="{{ $post->published_at->toISOString() }}">
                    {{ $post->published_at->format('F d, Y') }}
                </time>
                @if($post->status === 'draft' && auth()->check())
                    <span class="px-2 py-1 bg-yellow-500/20 text-yellow-300 rounded text-xs font-semibold">Draft</span>
                @endif
            </div>

            <h1 class="text-4xl font-bold text-white mb-6">
                {{ $post->title }}
            </h1>

            @if($post->excerpt)
                <p class="text-xl text-gray-300 mb-8 italic">
                    {{ $post->excerpt }}
                </p>
            @endif

            <div class="prose prose-invert prose-lg max-w-none
                prose-headings:text-white prose-p:text-gray-300 prose-strong:text-white
                prose-a:text-purple-400 prose-a:no-underline hover:prose-a:text-purple-300
                prose-code:text-purple-300 prose-pre:bg-black/50 prose-pre:text-gray-300
                prose-blockquote:text-gray-400 prose-blockquote:border-purple-500
                prose-li:text-gray-300">
                {!! $post->content !!}
            </div>
        </div>
    </article>

    @if($relatedPosts->count() > 0)
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-white mb-6">Related Posts</h2>

            <div class="grid md:grid-cols-3 gap-6">
                @foreach($relatedPosts as $relatedPost)
                    <article class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-lg shadow-lg overflow-hidden hover:border-purple-400 transition">
                        @if($relatedPost->featured_image)
                            <div class="aspect-video overflow-hidden">
                                <img src="{{ asset('storage/' . $relatedPost->featured_image) }}"
                                     alt="{{ $relatedPost->title }}"
                                     class="w-full h-full object-cover">
                            </div>
                        @endif

                        <div class="p-4">
                            <h3 class="text-lg font-bold text-white mb-2">
                                <a href="{{ route('blog.show', $relatedPost) }}" class="hover:text-purple-300 transition">
                                    {{ $relatedPost->title }}
                                </a>
                            </h3>

                            <div class="text-xs text-gray-400">
                                {{ $relatedPost->published_at->format('M d, Y') }}
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
