<?php

namespace Filaforge\OpensourceChat\Models;

use Illuminate\Database\Eloquent\Model;

class ModelProfile extends Model
{
    protected $table = 'oschat_model_profiles';

    protected $fillable = [
        'name','provider','model_id','base_url','api_key','stream','timeout','system_prompt','extra','per_minute_limit','per_day_limit'
    ];

    protected $casts = [
        'stream' => 'boolean',
        'extra' => 'array',
        'per_minute_limit' => 'integer',
        'per_day_limit' => 'integer',
    ];
}
