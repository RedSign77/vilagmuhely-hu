# Blog Related Contents Feature

## Overview

Display a grid of up to 4 related content cards (from Content Library) under each blog post, helping users discover relevant resources and increasing content engagement across the platform.

## User Story

**As a user reading a blog post, I want to see a "Related Contents" grid with a maximum of 4 content cards (like on Content Library page) in one row under the blog post text, so that I can discover relevant downloadable resources related to the topic.**

## Database Changes

### Posts Table Extension

Add `related_content_ids` JSON column to `posts` table for manual content selection:

```php
Schema::table('posts', function (Blueprint $table) {
    $table->json('related_content_ids')->nullable()->after('content');
});
```

**Structure:**
```json
[45, 67, 89, 123]
```

## Model Extensions

### Post Model (`app/Models/Post.php`)

**Add to casts:**
```php
protected $casts = [
    'published_at' => 'datetime',
    'related_content_ids' => 'array', // NEW
];
```

**Add relationship:**
```php
/**
 * Get manually selected related contents
 */
public function relatedContents(): BelongsToMany
{
    if (empty($this->related_content_ids)) {
        return $this->belongsToMany(Content::class)->whereRaw('1 = 0'); // Empty relation
    }

    return $this->belongsToMany(Content::class, null, null, null, null, null, 'content')
        ->whereIn('contents.id', $this->related_content_ids)
        ->whereIn('status', [Content::STATUS_PUBLIC, Content::STATUS_MEMBERS_ONLY])
        ->orderByRaw('FIELD(id, ' . implode(',', $this->related_content_ids) . ')');
}
```

**Add method:**
```php
/**
 * Get related contents (manual selection or auto-discovery)
 */
public function getRelatedContents(int $limit = 4): Collection
{
    // Priority 1: Manually selected related contents
    if (!empty($this->related_content_ids)) {
        $selected = Content::whereIn('id', $this->related_content_ids)
            ->whereIn('status', [Content::STATUS_PUBLIC, Content::STATUS_MEMBERS_ONLY])
            ->orderByRaw('FIELD(id, ' . implode(',', $this->related_content_ids) . ')')
            ->take($limit)
            ->get();

        if ($selected->count() >= $limit) {
            return $selected;
        }

        // If we have some but not enough, fill remaining with auto-discovery
        $remaining = $limit - $selected->count();
        $excludeIds = array_merge($this->related_content_ids, $selected->pluck('id')->toArray());

        $autoDiscovered = $this->discoverRelatedContents($remaining, $excludeIds);

        return $selected->concat($autoDiscovered);
    }

    // Priority 2: Auto-discovery based on keywords
    return $this->discoverRelatedContents($limit);
}

/**
 * Auto-discover related contents based on keywords and similarity
 */
protected function discoverRelatedContents(int $limit = 4, array $excludeIds = []): Collection
{
    // Extract keywords from post title and content
    $keywords = $this->extractKeywords();

    if (empty($keywords)) {
        return $this->getFallbackContents($limit, $excludeIds);
    }

    // Search for contents matching keywords
    $query = Content::whereIn('status', [Content::STATUS_PUBLIC, Content::STATUS_MEMBERS_ONLY])
        ->where(function($q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $q->orWhere('title', 'LIKE', "%{$keyword}%")
                  ->orWhere('body', 'LIKE', "%{$keyword}%")
                  ->orWhere('excerpt', 'LIKE', "%{$keyword}%");
            }
        });

    if (!empty($excludeIds)) {
        $query->whereNotIn('id', $excludeIds);
    }

    return $query->orderBy('views_count', 'desc')
        ->take($limit)
        ->get();
}

/**
 * Extract keywords from post for matching
 */
protected function extractKeywords(): array
{
    // Simple keyword extraction from title and first paragraph
    $text = $this->title . ' ' . strip_tags($this->excerpt ?? '');

    // Remove common words (stopwords)
    $stopwords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'been', 'be', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'should', 'could', 'may', 'might', 'can', 'this', 'that', 'these', 'those'];

    // Extract words
    preg_match_all('/\b\w{4,}\b/u', strtolower($text), $matches);
    $words = $matches[0] ?? [];

    // Remove stopwords and get top 5 most frequent
    $filtered = array_diff($words, $stopwords);
    $counts = array_count_values($filtered);
    arsort($counts);

    return array_slice(array_keys($counts), 0, 5);
}

/**
 * Get fallback contents when no keywords match
 */
protected function getFallbackContents(int $limit = 4, array $excludeIds = []): Collection
{
    // Return most popular public contents as fallback
    $query = Content::whereIn('status', [Content::STATUS_PUBLIC, Content::STATUS_MEMBERS_ONLY]);

    if (!empty($excludeIds)) {
        $query->whereNotIn('id', $excludeIds);
    }

    return $query->orderBy('views_count', 'desc')
        ->take($limit)
        ->get();
}
```

## Controller Updates

### BlogController (`app/Http/Controllers/BlogController.php`)

Update the `show` method:

```php
public function show(Post $post)
{
    // Existing code...

    // NEW: Get related contents (max 4)
    $relatedContents = $post->getRelatedContents(4);

    return view('blog.show', compact('post', 'relatedPosts', 'relatedContents'));
}
```

## Frontend Implementation

### Blade Template (`resources/views/blog/show.blade.php`)

Add after the post content (after line 62, inside the article tag):

```blade
{{-- Related Contents Section --}}
@if($relatedContents->count() > 0)
    <div class="mt-12 pt-12 border-t border-white/20">
        <h2 class="text-2xl font-bold text-white mb-6">📚 Related Resources</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedContents as $content)
                @include('blog.partials.content-card', ['content' => $content])
            @endforeach
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('library.index') }}"
               class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
                Browse All Content
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>
@endif
```

### Content Card Component (`resources/views/blog/partials/content-card.blade.php`)

Create new file matching Content Library design:

```blade
<div class="content-card bg-white/10 backdrop-blur-lg border border-white/20 rounded-lg shadow-lg overflow-hidden hover:border-purple-400 transition h-full flex flex-col">
    @if($content->featured_image)
        <div class="aspect-video overflow-hidden bg-gray-800">
            <img src="{{ asset('storage/' . $content->featured_image) }}"
                 alt="{{ $content->title }}"
                 class="w-full h-full object-cover">
        </div>
    @else
        <div class="aspect-video bg-gradient-to-br from-purple-900/50 to-blue-900/50 flex items-center justify-center">
            <svg class="w-16 h-16 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
        </div>
    @endif

    <div class="p-4 flex-1 flex flex-col">
        <div class="flex items-center gap-2 mb-3">
            <span class="px-2 py-1 bg-purple-600/30 text-purple-300 rounded text-xs font-semibold">
                {{ ucfirst(str_replace('_', ' ', $content->type)) }}
            </span>
            @if($content->category)
                <span class="px-2 py-1 rounded text-xs font-semibold" style="background-color: {{ $content->category->color }}20; color: {{ $content->category->color }};">
                    {{ $content->category->name }}
                </span>
            @endif
        </div>

        <h3 class="text-lg font-bold text-white mb-2 line-clamp-2">
            {{ $content->title }}
        </h3>

        @if($content->excerpt)
            <p class="text-sm text-gray-400 mb-4 line-clamp-2 flex-1">
                {{ Str::limit($content->excerpt, 100) }}
            </p>
        @endif

        <div class="flex items-center justify-between text-sm text-gray-400 mb-4">
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                {{ number_format($content->views_count ?? 0) }}
            </span>
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                {{ number_format($content->downloads_count ?? 0) }}
            </span>
        </div>

        <a href="{{ route('library.index') }}?highlight={{ $content->id }}"
           class="w-full inline-flex items-center justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition font-semibold">
            View Details
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>
</div>
```

## Filament Admin Integration

### PostResource Form Extension (`app/Filament/Admin/Resources/PostResource.php`)

Add to the form schema, in the "Post Content" section:

```php
Forms\Components\Section::make('Related Contents')
    ->description('Select up to 4 related contents to display under the blog post')
    ->schema([
        Forms\Components\Select::make('related_content_ids')
            ->label('Related Contents')
            ->multiple()
            ->searchable()
            ->preload()
            ->options(function() {
                return Content::whereIn('status', [Content::STATUS_PUBLIC, Content::STATUS_MEMBERS_ONLY])
                    ->orderBy('title')
                    ->pluck('title', 'id');
            })
            ->maxItems(4)
            ->helperText('Select up to 4 contents to display. If left empty, the system will auto-suggest related contents based on post keywords.')
            ->columnSpanFull(),

        Forms\Components\Placeholder::make('auto_discovery_note')
            ->label('')
            ->content('**Auto-Discovery:** If you select fewer than 4 contents (or none), the system will automatically suggest related contents based on post title and content keywords.')
            ->columnSpanFull(),
    ])
    ->collapsed()
    ->collapsible(),
```

### PostResource Infolist Extension

Add to the infolist (View action):

```php
Infolists\Components\Section::make('Related Contents')
    ->schema([
        Infolists\Components\TextEntry::make('related_content_ids')
            ->label('Manually Selected')
            ->formatStateUsing(function($state) {
                if (empty($state)) {
                    return 'None (Auto-discovery enabled)';
                }
                $contents = Content::whereIn('id', $state)->pluck('title');
                return $contents->implode(', ');
            }),

        Infolists\Components\TextEntry::make('related_contents_preview')
            ->label('Will Display')
            ->formatStateUsing(function($record) {
                $related = $record->getRelatedContents(4);
                return $related->pluck('title')->implode(', ') ?: 'None';
            }),
    ])
    ->collapsed(),
```

## Auto-Discovery Algorithm

### Keyword Extraction Logic

1. **Source Text**: Post title + excerpt (or first 200 chars of content)
2. **Processing**:
   - Convert to lowercase
   - Extract words 4+ characters
   - Remove common stopwords
   - Count word frequency
   - Take top 5 most frequent keywords
3. **Matching**:
   - Search Content `title`, `body`, `excerpt` for keywords
   - Order by relevance (more matching keywords = higher score)
   - Secondary sort by `views_count` (popularity)
   - Limit to 4 results

### Fallback Strategy

If auto-discovery finds fewer than requested:
1. Use manually selected contents first
2. Fill remaining slots with auto-discovered
3. If still insufficient, show most popular public contents

## CSS Styling

Add to `resources/css/app.css` or create `resources/css/blog.css`:

```css
/* Related Contents Section */
.content-card {
    transition: all 0.2s ease;
}

.content-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(168, 85, 247, 0.2);
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Responsive grid adjustments */
@media (max-width: 640px) {
    .grid-cols-1.sm\:grid-cols-2.lg\:grid-cols-4 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
}

@media (min-width: 640px) and (max-width: 1023px) {
    .grid-cols-1.sm\:grid-cols-2.lg\:grid-cols-4 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .grid-cols-1.sm\:grid-cols-2.lg\:grid-cols-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}
```

## Testing Requirements

### Unit Tests

**PostTest** (`tests/Unit/PostTest.php`):
```php
test('post can have related content ids', function() {
    $post = Post::factory()->create([
        'related_content_ids' => [1, 2, 3]
    ]);

    expect($post->related_content_ids)->toBe([1, 2, 3]);
});

test('post extracts keywords from title and excerpt', function() {
    $post = Post::factory()->create([
        'title' => 'Creating Fantasy Worldbuilding Maps',
        'excerpt' => 'Learn how to create detailed fantasy maps for your worldbuilding projects',
    ]);

    $keywords = $post->extractKeywords();

    expect($keywords)->toContain('fantasy')
        ->and($keywords)->toContain('worldbuilding')
        ->and($keywords)->toContain('maps');
});

test('post returns manually selected related contents', function() {
    $contents = Content::factory()->count(3)->create([
        'status' => Content::STATUS_PUBLIC
    ]);

    $post = Post::factory()->create([
        'related_content_ids' => $contents->pluck('id')->toArray()
    ]);

    $related = $post->getRelatedContents(4);

    expect($related)->toHaveCount(3)
        ->and($related->pluck('id')->toArray())->toBe($contents->pluck('id')->toArray());
});

test('post auto-discovers related contents when none selected', function() {
    Content::factory()->create([
        'title' => 'Fantasy Worldbuilding Guide',
        'status' => Content::STATUS_PUBLIC,
        'views_count' => 100,
    ]);

    Content::factory()->create([
        'title' => 'Sci-Fi Character Generator',
        'status' => Content::STATUS_PUBLIC,
        'views_count' => 50,
    ]);

    $post = Post::factory()->create([
        'title' => 'Creating Fantasy Worlds',
        'related_content_ids' => null,
    ]);

    $related = $post->getRelatedContents(4);

    expect($related)->toHaveCount(2)
        ->and($related->first()->title)->toContain('Fantasy');
});

test('post limits related contents to maximum of 4', function() {
    $contents = Content::factory()->count(10)->create([
        'status' => Content::STATUS_PUBLIC
    ]);

    $post = Post::factory()->create([
        'related_content_ids' => $contents->pluck('id')->toArray()
    ]);

    $related = $post->getRelatedContents(4);

    expect($related)->toHaveCount(4);
});
```

### Feature Tests

**BlogRelatedContentsTest** (`tests/Feature/BlogRelatedContentsTest.php`):
```php
test('blog post displays related contents section', function() {
    $post = Post::factory()->published()->create();
    $content = Content::factory()->create([
        'status' => Content::STATUS_PUBLIC,
        'title' => 'Test Content',
    ]);

    $post->update(['related_content_ids' => [$content->id]]);

    $response = $this->get(route('blog.show', $post));

    $response->assertOk()
        ->assertSee('Related Resources')
        ->assertSee($content->title);
});

test('blog post does not display related contents section when empty', function() {
    $post = Post::factory()->published()->create([
        'related_content_ids' => null
    ]);

    // Ensure no public contents exist
    Content::query()->delete();

    $response = $this->get(route('blog.show', $post));

    $response->assertOk()
        ->assertDontSee('Related Resources');
});

test('blog post shows maximum of 4 related contents', function() {
    $post = Post::factory()->published()->create();
    $contents = Content::factory()->count(10)->create([
        'status' => Content::STATUS_PUBLIC
    ]);

    $post->update(['related_content_ids' => $contents->pluck('id')->toArray()]);

    $response = $this->get(route('blog.show', $post));
    $relatedContents = $response->viewData('relatedContents');

    expect($relatedContents)->toHaveCount(4);
});

test('blog post auto-discovers contents when manual selection is empty', function() {
    $post = Post::factory()->published()->create([
        'title' => 'Fantasy Worldbuilding Tutorial',
        'related_content_ids' => null,
    ]);

    Content::factory()->create([
        'title' => 'Fantasy Map Generator',
        'status' => Content::STATUS_PUBLIC,
    ]);

    $response = $this->get(route('blog.show', $post));

    $response->assertOk()
        ->assertSee('Related Resources')
        ->assertSee('Fantasy Map Generator');
});

test('blog post only shows public and members only contents', function() {
    $post = Post::factory()->published()->create();

    $publicContent = Content::factory()->create(['status' => Content::STATUS_PUBLIC]);
    $draftContent = Content::factory()->create(['status' => Content::STATUS_DRAFT]);

    $post->update(['related_content_ids' => [$publicContent->id, $draftContent->id]]);

    $response = $this->get(route('blog.show', $post));
    $relatedContents = $response->viewData('relatedContents');

    expect($relatedContents)->toHaveCount(1)
        ->and($relatedContents->first()->id)->toBe($publicContent->id);
});
```

### Admin Tests

**PostResourceRelatedContentsTest** (`tests/Feature/Filament/PostResourceRelatedContentsTest.php`):
```php
test('admin can select related contents in post form', function() {
    $admin = User::factory()->supervisor()->create();
    $contents = Content::factory()->count(3)->create([
        'status' => Content::STATUS_PUBLIC
    ]);

    Livewire::actingAs($admin)
        ->test(EditPost::class, ['record' => Post::factory()->create()->id])
        ->set('data.related_content_ids', $contents->pluck('id')->toArray())
        ->call('save')
        ->assertHasNoErrors();

    expect(Post::first()->related_content_ids)->toBe($contents->pluck('id')->toArray());
});

test('admin cannot select more than 4 related contents', function() {
    $admin = User::factory()->supervisor()->create();
    $contents = Content::factory()->count(5)->create([
        'status' => Content::STATUS_PUBLIC
    ]);

    Livewire::actingAs($admin)
        ->test(EditPost::class, ['record' => Post::factory()->create()->id])
        ->set('data.related_content_ids', $contents->pluck('id')->toArray())
        ->assertHasErrors(['data.related_content_ids']);
});
```

## Performance Optimization

### Caching Strategy
- Cache related contents for each post (15 min TTL)
- Cache keyword extraction results (30 min TTL)
- Use eager loading for content relationships

```php
// In BlogController
$relatedContents = Cache::remember(
    "post.{$post->id}.related_contents",
    900, // 15 minutes
    fn() => $post->getRelatedContents(4)
);
```

### Database Indexes
- Index `contents.status` for faster filtering
- Index `contents.views_count` for popularity sorting
- Full-text index on `title`, `body`, `excerpt` for better keyword matching (optional)

## Configuration

Add to `config/blog.php` (create if needed):

```php
return [
    // Related contents settings
    'related_contents' => [
        'max_display' => 4,
        'enable_auto_discovery' => true,
        'min_keyword_length' => 4,
        'max_keywords' => 5,
        'cache_ttl' => 900, // 15 minutes
    ],

    // Keyword extraction stopwords
    'stopwords' => [
        'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at',
        'to', 'for', 'of', 'with', 'by', 'from', 'as', 'is',
        'was', 'are', 'were', 'been', 'be', 'have', 'has', 'had',
        'do', 'does', 'did', 'will', 'would', 'should', 'could',
        'may', 'might', 'can', 'this', 'that', 'these', 'those',
    ],
];
```

## Implementation Order

1. Create migration for `related_content_ids` column
2. Update Post model with casts and methods
3. Create content card Blade component
4. Update blog show template with related contents section
5. Update BlogController to load related contents
6. Add Filament form field for manual selection
7. Add CSS styling
8. Write unit tests for keyword extraction and content matching
9. Write feature tests for display logic
10. Test admin interface for content selection
11. Add caching layer
12. Deploy and monitor performance

## Migration Checklist

- [ ] Run migration: `php artisan migrate`
- [ ] Test auto-discovery with existing blog posts
- [ ] Verify content card design matches Content Library
- [ ] Test responsive layout (1, 2, 4 columns)
- [ ] Test manual content selection in admin
- [ ] Test max 4 contents validation
- [ ] Verify only public/members-only contents shown
- [ ] Test keyword extraction accuracy
- [ ] Test fallback to popular contents
- [ ] Verify caching works correctly
- [ ] Test "Browse All Content" link
- [ ] Check SEO impact (internal linking)

## Future Enhancements

- **ML-based Recommendations**: Use machine learning for better content matching
- **Click Tracking**: Track which related contents get clicked
- **A/B Testing**: Test different numbers of related contents (2 vs 4)
- **Category-based Matching**: Prioritize contents from same categories
- **Tag-based Matching**: If tags are added to blog posts
- **User Personalization**: Show contents based on user interests
- **Analytics Dashboard**: Track related content performance
