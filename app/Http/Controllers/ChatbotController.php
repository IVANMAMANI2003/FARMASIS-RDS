<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatbotController extends Controller
{
    protected $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Endpoint API para N8N
     * Recibe una pregunta y devuelve la respuesta en formato JSON
     */
    public function ask(Request $request): JsonResponse
    {
        $request->validate([
            'question' => 'required|string|max:500',
        ]);

        $question = $request->input('question');
        $response = $this->chatbotService->processQuestion($question);

        return response()->json($response);
    }

    /**
     * Endpoint alternativo que acepta diferentes formatos de entrada
     * Compatible con webhooks de N8N
     */
    public function webhook(Request $request): JsonResponse
    {
        // Intentar obtener la pregunta de diferentes campos comunes
        $question = $request->input('question')
            ?? $request->input('message')
            ?? $request->input('text')
            ?? $request->input('query')
            ?? $request->input('body.message')
            ?? '';

        if (empty($question)) {
            return response()->json([
                'success' => false,
                'message' => 'No se proporcionó ninguna pregunta. Envía el campo "question", "message" o "text".',
                'data' => null
            ], 400);
        }

        $response = $this->chatbotService->processQuestion($question);

        return response()->json($response);
    }

    /**
     * Health check endpoint para verificar que el servicio está funcionando
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'Chatbot FARMASIS',
            'version' => '1.0.0',
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}

