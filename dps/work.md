# Work items

## 2025-12-26

### SEO Social Media Images - COMPLETED ✓

Created comprehensive guide and implementation:
- Created `dps/features/social-media-images.md` with detailed specifications
- Planned 5 image variants for different platforms (OG, Twitter, various sizes)
- Created `app/Console/Commands/GenerateSocialImages.php` command using GD library
- Updated `resources/views/layouts/app.blade.php` with proper meta tags:
  - Open Graph image: `images/og/vilagmuhely-og.jpg` (1200x630)
  - Twitter Card image: `images/twitter/vilagmuhely-twitter.jpg` (1200x628)
  - Added width, height, and alt attributes for better SEO

**Next steps**:
- Run `php artisan social:generate-images` when Docker is available to create image variants
- Test social sharing on Facebook, Twitter, LinkedIn using debugging tools

### Database Fix - user_follows Table

**Issue**: Production error - missing `updated_at` column in `user_follows` table
- Error occurred in `NotifyFollowersOfNewContent` listener when processing follower notifications
- BelongsToMany relationship used `->withTimestamps()` but table only had `created_at`

**Fix**: Created migration `2025_12_27_000001_add_updated_at_to_user_follows_table.php`
- Adds `updated_at` timestamp column to `user_follows` table
- Run migration in production: `php artisan migrate`

