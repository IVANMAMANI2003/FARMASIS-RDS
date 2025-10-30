<div style="position: fixed !important; bottom: 24px !important; right: 24px !important; z-index: 99999 !important;">
    <!-- Botón flotante del chatbot -->
    @if(!$isOpen)
        <button
            wire:click="toggle"
            type="button"
            style="background-color: #2563eb !important; color: white !important; border-radius: 50% !important; width: 64px !important; height: 64px !important; box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.6), 0 4px 10px -2px rgba(37, 99, 235, 0.4) !important; display: flex !important; align-items: center !important; justify-content: center !important; position: relative !important; cursor: pointer !important; border: 3px solid white !important; padding: 0 !important; transition: all 0.3s !important;"
            aria-label="Abrir chatbot"
            onmouseover="this.style.backgroundColor='#1d4ed8'; this.style.transform='scale(1.1)'; this.style.boxShadow='0 15px 35px -5px rgba(37, 99, 235, 0.8)'"
            onmouseout="this.style.backgroundColor='#2563eb'; this.style.transform='scale(1)'; this.style.boxShadow='0 10px 25px -5px rgba(37, 99, 235, 0.6), 0 4px 10px -2px rgba(37, 99, 235, 0.4)'"
        >
            <!-- Icono de chat -->
            <svg style="width: 28px !important; height: 28px !important; fill: none; stroke: white; stroke-width: 2;" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <!-- Indicador de notificación (opcional) -->
            <span style="position: absolute !important; top: 4px !important; right: 4px !important; width: 12px !important; height: 12px !important; background-color: #10b981 !important; border-radius: 50% !important; border: 2px solid white !important; display: block !important;"></span>
        </button>
    @endif

    <!-- Panel del chatbot -->
    @if($isOpen)
        <div style="background-color: white; border-radius: 8px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); width: 380px; height: 600px; display: flex; flex-direction: column; border: 1px solid #e5e7eb;">
            <!-- Header del chat -->
            <div style="background-color: #4f46e5; color: white; padding: 12px 16px; border-radius: 8px 8px 0 0; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 32px; height: 32px; background-color: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 style="font-weight: 600; font-size: 14px; margin: 0;">Asistente Virtual</h3>
                        <p style="font-size: 12px; color: rgba(255,255,255,0.8); margin: 0;">En línea</p>
                    </div>
                </div>
                <button
                    wire:click="close"
                    type="button"
                    style="color: white; background: rgba(255,255,255,0.2); border-radius: 50%; padding: 4px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                    aria-label="Cerrar chatbot"
                >
                    <svg style="width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 2;" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Mensajes -->
            <div
                id="chatbot-messages"
                style="flex: 1; overflow-y: auto; padding: 16px; background-color: #f9fafb; display: flex; flex-direction: column; gap: 12px;"
            >
            @foreach($messages as $index => $message)
                <div style="display: flex; justify-content: {{ $message['type'] === 'user' ? 'flex-end' : 'flex-start' }};">
                    <div style="max-width: 80%; border-radius: 8px; padding: 8px 12px; {{ $message['type'] === 'user' ? 'background-color: #4f46e5; color: white;' : 'background-color: white; color: #1f2937; border: 1px solid #e5e7eb;' }}">
                        <p style="font-size: 14px; margin: 0勻; white-space: pre-line;">{{ $message['content'] }}</p>
                        <span style="font-size: 12px; margin-top: 4px; display: block; opacity: 0.7;">{{ $message['timestamp'] }}</span>
                    </div>
                </div>
            @endforeach

            @if($isLoading)
                <div style="display: flex; justify-content: flex-start;">
                    <div style="background-color: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 8px 12px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="display: flex; gap: 4px;">
                                <div style="width: 8px; height: 8px; background-color: #4f46e5; border-radius: 50%; animation: bounce 1s infinite;" style="animation-delay: 0ms"></div>
                                <div style="width: 8px; height: 8px; background-color: #4f46e5; border-radius: 50%; animation: bounce 1s infinite;" style="animation-delay: 150ms"></div>
                                <div style="width: 8px; height: 8px; background-color: #4f46e5; border-radius: 50%; animation: bounce 1s infinite;" style="animation-delay: 300ms"></div>
                            </div>
                            <span style="font-size: 12px; color: #6b7280;">Pensando...</span>
                        </div>
                    </div>
                </div>
            @endif
            </div>

            <!-- Input area -->
            <div style="border-top: 1px solid #e5e7eb; padding: 12px; background-color: white;">
                <form wire:submit.prevent="sendMessage" style="display: flex; gap: 8px;">
                    <input
                        type="text"
                        wire:model="question"
                        placeholder="Escribe tu pregunta..."
                        style="flex: 1; padding: 8px 12px; font-size: 14px; border: 1px solid #d1d5db; border-radius: 8px; outline: none;"
                        @if($isLoading) disabled @endif
                    />
                    <button
                        type="submit"
                        @if($isLoading || empty(trim($question))) disabled @endif
                        style="background-color: #4f46e5; color: white; border-radius: 8px; padding: 8px 16px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                    >
                        <svg style="width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 2;" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>

@script
<script>
    // Auto-scroll al final del chat cuando hay nuevos mensajes
    $wire.on('scroll-to-bottom', () => {
        setTimeout(() => {
            const chatMessages = document.getElementById('chatbot-messages');
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }, 100);
    });

    // Cerrar con ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && @js($isOpen)) {
            @this.close();
        }
    });
</script>
@endscript
