<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'oschat_api_key')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('oschat_api_key');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'oschat_api_key')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('oschat_api_key')->nullable()->after('remember_token');
            });
        }
    }
};
