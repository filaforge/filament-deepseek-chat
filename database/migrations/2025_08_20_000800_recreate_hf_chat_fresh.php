<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
	public function up(): void
	{
		// Add HF columns to users table
		Schema::table('users', function (Blueprint $table) {
			if (!Schema::hasColumn('users', 'hf_api_key')) {
				$table->text('hf_api_key')->nullable();
			}
		});

		// Create hf_model_profiles table first (parent table)
		if (!Schema::hasTable('hf_model_profiles')) {
			Schema::create('hf_model_profiles', function (Blueprint $table) {
				$table->id();
				$table->string('name');
				$table->string('provider')->default('huggingface');
				$table->string('model_id');
				$table->string('base_url')->nullable();
				$table->string('api_key')->nullable();
				$table->boolean('stream')->default(true);
				$table->boolean('is_active')->default(true);
				$table->unsignedInteger('timeout')->default(60);
				$table->text('system_prompt')->nullable();
				$table->json('extra')->nullable();
				$table->unsignedInteger('per_minute_limit')->nullable();
				$table->unsignedInteger('per_day_limit')->nullable();
				$table->timestamps();
			});
		}

		// Add foreign key reference after table creation
		Schema::table('users', function (Blueprint $table) {
			if (!Schema::hasColumn('users', 'hf_last_profile_id')) {
				$table->unsignedBigInteger('hf_last_profile_id')->nullable()->after('hf_api_key');
			}
		});

		Schema::table('users', function (Blueprint $table) {
			if (Schema::hasColumn('users', 'hf_last_profile_id') && !Schema::hasColumn('users', 'hf_last_profile_id')) {
				$table->foreign('hf_last_profile_id')->references('id')->on('hf_model_profiles')->nullOnDelete();
			}
		});

		// Create hf_model_profile_usages table
		if (!Schema::hasTable('hf_model_profile_usages')) {
			Schema::create('hf_model_profile_usages', function (Blueprint $table) {
				$table->id();
				$table->unsignedBigInteger('user_id');
				$table->unsignedBigInteger('model_profile_id');
				$table->timestamp('minute_at');
				$table->unsignedInteger('count')->default(0);
				$table->timestamps();
				$table->unique(['user_id','model_profile_id','minute_at'], 'hf_profile_usage_unique');
				$table->index(['model_profile_id','minute_at']);
			});
		}

		// Create hf_conversations table
		if (!Schema::hasTable('hf_conversations')) {
			Schema::create('hf_conversations', function (Blueprint $table) {
				$table->id();
				$table->foreignId('user_id')->constrained()->cascadeOnDelete();
				$table->string('title')->nullable();
				$table->string('model')->nullable();
				$table->json('messages');
				$table->timestamps();
			});
		}

		// Create hf_settings table
		if (!Schema::hasTable('hf_settings')) {
			Schema::create('hf_settings', function (Blueprint $table) {
				$table->id();
				$table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
				$table->string('model_id')->nullable();
				$table->string('base_url')->nullable();
				$table->boolean('use_openai')->default(true);
				$table->boolean('stream')->default(false);
				$table->integer('timeout')->default(60);
				$table->text('system_prompt')->nullable();
				$table->timestamps();
			});
		}

		// Seed default model profiles
		$this->seedDefaultProfiles();
	}

	private function seedDefaultProfiles(): void
	{
		// Only seed if the table exists and is empty
		if (!Schema::hasTable('hf_model_profiles') || DB::table('hf_model_profiles')->count() > 0) {
			return;
		}

		$defaultProfiles = [
			[
				'name'          => 'Cerebras – LLM Chat',
				'provider'      => 'cerebras',
				'model_id'      => 'meta-llama/Llama-3.3-70B-Instruct:cerebras',
				'base_url'      => 'https://router.huggingface.co',
				'api_key'       => null,
				'stream'        => true,
				'timeout'       => 120,
				'system_prompt' => 'You are a helpful AI assistant powered by LLaMA-3.3 via Cerebras.',
				'is_active'     => false,
			],
			[
				'name'          => 'Cohere Chat Model',
				'provider'      => 'cohere',
				'model_id'      => 'cohere/better-cohere-chat:cohere',
				'base_url'      => 'https://router.huggingface.co',
				'api_key'       => null,
				'stream'        => true,
				'timeout'       => 120,
				'system_prompt' => 'You are a helpful AI assistant powered by Cohere.',
				'is_active'     => false,
			],
			[
				'name'          => 'Featherless AI Chat',
				'provider'      => 'featherless-ai',
				'model_id'      => 'featherless-ai/chat-model:featherless-ai',
				'base_url'      => 'https://router.huggingface.co',
				'api_key'       => null,
				'stream'        => true,
				'timeout'       => 120,
				'system_prompt' => 'You are a helpful AI assistant powered by Featherless AI.',
				'is_active'     => false,
			],
			[
				'name'          => 'GPT-OSS 120B (Fireworks)',
				'provider'      => 'fireworks-ai',
				'model_id'      => 'openai/gpt-oss-120b:fireworks-ai',
				'base_url'      => 'https://router.huggingface.co',
				'api_key'       => null,
				'stream'        => true,
				'timeout'       => 120,
				'system_prompt' => 'You are a helpful AI assistant powered by GPT-OSS 120B.',
				'is_active'     => false,
			],
			[
				'name'          => 'Groq – Meta-LLaMA 3.8B Instruct',
				'provider'      => 'groq',
				'model_id'      => 'meta-llama/Meta-Llama-3-8B-Instruct:groq',
				'base_url'      => 'https://router.huggingface.co',
				'api_key'       => null,
				'stream'        => true,
				'timeout'       => 120,
				'system_prompt' => 'You are a helpful AI assistant powered by Meta-LLaMA-3-8B Instruct via Groq.',
				'is_active'     => false,
			],
			[
				'name'          => 'Hugging Face Inference API Chat',
				'provider'      => 'hf-inference',
				'model_id'      => 'meta-llama/Meta-Llama-3-8B-Instruct:hf-inference',
				'base_url'      => 'https://router.huggingface.co',
				'api_key'       => null,
				'stream'        => true,
				'timeout'       => 120,
				'system_prompt' => 'You are a helpful AI assistant powered by Meta-LLaMA-3-8B Instruct via HF Inference.',
				'is_active'     => false,
			],
			[
				'name'          => 'Hyperbolic Chat Model',
				'provider'      => 'hyperbolic',
				'model_id'      => 'hyperbolic/chat-model:hyperbolic',
				'base_url'      => 'https://router.huggingface.co',
				'api_key'       => null,
				'stream'        => true,
				'timeout'       => 120,
				'system_prompt' => 'You are a helpful AI assistant powered by Hyperbolic.',
				'is_active'     => false,
			],
			[
				'name'          => 'Nebius Chat Model',
				'provider'      => 'nebius',
				'model_id'      => 'nebius/chat-model:nebius',
				'base_url'      => 'https://router.huggingface.co',
				'api_key'       => null,
				'stream'        => true,
				'timeout'       => 120,
				'system_prompt' => 'You are a helpful AI assistant powered by Nebius.',
				'is_active'     => false,
			],
			[
				'name'          => 'Novita Chat Model',
				'provider'      => 'novita',
				'model_id'      => 'novita/chat-model:novita',
				'base_url'      => 'https://router.huggingface.co',
				'api_key'       => null,
				'stream'        => true,
				'timeout'       => 120,
				'system_prompt' => 'You are a helpful AI assistant powered by Novita.',
				'is_active'     => false,
			],
			[
				'name'          => 'Nscale Chat Model',
				'provider'      => 'nscale',
				'model_id'      => 'nscale/chat-model:nscale',
				'base_url'      => 'https://router.huggingface.co',
				'api_key'       => null,
				'stream'        => true,
				'timeout'       => 120,
				'system_prompt' => 'You are a helpful AI assistant powered by Nscale.',
				'is_active'     => false,
			],
			[
				'name'          => 'SambaNova Chat Model',
				'provider'      => 'sambanova',
				'model_id'      => 'sambanova/chat-model:sambanova',
				'base_url'      => 'https://router.huggingface.co',
				'api_key'       => null,
				'stream'        => true,
				'timeout'       => 120,
				'system_prompt' => 'You are a helpful AI assistant powered by SambaNova.',
				'is_active'     => false,
			],
			[
				'name'          => 'Together AI Chat Model',
				'provider'      => 'together',
				'model_id'      => 'together-ai/chat-model:together',
				'base_url'      => 'https://router.huggingface.co',
				'api_key'       => null,
				'stream'        => true,
				'timeout'       => 120,
				'system_prompt' => 'You are a helpful AI assistant powered by Together AI.',
				'is_active'     => false,
			],
		];

		foreach ($defaultProfiles as $profile) {
			DB::table('hf_model_profiles')->insert($profile);
		}
	}

	public function down(): void
	{
		// Drop tables in reverse order
		$tables = [
			'hf_model_profile_usages',
			'hf_conversations',
			'hf_settings',
			'hf_model_profiles',
		];

		foreach ($tables as $tableName) {
			if (Schema::hasTable($tableName)) {
				Schema::dropIfExists($tableName);
			}
		}

		// Remove user columns
		Schema::table('users', function (Blueprint $table) {
			if (Schema::hasColumn('users', 'hf_last_profile_id')) {
				try {
					$table->dropConstrainedForeignId('hf_last_profile_id');
				} catch (\Throwable $e) {
					try { $table->dropForeign(['hf_last_profile_id']); } catch (\Throwable $e2) {}
					try { $table->dropColumn('hf_last_profile_id'); } catch (\Throwable $e3) {}
				}
			}
			if (Schema::hasColumn('users', 'hf_api_key')) {
				$table->dropColumn('hf_api_key');
			}
		});
	}
};
