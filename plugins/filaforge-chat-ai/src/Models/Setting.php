<?php

namespace Filaforge\ChatAi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
	protected $table = 'chat_ai_settings';

	protected $fillable = [
		'user_id',
		'model_id',
		'base_url',
		'use_openai',
		'stream',
		'timeout',
		'system_prompt',
		'api_key',
		'last_profile_id',
	];

	protected $casts = [
		'use_openai' => 'boolean',
		'stream' => 'boolean',
		'timeout' => 'integer',
		'last_profile_id' => 'integer',
	];

	public function user(): BelongsTo
	{
		return $this->belongsTo(\App\Models\User::class);
	}
}


















