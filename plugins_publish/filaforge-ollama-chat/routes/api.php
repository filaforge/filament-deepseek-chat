<?php

use Illuminate\Support\Facades\Route;
use Filaforge\OllamaChat\Http\Controllers\ChatController;

Route::prefix('api/ollama-chat')->middleware(['api'])->group(function () {
    Route::post('send', [ChatController::class, 'send']);
    Route::get('models', [ChatController::class, 'models']);
});