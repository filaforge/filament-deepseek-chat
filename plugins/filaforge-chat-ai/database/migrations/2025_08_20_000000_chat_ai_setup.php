<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // chat_ai_model_profiles
        Schema::create('chat_ai_model_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider')->default('huggingface');
            $table->string('model_id');
            $table->string('base_url')->nullable();
            $table->string('api_key')->nullable();
            $table->boolean('stream')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('timeout')->default(120);
            $table->text('system_prompt')->nullable();
            $table->json('extra')->nullable();
            $table->unsignedInteger('per_minute_limit')->nullable();
            $table->unsignedInteger('per_day_limit')->nullable();
            $table->timestamps();
        });

        // chat_ai_model_profile_usages
        Schema::create('chat_ai_model_profile_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('model_profile_id');
            $table->timestamp('minute_at');
            $table->unsignedInteger('count')->default(0);
            $table->timestamps();

            $table->unique(['user_id','model_profile_id','minute_at'], 'chat_ai_profile_usage_unique');
            $table->index(['model_profile_id','minute_at']);
        });

        // chat_ai_conversations
        Schema::create('chat_ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('model')->nullable();
            $table->json('messages');
            $table->timestamps();
        });

        // chat_ai_settings
        Schema::create('chat_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('model_id')->nullable();
            $table->string('base_url')->nullable();
            $table->boolean('use_openai')->default(true);
            $table->boolean('stream')->default(true);
            $table->integer('timeout')->default(60);
            $table->text('system_prompt')->nullable();
            $table->text('api_key')->nullable();
            $table->foreignId('last_profile_id')->nullable()->constrained('chat_ai_model_profiles')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_ai_settings');
        Schema::dropIfExists('chat_ai_conversations');
        Schema::dropIfExists('chat_ai_model_profile_usages');
        Schema::dropIfExists('chat_ai_model_profiles');
    }
};
