<?php

namespace Filaforge\OllamaChat\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'ollama_settings';

    protected $fillable = [
        'key',
        'value',
    ];
}