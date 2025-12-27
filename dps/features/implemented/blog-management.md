# Blog Management System

Status: Implemented

## Overview
Complete blog content management system with Filament admin interface, automated SEO optimization, and public-facing blog pages.

## Database Schema

### posts Table
```php
- id: bigint unsigned (PK)
- author_id: bigint unsigned (FK → users.id, cascade delete)
- title: string(255)
- slug: string(255) unique
- excerpt: text nullable
- content: longtext
- featured_image: string(255) nullable
- status: string(255) default 'draft'
- meta_title: string(255) nullable
- meta_description: text nullable
- meta_keywords: string(255) nullable
- published_at: timestamp nullable
- created_at: timestamp
- updated_at: timestamp

Indexes:
- slug (unique)
- status
- published_at
```

## Models

### Post Model (`app/Models/Post.php`)

**Fillable Fields:**
- author_id, title, slug, excerpt, content, featured_image
- status, meta_title, meta_description, meta_keywords, published_at

**Casts:**
- published_at: datetime

**Relationships:**
- `author()`: BelongsTo User

**Scopes:**
- `published()`: Only published posts with published_at <= now
- `draft()`: Only draft posts
- `archived()`: Only archived posts

**Route Key:**
- Uses 'slug' instead of 'id'

**Methods:**
- `isPublished()`: Check if post is published and date has passed

**Auto-Slug Generation:**
- Creating: Auto-generates slug from title if empty
- Updating: Auto-updates slug if title changes (unless manually set)
- Ensures uniqueness by appending counter (-1, -2, etc.)

## Observer Pattern

### PostObserver (`app/Observers/PostObserver.php`)

**Events:**
- `creating()`: Generates SEO metadata before creation
- `updating()`: Updates SEO metadata before update

**SEO Metadata Generation:**
```php
1. meta_title: Auto-set from title if empty
2. meta_description: Extract first 160 chars from content if empty
3. published_at: Auto-set to now() when status changes to 'published'
```

**Registration:**
- Registered in `AppServiceProvider::boot()`

## Filament Admin Resource

### PostResource (`app/Filament/Admin/Resources/PostResource.php`)

**Navigation:**
- Icon: heroicon-o-document-text
- Group: Content
- Sort: 1

**Form Sections:**

1. **Post Content**
   - Title (live slug generation on blur)
   - Slug (unique, auto-generated, manual override allowed)
   - Excerpt (textarea, 3 rows)
   - Content (RichEditor with toolbar: bold, italic, link, lists, headings, blockquote, code)
   - Featured Image (image upload, editor, directory: blog/featured-images)

2. **Publishing**
   - Status (select: draft/published/archived, default: draft)
   - Published At (datetime picker, optional)
   - Author (relationship select, defaults to current user)

3. **SEO Metadata** (collapsible)
   - Meta Title (auto-generated from title)
   - Meta Description (max 160 chars, auto-generated from content)
   - Meta Keywords (comma-separated)

**Table Columns:**
- Title (searchable, sortable, limit 50)
- Author Name (searchable, sortable)
- Status (badge: draft=secondary, published=success, archived=warning)
- Published At (datetime, since format, placeholder if null)
- Created At (toggleable, hidden by default)
- Updated At (toggleable, hidden by default)

**Filters:**
- Status (draft/published/archived)
- Author (relationship)
- Published (custom query filter)

**Actions:**
- View (opens public URL in new tab, visible only if published)
- Edit
- Delete

**Bulk Actions:**
- Delete

**Default Sort:**
- created_at DESC

## Controllers

### BlogController (`app/Http/Controllers/BlogController.php`)

**Routes:**
- GET /blog → index()
- GET /blog/{post} → show(Post $post)

**index() Method:**
```php
- Query: Published posts with author relationship
- Order: published_at DESC
- Pagination: 12 per page
- View: blog.index
```

**show() Method:**
```php
- Route Model Binding: By slug
- Access Control: Only published posts for guests, all posts for authenticated
- Related Posts: Same author, limit 3, exclude current
- View: blog.show
```

## Views

### blog/index.blade.php

**Layout:** extends layouts.app

**SEO:**
- Title: Blog
- Meta Description: Latest blog posts about content creation
- Meta Keywords: blog, articles, content creation, gamification

**Features:**
- Grid layout (md:2, lg:3 columns)
- Featured image or placeholder icon
- Post metadata (anonymized author, published date)
- Excerpt with line-clamp
- "Read more" link
- Pagination
- Empty state for no posts

### blog/show.blade.php

**Layout:** extends layouts.app

**Dynamic SEO:**
- Title: post.meta_title ?? post.title
- Meta Description: post.meta_description
- Meta Keywords: post.meta_keywords
- OG Image: featured_image or default

**Features:**
- Back to blog link
- Featured image (aspect-video)
- Author and date (anonymized)
- Draft badge for authenticated users
- Excerpt (if present)
- Full content (prose styling)
- Related posts section (3 posts, same author)

## Routes

```php
// Public Routes
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post}', [BlogController::class, 'show'])->name('blog.show');
```

## Navigation Integration

**Main Layout (`layouts/app.blade.php`):**
- Added "Blog" link in navigation menu
- Position: Between Crystal Gallery and Dashboard/Login
- Route: route('blog.index')

## Status Workflow

### Draft Status
- Default status for new posts
- Visible only in admin panel
- Not accessible via public routes (404 for guests)
- No published_at date set

### Published Status
- Visible on public blog pages
- published_at auto-set to now() if empty
- Accessible via /blog/{slug}
- SEO metadata auto-generated
- Appears in blog index pagination

### Archived Status
- Removed from public visibility
- Maintained in database
- Accessible only in admin panel
- Can be republished by changing status

## SEO Features

### Automatic Optimization
1. **Slug Generation:**
   - Clean URLs from titles
   - Automatic uniqueness handling

2. **Meta Tags:**
   - Auto-generated meta_title from title
   - Auto-extracted meta_description (160 chars)
   - Manual override supported

3. **Structured Data:**
   - Page-level meta tags
   - Open Graph tags
   - Twitter Card tags

### Manual Controls
- Custom meta_title override
- Custom meta_description (max 160 chars)
- Manual meta_keywords
- Slug manual override

## Privacy & Anonymization

**Author Display:**
- Uses `$post->author->anonymized_name`
- Format: "Creator #ID"
- No avatar display
- Consistent with platform privacy policy

## File Storage

**Featured Images:**
- Directory: storage/app/public/blog/featured-images
- Upload via Filament image field
- Image editor integrated
- Served via asset('storage/...')

## Validation Rules

**Title:**
- Required
- Max 255 characters

**Slug:**
- Required
- Max 255 characters
- Unique in posts table

**Content:**
- Required
- No length limit (longtext)

**Status:**
- Required
- Enum: draft, published, archived

**Author:**
- Required
- Must exist in users table

## Query Optimization

**Index Strategy:**
- slug: Unique index for route model binding
- status: Index for filtering published posts
- published_at: Index for date ordering

**Eager Loading:**
- Always load 'author' relationship
- Prevents N+1 queries on list views

## Future Enhancements (Not Implemented)

**Potential Features:**
- Categories/Tags (Spatie Laravel Tags)
- Comments system
- Post views counter
- Reading time estimation
- Featured posts flag
- Multi-language support
- Scheduled publishing queue
- Draft previews with shareable links
- Post revisions/history
- Sitemap integration for blog posts

## Testing Considerations

**Key Test Cases:**
1. Slug auto-generation and uniqueness
2. SEO metadata auto-generation
3. Published scope filtering
4. Route model binding by slug
5. Access control (guests vs authenticated)
6. Observer triggers
7. Status transitions
8. Related posts query

## Files Modified/Created

**New Files:**
- database/migrations/2025_12_21_131706_create_posts_table.php
- app/Models/Post.php
- app/Observers/PostObserver.php
- app/Http/Controllers/BlogController.php
- app/Filament/Admin/Resources/PostResource.php
- app/Filament/Admin/Resources/PostResource/Pages/ListPosts.php
- app/Filament/Admin/Resources/PostResource/Pages/CreatePost.php
- app/Filament/Admin/Resources/PostResource/Pages/EditPost.php
- resources/views/blog/index.blade.php
- resources/views/blog/show.blade.php

**Modified Files:**
- app/Providers/AppServiceProvider.php (PostObserver registration)
- routes/web.php (blog routes)
- resources/views/layouts/app.blade.php (navigation link)

## Implementation Notes

1. **Observer vs Model Events:**
   - Using dedicated Observer for SEO logic
   - Keeps model clean and focused
   - Easier testing and maintenance

2. **Slug Strategy:**
   - Handled in model boot events
   - Ensures slugs always valid before save
   - No database constraints violated

3. **Published Logic:**
   - Both status='published' AND published_at<=now required
   - Supports scheduled publishing
   - Clear separation of draft/published states

4. **Related Posts:**
   - Simple same-author algorithm
   - Could be enhanced with tags/categories
   - Limited to 3 for performance

5. **Anonymization:**
   - Consistent with platform privacy approach
   - Uses User model accessor methods
   - No special blog-specific logic needed
