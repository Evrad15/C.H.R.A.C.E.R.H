<x-filament-panels::page>
    <div class="w-full max-w-4xl mx-auto flex flex-col justify-between" style="height: calc(100vh - 12rem); min-height: 550px;">
        
        <div class="flex-1 overflow-y-auto px-2 py-4 space-y-6" id="chat-box" style="box-shadow: none !important; border: none !important;">
            @foreach($messages as $msg)
                @if($msg['sender'] === 'ia')
                    <div class="flex items-start gap-4 max-w-3xl">
                        <div class="w-8 h-8 flex-shrink-0 rounded-full bg-gradient-to-tr from-blue-600 to-cyan-500 flex items-center justify-center text-sm shadow-md">
                            ✨
                        </div>
                        <div class="flex-1 pt-1">
                            <p class="text-sm font-medium text-gray-200 leading-relaxed whitespace-pre-line">
                                {!! nl2br(e($msg['text'])) !!}
                            </p>
                            <span class="block text-[10px] text-gray-500 mt-2 tracking-wide">{{ $msg['time'] }}</span>
                        </div>
                    </div>
                @else
                    <div class="flex items-start gap-4 max-w-xl ml-auto flex-row-reverse">
                        <div class="w-8 h-8 flex-shrink-0 rounded-full bg-gray-800 flex items-center justify-center font-bold text-xs text-gray-300 border border-gray-700">
                            RH
                        </div>
                        <div class="pt-1 text-right">
                            <p class="text-sm text-gray-200 leading-relaxed">
                                {{ $msg['text'] }}
                            </p>
                            <span class="block text-[10px] text-gray-500 mt-2 tracking-wide">{{ $msg['time'] }}</span>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-4 px-2">
            <form wire:submit.prevent="envoyerMessage" class="w-full bg-[#1e1e20] rounded-3xl p-4 shadow-2xl transition-all duration-200" style="border: none !important; outline: none !important; box-shadow: none !important;">
                
                <textarea 
                    wire:model.defer="messageInterne"
                    rows="2"
                    placeholder="Comment puis-je vous aider ?" 
                    class="w-full bg-transparent p-0 text-sm text-gray-100 placeholder-gray-500 resize-none block w-full"
                    style="border: none !important; outline: none !important; box-shadow: none !important; --tw-ring-color: transparent !important; ring: none !important;"
                    onkeydown="if(event.keyCode === 13 && !event.shiftKey) { event.preventDefault(); @this.call('envoyerMessage'); }"></textarea>
                
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-800/40">
                    
                    <div class="flex items-center gap-3 text-gray-400">
                        <button type="button" class="p-1.5 rounded-full hover:bg-gray-800 text-gray-400 hover:text-gray-200 transition-colors" style="border: none !important;">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375 0 11-.75 0 .375 0 01.75 0z"></path>
                            </svg>
                        </button>
                        
                        <button type="button" class="p-1.5 rounded-full hover:bg-gray-800 text-gray-400 hover:text-gray-200 transition-colors" style="border: none !important;">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3-3.75h6M12 15.75a3 3 0 01-3-3V6a3 3 0 116 0v6.75a3 3 0 01-3 3z"></path>
                            </svg>
                        </button>
                    </div>

                    <button type="submit" 
                            class="p-2 rounded-full bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-200" style="border: none !important;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>