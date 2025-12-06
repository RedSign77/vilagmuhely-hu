# Műhely Kristály (Workshop Crystal) Gamification System

## Overview

A 3D visual gamification system that represents each creator's growth, engagement, and diversity through a dynamically evolving crystal. Each user's crystal is unique and changes based on their activity, content quality, and community interactions.

## Crystal Dimensions

### 1. Size & Geometry (Facets)
**Driven by**: Content quantity + diversity across types

- **Metric**: `facet_count` (4-50)
- **Algorithm**: Base facets (4) + content-based facets (1 per 2 items) + diversity bonus (up to 20)
- **Visual Effect**: More facets = more complex, multifaceted crystal

### 2. Brightness & Clarity (Glow)
**Driven by**: Interaction quality (views, downloads, helpful ratings)

- **Metric**: `glow_intensity` (0.00-1.00)
- **Algorithm**: Logarithmic scaling of interaction score
- **Visual Effect**: Higher glow = brighter, more radiant crystal

### 3. Color & Layering (Essence)
**Driven by**: Dominant content categories + achievements

- **Metric**: `dominant_colors` (array of hex colors)
- **Algorithm**: Top 3 categories by content count
- **Visual Effect**: Multi-colored layers representing content variety

## Database Schema

### user_crystal_metrics
Stores aggregate metrics and cached 3D geometry per user.

| Field | Type | Description |
|-------|------|-------------|
| user_id | foreignId | FK to users table |
| total_content_count | unsignedInteger | Published content count |
| diversity_index | decimal(5,3) | Shannon entropy (0-1) |
| interaction_score | decimal(10,2) | Weighted views + downloads + ratings |
| engagement_score | decimal(10,2) | Ratings given + participation days |
| facet_count | unsignedTinyInteger | Crystal complexity (4-50) |
| glow_intensity | decimal(3,2) | Brightness level (0-1) |
| purity_level | decimal(3,2) | Transparency level (0-1) |
| dominant_colors | json | Array of hex colors |
| cached_geometry | json | Complete 3D data for frontend |
| last_calculated_at | timestamp | Last recalculation time |

### content_ratings
User ratings and critiques on content.

| Field | Type | Description |
|-------|------|-------------|
| content_id | foreignId | FK to contents table |
| user_id | foreignId | FK to users (rater) |
| rating | unsignedTinyInteger | 1-5 stars |
| critique_text | text | Optional written feedback |
| is_helpful | boolean | Marked helpful by creator |

### crystal_activity_queue
Temporary queue for batching metric updates.

| Field | Type | Description |
|-------|------|-------------|
| user_id | foreignId | User to update |
| activity_type | string | Event type |
| metadata | json | Additional context |
| processed_at | timestamp | Processing completion time |

## Event Flow

### 1. Trigger Events

**ContentPublishedEvent**
- Fired when: Content status changes to public
- Queues: `content_published` activity for creator

**ContentViewedEvent**
- Fired when: Content view count incremented
- Queues: `content_viewed` activity for creator

**ContentDownloadedEvent**
- Fired when: Content download count incremented
- Queues: `content_downloaded` activity for creator

**ContentRatedEvent**
- Fired when: New rating created
- Queues: `content_rated` for both creator AND rater

**AchievementUnlockedEvent**
- Fired when: User earns an achievement (future)
- Queues: `achievement_unlocked` activity

### 2. Queue Activities

`QueueCrystalUpdateListener` catches all events and adds entries to `crystal_activity_queue` table. **Does not recalculate immediately** - batching approach.

### 3. Batch Processing (Every 30 Minutes)

**Scheduled Command**: `crystal:process-updates`
- Runs every 30 minutes
- Finds users with unprocessed activities
- Dispatches `RecalculateCrystalMetricsJob` for each user

### 4. Async Recalculation

**Job**: `RecalculateCrystalMetricsJob`
- Uses `CrystalCalculatorService` to recalculate all metrics
- Generates 3D geometry (Fibonacci sphere vertices + convex hull faces)
- Caches result in `cached_geometry` JSON field
- Marks queue activities as processed

## Algorithms

### Diversity Index (Shannon Entropy)
```
H = -Σ(p_i * log₂(p_i))
normalized = H / log₂(5)  // Max 5 content types
```

### Interaction Score (Weighted)
```
score = (views × 0.3) + (downloads × 0.5) + (helpful_ratings × 1.0)
```

### Engagement Score (Weighted)
```
score = (ratings_given × 0.4) + (participation_days × 0.6)
```

### Facet Count
```
facets = 4 + floor(content_count / 2) + floor(diversity_index × 20)
capped at 50
```

### Glow Intensity (Logarithmic)
```
intensity = log₁₀(interaction_score + 1) / 4
range: 0.00 - 1.00
```

### Purity Level (Logarithmic)
```
purity = 0.3 + log₁₀(engagement_score + 1) / 5
range: 0.30 - 1.00
```

## 3D Geometry Generation

### Fibonacci Sphere
Generates evenly distributed points on a sphere surface using the golden ratio.

```
φ = (1 + √5) / 2  // Golden ratio
for each point i:
  y = 1 - (2i / (n-1))
  radius = √(1 - y²)
  θ = 2π × i / φ
  x = cos(θ) × radius
  z = sin(θ) × radius
```

### Convex Hull Approximation
Simple triangulation connecting neighboring points.

### Output Format (JSON)
```json
{
  "vertices": [[x, y, z], ...],
  "faces": [[v1, v2, v3], ...],
  "normals": [[nx, ny, nz], ...],
  "colors": [[r, g, b], ...]
}
```

Ready for direct consumption by Three.js `BufferGeometry`.

## API Endpoints

### GET /api/v1/crystals/{userId}
Get crystal data for a specific user.

**Response**:
```json
{
  "success": true,
  "data": {
    "user": {"id": 1, "name": "John Doe", "avatar": "..."},
    "metrics": {
      "total_content": 25,
      "diversity": 0.856,
      "interaction_score": 1523.50,
      "engagement_score": 245.80
    },
    "crystal": {
      "facets": 32,
      "glow_intensity": 0.78,
      "purity": 0.65,
      "colors": ["#6366f1", "#ec4899", "#10b981"]
    },
    "geometry": {
      "vertices": [...],
      "faces": [...],
      "normals": [...],
      "colors": [...]
    },
    "last_updated": "2024-12-06T10:30:00Z"
  }
}
```

### GET /api/v1/crystals/gallery?sort=interaction&limit=20
Get top crystals (gallery view).

**Query Parameters**:
- `sort`: `interaction`, `diversity`, or `engagement`
- `limit`: 1-50 (default 20)

### GET /api/v1/crystals/leaderboard
Get top 10 users in each category.

**Response**:
```json
{
  "success": true,
  "data": {
    "interaction": [{...}],
    "diversity": [{...}],
    "engagement": [{...}]
  }
}
```

### POST /api/v1/content/{contentId}/rate (Auth Required)
Rate a content item.

**Request**:
```json
{
  "rating": 5,
  "critique_text": "Excellent worldbuilding!"
}
```

### POST /api/v1/content/{contentId}/rate/{ratingId}/helpful (Auth Required)
Mark a rating as helpful (content creator only).

## Frontend Integration (TODO)

### Three.js Crystal Viewer

**Location**: `resources/js/components/CrystalViewer.js`

**Usage**:
```javascript
import { CrystalViewer } from './components/CrystalViewer.js';
new CrystalViewer('crystal-viewer', userId);
```

**Features**:
- Fetches crystal data from API
- Creates Three.js scene with BufferGeometry
- Applies vertex colors, glow, and transparency
- Orbit controls for interaction
- Slow rotation animation

### Integration Points

1. **User Profile Page**: Large 3D crystal display
2. **Content List Thumbnails**: Small crystal icon next to creator
3. **Public Gallery**: Grid of all creators' crystals

## Manual Commands

```bash
# Process crystal updates manually
docker exec vilagmuhely-php-fpm-1 php artisan crystal:process-updates

# View scheduled tasks
docker exec vilagmuhely-php-fpm-1 php artisan schedule:list

# Test crystal calculation for specific user
docker exec vilagmuhely-php-fpm-1 php artisan tinker
>>> $user = User::find(1);
>>> app(CrystalCalculatorService::class)->recalculateMetrics($user);
```

## Testing Crystal Events

```php
use Webtechsolutions\ContentEngine\Events\ContentPublishedEvent;
use Webtechsolutions\ContentEngine\Models\Content;

$content = Content::find(1);
event(new ContentPublishedEvent($content));

// Check queue
CrystalActivityQueue::unprocessed()->get();

// Process immediately (bypass schedule)
RecalculateCrystalMetricsJob::dispatch($content->creator_id);
```

## Performance Considerations

- **Batch Processing**: Updates queued and processed every 30 min (not realtime)
- **Cached Geometry**: 3D data pre-calculated and stored as JSON
- **Indexed Queries**: All metric queries use indexed columns
- **Queue Workers**: Ensure queue workers are running for job processing
- **Auto Cleanup**: Old processed activities deleted after 30 days

## Future Enhancements

- [ ] Achievement system (badges, titles)
- [ ] Crystal evolution history viewer
- [ ] Exportable 3D models (STL/OBJ)
- [ ] Crystal customization themes
- [ ] Social sharing of crystals
- [ ] Crystal comparison tool
- [ ] Real-time WebSocket updates (optional)

## Troubleshooting

**Crystals not updating?**
- Check queue worker is running: `docker exec vilagmuhely-php-fpm-1 php artisan queue:listen`
- Check schedule is running: `docker exec vilagmuhely-php-fpm-1 php artisan schedule:work`
- Manually process: `docker exec vilagmuhely-php-fpm-1 php artisan crystal:process-updates`

**API returns 404?**
- Run migrations: `docker exec vilagmuhely-php-fpm-1 php artisan migrate`
- Clear caches: `docker exec vilagmuhely-php-fpm-1 php artisan optimize:clear`

**Geometry looks wrong?**
- Check `cached_geometry` JSON structure
- Verify facet_count is within 4-50 range
- Test recalculation manually in Tinker

---

**Created**: 2024-12-06
**Version**: 1.0
**Status**: Backend Complete - Frontend Pending
