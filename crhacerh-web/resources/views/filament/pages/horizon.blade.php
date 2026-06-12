<x-filament-panels::page>
    <div class="w-full max-w-4xl mx-auto flex flex-col justify-between" style="height: calc(100vh - 12rem); min-height: 550px;">

        <div class="flex-1 overflow-y-auto px-2 py-4 space-y-6" id="chat-box" style="box-shadow: none !important; border: none !important;">
            <div class="flex flex-wrap gap-2 mb-4">
                <button wire:click="genererRapport('day')" type="button" class="rounded-full bg-blue-600 px-3 py-1 text-xs font-semibold text-white hover:bg-blue-500 transition">Rapport jour</button>
                <button wire:click="genererRapport('week')" type="button" class="rounded-full bg-cyan-600 px-3 py-1 text-xs font-semibold text-white hover:bg-cyan-500 transition">Rapport semaine</button>
                <button wire:click="genererRapport('month')" type="button" class="rounded-full bg-violet-600 px-3 py-1 text-xs font-semibold text-white hover:bg-violet-500 transition">Rapport mois</button>
                <button wire:click="genererRapport('year')" type="button" class="rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-500 transition">Rapport année</button>
                <button wire:click="detecterAnomalies('week')" type="button" class="rounded-full bg-orange-600 px-3 py-1 text-xs font-semibold text-white hover:bg-orange-500 transition">Détecter anomalies</button>
            </div>
            @foreach($messages as $msg)
            @if($msg['sender'] === 'ia')
            <div class="flex items-start gap-4 max-w-3xl">
                <div class="w-8 h-8 flex-shrink-0 rounded-full bg-gradient-to-tr from-blue-600 to-cyan-500 flex items-center justify-center text-sm shadow-md">
                    ✨
                </div>
                <div class="flex-1 pt-1" x-init="renderChartIfPresent($el); speakLastMessage('{{ addslashes(str_replace([&quot;\r&quot;, &quot;\n&quot;], ' ', strip_tags($msg['text']))) }}', '{{ md5($msg['text'] . $msg['time']) }}');">
                    <div class="text-sm font-medium text-gray-200 leading-relaxed [&_a]:text-blue-400 [&_a]:underline [&_a]:font-bold [&_ul]:list-disc [&_ul]:ml-4 [&_ol]:list-decimal [&_ol]:ml-4">
                        {!! \Illuminate\Support\Str::markdown($msg['text']) !!}
                    </div>
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
                <input type="file" wire:model="fileUpload" id="file-upload-input" class="hidden" accept="image/*,application/pdf">

                @if($fileUpload)
                <div class="mb-2 flex items-center justify-between bg-gray-800/60 rounded-xl p-2 border border-gray-700/50">
                    <div class="flex items-center gap-2 text-xs text-gray-300">
                        <span>📎 {{ $fileUpload->getClientOriginalName() }}</span>
                        <span class="text-[10px] text-gray-400">({{ number_format($fileUpload->getSize() / 1024, 1) }} KB)</span>
                    </div>
                    <button type="button" wire:click="$set('fileUpload', null)" class="text-xs text-red-400 hover:text-red-300">Supprimer</button>
                </div>
                @endif

                <textarea
                    wire:model.defer="messageInterne"
                    rows="2"
                    placeholder="Comment puis-je vous aider ?"
                    class="w-full bg-transparent p-0 text-sm text-gray-100 placeholder-gray-500 resize-none block w-full"
                    style="border: none !important; outline: none !important; box-shadow: none !important; --tw-ring-color: transparent !important; ring: none !important;"
                    onkeydown="if(event.keyCode === 13 && !event.shiftKey) { event.preventDefault(); @this.call('envoyerMessage'); }"></textarea>

                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-800/40">

                    <div class="flex items-center gap-3 text-gray-400">
                        <button type="button" onclick="document.getElementById('file-upload-input').click()" class="p-1.5 rounded-full hover:bg-gray-800 text-gray-400 hover:text-gray-200 transition-colors" style="border: none !important;" title="Joindre un justificatif (Image/PDF)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375 0 11-.75 0 .375 0 01.75 0z"></path>
                            </svg>
                        </button>

                        <button type="button" id="mic-btn" onclick="startSpeechRecognition()" class="p-1.5 rounded-full hover:bg-gray-800 text-gray-400 hover:text-red-500 transition-colors" style="border: none !important;" title="Dicter le message">
                            <svg id="mic-icon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3-3.75h6M12 15.75a3 3 0 01-3-3V6a3 3 0 116 0v6.75a3 3 0 01-3 3z"></path>
                            </svg>
                        </button>

                        <button type="button" id="speaker-btn" onclick="toggleVocalRead()" class="p-1.5 rounded-full hover:bg-gray-800 text-gray-400 hover:text-green-500 transition-colors" style="border: none !important;" title="Lecture vocale active/désactive">
                            <svg id="speaker-icon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z"></path>
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
                });
                observer.observe(chatBox, { childList: true, subtree: true });
            }

            // Run initially
            fixLinks();
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