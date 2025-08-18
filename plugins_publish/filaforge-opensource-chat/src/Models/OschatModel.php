<?php

namespace Filaforge\OpensourceChat\Models;

use Illuminate\Database\Eloquent\Model;

class OschatModel extends Model
{
    protected $table = 'oschat_models';

    protected $fillable = [
        'profile_id', 'provider', 'name', 'model_id', 'description', 'meta', 'extra'
    ];

    protected $casts = [
        'meta' => 'array',
        'extra' => 'array',
    ];
}
