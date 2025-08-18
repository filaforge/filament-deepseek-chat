<?php

namespace Filaforge\DeepseekChat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeepseekSetting extends Model
{
    protected $table = 'deepseek_settings';
    
    protected $fillable = [
        'user_id',
        'api_key',
        'base_url',
        'stream',
        'timeout',
        'allow_roles',
    ];

    protected $casts = [
        'stream' => 'boolean',
        'timeout' => 'integer',
        'allow_roles' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get or create settings for a user
     */
    public static function forUser(int $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'base_url' => config('deepseek-chat.base_url', 'https://api.deepseek.com'),
                'stream' => config('deepseek-chat.stream', false),
                'timeout' => config('deepseek-chat.timeout', 60),
                'allow_roles' => config('deepseek-chat.allow_roles', []),
            ]
        );
    }

    /**
     * Get the API key for a user, falling back to config/env
     */
    public static function getApiKeyForUser(int $userId): ?string
    {
        // Check environment variable first (highest priority)
        if (env('DEEPSEEK_API_KEY')) {
            return env('DEEPSEEK_API_KEY');
        }
        
        // Then check config
        if (config('deepseek-chat.api_key')) {
            return config('deepseek-chat.api_key');
        }
        
        // Finally check user settings
        $setting = static::where('user_id', $userId)->first();
        return $setting?->api_key;
    }

    /**
     * Get the base URL for a user, falling back to config/env
     */
    public static function getBaseUrlForUser(int $userId): string
    {
        // Check environment variable first (highest priority)
        if (env('DEEPSEEK_BASE_URL')) {
            return env('DEEPSEEK_BASE_URL');
        }
        
        // Then check config
        if (config('deepseek-chat.base_url')) {
            return config('deepseek-chat.base_url');
        }
        
        // Finally check user settings
        $setting = static::where('user_id', $userId)->first();
        return $setting?->base_url ?: 'https://api.deepseek.com';
    }

    /**
     * Get the stream setting for a user, falling back to config/env
     */
    public static function getStreamForUser(int $userId): bool
    {
        // Check environment variable first (highest priority)
        if (env('DEEPSEEK_STREAM') !== null) {
            return filter_var(env('DEEPSEEK_STREAM'), FILTER_VALIDATE_BOOLEAN);
        }
        
        // Then check config
        if (config('deepseek-chat.stream') !== null) {
            return config('deepseek-chat.stream');
        }
        
        // Finally check user settings
        $setting = static::where('user_id', $userId)->first();
        return $setting?->stream ?? false;
    }

    /**
     * Get the timeout for a user, falling back to config/env
     */
    public static function getTimeoutForUser(int $userId): int
    {
        // Check environment variable first (highest priority)
        if (env('DEEPSEEK_TIMEOUT')) {
            return (int) env('DEEPSEEK_TIMEOUT');
        }
        
        // Then check config
        if (config('deepseek-chat.timeout')) {
            return (int) config('deepseek-chat.timeout');
        }
        
        // Finally check user settings
        $setting = static::where('user_id', $userId)->first();
        return $setting?->timeout ?: 60;
    }

    /**
     * Get the allow_roles for a user, falling back to config/env
     */
    public static function getAllowRolesForUser(int $userId): array
    {
        // Check environment variable first (highest priority)
        if (env('DEEPSEEK_ALLOW_ROLES')) {
            $roles = explode(',', env('DEEPSEEK_ALLOW_ROLES'));
            return array_map('trim', $roles);
        }
        
        // Then check config
        if (config('deepseek-chat.allow_roles')) {
            return (array) config('deepseek-chat.allow_roles');
        }
        
        // Finally check user settings
        $setting = static::where('user_id', $userId)->first();
        return $setting?->allow_roles ?: [];
    }

    /**
     * Check if a user has access based on roles
     */
    public static function userHasAccess(int $userId): bool
    {
        $allowRoles = static::getAllowRolesForUser($userId);
        
        // If no roles specified, allow all authenticated users
        if (empty($allowRoles)) {
            return true;
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return false;
        }

        // Check if user has any of the allowed roles
        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole($allowRoles);
        }

        // Fallback to role property
        $role = data_get($user, 'role');
        return $role ? in_array($role, $allowRoles, true) : false;
    }
}
