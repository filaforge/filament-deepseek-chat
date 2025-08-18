<?php

namespace Filaforge\OpensourceChat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $table = 'oschat_settings';

    protected $fillable = [
        'user_id',
        'model_id',
        'base_url',
        'stream',
        'timeout',
        'system_prompt',
    ];

    protected $casts = [
        'stream' => 'boolean',
        'timeout' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
