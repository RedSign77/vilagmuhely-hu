# Social Media Images Feature

Status: Implemented

## Overview
Implementation of optimized social media sharing images for Open Graph and Twitter Card meta tags to improve social media presence and sharing appearance.

## Source Image

**Location**: `storage/app/public/vilagmuhely-title.jpg`
- **Dimensions**: 2016x1152 px
- **Aspect Ratio**: 1.75:1 (7:4)
- **File Size**: 243.5 kB
- **Purpose**: Primary social media sharing image

## Platform Requirements

### Open Graph (Facebook, LinkedIn, Discord)

**Recommended Sizes**:
1. **Large Image** (Primary): 1200x630 px (1.91:1 ratio)
   - Facebook feed, LinkedIn, Discord embeds
   - Minimum: 600x315 px
   - Maximum: 8 MB

2. **Square Image** (Alternative): 1200x1200 px (1:1 ratio)
   - Instagram, WhatsApp sharing
   - Used when square format preferred

### Twitter Card

**Recommended Sizes**:
1. **Summary Large Image** (Primary): 1200x628 px (1.91:1 ratio)
   - Large card format with prominent image
   - Minimum: 300x157 px
   - Maximum: 5 MB

2. **Summary Card** (Alternative): 800x800 px (1:1 ratio)
   - Smaller square format
   - Used for compact displays

### Additional Formats

1. **TikTok**: 1080x1920 px (9:16 ratio - vertical)
   - Not for meta tags, but for platform uploads

2. **Instagram Stories**: 1080x1920 px (9:16 ratio)
   - Not for meta tags, but for platform uploads

## Image Variants Plan

### Variant 1: Open Graph Large (Primary)
- **Filename**: `vilagmuhely-og.jpg`
- **Dimensions**: 1200x630 px
- **Source Crop**: Crop from center of 2016x1152 to 1200x630
- **Quality**: 85%
- **Location**: `public/images/og/vilagmuhely-og.jpg`
- **Usage**: Default Open Graph image for all pages

### Variant 2: Open Graph Square
- **Filename**: `vilagmuhely-og-square.jpg`
- **Dimensions**: 1200x1200 px
- **Source Crop**: Crop from center of 2016x1152 to 1200x1200
- **Quality**: 85%
- **Location**: `public/images/og/vilagmuhely-og-square.jpg`
- **Usage**: Alternative OG image for specific contexts

### Variant 3: Twitter Card Large
- **Filename**: `vilagmuhely-twitter.jpg`
- **Dimensions**: 1200x628 px
- **Source Crop**: Crop from center of 2016x1152 to 1200x628
- **Quality**: 85%
- **Location**: `public/images/twitter/vilagmuhely-twitter.jpg`
- **Usage**: Twitter large image card

### Variant 4: Twitter Card Square
- **Filename**: `vilagmuhely-twitter-square.jpg`
- **Dimensions**: 800x800 px
- **Source Crop**: Crop from center of 2016x1152 to 800x800
- **Quality**: 85%
- **Location**: `public/images/twitter/vilagmuhely-twitter-square.jpg`
- **Usage**: Twitter summary card

### Variant 5: High Resolution Fallback
- **Filename**: `vilagmuhely-og-hires.jpg`
- **Dimensions**: 2016x1058 px (cropped to 1.91:1)
- **Source Crop**: Minimal crop to achieve 1.91:1 ratio
- **Quality**: 90%
- **Location**: `public/images/og/vilagmuhely-og-hires.jpg`
- **Usage**: High-quality fallback for platforms supporting larger images

## Image Generation Commands

### Using ImageMagick
```bash
# Create directories
mkdir -p public/images/og
mkdir -p public/images/twitter

# Variant 1: Open Graph Large (1200x630)
docker exec vilagmuhely-php-fpm-1 convert storage/app/public/vilagmuhely-title.jpg \
  -resize 1200x630^ \
  -gravity center \
  -extent 1200x630 \
  -quality 85 \
  public/images/og/vilagmuhely-og.jpg

# Variant 2: Open Graph Square (1200x1200)
docker exec vilagmuhely-php-fpm-1 convert storage/app/public/vilagmuhely-title.jpg \
  -resize 1200x1200^ \
  -gravity center \
  -extent 1200x1200 \
  -quality 85 \
  public/images/og/vilagmuhely-og-square.jpg

# Variant 3: Twitter Card Large (1200x628)
docker exec vilagmuhely-php-fpm-1 convert storage/app/public/vilagmuhely-title.jpg \
  -resize 1200x628^ \
  -gravity center \
  -extent 1200x628 \
  -quality 85 \
  public/images/twitter/vilagmuhely-twitter.jpg

# Variant 4: Twitter Card Square (800x800)
docker exec vilagmuhely-php-fpm-1 convert storage/app/public/vilagmuhely-title.jpg \
  -resize 800x800^ \
  -gravity center \
  -extent 800x800 \
  -quality 85 \
  public/images/twitter/vilagmuhely-twitter-square.jpg

# Variant 5: High Resolution (2016x1058)
docker exec vilagmuhely-php-fpm-1 convert storage/app/public/vilagmuhely-title.jpg \
  -resize 2016x1058^ \
  -gravity center \
  -extent 2016x1058 \
  -quality 90 \
  public/images/og/vilagmuhely-og-hires.jpg
```

### Using PHP GD/Intervention (Laravel Command)

Create artisan command: `php artisan make:command GenerateSocialImages`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Intervention\Image\Facades\Image;

class GenerateSocialImages extends Command
{
    protected $signature = 'social:generate-images';
    protected $description = 'Generate optimized social media sharing images';

    public function handle()
    {
        $source = storage_path('app/public/vilagmuhely-title.jpg');

        if (!file_exists($source)) {
            $this->error('Source image not found: ' . $source);
            return 1;
        }

        // Create directories
        if (!is_dir(public_path('images/og'))) {
            mkdir(public_path('images/og'), 0755, true);
        }
        if (!is_dir(public_path('images/twitter'))) {
            mkdir(public_path('images/twitter'), 0755, true);
        }

        $this->info('Generating social media images...');

        // Variant 1: Open Graph Large
        $this->generateImage($source, 1200, 630, 'images/og/vilagmuhely-og.jpg', 85);

        // Variant 2: Open Graph Square
        $this->generateImage($source, 1200, 1200, 'images/og/vilagmuhely-og-square.jpg', 85);

        // Variant 3: Twitter Card Large
        $this->generateImage($source, 1200, 628, 'images/twitter/vilagmuhely-twitter.jpg', 85);

        // Variant 4: Twitter Card Square
        $this->generateImage($source, 800, 800, 'images/twitter/vilagmuhely-twitter-square.jpg', 85);

        // Variant 5: High Resolution
        $this->generateImage($source, 2016, 1058, 'images/og/vilagmuhely-og-hires.jpg', 90);

        $this->info('All social media images generated successfully!');
        return 0;
    }

    private function generateImage(string $source, int $width, int $height, string $destination, int $quality)
    {
        $img = Image::make($source);
        $img->fit($width, $height, function ($constraint) {
            $constraint->upsize();
        });
        $img->save(public_path($destination), $quality);

        $this->line("✓ Generated: {$destination} ({$width}x{$height})");
    }
}
```

## Layout Integration

### Update `resources/views/layouts/app.blade.php`

Replace the placeholder Open Graph and Twitter image meta tags:

```php
<!-- Open Graph / Facebook -->
<meta property="og:image" content="@yield('og_image', asset('images/og/vilagmuhely-og.jpg'))">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="@yield('og_image_alt', 'Világműhely - Grow Your Crystal')">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:image" content="@yield('twitter_image', asset('images/twitter/vilagmuhely-twitter.jpg'))">
<meta name="twitter:image:alt" content="@yield('twitter_image_alt', 'Világműhely - Grow Your Crystal')">
```

### Page-Specific Image Overrides

Pages can override the default images by defining sections:

```php
{{-- Using high-res image for specific page --}}
@section('og_image', asset('images/og/vilagmuhely-og-hires.jpg'))
@section('og_image_alt', 'Custom description for this page')

{{-- Using square image for specific context --}}
@section('og_image', asset('images/og/vilagmuhely-og-square.jpg'))
```

## Privacy & Public Pages

**Important**: Social media images are used in meta tags on ALL pages, including admin/private pages. Meta tags don't expose private content - they only define what appears when someone shares the page URL.

**Security considerations**:
- Meta tags appear in HTML source but don't expose private data
- Images should be generic branding, not user-specific content
- For authenticated-only pages, sharing URL won't reveal content without login
- Admin pages return 403/redirect for non-authenticated users

**Implementation notes**:
- Use same branding images across all pages
- Don't include user-specific or sensitive information in OG images
- Images are public in `/public/images/` directory (intentional for social crawlers)

## Testing & Validation

### Testing Tools

1. **Facebook Sharing Debugger**
   - URL: https://developers.facebook.com/tools/debug/
   - Test Open Graph tags
   - Clear cache and re-scrape

2. **Twitter Card Validator**
   - URL: https://cards-dev.twitter.com/validator
   - Test Twitter Card tags
   - Preview card appearance

3. **LinkedIn Post Inspector**
   - URL: https://www.linkedin.com/post-inspector/
   - Test LinkedIn sharing
   - Clear cache

4. **Discord Embed Visualizer**
   - Share link in Discord
   - Check embed appearance
   - Verify image loading

### Validation Checklist

- [ ] Images generated in correct dimensions
- [ ] Images under size limits (OG: 8MB, Twitter: 5MB)
- [ ] Images accessible via public URL
- [ ] Meta tags correctly reference image paths
- [ ] Facebook debugger shows correct image
- [ ] Twitter validator shows correct card
- [ ] LinkedIn inspector shows correct preview
- [ ] Discord embeds display properly
- [ ] Images have appropriate alt text
- [ ] Images load quickly (under 2 seconds)

## Performance Optimization

### Image Optimization
- Use JPEG format for photographic images
- Quality 85% provides good balance of size/quality
- Consider WebP format for modern browsers (fallback to JPEG)
- Enable CDN caching for social images

### Caching Headers
Add to `.htaccess` or nginx config:
```nginx
location ~* \.(jpg|jpeg|png|gif|webp)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

## Maintenance

### When to Update Images
- Branding changes
- Logo updates
- Major product changes
- Seasonal campaigns
- Special events

### Update Process
1. Replace source image in `storage/app/public/vilagmuhely-title.jpg`
2. Run `php artisan social:generate-images` or ImageMagick commands
3. Clear CDN cache if using CDN
4. Test with Facebook debugger to force re-scrape
5. Test with Twitter validator

## Directory Structure

```
public/
├── images/
│   ├── og/
│   │   ├── vilagmuhely-og.jpg (1200x630)
│   │   ├── vilagmuhely-og-square.jpg (1200x1200)
│   │   └── vilagmuhely-og-hires.jpg (2016x1058)
│   └── twitter/
│       ├── vilagmuhely-twitter.jpg (1200x628)
│       └── vilagmuhely-twitter-square.jpg (800x800)

storage/
└── app/
    └── public/
        └── vilagmuhely-title.jpg (2016x1152 - source)
```

## Implementation Steps

1. **Install dependencies** (if using Intervention Image):
   ```bash
   docker exec vilagmuhely-php-fpm-1 composer require intervention/image
   ```

2. **Create directories**:
   ```bash
   mkdir -p public/images/og
   mkdir -p public/images/twitter
   ```

3. **Generate images** using either:
   - ImageMagick commands (faster, no Laravel dependency)
   - Artisan command (more integrated with Laravel)

4. **Update layout** with new meta tag values

5. **Test** using social media debugging tools

6. **Deploy** and verify in production

7. **Update CHANGELOG.md** with changes

## Expected Results

- Professional appearance when sharing links on social media
- Consistent branding across all platforms
- Improved click-through rates from social shares
- Better recognition of Világműhely brand
- Enhanced social media presence
