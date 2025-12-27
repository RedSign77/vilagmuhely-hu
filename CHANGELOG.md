# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased] - 2025-12-27
### Added
- Social media sharing image optimization system with platform-specific variants
- Artisan command for generating optimized social media images
- Enhanced Open Graph and Twitter Card meta tags with image dimensions and alt text
- Comprehensive social media images guide documentation
### Fixed
- Added missing updated_at column to user_follows table to fix follower notifications

## [1.1.4] - 2025-12-25
### Changed
- Select Roles field in scheduled emails now connected to Roles resource instead of hardcoded options
- Data Source option changed from "Orders" to "Invited Users"
- Added invitation status filtering for invited users (pending, accepted, expired, cancelled)

## [1.1.1 - 1.1.3] - 2025-12-25
### Changed
- Email templates now use Laravel mail layout with Cards Forge custom theme
- Email Templates and Scheduled Emails moved to System Settings navigation group
- Preview modal now displays emails with actual site header and footer styling
- Renamed "Run Now" to "Force Run" action with enhanced feedback showing sent/skipped counts
- Force Run action now explicitly shows scheduled time being bypassed in confirmation modal

### Added
- Custom Email Template System for managing email templates with Markdown support
- EmailTemplate model and database table for storing reusable email templates
- EmailTemplateResource in Filament admin panel (supervisor-only access)
- Markdown editor with live preview functionality for email content
- Variable injection system supporting {{ variable }} placeholders in subject and body
- TemplateEmail mailable class for sending template-based emails with custom HTML layout
- Preview modal for templates showing rendered content
- Available variables cheat sheet in template form
- Custom HTML email template with responsive design and inline CSS
- Feature documentation in dps/features/custom-email-templates.md
- Advanced Scheduled Email Dispatcher for automated email campaigns
- ScheduledEmail model with cron expression scheduling and multi-source data support
- EmailDispatchLog model for deduplication tracking
- Cron expression validation with human-friendly display
- Dynamic recipient targeting (all users, specific roles, individual selection)

## [1.1.0] - 2025-12-22
### Added
- The Forge user profile system with username-based URLs, RPG-style stats, portfolio sections, and activity feed
### Changed
- User profiles display anonymized names for privacy
### Fixed
- Crystal canvas sizing and BelongsToMany relationship issues on Forge profiles

## [1.0.9] - 2025-12-22
### Added
- Terms and Conditions and Privacy Policy acceptance on registration with footer links
### Changed
- Blog Posts menu restricted to supervisor role

## [1.0.5 - 1.0.8] - 2025-12-21
### Added
- Blog management system with Filament admin, SEO metadata, pagination, and home page section
- Tailwind CSS Typography plugin
### Fixed
- Blog post styling and character encoding issues

## [1.0.4] - 2025-12-21
### Added
- User anonymization on all public pages

## [1.0.2 - 1.0.3] - 2025-12-21
### Added
- Comprehensive SEO meta tags (Open Graph, Twitter Cards, JSON-LD)
- Dynamic sitemap generation with caching
- Google Analytics tracking

## [1.0.1] - 2025-12-21
### Added
- Featured Content section on home page
### Fixed
- Version configuration TypeError in footer views

## [1.0.0] - 2025-12-21
### Added
- Initial release with crystal visualization, content management, authentication, and invitation system
