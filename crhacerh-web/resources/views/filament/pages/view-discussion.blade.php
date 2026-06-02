<x-filament-panels::page>
    <div class="h-[80vh] flex flex-col bg-gray-900 border border-gray-700 rounded-lg shadow-sm overflow-hidden">
        
        <div class="p-4 border-b border-gray-700 flex items-center gap-3 bg-gray-800">
            <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold">
                {{ substr($this->discussion->receiver->first_name ?? '?', 0, 1) }}
            </div>
            <h2 class="text-white font-semibold">
                {{ $this->discussion->receiver->first_name }} {{ $this->discussion->discussion->receiver->last_name ?? '' }}
            </h2>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-4">
            @foreach($this->discussion->messages as $message)
                <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[70%] p-3 rounded-lg {{ $message->sender_id === auth()->id() ? 'bg-primary-600 text-white' : 'bg-gray-700 text-gray-200' }}">
                        <p class="text-sm">{{ $message->message }}</p>
                        <span class="text-[10px] opacity-70 block text-right mt-1">{{ $message->created_at->format('H:i') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <form wire:submit="sendMessage" class="p-4 border-t border-gray-700 bg-gray-800 flex gap-2">
            <input 
                wire:model="newMessage" 
                type="text" 
                placeholder="Écrire un message..." 
                class="flex-1 bg-gray-900 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-500 outline-none"
            >
            <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-500 transition">
                Envoyer
            </button>
        </form>
    </div>
</x-filament-panels::page>