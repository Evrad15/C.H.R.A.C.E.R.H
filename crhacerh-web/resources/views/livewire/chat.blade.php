<div class="grid grid-cols-12 gap-4 h-[75vh] antialiased">
    <div class="col-span-12 md:col-span-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden flex flex-col">
        <div class="p-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-gray-700 dark:text-gray-200">Conversations</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Sélectionnez un employé pour ouvrir le chat</p>
                </div>
                @if($threads->isEmpty())
                <span class="text-xs text-gray-400">Aucune discussion</span>
                @endif
            </div>
        </div>
        <div class="flex-grow overflow-y-auto">
            @foreach($threads as $thread)
            <button wire:click="selectThread({{ $thread->id }})"
                class="w-full text-left p-4 border-b border-gray-50 dark:border-gray-800 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition {{ $activeThreadId == $thread->id ? 'bg-primary-50 dark:bg-primary-900/30 border-l-4 border-l-primary-500' : '' }}">
                <div class="flex justify-between items-center mb-1">
                    <div class="space-y-1">
                        <div class="font-semibold text-sm text-gray-800 dark:text-gray-100 truncate">
                            {{ $thread->subject ?: $thread->participantsString(Auth::id()) }}
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $thread->latest_message->body ?? 'Pas de message' }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        @if($thread->isUnread(Auth::id()))
                        <span class="inline-flex items-center justify-center h-6 min-w-[1.5rem] rounded-full bg-primary-600 text-white text-[11px] font-semibold">{{ $thread->userUnreadMessagesCount(Auth::id()) }}</span>
                        @endif
                        <span class="text-[10px] text-gray-400">{{ optional($thread->updated_at)->diffForHumans() }}</span>
                    </div>
                </div>
            </button>
            @endforeach
        </div>
    </div>

    <div class="col-span-12 md:col-span-8 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl flex flex-col overflow-hidden">
        @if($activeThread)
        <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex flex-col gap-2 bg-gray-50 dark:bg-gray-800/50">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100 truncate">{{ $activeThread->subject ?: $activeThread->participantsString(Auth::id()) }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Participants : {{ $activeThread->participantsString(Auth::id()) }}</p>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400">Dernier message {{ optional($activeThread->updated_at)->diffForHumans() }}</span>
            </div>
        </div>

        <div class="flex-grow p-6 overflow-y-auto space-y-4 bg-gray-50/50 dark:bg-gray-950/20" id="chat-messages">
            @foreach($messages as $msg)
            <div class="flex {{ $msg->user_id == Auth::id() ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[70%] p-3 rounded-2xl shadow-sm {{ $msg->user_id == Auth::id() ? 'bg-primary-600 text-white rounded-tr-none' : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-tl-none border border-gray-100 dark:border-gray-700' }}">
                    @if($msg->attachment)
                    <div class="mb-2">
                        <a href="{{ Storage::disk('public')->url($msg->attachment) }}" target="_blank" class="inline-flex items-center gap-2 text-sm font-medium text-primary-700 dark:text-primary-300">
                            <i class="fa-solid fa-paperclip"></i>
                            {{ basename($msg->attachment) }}
                        </a>
                    </div>
                    @endif
                    <p class="text-sm">{{ $msg->body }}</p>
                    <span class="text-[10px] block mt-1 opacity-70">{{ $msg->created_at->format('H:i') }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <form wire:submit.prevent="sendMessage" class="p-4 border-t border-gray-100 dark:border-gray-800 flex flex-col gap-3 bg-white dark:bg-gray-900">
            <div class="flex items-center gap-2">
                <input type="text" wire:model.defer="newMessage" placeholder="Écrivez votre message..."
                    class="flex-grow rounded-full border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-sm px-4 py-3 focus:ring-primary-500 focus:border-primary-500">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white p-3 rounded-full transition">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
            <div class="flex flex-col gap-2">
                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 cursor-pointer">
                    <input type="file" wire:model="attachment" class="hidden">
                    <span class="inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-gray-700 px-4 py-2 bg-gray-50 dark:bg-gray-950 text-sm hover:bg-gray-100 dark:hover:bg-gray-900 transition">
                        <i class="fa-solid fa-paperclip"></i>
                        Joindre un fichier
                    </span>
                </label>
                @if($attachment)
                <div class="flex items-center justify-between rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 bg-gray-50 dark:bg-gray-950 text-sm text-gray-700 dark:text-gray-200">
                    <span>{{ $attachment->getClientOriginalName() }}</span>
                    <button type="button" wire:click.prevent="$set('attachment', null)" class="text-primary-600 hover:text-primary-800 dark:text-primary-300">Supprimer</button>
                </div>
                @endif
                @error('attachment') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
            </div>
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