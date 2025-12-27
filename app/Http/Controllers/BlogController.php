<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class BlogController extends Controller
{
    /**
     * Display a listing of published blog posts.
     */
    public function index(): View
    {
        $posts = Post::published()
            ->with('author')
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return view('blog.index', [
            'posts' => $posts,
        ]);
    }

    /**
     * Display the specified blog post.
     */
    public function show(Post $post): View
    {
        // Only show published posts to non-authenticated users
        if (! $post->isPublished() && ! auth()->check()) {
            abort(404);
        }

        $post->load('author');

        // Get related posts (same author or similar content)
        $relatedPosts = Post::published()
            ->where('id', '!=', $post->id)
            ->where('author_id', $post->author_id)
            ->limit(3)
            ->get();

        // Get related contents (max 3)
        $relatedContents = $post->getRelatedContents(3);

        return view('blog.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'relatedContents' => $relatedContents,
        ]);
    }
}
