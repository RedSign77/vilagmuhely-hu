<?php

namespace App\Http\Controllers;

use Webtechsolutions\ContentEngine\Models\Content;

class ContentLibraryController extends Controller
{
    public function index()
    {
        $contents = Content::query()
            ->public()
            ->published()
            ->with(['category', 'creator', 'tags'])
            ->orderByDesc('published_at')
            ->limit(16)
            ->get();

        return view('library.index', [
            'contents' => $contents,
        ]);
    }
}
