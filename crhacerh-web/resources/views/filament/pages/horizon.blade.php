<x-filament-panels::page>
    <!-- Custom CSS for Gemini/Horizon Premium Aesthetics -->
    <style>
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
        .gemini-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        .dark .gemini-card {
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(71, 85, 105, 0.3);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        }
        .gemini-card:hover {
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(99, 102, 241, 0.1);
            transform: translateY(-2px);
        }
    </style>

    <div class="w-full max-w-4xl mx-auto flex flex-col justify-between" style="height: calc(100vh - 12rem); min-height: 580px;">

        <!-- Chat Container -->
        <div class="flex-1 overflow-y-auto px-2 py-4 space-y-6" id="chat-box" style="scrollbar-width: thin;">
            
            @if(count($messages) <= 2)
                <!-- Gemini Onboarding View -->
                <div class="flex flex-col items-center justify-center py-8 text-center message-fade-in">
                    <!-- Glowing Logo -->
                    <div class="relative w-16 h-16 rounded-full bg-gradient-to-tr from-blue-600 via-indigo-500 to-pink-500 flex items-center justify-center text-3xl shadow-lg mb-6 animate-pulse gemini-glow">
                        ✨
                    </div>
                    
                    <!-- Welcome Title -->
                    <h1 class="text-4xl font-bold tracking-tight mb-2">
                        <span class="gemini-gradient-text">Bonjour, Administrateur</span>
                    </h1>
                    <p class="text-md text-gray-500 dark:text-gray-400 mb-8 max-w-lg">
                        Je suis Horizon, l'assistant RH intelligent de C.H.R.A.C.E.R.H. Que puis-je faire pour vous aujourd'hui ?
                    </p>

                    <!-- Suggestions Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full max-w-2xl text-left">
                        <div wire:click="genererRapport('day')" class="gemini-card cursor-pointer p-5 rounded-2xl transition-all duration-300 flex items-start gap-4">
                            <span class="text-2xl p-2.5 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-500">📊</span>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Rapport du jour</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Générer le rapport de présence quotidien et le statut global.</p>
                            </div>
                        </div>

                        <div wire:click="genererRapport('week')" class="gemini-card cursor-pointer p-5 rounded-2xl transition-all duration-300 flex items-start gap-4">
                            <span class="text-2xl p-2.5 rounded-xl bg-cyan-50 dark:bg-cyan-900/20 text-cyan-500">📈</span>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Rapport hebdomadaire</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Créer le bilan d'assiduité de la semaine et l'exporter en Excel.</p>
                            </div>
                        </div>

                        <div wire:click="detecterAnomalies('week')" class="gemini-card cursor-pointer p-5 rounded-2xl transition-all duration-300 flex items-start gap-4">
                            <span class="text-2xl p-2.5 rounded-xl bg-orange-50 dark:bg-orange-900/20 text-orange-500">🚨</span>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Détecter les anomalies</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Analyser les doublons et les absences de la semaine.</p>
                            </div>
                        </div>

                        <div wire:click="genererRapport('month')" class="gemini-card cursor-pointer p-5 rounded-2xl transition-all duration-300 flex items-start gap-4">
                            <span class="text-2xl p-2.5 rounded-xl bg-violet-50 dark:bg-violet-900/20 text-violet-500">📅</span>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Rapport mensuel</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Générer le rapport analytique de présence pour le mois.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Active Conversation View -->
                <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-100 dark:border-gray-800 pb-4">
                    <button wire:click="genererRapport('day')" type="button" class="rounded-full border border-blue-500/30 bg-blue-500/5 px-3 py-1 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-500/10 transition">Rapport jour</button>
                    <button wire:click="genererRapport('week')" type="button" class="rounded-full border border-cyan-500/30 bg-cyan-500/5 px-3 py-1 text-xs font-semibold text-cyan-600 dark:text-cyan-400 hover:bg-cyan-500/10 transition">Rapport semaine</button>
                    <button wire:click="genererRapport('month')" type="button" class="rounded-full border border-violet-500/30 bg-violet-500/5 px-3 py-1 text-xs font-semibold text-violet-600 dark:text-violet-400 hover:bg-violet-500/10 transition">Rapport mois</button>
                    <button wire:click="genererRapport('year')" type="button" class="rounded-full border border-emerald-500/30 bg-emerald-500/5 px-3 py-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/10 transition">Rapport année</button>
                    <button wire:click="detecterAnomalies('week')" type="button" class="rounded-full border border-orange-500/30 bg-orange-500/5 px-3 py-1 text-xs font-semibold text-orange-600 dark:text-orange-400 hover:bg-orange-500/10 transition">Détecter anomalies</button>
                </div>

                @foreach($messages as $msg)
                    @if($msg['sender'] === 'ia')
                        @php
                            $msgCleanText = addslashes(str_replace(["\r", "\n"], ' ', strip_tags($msg['text'])));
                            $msgHash      = md5($msg['text'] . $msg['time']);
                        @endphp
                        <div class="flex items-start gap-4 max-w-3xl message-fade-in">
                            <div class="w-9 h-9 flex-shrink-0 rounded-full bg-gradient-to-tr from-blue-600 via-indigo-500 to-pink-500 flex items-center justify-center text-sm shadow-md">
                                ✨
                            </div>
                            <div class="flex-1 pt-1" x-init="renderChartIfPresent($el); speakLastMessage('{{ $msgCleanText }}', '{{ $msgHash }}');">
                                <div class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed space-y-2 [&_a]:text-blue-500 [&_a]:underline [&_a]:font-bold [&_ul]:list-disc [&_ul]:ml-5 [&_ol]:list-decimal [&_ol]:ml-5 [&_strong]:font-semibold [&_p]:mb-2">
                                    {!! \Illuminate\Support\Str::markdown($msg['text']) !!}
                                </div>
                                <span class="block text-[10px] text-gray-400 dark:text-gray-500 mt-2 tracking-wide">{{ $msg['time'] }}</span>
                            </div>
                        </div>
                    @else
                        <!-- User message -->
                        <div class="flex items-start gap-4 max-w-xl ml-auto flex-row-reverse message-fade-in">
                            <div class="w-9 h-9 flex-shrink-0 rounded-full bg-indigo-600 flex items-center justify-center font-bold text-xs text-white shadow">
                                RH
                            </div>
                            <div class="pt-1 text-right">
                                <div class="inline-block bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-2xl px-4 py-2.5 text-sm leading-relaxed border border-gray-200 dark:border-gray-700">
                                    {{ $msg['text'] }}
                                </div>
                                <span class="block text-[10px] text-gray-400 dark:text-gray-500 mt-2 tracking-wide">{{ $msg['time'] }}</span>
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif

        </div>

        <!-- Input Capsule -->
        <div class="mt-4 px-2">
            <form wire:submit.prevent="envoyerMessage" class="w-full bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/80 rounded-3xl p-4 shadow-xl transition-all duration-300 focus-within:border-indigo-500/50 focus-within:ring-2 focus-within:ring-indigo-500/10">
                <input type="file" wire:model="fileUpload" id="file-upload-input" class="hidden" accept="image/*,application/pdf">

                @if($fileUpload)
                    <div class="mb-3 flex items-center justify-between bg-indigo-50/50 dark:bg-indigo-950/10 rounded-xl p-2.5 border border-indigo-200/30 dark:border-indigo-900/30">
                        <div class="flex items-center gap-2 text-xs text-indigo-700 dark:text-indigo-300 font-medium">
                            <span>📎 {{ $fileUpload->getClientOriginalName() }}</span>
                            <span class="text-[10px] text-gray-400">({{ number_format($fileUpload->getSize() / 1024, 1) }} KB)</span>
                        </div>
                        <button type="button" wire:click="$set('fileUpload', null)" class="text-xs text-red-500 hover:text-red-400 font-semibold">Supprimer</button>
                    </div>
                @endif

                <textarea
                    wire:model.defer="messageInterne"
                    rows="2"
                    placeholder="Posez-moi une question sur les présences ou demandez un rapport..."
                    class="w-full bg-transparent p-0 text-sm text-gray-800 dark:text-gray-150 placeholder-gray-400 dark:placeholder-gray-500 resize-none block border-0 focus:ring-0"
                    style="border: none !important; outline: none !important; box-shadow: none !important;"
                    onkeydown="if(event.keyCode === 13 && !event.shiftKey) { event.preventDefault(); @this.call('envoyerMessage'); }"></textarea>

                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-150 dark:border-gray-700/50">

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="document.getElementById('file-upload-input').click()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 hover:text-indigo-500 dark:hover:text-indigo-400 transition-colors" style="border: none !important;" title="Joindre un justificatif (Image/PDF)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375 0 11-.75 0 .375 0 01.75 0z"></path>
                            </svg>
                        </button>

                        <button type="button" id="mic-btn" onclick="startSpeechRecognition()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors" style="border: none !important;" title="Dicter le message">
                            <svg id="mic-icon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3-3.75h6M12 15.75a3 3 0 01-3-3V6a3 3 0 116 0v6.75a3 3 0 01-3 3z"></path>
                            </svg>
                        </button>

                        <button type="button" id="speaker-btn" onclick="toggleVocalRead()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 hover:text-green-500 dark:hover:text-green-400 transition-colors" style="border: none !important;" title="Lecture vocale active/désactive">
                            <svg id="speaker-icon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z"></path>
                            </svg>
                        </button>
                    </div>

                    <button type="submit" class="p-2 rounded-full bg-gray-900 hover:bg-gray-800 text-white dark:bg-indigo-600 dark:hover:bg-indigo-500 dark:text-white transition-all duration-200" style="border: none !important;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function fixLinks() {
                document.querySelectorAll('#chat-box a').forEach(anchor => {
                    let href = anchor.getAttribute('href');
                    if (href && (href.includes('/storage/reports/') || href.includes('rapport_'))) {
                        anchor.setAttribute('download', '');
                        if (href.startsWith('http://localhost/storage/')) {
                            let correctUrl = href.replace('http://localhost/storage/', window.location.origin + '/storage/');
                            anchor.setAttribute('href', correctUrl);
                        } else if (href.startsWith('/storage/')) {
                            anchor.setAttribute('href', window.location.origin + href);
                        }
                    }
                });
            }

            const chatBox = document.getElementById('chat-box');
            if (chatBox) {
                const observer = new MutationObserver(() => {
                    fixLinks();
                    chatBox.scrollTop = chatBox.scrollHeight;
                });
                observer.observe(chatBox, { childList: true, subtree: true });
            }

            // Run initially
            fixLinks();
            if (chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        });

        // Speech Recognition
        let recognition;
        function startSpeechRecognition() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRecognition) {
                alert("Votre navigateur ne supporte pas la reconnaissance vocale.");
                return;
            }

            if (recognition && recognition.recording) {
                recognition.stop();
                return;
            }

            recognition = new SpeechRecognition();
            recognition.lang = 'fr-FR';
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;

            const micIcon = document.getElementById('mic-icon');
            const micBtn = document.getElementById('mic-btn');

            recognition.onstart = function() {
                recognition.recording = true;
                micBtn.classList.add('text-red-500', 'bg-red-500/10');
            };

            recognition.onend = function() {
                recognition.recording = false;
                micBtn.classList.remove('text-red-500', 'bg-red-500/10');
            };

            recognition.onresult = function(event) {
                const speechToText = event.results[0][0].transcript;
                const textarea = document.querySelector('textarea[wire:model\\.defer="messageInterne"]');
                if (textarea) {
                    textarea.value = speechToText;
                    textarea.dispatchEvent(new Event('input'));
                }
            };

            recognition.start();
        }

        // Text-to-Speech
        window.vocalActive = false;
        let spokenMessages = new Set();
        function toggleVocalRead() {
            window.vocalActive = !window.vocalActive;
            const speakerBtn = document.getElementById('speaker-btn');
            if (window.vocalActive) {
                speakerBtn.classList.add('text-green-500', 'bg-green-500/10');
            } else {
                speakerBtn.classList.remove('text-green-500', 'bg-green-500/10');
                window.speechSynthesis.cancel();
            }
        }

        function speakLastMessage(text, timeId) {
            if (!window.vocalActive) return;
            if (spokenMessages.has(timeId)) return;
            spokenMessages.add(timeId);

            let cleanText = text.replace(/\[CHART:.*?\]/g, '').replace(/<[^>]*>?/gm, '').trim();
            if (!cleanText) return;

            let utterance = new SpeechSynthesisUtterance(cleanText);
            utterance.lang = 'fr-FR';
            window.speechSynthesis.speak(utterance);
        }

        // Chart.js rendering
        function renderChartIfPresent(container) {
            const textNode = container.querySelector('.text-sm');
            if (!textNode) return;
            
            const html = textNode.innerHTML;
            const match = html.match(/\[CHART:(.*?)\]/);
            if (!match) return;

            try {
                const cleanJson = match[1].replace(/&quot;/g, '"').replace(/&amp;/g, '&');
                const chartData = JSON.parse(cleanJson);
                textNode.innerHTML = html.replace(/\[CHART:.*?\]/, '');

                const canvas = document.createElement('canvas');
                canvas.className = 'mt-4 rounded-xl bg-gray-900/60 p-4 border border-gray-800 max-w-full';
                canvas.style.maxHeight = '250px';
                container.appendChild(canvas);

                loadChartJs(function() {
                    new Chart(canvas, {
                        type: chartData.type || 'bar',
                        data: {
                            labels: chartData.labels,
                            datasets: chartData.datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    labels: { color: '#e5e7eb' }
                                }
                            },
                            scales: {
                                x: { ticks: { color: '#9ca3af' } },
                                y: { ticks: { color: '#9ca3af' } }
                            }
                        }
                    });
                });
            } catch (e) {
                console.error("Failed to render chart:", e);
            }
        }

        // Lazy load Chart.js
        function loadChartJs(callback) {
            if (window.Chart) {
                callback();
                return;
            }
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.onload = callback;
            document.head.appendChild(script);
        }
    </script>
</x-filament-panels::page>