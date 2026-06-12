<div class="grid grid-cols-12 gap-4 h-[calc(100vh-21rem)] max-h-[calc(100vh-21rem)] overflow-hidden antialiased" wire:poll.keep-alive.3s="loadThreads">
    <div class="col-span-12 {{ $activeThreadId ? 'hidden' : 'flex' }} h-full bg-gray-50/30 dark:bg-gray-900/30 backdrop-blur-sm border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden flex flex-col">
        <div class="p-4 border-b border-gray-100 dark:border-gray-800 bg-gray-100/30 dark:bg-gray-800/30">
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
        <div class="flex-grow min-h-0 overflow-y-auto">
            @foreach($threads as $thread)
            <button wire:click="selectThread({{ $thread->id }})"
                class="group w-full text-left p-4 border-b border-gray-50 dark:border-gray-850 hover:bg-primary-50/40 dark:hover:bg-primary-950/20 transition-all duration-200 {{ $activeThreadId == $thread->id ? 'bg-primary-50/80 dark:bg-primary-900/20 border-l-4 border-l-primary-500' : 'border-l-4 border-l-transparent' }}">
                <div class="flex justify-between items-center gap-4">
                    <div class="flex-grow min-w-0 space-y-1">
                        <div class="font-semibold text-sm text-gray-800 dark:text-gray-100 truncate">
                            {{ $thread->subject ?: $thread->participantsString(Auth::id(), ['first_name', 'last_name']) }}
                        </div>
                        <div class="text-[10px] text-gray-400">{{ optional($thread->updated_at)->diffForHumans() }}</div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        @if($thread->isUnread(Auth::id()))
                        <span class="inline-flex items-center justify-center h-5 min-w-[1.25rem] rounded-full bg-primary-600 text-white text-[10px] font-semibold px-1">{{ $thread->userUnreadMessagesCount(Auth::id()) }}</span>
                        @endif
                        @php
                            $participantName = $thread->subject ?: $thread->participantsString(Auth::id(), ['first_name', 'last_name']);
                            $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($participantName) . '&background=6366f1&color=ffffff&size=128&rounded=true';
                        @endphp
                        <img src="{{ $avatarUrl }}" alt="{{ $participantName }}" class="w-9 h-9 rounded-full object-cover border-2 border-primary-500/10 shadow-sm transition-transform duration-300 group-hover:scale-110">
                    </div>
                </div>
            </button>
            @endforeach
        </div>
    </div>

    <div class="col-span-12 {{ $activeThreadId ? 'flex' : 'hidden' }} h-full bg-gray-50/30 dark:bg-gray-900/30 backdrop-blur-sm border border-gray-200 dark:border-gray-800 rounded-xl flex flex-col overflow-hidden">
        @if($activeThread)
        <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex flex-col gap-2 bg-gray-100/30 dark:bg-gray-800/30">
            <div class="flex items-center gap-3">
                <button wire:click="$set('activeThreadId', null)" class="p-2 -ml-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                </button>
                <div class="flex-grow min-w-0">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100 truncate">{{ $activeThread->subject ?: $activeThread->participantsString(Auth::id(), ['first_name', 'last_name']) }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Participants : {{ $activeThread->participantsString(Auth::id(), ['first_name', 'last_name']) }}</p>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap hidden sm:inline">Dernier message {{ optional($activeThread->updated_at)->diffForHumans() }}</span>
            </div>
        </div>

        <div class="flex-grow min-h-0 p-6 overflow-y-auto space-y-4 bg-transparent" 
            id="chat-messages"
            x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
            x-effect="$nextTick(() => { $el.scrollTop = $el.scrollHeight })">
            @foreach($messages as $msg)
            <div class="flex {{ $msg->user_id == Auth::id() ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[70%] p-3 rounded-2xl {{ $msg->user_id == Auth::id() ? 'bg-gradient-to-br from-primary-500 to-primary-600 text-white rounded-tr-none border border-primary-400/20 shadow-md shadow-primary-500/10' : 'bg-gray-100/80 dark:bg-gray-800/80 text-gray-800 dark:text-gray-200 rounded-tl-none border border-gray-200/50 dark:border-gray-700/50 backdrop-blur-sm' }}">
                    @if($msg->attachment)
                    <div class="mb-2">
                        <a href="{{ Storage::disk('public')->url($msg->attachment) }}" target="_blank" class="inline-flex items-center gap-2 text-sm font-medium {{ $msg->user_id == Auth::id() ? 'text-white hover:text-white/80 underline decoration-white/40' : 'text-primary-600 dark:text-primary-400 hover:text-primary-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                            </svg>
                            {{ basename($msg->attachment) }}
                        </a>
                    </div>
                    @endif
                    <p class="text-sm leading-relaxed">{{ $msg->body }}</p>
                    <span class="text-[10px] block mt-1 opacity-75">{{ $msg->created_at->format('H:i') }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <form wire:submit.prevent="sendMessage" class="p-3 border-t border-gray-100 dark:border-gray-800/80 bg-transparent flex flex-col gap-2 shrink-0">
            <div class="flex items-center gap-2">
                <!-- Bouton trombone pour pièce jointe -->
                <label class="p-2 text-gray-500 dark:text-gray-400 hover:text-primary-500 dark:hover:text-primary-400 hover:bg-gray-100/50 dark:hover:bg-gray-800/50 rounded-full cursor-pointer transition shrink-0">
                    <input type="file" wire:model="attachment" class="hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                    </svg>
                </label>

                <!-- Champ de saisie -->
                <input type="text" wire:model.defer="newMessage" placeholder="Écrivez un message..."
                    class="flex-grow rounded-full border border-gray-200/60 dark:border-gray-700/60 bg-gray-50/40 dark:bg-gray-850/40 text-sm px-4 py-2.5 focus:ring-primary-500 focus:border-primary-500 focus:bg-white dark:focus:bg-gray-800 focus:outline-none transition">

                <!-- Bouton d'envoi -->
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white p-2.5 rounded-full transition flex items-center justify-center shrink-0 shadow-md shadow-primary-500/10">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                </button>
            </div>

            <!-- Affichage du fichier joint en cours -->
            @if($attachment)
            <div class="flex items-center justify-between rounded-xl border border-gray-200/60 dark:border-gray-700/60 px-3 py-1.5 bg-gray-100/40 dark:bg-gray-800/40 text-xs text-gray-700 dark:text-gray-200">
                <span class="truncate max-w-[80%]">{{ $attachment->getClientOriginalName() }}</span>
                <button type="button" wire:click.prevent="$set('attachment', null)" class="text-primary-600 hover:text-primary-800 dark:text-primary-300 font-semibold">Supprimer</button>
            </div>
            @endif
            @error('attachment') <span class="text-xs text-red-600 dark:text-red-400 px-2">{{ $message }}</span> @enderror
        </form>
        @endif
    </div>
</div>