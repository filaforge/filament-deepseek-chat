## v0.1.0 - 2025-08-19

- Initial public release of HuggingFace Chat for Filament v4
- Pages: Chat, Conversations, Settings
- Models: Conversation, ModelProfile, ModelProfileUsage, Setting
- Config: `config/hf-chat.php` with API token, defaults, and options
- Assets: CSS/JS for chat UI; translations and views published
- Migrations for conversations, settings, model profiles and usages
- Panel plugin and service provider registration

# Changelog

All notable changes to the Filaforge HuggingFace Chat plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Model profiles management system
- Support for multiple AI providers (HuggingFace, Ollama, DeepSeek)
- Conversation history and management
- User-specific settings and API keys
- Streaming response support
- Rate limiting and usage tracking

### Fixed
- Settings page form rendering issues
- Form validation and error handling
- API key format validation
- Database migration compatibility

### Changed
- Improved UI/UX with better form layouts
- Enhanced error messages and notifications
- Better code organization and type safety

## [1.0.0] - Initial Release

### Added
- Basic HuggingFace chat integration
- Simple settings management
- Conversation storage
- Filament panel integration

