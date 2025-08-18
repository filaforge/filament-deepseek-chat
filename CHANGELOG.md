# Changelog

All notable changes to `filaforge/deepseek-chat` will be documented in this file.

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
