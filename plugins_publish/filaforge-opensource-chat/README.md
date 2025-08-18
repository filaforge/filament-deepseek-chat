# Filaforge Open Source Chat

FilamentPHP plugin providing an open-source chat interface with:

- Model Profiles (multiple model endpoints / providers)
- User Settings (per-user chat preferences)
- Conversations (stored messages)

## Installation

```
composer require filaforge/opensource-chat
php artisan vendor:publish --tag="opensource-chat-migrations"
php artisan migrate
```

(If config desired)
```
php artisan vendor:publish --tag="opensource-chat-config"
```

## Pages

Registers Filament pages:
- OpenSourceChatPage (main chat)
- OpenSourceSettingsPage (user settings)

## Migrations

Creates tables:
- oschat_conversations
- oschat_model_profiles
- oschat_settings
- adds oschat_last_profile_id to users

## TODO
- Implement Livewire chat logic & streaming
- Add Filament form/table components to manage profiles & conversations

PRs welcome.
