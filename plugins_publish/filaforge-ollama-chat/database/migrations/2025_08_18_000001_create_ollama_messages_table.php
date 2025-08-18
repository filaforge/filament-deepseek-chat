<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOllamaMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('ollama_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ollama_conversations')->onDelete('cascade');
            $table->text('message');
            $table->string('sender');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ollama_messages');
    }
}