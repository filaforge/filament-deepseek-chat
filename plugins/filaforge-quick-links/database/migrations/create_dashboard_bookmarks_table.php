<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('dashboard_bookmarks')) {
            Schema::create('dashboard_bookmarks', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('label');
                $table->string('url');
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_bookmarks');
    }
};


