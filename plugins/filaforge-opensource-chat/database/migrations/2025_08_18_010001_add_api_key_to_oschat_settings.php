<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('oschat_settings')) {
            Schema::table('oschat_settings', function (Blueprint $table) {
                if (! Schema::hasColumn('oschat_settings', 'api_key')) {
                    $table->string('api_key')->nullable()->after('base_url');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('oschat_settings')) {
            Schema::table('oschat_settings', function (Blueprint $table) {
                if (Schema::hasColumn('oschat_settings', 'api_key')) {
                    $table->dropColumn('api_key');
                }
            });
        }
    }
};
