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
    ];

    protected $casts = [
        'stream' => 'boolean',
        'timeout' => 'integer',
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
            ]
        );
    }

    /**
     * Get the API key for a user, falling back to config
     */
    public static function getApiKeyForUser(int $userId): ?string
    {
        $setting = static::where('user_id', $userId)->first();
        return $setting?->api_key ?: config('deepseek-chat.api_key');
    }

    /**
     * Get the base URL for a user, falling back to config
     */
    public static function getBaseUrlForUser(int $userId): string
    {
        $setting = static::where('user_id', $userId)->first();
        return $setting?->base_url ?: config('deepseek-chat.base_url', 'https://api.deepseek.com');
    }

    /**
     * Get the stream setting for a user, falling back to config
     */
    public static function getStreamForUser(int $userId): bool
    {
        $setting = static::where('user_id', $userId)->first();
        return $setting?->stream ?? config('deepseek-chat.stream', false);
    }

    /**
     * Get the timeout for a user, falling back to config
     */
    public static function getTimeoutForUser(int $userId): int
    {
        $setting = static::where('user_id', $userId)->first();
        return $setting?->timeout ?: config('deepseek-chat.timeout', 60);
    }
}
