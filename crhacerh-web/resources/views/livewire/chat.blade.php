<div class="grid grid-cols-12 gap-6 h-[calc(100vh-20rem)] max-h-[calc(100vh-20rem)] overflow-hidden antialiased bg-transparent" wire:poll.keep-alive.3s="loadThreads">
    
    <!-- Custom CSS for strict theme-based colors and animations -->
    <style>
        /* ByeWind / SnowUI style additions */
        .snow-card {
            background-color: #ffffff !important;
            border: 1px solid #e3e3e0 !important;
            border-radius: 16px !important;
            box-shadow: 0px 1px 3px 0px rgba(0, 0, 0, 0.05) !important;
        }
        .dark .snow-card {
            background-color: #1c1c1c !important;
            border: 1px solid #3e3e3a !important;
        }

        .snow-search-container {
            position: relative;
        }

        .snow-search-input {
            width: 100%;
            padding: 8px 12px 8px 36px !important;
            font-size: 0.75rem !important;
            border-radius: 9999px !important;
            border: none !important;
            background-color: rgba(0, 0, 0, 0.04) !important;
            color: #1c1c1c !important;
            transition: all 0.2s ease;
        }
        .dark .snow-search-input {
            background-color: rgba(255, 255, 255, 0.06) !important;
            color: #ededec !important;
        }
        .snow-search-input:focus {
            background-color: rgba(0, 0, 0, 0.06) !important;
            box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.4) !important;
        }
        .dark .snow-search-input:focus {
            background-color: rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.6) !important;
        }

        .snow-chat-input {
            border: none !important;
            background-color: rgba(0, 0, 0, 0.04) !important;
            border-radius: 9999px !important;
            padding: 10px 16px !important;
            font-size: 0.875rem !important;
            color: #1e293b !important;
        }
        .dark .snow-chat-input {
            background-color: rgba(255, 255, 255, 0.06) !important;
            color: #f8fafc !important;
        }

        /* Force Input Text Colors */
        .chat-input-field {
            color: #0f172a !important; /* Dark slate in light theme */
        }
        .dark .chat-input-field {
            color: #f8fafc !important; /* Off-white in dark theme */
        }

        /* Force General Text Colors */
        .chat-text-color {
            color: #1e293b !important; /* Slate-800 in light theme */
        }
        .dark .chat-text-color {
            color: #f1f5f9 !important; /* Slate-100 in dark theme */
        }

        /* Bubble Sent Style */
        .bubble-sent {
            background-color: rgba(130, 201, 30, 0.18) !important; /* Low opacity lime green */
            color: #1e293b !important; /* Dark text for perfect readability in light mode */
            border-color: rgba(130, 201, 30, 0.35) !important;
            border-radius: 18px 18px 4px 18px !important;
            padding: 14px 18px !important;
        }
        .bubble-sent p {
            color: #1e293b !important;
            font-size: 0.925rem !important;
            line-height: 1.6 !important;
        }
        .dark .bubble-sent {
            background-color: rgba(130, 201, 30, 0.28) !important; /* Slightly more opaque for dark mode visibility */
            color: #f8fafc !important;
            border-color: rgba(130, 201, 30, 0.45) !important;
        }
        .dark .bubble-sent p {
            color: #f8fafc !important;
        }

        /* Bubble Received Style */
        .bubble-received {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
            border-color: rgba(226, 232, 240, 0.5) !important;
            border-radius: 18px 18px 18px 4px !important;
            padding: 14px 18px !important;
        }
        .bubble-received p {
            color: #1e293b !important;
            font-size: 0.925rem !important;
            line-height: 1.6 !important;
        }
        
        /* Dark mode overrides for Received Bubble */
        .dark .bubble-received {
            background-color: #334155 !important;
            color: #f8fafc !important;
            border-color: rgba(71, 85, 105, 0.5) !important;
        }
        .dark .bubble-received p {
            color: #f8fafc !important;
        }

        /* Attachment Links inside Bubbles */
        .bubble-sent .attachment-link {
            background-color: rgba(130, 201, 30, 0.12) !important;
            color: #1e293b !important;
        }
        .bubble-sent .attachment-link:hover {
            background-color: rgba(130, 201, 30, 0.22) !important;
        }
        .dark .bubble-sent .attachment-link {
            background-color: rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
        }
        .dark .bubble-sent .attachment-link:hover {
            background-color: rgba(255, 255, 255, 0.25) !important;
        }
        .bubble-received .attachment-link {
            background-color: rgba(99, 102, 241, 0.1) !important;
            color: #4f46e5 !important;
        }
        .bubble-received .attachment-link:hover {
            background-color: rgba(99, 102, 241, 0.2) !important;
        }
        .dark .bubble-received .attachment-link {
            background-color: rgba(165, 180, 252, 0.1) !important;
            color: #a5b4fc !important;
        }
        .dark .bubble-received .attachment-link:hover {
            background-color: rgba(165, 180, 252, 0.2) !important;
        }

        .gemini-gradient-text {
            background: linear-gradient(74deg, #4285f4 0%, #9b72cb 25%, #d96570 50%, #f59e0b 75%, #4285f4 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shine 6s linear infinite;
        }
        @keyframes shine {
            to { background-position: 200% center; }
        }
        .gemini-glow {
            box-shadow: 0 0 40px rgba(99, 102, 241, 0.2);
        }
        .message-fade-in {
            animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    
    <!-- Conversations List Panel -->
    <div class="col-span-12 {{ $activeThreadId ? 'hidden' : 'flex' }} h-full snow-card overflow-hidden flex flex-col">
        
        <!-- Header & Search -->
        <div class="p-4 border-b border-gray-100 dark:border-gray-800 space-y-3">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-gray-100 text-base">Conversations</h3>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Messagerie interne des employés</p>
                </div>
                @if($threads->isEmpty() && empty($search))
                    <span class="text-[10px] text-gray-400">Aucune discussion</span>
                @endif
            </div>

            <!-- Search Input -->
            <div class="snow-search-container">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400 dark:text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input type="text" wire:model.live.debounce.250ms="search" placeholder="Rechercher une discussion..." 
                    class="snow-search-input">
                @if(!empty($search))
                    <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
            </div>
        </div>

        <!-- Scrollable Threads List -->
        <div class="flex-grow min-h-0 overflow-y-auto p-2 space-y-1">
            @forelse($threads as $thread)
                <button wire:click="selectThread({{ $thread->id }})"
                    class="group w-full text-left p-3.5 rounded-xl transition-all duration-200 {{ $activeThreadId == $thread->id ? 'bg-black/[0.04] dark:bg-white/[0.06] font-semibold' : 'hover:bg-black/[0.02] dark:hover:bg-white/[0.03]' }}">
                    <div class="flex justify-between items-center gap-3">
                        
                        <!-- Details -->
                        <div class="flex-grow min-w-0 space-y-1">
                            <div class="chat-text-color font-semibold text-sm truncate">
                                {{ $thread->subject ?: $thread->participantsString(Auth::id(), ['first_name', 'last_name']) }}
                            </div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                {{ optional($thread->updated_at)->diffForHumans() }}
                            </div>
                        </div>

                        <!-- Badge count & Avatar -->
                        <div class="flex items-center gap-3 shrink-0">
                            @if($thread->isUnread(Auth::id()))
                                <span class="inline-flex items-center justify-center h-5 min-w-[1.25rem] rounded-full bg-indigo-600 text-white text-[9px] font-bold px-1.5 shadow-md shadow-indigo-600/10">
                                    {{ $thread->userUnreadMessagesCount(Auth::id()) }}
                                </span>
                            @endif
                            @php
                                $participantName = $thread->subject ?: $thread->participantsString(Auth::id(), ['first_name', 'last_name']);
                                $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($participantName) . '&background=6366f1&color=ffffff&size=128&rounded=true';
                            @endphp
                            <img src="{{ $avatarUrl }}" alt="{{ $participantName }}" class="w-9 h-9 rounded-full object-cover border border-gray-200 dark:border-gray-700 shadow-sm transition-transform duration-300 group-hover:scale-105">
                        </div>

                    </div>
                </button>
            @empty
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <p class="text-2xl mb-2">🔍</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Aucun résultat trouvé.</p>
                </div>
            @endforelse
        </div>

    </div>

    <!-- Active Conversation Panel -->
    <div class="col-span-12 {{ $activeThreadId ? 'flex' : 'hidden' }} h-full snow-card flex flex-col overflow-hidden">
        
        @if($activeThread)
            
            <!-- Active Chat Header -->
            <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex flex-col gap-2">
                <div class="flex items-center gap-3">
                    
                    <!-- Back Arrow -->
                    <button wire:click="$set('activeThreadId', null)" class="p-2 -ml-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2050/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                    </button>

                    <div class="flex-grow min-w-0">
                        <h3 class="font-bold text-base text-gray-900 dark:text-gray-100 truncate">
                            {{ $activeThread->subject ?: $activeThread->participantsString(Auth::id(), ['first_name', 'last_name']) }}
                        </h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                            Participants : {{ $activeThread->participantsString(Auth::id(), ['first_name', 'last_name']) }}
                        </p>
                    </div>

                    <span class="text-[10px] text-gray-500 dark:text-gray-400 whitespace-nowrap hidden sm:inline">
                        Actif {{ optional($activeThread->updated_at)->diffForHumans() }}
                    </span>

                </div>
            </div>

            <!-- Messages Stream -->
            <div class="flex-grow min-h-0 p-6 overflow-y-auto space-y-4 bg-gray-50/10 dark:bg-transparent" 
                id="chat-messages"
                x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
                x-effect="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
                style="scrollbar-width: thin;">
                
                @foreach($messages as $msg)
                    <div class="flex {{ $msg->user_id == Auth::id() ? 'justify-end' : 'justify-start' }} message-fade-in">
                        
                        <div class="max-w-[70%] flex flex-col {{ $msg->user_id == Auth::id() ? 'items-end' : 'items-start' }}">
                            
                            <!-- Bubble Card -->
                            <div class="p-3.5 {{ $msg->user_id == Auth::id() ? 'bubble-sent shadow-sm' : 'bubble-received shadow-sm' }} border">
                                
                                <!-- Attachment view inside bubble -->
                                @if($msg->attachment)
                                    <div class="mb-2">
                                        <a href="{{ Storage::disk('public')->url($msg->attachment) }}" target="_blank" 
                                            class="inline-flex items-center gap-2 p-2 rounded-xl text-xs font-semibold attachment-link transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                                            </svg>
                                            <span class="truncate max-w-[150px]">{{ basename($msg->attachment) }}</span>
                                        </a>
                                    </div>
                                @endif

                                <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $msg->body }}</p>
                            </div>

                            <!-- Metadata under bubble -->
                            <span class="text-[9px] text-gray-400 dark:text-gray-500 mt-1.5 px-1">
                                {{ $msg->created_at->format('H:i') }}
                            </span>

                        </div>

                    </div>
                @endforeach

            </div>

            <!-- Chat Footer Input -->
            <form wire:submit.prevent="sendMessage" class="p-4 border-t border-gray-100 dark:border-gray-800 flex flex-col gap-2 shrink-0">
                
                <div class="flex items-center gap-3">
                    
                    <!-- Paperclip file input -->
                    <label class="p-2.5 text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full cursor-pointer transition shrink-0">
                        <input type="file" wire:model="attachment" class="hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                        </svg>
                    </label>

                    <!-- Text input capsule -->
                    <input type="text" wire:model.defer="newMessage" placeholder="Rédiger votre message..."
                        class="snow-chat-input flex-grow transition">

                    <!-- Send button -->
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white p-2.5 rounded-full transition flex items-center justify-center shrink-0 shadow-md shadow-indigo-600/10">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                        </svg>
                    </button>

                </div>

                <!-- Attachment file preview inside form footer -->
                @if($attachment)
                    <div class="flex items-center justify-between rounded-xl border border-indigo-200/40 dark:border-indigo-900/40 px-3.5 py-2 bg-indigo-50/30 dark:bg-indigo-950/10 text-xs text-indigo-700 dark:text-indigo-300">
                        <span class="truncate max-w-[80%] font-semibold">📎 {{ $attachment->getClientOriginalName() }}</span>
                        <button type="button" wire:click.prevent="$set('attachment', null)" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-bold font-semibold">Supprimer</button>
                    </div>
                @endif
                
                @error('attachment') 
                    <span class="text-xs text-red-600 dark:text-red-400 px-2 font-medium">{{ $message }}</span> 
                @enderror

            </form>

        @endif

    </div>

</div>