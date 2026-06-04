<div class="grid grid-cols-12 gap-4 h-[75vh] antialiased">
    <div class="col-span-12 md:col-span-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden flex flex-col">
        <div class="p-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
            <h3 class="font-bold text-gray-700 dark:text-gray-200">Conversations</h3>
        </div>
        <div class="flex-grow overflow-y-auto">
            @foreach($threads as $thread)
                <button wire:click="selectThread({{ $thread->id }})" 
                        class="w-full text-left p-4 border-b border-gray-50 dark:border-gray-800 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition {{ $activeThreadId == $thread->id ? 'bg-primary-50 dark:bg-primary-900/30 border-l-4 border-l-primary-500' : '' }}">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-sm">{{ $thread->subject }}</span>
                        @if($thread->isUnread(Auth::id()))
                            <span class="h-2 w-2 bg-primary-500 rounded-full"></span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 truncate">{{ $thread->latest_message->body ?? 'Pas de message' }}</p>
                </button>
            @endforeach
        </div>
    </div>

    <div class="col-span-12 md:col-span-8 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl flex flex-col overflow-hidden">
        @if($activeThread)
            <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex items-center bg-gray-50 dark:bg-gray-800/50">
                <span class="font-bold">{{ $activeThread->subject }}</span>
            </div>

            <div class="flex-grow p-6 overflow-y-auto space-y-4 bg-gray-50/50 dark:bg-gray-950/20">
                @foreach($messages as $msg)
                    <div class="flex {{ $msg->user_id == Auth::id() ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[70%] p-3 rounded-2xl shadow-sm {{ $msg->user_id == Auth::id() ? 'bg-primary-600 text-white rounded-tr-none' : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-tl-none border border-gray-100 dark:border-gray-700' }}">
                            <p class="text-sm">{{ $msg->body }}</p>
                            <span class="text-[10px] block mt-1 opacity-70">{{ $msg->created_at->format('H:i') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <form wire:submit.prevent="sendMessage" class="p-4 border-t border-gray-100 dark:border-gray-800 flex gap-2 bg-white dark:bg-gray-900">
                <input type="text" wire:model="newMessage" placeholder="Écrivez votre message..." 
                       class="flex-grow rounded-full border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-sm focus:ring-primary-500 focus:border-primary-500">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white p-2 rounded-full px-6 transition">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>
        @else
            <div class="flex-grow flex items-center justify-center text-gray-400">
                <div class="text-center">
                    <i class="fa-solid fa-comments text-5xl mb-4 opacity-20"></i>
                    <p>Sélectionnez une discussion pour commencer</p>
                </div>
            </div>
        @endif
    </div>
</div>