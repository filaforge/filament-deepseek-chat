<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deepseek_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('api_key')->nullable();
            $table->string('base_url')->default('https://api.deepseek.com');
            $table->boolean('stream')->default(false);
            $table->integer('timeout')->default(60);
            $table->json('allow_roles')->nullable();
            $table->timestamps();

            // Ensure one settings record per user
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deepseek_settings');
    }
};
