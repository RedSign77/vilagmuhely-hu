@extends('layouts.app')

@section('title', 'Blog')
@section('meta_description', 'Read our latest blog posts about content creation, gamification, and creative tools.')
@section('meta_keywords', 'blog, articles, content creation, gamification, creative tools')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-12">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-400 via-violet-400 to-indigo-400 bg-clip-text text-transparent mb-4">
            Blog
        </h1>
        <p class="text-gray-300 text-lg">
            Insights, updates, and stories from the Világműhely community
        </p>
    </div>

    @if($posts->count() > 0)
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($posts as $post)
                <article class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-lg shadow-lg overflow-hidden hover:border-purple-400 transition group">
                    @if($post->featured_image)
                        <div class="aspect-video overflow-hidden">
                            <img src="{{ asset('storage/' . $post->featured_image) }}"
                                 alt="{{ $post->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        </div>
                    @else
                        <div class="aspect-video bg-gradient-to-br from-purple-600/20 to-cyan-600/20 flex items-center justify-center">
                            <svg class="w-16 h-16 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    @endif

                    <div class="p-6">
                        <div class="flex items-center gap-2 text-sm text-gray-400 mb-3">
                            <span>By {{ $post->author->getDisplayName() }}</span>
                            <span>•</span>
                            <time datetime="{{ $post->published_at->toISOString() }}">
                                {{ $post->published_at->format('M d, Y') }}
                            </time>
                        </div>

                        <h2 class="text-xl font-bold text-white mb-3 group-hover:text-purple-300 transition">
                            <a href="{{ route('blog.show', $post) }}">
                                {{ $post->title }}
                            </a>
                        </h2>

                        @if($post->excerpt)
                            <p class="text-gray-300 text-sm mb-4 line-clamp-3">
                                {{ $post->excerpt }}
                            </p>
                        @endif

                        <a href="{{ route('blog.show', $post) }}"
                           class="inline-flex items-center text-purple-400 hover:text-purple-300 transition text-sm font-semibold">
                            Read more
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-12">
            {{ $posts->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-6xl mb-4">\ud83d\udcdd</div>
            <p class="text-xl text-gray-400">No blog posts yet</p>
            <p class="text-gray-500 mt-2">Check back later for new content!</p>
        </div>
    @endif
</div>
@endsection
