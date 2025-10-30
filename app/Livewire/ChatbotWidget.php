<?php

namespace App\Livewire;

use App\Services\ChatbotService;
use Livewire\Component;

class ChatbotWidget extends Component
{
    public $isOpen = false;
    public $messages = [];
    public $question = '';
    public $isLoading = false;

    protected $chatbotService;

    public function boot(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    public function mount()
    {
        // Mensaje de bienvenida
        $this->messages[] = [
            'type' => 'bot',
            'content' => '¡Hola! Soy el asistente virtual de FARMASIS. ¿En qué puedo ayudarte? Puedo consultar productos, stock, ventas, categorías y más.',
            'timestamp' => now()->format('H:i')
        ];
    }

    public function toggle()
    {
        $this->isOpen = !$this->isOpen;

        if ($this->isOpen) {
            $this->dispatch('scroll-to-bottom');
        }
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function sendMessage()
    {
        if (empty(trim($this->question))) {
            return;
        }

        // Agregar mensaje del usuario
        $this->messages[] = [
            'type' => 'user',
            'content' => $this->question,
            'timestamp' => now()->format('H:i')
        ];

        $userQuestion = $this->question;
        $this->question = '';
        $this->isLoading = true;

        try {
            // Procesar la pregunta
            $response = $this->chatbotService->processQuestion($userQuestion);

            // Construir respuesta del bot
            $botMessage = $response['message'];

            if ($response['success'] && !empty($response['data'])) {
                $data = $response['data'];

                if (is_array($data) && isset($data[0]) && is_array($data[0])) {
                    // Es una lista de items
                    $botMessage .= "\n\n";
                    foreach ($data as $index => $item) {
                        $botMessage .= ($index + 1) . ". ";
                        foreach ($item as $key => $value) {
                            $keyFormatted = ucfirst(str_replace('_', ' ', $key));
                            $botMessage .= "{$keyFormatted}: {$value} | ";
                        }
                        $botMessage = rtrim($botMessage, ' | ') . "\n";
                    }
                } elseif (is_array($data) && !isset($data[0])) {
                    // Es un objeto único
                    $botMessage .= "\n\n";
                    foreach ($data as $key => $value) {
                        $keyFormatted = ucfirst(str_replace('_', ' ', $key));
                        if (is_array($value)) {
                            $botMessage .= "{$keyFormatted}:\n";
                            foreach ($value as $item) {
                                $botMessage .= "  - {$item}\n";
                            }
                        } else {
                            $botMessage .= "{$keyFormatted}: {$value}\n";
                        }
                    }
                }
            }

            $this->messages[] = [
                'type' => 'bot',
                'content' => $botMessage,
                'timestamp' => now()->format('H:i'),
                'success' => $response['success']
            ];
        } catch (\Exception $e) {
            $this->messages[] = [
                'type' => 'bot',
                'content' => 'Lo siento, ocurrió un error al procesar tu consulta. Por favor, intenta de nuevo.',
                'timestamp' => now()->format('H:i'),
                'success' => false
            ];
        } finally {
            $this->isLoading = false;
        }

        // Scroll al final del chat
        $this->dispatch('scroll-to-bottom');
    }

    public function render()
    {
        return view('livewire.chatbot-widget');
    }
}

