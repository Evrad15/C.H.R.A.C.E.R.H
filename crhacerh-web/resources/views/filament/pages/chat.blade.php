<x-filament-panels::page>
    <div class="h-[80vh] flex flex-col bg-gray-900 border border-gray-700 rounded-lg shadow-sm overflow-hidden">
        
        <div class="flex-shrink-0 p-4 border-b border-gray-700 bg-gray-900 flex items-center justify-between">
            <p class="text-sm text-gray-400">
                {{ count($this->discussions) }} discussion(s)
            </p>
            
            <div class="w-48">
                <input 
                    wire:model.live.debounce.300ms="search" 
                    type="text" 
                    placeholder="Rechercher..." 
                    class="w-full px-3 py-1.5 bg-gray-800 border border-gray-600 text-white text-sm rounded focus:ring-1 focus:ring-primary-500 outline-none transition"
                >
            </div>
        </div>

        <div class="flex-1 overflow-y-auto">
            @if($this->discussions->count() > 0)
                <div class="divide-y divide-gray-800">
                    @foreach($this->discussions as $discussion)
                        @php
                            $other = ($discussion->sender_id === auth()->id()) ? $discussion->receiver : $discussion->sender;
                            $lastMessage = $discussion->messages()->latest()->first();
                            $isUnread = $lastMessage && $lastMessage->sender_id !== auth()->id() && !$lastMessage->read_at;
                        @endphp
                        
                        <a href="{{ \App\Filament\Pages\ViewDiscussion::getUrl(['record' => $discussion->id]) }}" 
                           class="flex items-center gap-4 p-4 hover:bg-gray-800 transition duration-150 group">
                            
                            <div class="w-12 h-12 flex-shrink-0 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold text-lg shadow-md overflow-hidden">
                                {{ substr($other->first_name ?? '?', 0, 1) }}{{ substr($other->last_name ?? '?', 0, 1) }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-baseline justify-between mb-0.5">
                                    <h3 class="font-semibold text-white truncate">
                                        {{ $other ? $other->first_name . ' ' . $other->last_name : 'Utilisateur supprimé' }}
                                    </h3>
                                    <span class="text-xs text-gray-500">{{ $lastMessage ? $lastMessage->created_at->diffForHumans() : '' }}</span>
                                </div>
                                <p class="text-xs text-gray-400 truncate">
                                    {{ $lastMessage ? Str::limit($lastMessage->message, 40) : 'Aucun message' }}
                                </p>
                            </div>

                            @if($isUnread)
                                <div class="w-2 h-2 bg-primary-500 rounded-full animate-pulse"></div>
                            @endif
                        </a>
                    @endforeach
                </div>
            @else
                <div class="h-full flex items-center justify-center text-gray-500 text-sm">
                    Aucune discussion trouvée.
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>