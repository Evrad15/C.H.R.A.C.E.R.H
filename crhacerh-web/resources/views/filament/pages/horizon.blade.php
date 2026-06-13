<x-filament-panels::page>
<style>
    /* ── Animations ─────────────────────────────────────────── */
    @keyframes hGradShift {
        0%   { background-position:0% 50%; }
        50%  { background-position:100% 50%; }
        100% { background-position:0% 50%; }
    }
    @keyframes hPulse {
        0%,100% { transform:scale(1); opacity:1; }
        50%      { transform:scale(1.08); opacity:.85; }
    }
    @keyframes hFadeUp {
        from { opacity:0; transform:translateY(12px); }
        to   { opacity:1; transform:translateY(0); }
    }
    @keyframes hTyping {
        0%,80%,100% { transform:scale(0); opacity:.4; }
        40%          { transform:scale(1);   opacity:1; }
    }

    /* ── Layout ─────────────────────────────────────────────── */
    .h-shell {
        display:flex; flex-direction:column; height:calc(100vh - 10rem);
        min-height:540px; max-width:860px; margin:0 auto;
    }
    .h-messages {
        flex:1; overflow-y:auto; display:flex; flex-direction:column;
        gap:1.25rem; padding:.5rem 0; scrollbar-width:thin;
    }
    .h-messages::-webkit-scrollbar { width:4px; }
    .h-messages::-webkit-scrollbar-thumb { background:#e5e7eb; border-radius:9999px; }

    /* ── Welcome ─────────────────────────────────────────────── */
    .h-welcome { display:flex;flex-direction:column;align-items:center;padding:2rem 1rem;text-align:center; }
    .h-orb {
        width:68px; height:68px; border-radius:50%; margin-bottom:1.5rem;
        background:linear-gradient(135deg,#4C6EF5,#7C3AED,#EC4899,#F59E0B);
        background-size:300% 300%; animation:hGradShift 4s ease infinite, hPulse 3s ease-in-out infinite;
        display:flex;align-items:center;justify-content:center; font-size:1.75rem;
        box-shadow:0 8px 32px rgba(76,110,245,.35);
    }
    .h-gradient-text {
        background:linear-gradient(90deg,#4C6EF5,#7C3AED,#EC4899,#F59E0B);
        background-size:300% auto; -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        animation:hGradShift 5s ease infinite;
    }
    .h-suggestion {
        background:#fff; border:1px solid #EAECF0; border-radius:14px; padding:.85rem 1rem;
        cursor:pointer; text-align:left; width:100%; display:flex; align-items:flex-start; gap:.75rem;
        transition:border-color .2s,box-shadow .2s,transform .2s; box-shadow:0 1px 3px rgba(0,0,0,.04);
    }
    .h-suggestion:hover {
        border-color:#4C6EF5; box-shadow:0 4px 16px rgba(76,110,245,.12);
        transform:translateY(-2px);
    }
    .h-suggestion-icon {
        width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;
        font-size:1rem;flex-shrink:0;
    }

    /* ── Messages ─────────────────────────────────────────────── */
    .h-msg { animation:hFadeUp .35s ease-out; }
    .h-msg-ia {
        display:flex; align-items:flex-start; gap:.75rem; max-width:720px;
    }
    .h-msg-user {
        display:flex; align-items:flex-start; gap:.75rem; max-width:640px;
        margin-left:auto; flex-direction:row-reverse;
    }
    .h-avatar-ia {
        width:34px;height:34px;border-radius:50%;flex-shrink:0;
        background:linear-gradient(135deg,#4C6EF5,#7C3AED,#EC4899);
        display:flex;align-items:center;justify-content:center;font-size:.9rem;
        box-shadow:0 2px 8px rgba(76,110,245,.3);
    }
    .h-avatar-user {
        width:34px;height:34px;border-radius:50%;flex-shrink:0;
        background:#4C6EF5;display:flex;align-items:center;justify-content:center;
        font-size:.6rem;font-weight:800;color:#fff;
    }
    .h-bubble-ia {
        background:#fff;border:1px solid #EAECF0;border-radius:0 14px 14px 14px;
        padding:.85rem 1rem;box-shadow:0 1px 4px rgba(0,0,0,.05);
        font-size:.82rem;color:#101828;line-height:1.7;
        /* Markdown-rendered links, lists, bold */
    }
    .h-bubble-ia a { color:#4C6EF5;font-weight:600;text-decoration:underline; }
    .h-bubble-ia ul,
    .h-bubble-ia ol { padding-left:1.25rem;margin:.35rem 0; }
    .h-bubble-ia strong { font-weight:700; }
    .h-bubble-ia p { margin:.25rem 0; }
    .h-bubble-user {
        background:#4C6EF5;border-radius:14px 0 14px 14px;
        padding:.75rem 1rem;font-size:.82rem;color:#fff;line-height:1.6;
    }
    .h-time { font-size:.6rem;color:#98A2B3;margin-top:.3rem; }

    /* ── Chips ──────────────────────────────────────────────── */
    .h-chips { display:flex;flex-wrap:wrap;gap:.5rem;padding:.75rem 0 .25rem; }
    .h-chip {
        padding:.3rem .75rem;border-radius:9999px;font-size:.7rem;font-weight:600;
        cursor:pointer;border:1px solid;transition:background .15s,transform .1s;
    }
    .h-chip:hover { transform:scale(1.03); }
    .h-chip-blue   { background:#EEF2FF;color:#4338CA;border-color:#C7D2FE; }
    .h-chip-cyan   { background:#ECFEFF;color:#0E7490;border-color:#A5F3FC; }
    .h-chip-violet { background:#F5F3FF;color:#6D28D9;border-color:#DDD6FE; }
    .h-chip-green  { background:#ECFDF5;color:#065F46;border-color:#A7F3D0; }
    .h-chip-orange { background:#FFF7ED;color:#92400E;border-color:#FED7AA; }

    /* ── Input ──────────────────────────────────────────────── */
    .h-input-wrap {
        background:#fff;border:1.5px solid #EAECF0;border-radius:18px;
        padding:.85rem 1rem;box-shadow:0 2px 12px rgba(0,0,0,.06);
        transition:border-color .2s,box-shadow .2s;
    }
    .h-input-wrap:focus-within {
        border-color:#4C6EF5;box-shadow:0 2px 12px rgba(76,110,245,.15);
    }
    .h-textarea {
        width:100%;background:transparent;border:none;outline:none;resize:none;
        font-size:.84rem;color:#101828;line-height:1.6;
        font-family:inherit;box-shadow:none !important;
    }
    .h-send-btn {
        width:36px;height:36px;border-radius:50%;background:#4C6EF5;border:none;cursor:pointer;
        display:flex;align-items:center;justify-content:center;color:#fff;
        transition:background .15s,transform .15s;flex-shrink:0;
    }
    .h-send-btn:hover { background:#3B5BDB;transform:scale(1.05); }
    .h-icon-btn {
        width:32px;height:32px;border-radius:50%;border:none;cursor:pointer;background:transparent;
        display:flex;align-items:center;justify-content:center;color:#9CA3AF;
        transition:background .15s,color .15s;
    }
    .h-icon-btn:hover { background:#F3F4F6;color:#374151; }
    .h-file-preview {
        background:#EEF2FF;border:1px solid #C7D2FE;border-radius:10px;padding:.5rem .75rem;
        display:flex;align-items:center;gap:.5rem;font-size:.72rem;color:#4338CA;font-weight:600;
        margin-bottom:.75rem;
    }
</style>

<div class="h-shell">

    {{-- ─── CHAT AREA ─────────────────────────────────────── --}}
    <div class="h-messages" id="h-chat-box">

        @if(count($messages) <= 2)
        {{-- WELCOME --}}
        <div class="h-welcome h-msg">
            <div class="h-orb">✦</div>
            <h1 style="font-size:1.9rem;font-weight:900;letter-spacing:-.03em;margin-bottom:.4rem;">
                <span class="h-gradient-text">Bonjour, Administrateur</span>
            </h1>
            <p style="font-size:.85rem;color:#667085;max-width:480px;line-height:1.7;margin-bottom:2rem;">
                Je suis <strong>Horizon</strong>, l'assistant RH intelligent de C.H.R.A.C.E.R.H.<br>
                Que souhaitez-vous faire aujourd'hui ?
            </p>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;width:100%;max-width:560px;">

                <button wire:click="genererRapport('week')" class="h-suggestion" type="button">
                    <div class="h-suggestion-icon" style="background:#EEF2FF;">📈</div>
                    <div>
                        <div style="font-size:.8rem;font-weight:700;color:#111827;">Rapport hebdomadaire</div>
                        <div style="font-size:.68rem;color:#667085;margin-top:.15rem;">Bilan de la semaine en cours</div>
                    </div>
                </button>

                <button wire:click="genererRapport('day')" class="h-suggestion" type="button">
                    <div class="h-suggestion-icon" style="background:#ECFDF3;">📊</div>
                    <div>
                        <div style="font-size:.8rem;font-weight:700;color:#111827;">Rapport du jour</div>
                        <div style="font-size:.68rem;color:#667085;margin-top:.15rem;">Présences & absences aujourd'hui</div>
                    </div>
                </button>

                <button wire:click="detecterAnomalies('week')" class="h-suggestion" type="button">
                    <div class="h-suggestion-icon" style="background:#FEF3F2;">🚨</div>
                    <div>
                        <div style="font-size:.8rem;font-weight:700;color:#111827;">Détecter les anomalies</div>
                        <div style="font-size:.68rem;color:#667085;margin-top:.15rem;">Doublons, contradictions, retards</div>
                    </div>
                </button>

                <button wire:click="genererRapport('month')" class="h-suggestion" type="button">
                    <div class="h-suggestion-icon" style="background:#F5F3FF;">📅</div>
                    <div>
                        <div style="font-size:.8rem;font-weight:700;color:#111827;">Rapport mensuel</div>
                        <div style="font-size:.68rem;color:#667085;margin-top:.15rem;">Analyse globale du mois</div>
                    </div>
                </button>

            </div>
        </div>

        @else
        {{-- CONVERSATION --}}
        <div class="h-chips">
            <button wire:click="genererRapport('day')"   type="button" class="h-chip h-chip-blue">📊 Rapport jour</button>
            <button wire:click="genererRapport('week')"  type="button" class="h-chip h-chip-cyan">📈 Rapport semaine</button>
            <button wire:click="genererRapport('month')" type="button" class="h-chip h-chip-violet">📅 Rapport mois</button>
            <button wire:click="genererRapport('year')"  type="button" class="h-chip h-chip-green">🗓 Rapport année</button>
            <button wire:click="detecterAnomalies('week')" type="button" class="h-chip h-chip-orange">🚨 Anomalies</button>
        </div>

        @foreach($messages as $msg)
            @if($msg['sender'] === 'ia')
            @php
                $msgClean = addslashes(str_replace(["\r","\n"], ' ', strip_tags($msg['text'])));
                $msgHash  = md5($msg['text'].$msg['time']);
            @endphp
            <div class="h-msg h-msg-ia">
                <div class="h-avatar-ia">✦</div>
                <div style="flex:1;" x-init="renderChartIfPresent($el); speakLastMessage('{{ $msgClean }}', '{{ $msgHash }}');">
                    <div class="h-bubble-ia">
                        {!! \Illuminate\Support\Str::markdown($msg['text']) !!}
                    </div>
                    <div class="h-time">{{ $msg['time'] }}</div>
                </div>
            </div>
            @else
            <div class="h-msg h-msg-user">
                <div class="h-avatar-user">RH</div>
                <div>
                    <div class="h-bubble-user">{{ $msg['text'] }}</div>
                    <div class="h-time" style="text-align:right;">{{ $msg['time'] }}</div>
                </div>
            </div>
            @endif
        @endforeach

        @endif
    </div>

    {{-- ─── INPUT AREA ─────────────────────────────────────── --}}
    <div style="padding-top:.75rem;">
        <form wire:submit.prevent="envoyerMessage">
            <input type="file" wire:model="fileUpload" id="h-file-input" class="hidden" accept="image/*,application/pdf">

            @if($fileUpload)
            <div class="h-file-preview">
                <i class="fa-solid fa-paperclip"></i>
                {{ $fileUpload->getClientOriginalName() }}
                <span style="color:#818CF8;font-size:.65rem;">({{ number_format($fileUpload->getSize()/1024, 1) }} KB)</span>
                <button type="button" wire:click="$set('fileUpload', null)"
                    style="margin-left:auto;color:#F43F5E;font-size:.68rem;background:none;border:none;cursor:pointer;font-weight:700;">
                    ✕
                </button>
            </div>
            @endif

            <div class="h-input-wrap">
                <textarea
                    wire:model.defer="messageInterne"
                    rows="2"
                    class="h-textarea"
                    placeholder="Posez-moi une question sur les présences ou demandez un rapport..."
                    onkeydown="if(event.keyCode===13&&!event.shiftKey){event.preventDefault();@this.call('envoyerMessage');}"></textarea>

                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:.6rem;
                            padding-top:.6rem;border-top:1px solid #F3F4F6;">
                    <div style="display:flex;gap:.25rem;">
                        <button type="button" class="h-icon-btn" onclick="document.getElementById('h-file-input').click()" title="Joindre un fichier">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/>
                            </svg>
                        </button>
                        <button type="button" class="h-icon-btn" id="h-mic-btn" onclick="startSpeechRecognition()" title="Dictée vocale">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3-3.75h6M12 15.75a3 3 0 01-3-3V6a3 3 0 116 0v6.75a3 3 0 01-3 3z"/>
                            </svg>
                        </button>
                        <button type="button" class="h-icon-btn" id="h-speaker-btn" onclick="toggleVocalRead()" title="Lecture vocale">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z"/>
                            </svg>
                        </button>
                    </div>
                    <button type="submit" class="h-send-btn" title="Envoyer">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto-scroll
    function scrollBottom() {
        const box = document.getElementById('h-chat-box');
        if (box) box.scrollTop = box.scrollHeight;
    }
    scrollBottom();
    const observer = new MutationObserver(() => { scrollBottom(); fixLinks(); });
    const box = document.getElementById('h-chat-box');
    if (box) observer.observe(box, { childList:true, subtree:true });

    // Fix download links
    function fixLinks() {
        document.querySelectorAll('#h-chat-box a').forEach(a => {
            const href = a.getAttribute('href') || '';
            if (href.includes('/storage/reports/') || href.match(/\.(xlsx|pdf)$/i)) {
                if (!a.getAttribute('data-fixed')) {
                    a.setAttribute('download', '');
                    a.setAttribute('target', '_blank');
                    a.setAttribute('data-fixed', '1');
                }
            }
        });
    }
    fixLinks();
});

// Speech recognition
let recognition;
function startSpeechRecognition() {
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SR) { alert('Votre navigateur ne supporte pas la reconnaissance vocale.'); return; }
    if (recognition && recognition.recording) { recognition.stop(); return; }
    recognition = new SR();
    recognition.lang = 'fr-FR'; recognition.interimResults = false;
    const btn = document.getElementById('h-mic-btn');
    recognition.onstart  = () => { recognition.recording=true;  btn.style.color='#EF4444'; btn.style.background='#FEF2F2'; };
    recognition.onend    = () => { recognition.recording=false; btn.style.color=''; btn.style.background=''; };
    recognition.onresult = e => {
        const t = e.results[0][0].transcript;
        const ta = document.querySelector('textarea.h-textarea');
        if (ta) { ta.value=t; ta.dispatchEvent(new Event('input')); }
    };
    recognition.start();
}

// TTS
window.vocalActive = false;
let spokenSet = new Set();
function toggleVocalRead() {
    window.vocalActive = !window.vocalActive;
    const btn = document.getElementById('h-speaker-btn');
    if (window.vocalActive) { btn.style.color='#10B981'; btn.style.background='#ECFDF5'; }
    else { btn.style.color=''; btn.style.background=''; speechSynthesis.cancel(); }
}
function speakLastMessage(text, id) {
    if (!window.vocalActive || spokenSet.has(id)) return;
    spokenSet.add(id);
    const u = new SpeechSynthesisUtterance(text.replace(/<[^>]*>/g,'').trim());
    u.lang = 'fr-FR'; speechSynthesis.speak(u);
}

// Chart.js
function renderChartIfPresent(container) {
    const textEl = container.querySelector('.h-bubble-ia');
    if (!textEl) return;
    const match = textEl.innerHTML.match(/\[CHART:(.*?)\]/);
    if (!match) return;
    try {
        const data = JSON.parse(match[1].replace(/&quot;/g,'"').replace(/&amp;/g,'&'));
        textEl.innerHTML = textEl.innerHTML.replace(/\[CHART:.*?\]/, '');
        const canvas = document.createElement('canvas');
        canvas.style.cssText = 'margin-top:.75rem;max-height:220px;border-radius:12px;';
        container.appendChild(canvas);
        loadChartJs(() => new Chart(canvas, {
            type: data.type || 'bar',
            data: { labels:data.labels, datasets:data.datasets },
            options: { responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{ labels:{ color:'#667085', font:{size:11} } } },
                scales:{ x:{ticks:{color:'#98A2B3'}}, y:{ticks:{color:'#98A2B3'}} } }
        }));
    } catch(e) { console.error(e); }
}
function loadChartJs(cb) {
    if (window.Chart) { cb(); return; }
    const s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
    s.onload = cb; document.head.appendChild(s);
}
</script>
</x-filament-panels::page>