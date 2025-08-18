<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'oschat_api_key')) {
                    $table->string('oschat_api_key')->nullable()->after('remember_token');
                }
                if (! Schema::hasColumn('users', 'oschat_last_profile_id')) {
                    $table->unsignedBigInteger('oschat_last_profile_id')->nullable()->after('oschat_api_key');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'oschat_api_key')) {
                    $table->dropColumn('oschat_api_key');
                }
                if (Schema::hasColumn('users', 'oschat_last_profile_id')) {
                    $table->dropColumn('oschat_last_profile_id');
                }
            });
        }
    }
};
