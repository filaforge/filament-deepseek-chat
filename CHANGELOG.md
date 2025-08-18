# Changelog

All notable changes to `filaforge/deepseek-chat` will be documented in this file.

## v0.8.3 - 2025-08-18
- **IMPROVED**: Settings now display in main section instead of modal
- **REMOVED**: Settings button from header (no longer needed)
- **NEW**: Dynamic button replacement - Set API Key becomes Settings after key is saved
- **IMPROVED**: Better user experience with inline settings management
- **FIXED**: Settings button now properly toggles settings view in main section
- **IMPROVED**: Cleaner interface with contextual button behavior

## v0.8.2 - 2025-08-18
- **NEW**: Settings modal integrated directly in chat window
- **FIXED**: Route error when clicking settings button
- **NEW**: Comprehensive settings form with all configuration options
- **IMPROVED**: Better user experience with inline settings management
- **NEW**: API configuration section (API key, base URL, stream, timeout)
- **NEW**: Access control section (allowed roles configuration)
- **IMPROVED**: Real-time form updates with wire:model
- **FIXED**: Settings button now opens modal instead of navigating to separate page

## v0.8.1 - 2025-08-18
- **IMPROVED**: Better UI and user experience
- **NEW**: Settings action button in top right header
- **IMPROVED**: Empty state text now includes hyperlink to DeepSeek platform
- **IMPROVED**: Better spacing with margin-top styling
- **IMPROVED**: Set API Key button only shows when needed
- **FIXED**: DeepseekSettingsPage now properly registered in panel
- **IMPROVED**: Cleaner interface with conditional button visibility

## v0.8.0 - 2025-08-18
- **STABLE RELEASE**: Clean, working version with all fixes applied
- **FIXED**: All version mismatches in git tags resolved
- **FIXED**: Migration conflicts completely resolved
- **FIXED**: Installation process simplified and made safe
- **IMPROVED**: Database schema cleaned up and optimized
- **IMPROVED**: All conflicting migration files removed
- **IMPROVED**: Production-ready installation process
- **BREAKING CHANGE**: Now uses separate `deepseek_settings` table instead of user columns

## v0.7.0 - 2025-08-18
- **BREAKING CHANGE**: Simplified installation process for better reliability
- **REMOVED**: Automatic migration execution during package installation
- **REMOVED**: Automatic optimization (php artisan optimize) during installation
- **IMPROVED**: Installation now requires manual migration execution
- **IMPROVED**: Better separation of concerns - installation vs. setup
- **FIXED**: Installation no longer breaks sites during composer install
- **IMPROVED**: More predictable and safer installation process

## v0.6.0 - 2025-08-18
- **NEW**: Auto-toggle Set API Key modal when API key is missing
- **NEW**: Enhanced empty state with conditional messaging based on API key status
- **NEW**: Automatic modal trigger when user tries to send message without API key
- **IMPROVED**: Better user experience with guided API key setup
- **IMPROVED**: Success notification when API key is saved
- **IMPROVED**: Automatic page refresh after API key is set
- **IMPROVED**: Helpful error messages with links to DeepSeek Console

## v0.5.0 - 2025-08-18
- **NEW**: Added `allow_roles` column to `deepseek_settings` table
- **NEW**: Implemented environment variable override system with priority: ENV > Config > Settings Table
- **NEW**: Support for `DEEPSEEK_API_KEY`, `DEEPSEEK_BASE_URL`, `DEEPSEEK_STREAM`, `DEEPSEEK_TIMEOUT`, `DEEPSEEK_ALLOW_ROLES` environment variables
- **NEW**: Enhanced role-based access control with per-user role configuration
- **IMPROVED**: Settings form now includes allow_roles field with comma-separated input
- **IMPROVED**: Better separation of configuration sources and priority handling
- **IMPROVED**: More flexible deployment options with environment variable support

## v0.4.0 - 2025-08-18
- **REFACTOR**: Replaced users table column with dedicated `deepseek_settings` table
- **NEW**: Added `DeepseekSetting` model with comprehensive settings management
- **NEW**: Enhanced settings page with all configurable options (API key, base URL, stream, timeout)
- **IMPROVED**: Better separation of concerns - settings are now in their own table
- **IMPROVED**: More flexible settings management per user
- **IMPROVED**: Cleaner database schema without modifying core users table
- **BREAKING CHANGE**: Database structure changed - requires migration to new table structure

## v0.3.0 - 2025-08-18
- **NEW**: Enhanced automatic installation with full asset publishing
- **NEW**: Automatic config file publishing to `config/deepseek-chat.php`
- **NEW**: Automatic views publishing to `resources/views/vendor/deepseek-chat/`
- **NEW**: Automatic migrations publishing to `database/migrations/`
- **NEW**: Automatic optimization with `php artisan optimize`
- **NEW**: First-time installation detection and logging
- **NEW**: Enhanced error handling and logging for all operations
- **IMPROVED**: Better user experience with zero manual commands required
- **IMPROVED**: Comprehensive installation verification and feedback

## v0.2.0 - 2025-01-XX
- **BREAKING CHANGE**: Migrations are now automatically executed during package installation
- **NEW**: Automatic asset publishing - config, views, and migrations are published automatically
- **NEW**: Automatic optimization - runs `php artisan optimize` after installation
- **NEW**: Enhanced logging for all installation operations
- No more manual migration commands required
- No more manual asset publishing required
- No more manual optimization required
- Improved installation experience for end users
- Added automatic migration detection and execution

## v0.1.0 - 2025-08-13
- Initial public release for Filament v4.
- Deepseek Chat page and settings.
# Changelog

## 1.0.0 - Initial release

- initial release
