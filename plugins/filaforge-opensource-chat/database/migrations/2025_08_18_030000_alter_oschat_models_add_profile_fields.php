<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('oschat_models', function (Blueprint $table) {
            if (! Schema::hasColumn('oschat_models', 'base_url')) {
                $table->string('base_url')->nullable()->after('model_id');
            }
            if (! Schema::hasColumn('oschat_models', 'api_key')) {
                $table->text('api_key')->nullable()->after('base_url');
            }
            if (! Schema::hasColumn('oschat_models', 'stream')) {
                $table->boolean('stream')->default(true)->after('api_key');
            }
            if (! Schema::hasColumn('oschat_models', 'timeout')) {
                $table->integer('timeout')->default(60)->after('stream');
            }
            if (! Schema::hasColumn('oschat_models', 'system_prompt')) {
                $table->text('system_prompt')->nullable()->after('timeout');
            }
            if (! Schema::hasColumn('oschat_models', 'per_minute_limit')) {
                $table->integer('per_minute_limit')->nullable()->after('extra');
            }
            if (! Schema::hasColumn('oschat_models', 'per_day_limit')) {
                $table->integer('per_day_limit')->nullable()->after('per_minute_limit');
            }
            if (! Schema::hasColumn('oschat_models', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('per_day_limit');
            }
        });
    }

    public function down()
    {
        Schema::table('oschat_models', function (Blueprint $table) {
            foreach (['base_url','api_key','stream','timeout','system_prompt','per_minute_limit','per_day_limit','is_active'] as $col) {
                if (Schema::hasColumn('oschat_models', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
