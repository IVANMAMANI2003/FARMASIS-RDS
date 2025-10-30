<?php

use App\Http\Controllers\ChatbotController;
use Illuminate\Support\Facades\Route;

// Rutas del chatbot para N8N
Route::prefix('chatbot')->group(function () {
    // Endpoint principal para consultas
    Route::post('/ask', [ChatbotController::class, 'ask'])->name('chatbot.ask');

    // Webhook compatible con N8N (acepta múltiples formatos)
    Route::post('/webhook', [ChatbotController::class, 'webhook'])->name('chatbot.webhook');

    // Health check
    Route::get('/health', [ChatbotController::class, 'health'])->name('chatbot.health');
});

