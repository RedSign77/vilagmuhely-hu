# Content Engine Package

A comprehensive content management system for the Világműhely application, supporting multiple content types with rich metadata and access control.

## Features

- **Multiple Content Types**: Digital files, image galleries, markdown posts, articles, and RPG modules
- **Rich Metadata**: Categories, tags, creators, publish dates, and custom metadata
- **Access Control**: Draft, preview, members-only, and public content statuses
- **File Management**: Upload and manage PDF and ZIP files with size tracking
- **Image Galleries**: Multi-image support with reorderable images
- **Markdown Support**: Full markdown editing for posts and articles
- **SEO Optimization**: Meta titles and descriptions
- **Statistics Tracking**: View and download counts
- **Soft Deletes**: Safely delete and restore content
- **Filterable & Searchable**: Advanced filtering and search capabilities

## Content Types

### 1. Digital File (PDF, ZIP)
Upload and distribute digital files such as PDFs and ZIP archives.

**Features:**
- File upload (max 100MB)
- File type and size tracking
- Download counter
- Access control (public preview or full access for members)

**Use Cases:**
- RPG rulebooks (PDF)
- Adventure modules (PDF)
- Asset packs (ZIP)
- Templates and resources

### 2. Image Gallery
Create collections of images.

**Features:**
- Multiple image uploads (up to 50 images)
- Image editor integration
- Reorderable images
- Featured image support

**Use Cases:**
- Character art galleries
- Map collections
- Inspiration boards
- Portfolio showcases

### 3. Markdown Post
Short-form content with markdown formatting.

**Features:**
- Markdown editor with toolbar
- Code blocks and tables
- Excerpt support
- Quick publishing

**Use Cases:**
- News and announcements
- Quick tips and tricks
- Community updates
- Short stories

### 4. Long Article / Tutorial
In-depth content for tutorials and guides.

**Features:**
- Full markdown editing
- Table of contents support
- SEO optimization
- Featured image

**Use Cases:**
- Worldbuilding guides
- Game master tutorials
- How-to articles
- In-depth lore documents

### 5. RPG Module / Card Pack / Worldbuilding Draft
Specialized content for RPG and creative projects.

**Features:**
- Markdown content
- Custom metadata fields
- File attachments
- Gallery support

**Use Cases:**
- Campaign settings
- NPC collections
- Card game decks
- Worldbuilding templates

## Content Status

Content can have one of four statuses:

### 1. Draft
- Not visible to anyone except administrators
- Work in progress
- Unpublished

### 2. Public Preview
- Visible to all users
- Limited preview (excerpt only)
- Full content requires login/membership

### 3. Members Only (Full)
- Only visible to registered users
- Full content access for members
- Requires authentication

### 4. Public (Full)
- Visible to everyone
- Full content access
- No authentication required

## Installation

The package is auto-discovered and automatically registered in your Laravel application.

### Database Migration

Run migrations to create the necessary tables:

```bash
php artisan migrate
```

This creates the following tables:
- `content_categories` - Content categories
- `content_tags` - Content tags
- `contents` - Main content table
- `content_tag` - Content-tag relationships (pivot table)

### Package Registration

Add to `composer.json`:

```bash
composer require webtechsolutions/content-engine
```

## File Structure

```
packages/content-engine/
├── database/
│   └── migrations/
│       ├── 2024_01_01_000001_create_content_categories_table.php
│       ├── 2024_01_01_000002_create_content_tags_table.php
│       ├── 2024_01_01_000003_create_contents_table.php
│       └── 2024_01_01_000004_create_content_tag_pivot_table.php
├── src/
│   ├── Filament/
│   │   └── Resources/
│   │       ├── ContentResource.php
│   │       ├── ContentResource/
│   │       │   └── Pages/
│   │       │       ├── ListContents.php
│   │       │       ├── CreateContent.php
│   │       │       └── EditContent.php
│   │       ├── ContentCategoryResource.php
│   │       ├── ContentCategoryResource/
│   │       │   └── Pages/
│   │       │       ├── ListContentCategories.php
│   │       │       ├── CreateContentCategory.php
│   │       │       └── EditContentCategory.php
│   │       ├── ContentTagResource.php
│   │       └── ContentTagResource/
│   │           └── Pages/
│   │               ├── ListContentTags.php
│   │               ├── CreateContentTag.php
│   │               └── EditContentTag.php
│   ├── Models/
│   │   ├── Content.php
│   │   ├── ContentCategory.php
│   │   └── ContentTag.php
│   └── Providers/
│       └── ContentEngineServiceProvider.php
├── composer.json
└── README.md
```

## Usage

### Accessing Content Management

Navigate to: **Admin → Content Engine → Contents**

Additional resources:
- **Categories**: Admin → Content Engine → Categories
- **Tags**: Admin → Content Engine → Tags

### Creating Content

1. Navigate to **Content Engine → Contents**
2. Click **New Content**
3. Fill in basic information:
   - Title (required)
   - Content type (required)
   - Status (required)
4. Add content based on type:
   - **Digital File**: Upload PDF or ZIP file
   - **Image Gallery**: Upload multiple images
   - **Markdown/Article/RPG**: Write content in markdown editor
5. Set metadata:
   - Category
   - Tags
   - Creator
   - Publish date
6. Optionally add:
   - Featured image
   - SEO meta title and description
   - Custom metadata
7. Click **Create**

### Managing Categories

Categories organize content into logical groups.

**Creating a Category:**
1. Navigate to **Content Engine → Categories**
2. Click **New Category**
3. Fill in details:
   - Name (e.g., "Tutorials")
   - Description
   - Color (for UI styling)
   - Icon (Heroicon name)
   - Sort order
4. Click **Create**

**Category Features:**
- Reorderable via drag-and-drop
- Color-coded for visual organization
- Active/inactive status
- Content count tracking

### Managing Tags

Tags provide flexible, cross-cutting categorization.

**Creating a Tag:**
1. Navigate to **Content Engine → Tags**
2. Click **New Tag**
3. Fill in:
   - Name (e.g., "beginner-friendly")
   - Color
4. Click **Create**

**Tag Features:**
- Auto-generated slugs
- Color-coded badges
- Content count tracking
- Multiple tags per content

## Content Tabs

The content list page includes tabs for quick filtering:

- **All Content** - All content items
- **Digital Files** - PDF and ZIP files only
- **Image Galleries** - Gallery content only
- **Markdown Posts** - Short-form posts
- **Articles** - Long-form articles and tutorials
- **RPG Modules** - RPG and worldbuilding content
- **Drafts** - Unpublished content
- **Published** - Published content only

## Programmatic Usage

### Querying Content

```php
use Webtechsolutions\ContentEngine\Models\Content;

// Get all public content
$publicContent = Content::public()->get();

// Get published articles
$articles = Content::ofType(Content::TYPE_ARTICLE)
    ->published()
    ->get();

// Get member-accessible content
$memberContent = Content::forMembers()->get();

// Get content by category
$tutorials = Content::whereHas('category', function ($query) {
    $query->where('slug', 'tutorials');
})->get();

// Get content with specific tags
$beginnerContent = Content::whereHas('tags', function ($query) {
    $query->where('slug', 'beginner-friendly');
})->get();
```

### Creating Content Programmatically

```php
use Webtechsolutions\ContentEngine\Models\Content;

$content = Content::create([
    'title' => 'Getting Started Guide',
    'type' => Content::TYPE_ARTICLE,
    'status' => Content::STATUS_PUBLIC,
    'body' => '# Welcome\n\nThis is a markdown tutorial...',
    'excerpt' => 'Learn the basics of worldbuilding',
    'category_id' => 1,
    'creator_id' => auth()->id(),
    'published_at' => now(),
]);

// Attach tags
$content->tags()->attach([1, 2, 3]);
```

### Tracking Statistics

```php
// Increment view count
$content->incrementViews();

// Increment download count
$content->incrementDownloads();

// Check stats
$viewCount = $content->views_count;
$downloadCount = $content->downloads_count;
```

### Content Status Helpers

```php
// Check if content is published
if ($content->isPublished()) {
    // Content is published
}

// Check if content is public
if ($content->isPublic()) {
    // Content is publicly accessible
}

// Check if content requires authentication
if ($content->requiresAuth()) {
    // Requires login
}
```

## Model Relationships

### Content Model

```php
// Get creator
$creator = $content->creator;

// Get category
$category = $content->category;

// Get tags
$tags = $content->tags;
```

### Category Model

```php
// Get all content in category
$contents = $category->contents;

// Get active categories only
$activeCategories = ContentCategory::active()->get();

// Get ordered categories
$categories = ContentCategory::ordered()->get();
```

### Tag Model

```php
// Get all content with tag
$contents = $tag->contents;
```

## File Storage

Files are stored in the following directories:

- `storage/app/public/content/featured/` - Featured images
- `storage/app/public/content/files/` - Digital files (PDF, ZIP)
- `storage/app/public/content/galleries/` - Gallery images

Make sure to run `php artisan storage:link` to create the symbolic link.

## SEO Features

Each content item supports:

- **Meta Title**: Custom title for search engines (max 60 characters)
- **Meta Description**: Custom description for search engines (max 160 characters)
- **Slug**: URL-friendly identifier (auto-generated from title)
- **Featured Image**: Social media sharing image

## Security Considerations

- File uploads are restricted to PDF and ZIP formats
- Maximum file size: 100MB
- File paths are validated and sanitized
- Soft deletes prevent accidental data loss
- Access control through status field
- Creator tracking for all content

## Customization

### Adding Custom Content Types

Edit `Content.php` model and migration:

```php
// In migration
'type' => enum('type', [
    'digital_file',
    'image_gallery',
    'markdown_post',
    'article',
    'rpg_module',
    'custom_type', // Add new type
]);

// In Content model
public const TYPE_CUSTOM = 'custom_type';

public static function getTypes(): array
{
    return [
        // ... existing types
        self::TYPE_CUSTOM => 'Custom Content Type',
    ];
}
```

### Adding Custom Metadata

Use the `metadata` JSON field to store custom key-value pairs:

```php
$content->metadata = [
    'difficulty' => 'beginner',
    'estimated_time' => '30 minutes',
    'prerequisites' => ['basic-knowledge'],
];
$content->save();
```

## License

This package is proprietary software developed for the Világműhely application.
