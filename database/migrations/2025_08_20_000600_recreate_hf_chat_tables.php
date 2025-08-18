<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		// users: add hf_api_key & hf_last_profile_id
		Schema::table('users', function (Blueprint $table) {
			if (!Schema::hasColumn('users', 'hf_api_key')) {
				$table->text('hf_api_key')->nullable();
			}
		});
		Schema::table('users', function (Blueprint $table) {
			if (!Schema::hasColumn('users', 'hf_last_profile_id')) {
				$table->unsignedBigInteger('hf_last_profile_id')->nullable()->after('hf_api_key');
				$table->foreign('hf_last_profile_id')->references('id')->on('hf_model_profiles')->nullOnDelete();
			}
		});

		// hf_model_profiles
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

		// hf_model_profile_usages
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

		// hf_conversations
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

		// hf_settings
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
	}

	public function down(): void
	{
		// No rollback; use uninstall migration instead
	}
};



