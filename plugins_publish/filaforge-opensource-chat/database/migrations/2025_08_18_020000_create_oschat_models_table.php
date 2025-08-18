<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('oschat_models', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('profile_id')->nullable()->index();
            $table->string('provider')->nullable();
            $table->string('name')->nullable();
            $table->string('model_id')->nullable()->index();
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('oschat_models');
    }
};
