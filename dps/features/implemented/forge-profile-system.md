# The Forge - User Profile System

Status: Implemented

## Overview

The Forge is a public user profile system that transforms creator identities into immersive, gamified experiences. Each user gets a unique SEO-friendly profile page featuring their 3D crystal, RPG-style stats, content portfolio, and activity timeline.

## URL Structure

**Primary Route**: `/forge/{username}`
- SEO-friendly username-based URLs
- Example: `vilagmuhely.hu/forge/zoltan-nemeth`

**Privacy**: User real names are anonymized as "Creator #{id}" on public profiles

## RPG Stats Mapping

### Rank/Level (Based on Complexity)
- **Source**: `facet_count` (4-50)
- **Display**: "Rank • Lv.X"

**Rank Tiers**:
- **Novice**: 1-7 facets
- **Journeyman**: 8-14 facets
- **Apprentice**: 15-24 facets
- **Craftsperson**: 25-34 facets
- **Artisan**: 35-44 facets
- **Crystal Master**: 45-50 facets

### Aura/Resonance (Based on Brightness)
- **Source**: `glow_intensity` (0.0-1.0)
- **Display**: "X%" (0-100)
- **Calculation**: `round(glow_intensity * 100)`

### Essence/Clarity (Based on Purity)
- **Source**: `purity_level` (0.3-1.0)
- **Display**: "X%" (30-100)
- **Calculation**: `round(purity_level * 100)`

## Portfolio Sections

### 1. Authored Works (📚)
- **Source**: `User->contents()->published()`
- **Display**: Latest 6 works
- **Content**: Title, description (100 chars), type badge, category, views, downloads

### 2. The Vault (🗝️)
- **Source**: `User->downloads()` (all downloads, free + paid)
- **Display**: Latest 6 collected items
- **Content**: Same as Authored Works

### 3. Echoes (💬)
- **Source**: `User->reviews()->approved()`
- **Display**: Latest 5 reviews
- **Content**: Title, text (200 chars), helpful votes, content link, timestamp

## Activity Feed

### Source
- **Table**: `crystal_activity_queue`
- **Limit**: 20 most recent activities
- **Order**: `created_at DESC`

### Activity Types & Icons
- `content_published` → 📝 "Published new content"
- `content_downloaded` → ⬇️ "Downloaded content from the library"
- `content_rated` → ⭐ "Rated a piece of content"
- `content_reviewed` → 💬 "Wrote a review"
- `achievement_unlocked` → 🏆 "Unlocked achievement: {name}"
- `invitation_sent` → ✉️ "Invited a new creator"
- `invitation_accepted` → 🤝 "Joined the workshop"
- `content_milestone_views` → 👁️ "Content reached {X} views"
- `content_milestone_downloads` → 🎯 "Content reached {X} downloads"

## SEO Implementation

### Meta Tags
**Title**: `"{Creator #ID}'s Forge – {Color} Crystal {Rank} | Világműhely"`
**Description**: `"Explore {Creator #ID}'s creative forge: Level {X} {Rank} with {N} works, {M} crystal facets, and {P}% aura resonance."`

### Sitemap
- **Priority**: 0.8
- **Change Frequency**: Weekly
- **Last Modified**: `crystal_metric.last_calculated_at ?? user.updated_at`
- **Filter**: Only users with `username` AND `crystalMetric`

## Database Schema

### users table (additions)
```
username VARCHAR(50) UNIQUE NOT NULL
INDEX (username)
```

### Relationships
- `User->contents()` - Authored works
- `User->downloads()` - The Vault
- `User->reviews()` - Echoes
- `User->crystalMetric()` - RPG stats source

## User Model Extensions

### Accessors
- `rpg_stats` - Returns array with rank, level, aura, essence
- `crystal_color_name` - Returns simple color name from dominant_colors[0]
- `anonymized_name` - Returns "Creator #{id}" (existing)

### Methods
- `getRouteKeyName()` - Returns 'username' for route binding
- `calculateRank(int $level)` - Maps level to rank title
- `hexToColorName(string $hex)` - Converts hex to simple color name
- `getRecentActivities(int $limit = 20)` - Fetches from activity queue

## Frontend Components

### 3D Crystal Viewer
**Reused**: Existing `CrystalViewer.js` component
**Configuration**:
- `data-user-id`: User ID
- `data-auto-rotate`: true
- `data-rotation-speed`: 0.004
- `data-camera-distance`: 3.5
- `data-size`: large

### Alpine.js Tabs
**Library**: Alpine.js 3.x (CDN)
**State**: `x-data="{ activeTab: 'authored' }"`
**Tabs**: authored | vault | echoes

## Styling

### Key CSS Classes
- `.forge-hero-section` - Gradient background container
- `.forge-stats-panel` - RPG stats display
- `.stat-progress-fill` - Progress bar (purple/amber/blue)
- `.portfolio-tabs` - Tab navigation
- `.content-grid` - Responsive card grid (300px min)
- `.activity-timeline` - Vertical timeline
- `.empty-state` - Empty content message

### Responsive Breakpoints
- **1024px**: Reduce crystal height, adjust grid
- **768px**: Vertical tabs, single column grid

## Performance Considerations

### Query Optimization
- **Eager Loading**: `contents`, `downloads`, `reviews` loaded with limits
- **N+1 Prevention**: `with(['content'])` on reviews
- **Count Queries**: Separate count() queries for totals

### Caching
- **Sitemap**: 12-hour cache
- **Crystal Data**: Pre-cached geometry in `cached_geometry` JSON field

## Privacy & Security

### Privacy
- Anonymized display names ("Creator #{id}")
- Username only visible in URL
- No real names on public profiles

### Security
- Username validation: alphanumeric + hyphens/underscores
- Route regex constraints: `[a-zA-Z0-9_-]+`
- XSS protection: Blade {{ }} escaping

## Phase 2 Enhancements (Deferred)

- Full pagination for "View All" sections
- Follow system for Crystal Masters
- Social sharing buttons
- Achievements display section
- Profile customization options
- Advanced filtering/sorting
- Analytics tracking (profile views)

## Testing

### Feature Tests (ForgeProfileTest)
- Display profile with username
- Redirect to gallery if no metrics
- Show anonymized name for privacy
- Display RPG stats correctly
- Display activity feed

### Unit Tests (UserRpgStatsTest)
- Calculate RPG stats correctly
- Handle users without metrics
- Calculate correct ranks at different levels
- Convert percentages correctly
- Return correct color names

## Implementation Files

### New Files
- `database/migrations/YYYY_MM_DD_add_username_to_users_table.php`
- `database/seeders/UsernameSeeder.php`
- `app/Http/Controllers/ForgeController.php`
- `resources/views/forge/profile.blade.php`
- `resources/views/forge/partials/content-card.blade.php`
- `resources/views/forge/partials/review-card.blade.php`
- `resources/css/forge-profile.css`
- `tests/Feature/ForgeProfileTest.php`
- `tests/Unit/UserRpgStatsTest.php`

### Modified Files
- `app/Models/User.php` - Added username, RPG stats, helpers
- `routes/web.php` - Added Forge route, updated sitemap
- `resources/css/app.css` - Imported forge-profile.css

## Deployment Checklist

- [x] Run migration: `php artisan migrate`
- [x] Seed usernames: `php artisan db:seed --class=UsernameSeeder`
- [x] Build assets: `npm run build`
- [x] Clear cache: `php artisan cache:clear`
- [x] Regenerate sitemap (auto-cached for 12 hours)
- [x] Test Forge profile access for existing users
- [x] Verify SEO meta tags in browser
