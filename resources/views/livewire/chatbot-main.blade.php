<div>
    <div class="mx-6 mb-4">
        <h2 class="text-3xl font-bold text-gray-800 dark:text-white">Chatbot FARMASIS</h2>
        <div class="border-b-2 border-indigo-600 w-60 mt-2"></div>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl border-1 sm:rounded-lg dark:bg-gray-800/50 dark:bg-gradient-to-bl">
            <!-- Área de chat -->
            <div class="h-[600px] flex flex-col">
                <!-- Header del chat -->
                <div class="bg-indigo-600 text-white px-6 py-4 rounded-t-lg">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-lg">Asistente Virtual</h3>
                            <p class="text-sm text-indigo-100">Sistema de Farmacia - En línea</p>
                        </div>
                    </div>
                </div>

                <!-- Mensajes -->
                <div id="chat-messages" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50 dark:bg-gray-900/50">
                    @foreach($messages as $index => $message)
                        <div class="flex {{ $message['type'] === 'user' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[80%] rounded-lg px-4 py-2 {{ $message['type'] === 'user' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-white border border-gray-200 dark:border-gray-700' }}">
                                <div class="flex items-start space-x-2">
                                    @if($message['type'] === 'bot')
                                        <svg class="w-5 h-5 mt-0.5 text-indigo-600 dark:text-indigo-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                    <div class="flex-1">
                                        <p class="text-sm whitespace-pre-line">{{ $message['content'] }}</p>
                                        <span class="text-xs mt-1 block opacity-70">{{ $message['timestamp'] }}</span>
                                    </div>
                                    @if($message['type'] === 'user')
                                        <svg class="w-5 h-5 mt-0.5 text-white flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if($isLoading)
                        <div class="flex justify-start">
                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-2">
                                <div class="flex items-center space-x-2">
                                    <div class="flex space-x-1">
                                        <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                        <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                        <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                                    </div>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Pensando...</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Input area -->
                <div class="border-t border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-800">
                    <form wire:submit.prevent="sendMessage" class="flex space-x-3">
                        <div class="flex-1">
                            <x-input
                                type="text"
                                wire:model="question"
                                placeholder="Escribe tu pregunta aquí..."
                                class="w-full"
                                :disabled="$isLoading"
                            />
                        </div>
                        <flux:button
                            type="submit"
                            variant="primary"
                            icon="paper-airplane"
                            :disabled="$isLoading || empty(trim($question))"
                            class="bg-indigo-600 hover:bg-indigo-700"
                        >
                            Enviar
                        </flux:button>
                    </form>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">
                        Puedes preguntar sobre productos, stock, ventas, categorías y más.
                    </p>
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        // Auto-scroll al final del chat cuando hay nuevos mensajes
        $wire.on('scroll-to-bottom', () => {
            setTimeout(() => {
                const chatMessages = document.getElementById('chat-messages');
                if (chatMessages) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            }, 100);
        });

        // Scroll inicial
        window.addEventListener('load', () => {
            const chatMessages = document.getElementById('chat-messages');
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        });
    </script>
    @endscript
</div>

