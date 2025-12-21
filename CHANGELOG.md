# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.8] - 2025-12-21
### Added
- Latest Blog Posts section on home page displaying 4 most recent blog posts
- Blog posts grid layout with featured images and excerpts
- "View All Blog Posts" link to full blog page

## [1.0.7] - 2025-12-21
### Added
- Tailwind CSS Typography plugin for proper blog content formatting

### Fixed
- Blog post content now displays with correct formatting (headings, paragraphs, lists, code blocks)
- Typography styles properly applied to blog post content

## [1.0.6] - 2025-12-21
### Fixed
- Blog post content styling with proper prose classes for dark theme
- Character encoding issues in blog views (Unicode escape sequences)
- Meta description generation now properly strips HTML and limits to exactly 160 characters

### Changed
- Renamed "Posts" to "Blog Posts" in Filament admin
- Moved Blog Posts resource under Content Engine navigation group
- Added Blog link to welcome page navigation

## [1.0.5] - 2025-12-21
### Added
- Blog management system with Filament admin resource
- Intelligent slug generation from post titles
- Automated SEO metadata generation
- Public blog pages with pagination
- Post status management (draft, published, archived)
- RichEditor for blog content creation
- Featured image support for blog posts
- Related posts functionality

### Changed
- Added blog navigation link to main layout

## [1.0.4] - 2025-12-21
### Added
- User anonymization on all public pages for privacy protection

### Changed
- Anonymized user names and avatars on public pages (Featured Content, Top Crystals, Crystal Gallery, Individual Crystal pages)

## [1.0.3] - 2025-12-21

### Added
- Dynamic sitemap generation with 12-hour caching
- Sitemapable interface implementation on User
- sitemap artisan command for manual sitemap generation

### Changed
- Optimized sitemap generation
- Added performance caching to sitemap route

## [1.0.2] - 2025-12-21

### Added
- Comprehensive SEO meta tags including description, keywords, and author
- Open Graph tags for enhanced social media sharing on Facebook
- Twitter Card tags for better Twitter previews
- Canonical URLs for all pages
- Theme color and mobile app meta tags
- JSON-LD structured data for Organization and WebSite schemas
- Robots.txt configuration
- Spatie Laravel Sitemap package integration

### Changed
- Enhanced all view templates with SEO meta sections

## [1.0.1] - 2025-12-21

### Added
- Featured Content section on home page showcasing latest library items
- Google Analytics tracking across all pages
- SEO optimization plan documentation

### Fixed
- Fixed TypeError in footer views where version configuration was being accessed incorrectly
- Public Change Log page with matching home page design
- Project memory documentation

### Changed
- Moved application version configuration to dedicated file
- Updated version references across application to use configuration


## [1.0.0] - 2025-12-21

### Added
- Initial release of Világműhely
- Crystal visualization system
- Content management
- User authentication and registration
- Crystal metrics calculation (facets, glow, colors, geometry)
- Automatic crystal updates every 30 minutes
- Crystal Gallery
- Content Library
- Invitation system
