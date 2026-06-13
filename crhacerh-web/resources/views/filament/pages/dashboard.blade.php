<x-filament-panels::page>
    <div class="mb-6">
        <p class="text-gray-500 dark:text-gray-400 mt-2">
            Semaine {{ now()->weekOfYear }} • {{ now()->translatedFormat('l j F Y') }}
        </p>
    </div>

    <div class="mb-8" style="width: 100%;">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-1 h-6 bg-primary-500 rounded-full"></span>
            Indicateurs Clés
        </h2>

        <div style="display: flex; flex-direction: row; justify-content: space-between; gap: 1rem; width: 100%;">

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition-shadow duration-300" style="flex: 1;">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Total Employés
                    </span>
                    <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-lg">
                        👥
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $this->totalEmployes }}
                    </p>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Effectif global actif</p>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-red-200 dark:border-red-900/30 rounded-lg p-6 hover:shadow-lg transition-shadow duration-300 border-l-4 border-l-red-500" style="flex: 1;">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Absents (Jour)
                    </span>
                    <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-lg">
                        ❌
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-red-600 dark:text-red-400">
                        {{ $this->absentsSemaine }}
                    </p>
                    @if($this->tendanceAbsents >= 0)
                    <span class="text-xs font-bold text-red-600 dark:text-red-400">
                        ↑ +{{ $this->tendanceAbsents }}
                    </span>
                    @else
                    <span class="text-xs font-bold text-green-600 dark:text-green-400">
                        ↓ {{ abs($this->tendanceAbsents) }}
                    </span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">vs jour précédent</p>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-amber-200 dark:border-amber-900/30 rounded-lg p-6 hover:shadow-lg transition-shadow duration-300 border-l-4 border-l-amber-500" style="flex: 1;">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        En Retard (Jour)
                    </span>
                    <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-lg">
                        ⏰
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">
                        {{ $this->retardsSemaine }}
                    </p>
                    @if($this->tendanceRetards >= 0)
                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400">
                        ↑ +{{ $this->tendanceRetards }}
                    </span>
                    @else
                    <span class="text-xs font-bold text-green-600 dark:text-green-400">
                        ↓ {{ abs($this->tendanceRetards) }}
                    </span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">vs jour précédent</p>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-green-200 dark:border-green-900/30 rounded-lg p-6 hover:shadow-lg transition-shadow duration-300 border-l-4 border-l-green-500" style="flex: 1;">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        À l'Heure (Jour)
                    </span>
                    <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-lg">
                        ✅
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                        {{ $this->aLHeureSemaine }}
                    </p>
                    @if($this->tendanceALHeure >= 0)
                    <span class="text-xs font-bold text-green-600 dark:text-green-400">
                        ↑ +{{ $this->tendanceALHeure }}
                    </span>
                    @else
                    <span class="text-xs font-bold text-red-600 dark:text-red-400">
                        ↓ {{ abs($this->tendanceALHeure) }}
                    </span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">vs jour précédent</p>
            </div>

        </div>
    </div>

    <div class="lg:col-span-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 flex flex-col">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-1 h-4 bg-primary-500 rounded-full"></span>
            Évolution des Présences (Semaine en cours)
        </h3>

        {{-- Légende --}}
        <div class="flex items-center gap-6 mb-4 text-xs font-medium">
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-3 h-3 rounded-full" style="background:#22c55e;"></span>
                <span class="text-gray-600 dark:text-gray-300">Présents</span>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-3 h-3 rounded-full" style="background:#f59e0b;"></span>
                <span class="text-gray-600 dark:text-gray-300">Retards</span>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-3 h-3 rounded-full" style="background:#ef4444;"></span>
                <span class="text-gray-600 dark:text-gray-300">Absents</span>
            </span>
        </div>

        <div class="relative" style="height: 256px;">
            <canvas id="presenceWeekChart"></canvas>
        </div>
    </div>

    {{-- Chart.js (CDN) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" defer></script>
    <script>
        (function () {
            var chartData = @json($this->chartData);

            function initChart() {
                var canvas = document.getElementById('presenceWeekChart');
                if (!canvas) { return; }
                if (typeof Chart === 'undefined') {
                    setTimeout(initChart, 100);
                    return;
                }

                // detect dark mode
                var isDark = document.documentElement.classList.contains('dark');
                var gridColor  = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.07)';
                var labelColor = isDark ? '#9ca3af' : '#6b7280';

                new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: chartData.labels || [],
                        datasets: [
                            {
                                label: 'Présents',
                                data: chartData.assidus || [],
                                borderColor: '#22c55e',
                                backgroundColor: 'rgba(34,197,94,0.12)',
                                pointBackgroundColor: '#22c55e',
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                tension: 0.4,
                                fill: true,
                                borderWidth: 2,
                            },
                            {
                                label: 'Retards',
                                data: chartData.retards || [],
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245,158,11,0.10)',
                                pointBackgroundColor: '#f59e0b',
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                tension: 0.4,
                                fill: true,
                                borderWidth: 2,
                            },
                            {
                                label: 'Absents',
                                data: chartData.absences || [],
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239,68,68,0.10)',
                                pointBackgroundColor: '#ef4444',
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                tension: 0.4,
                                fill: true,
                                borderWidth: 2,
                            },
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: isDark ? '#1f2937' : '#ffffff',
                                titleColor: isDark ? '#f3f4f6' : '#111827',
                                bodyColor: isDark ? '#d1d5db' : '#374151',
                                borderColor: isDark ? '#374151' : '#e5e7eb',
                                borderWidth: 1,
                                padding: 10,
                                callbacks: {
                                    label: function(ctx) {
                                        return '  ' + ctx.dataset.label + ' : ' + ctx.parsed.y;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: gridColor },
                                ticks: { color: labelColor, font: { size: 12 } }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: labelColor, font: { size: 12 }, stepSize: 1 }
                            }
                        }
                    }
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initChart);
            } else {
                initChart();
            }
        })();
    </script>

    <div class="mb-8" style="width: 100%;">
        <div style="display: flex; flex-direction: row; gap: 1.5rem; width: 100%; items: stretch;">

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition-shadow duration-300" style="flex: 1; min-height: 380px; display: flex; flex-direction: column; justify-content: space-between;">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="inline-block w-3 h-3 rounded-full bg-red-500 animate-pulse"></span>
                    Anomalies de la Semaine
                </h3>

                <div class="space-y-2 overflow-y-auto pr-1" style="flex: 1; max-height: 260px;">
                    @php
                        $hasAnomalies = false;
                    @endphp

                    {{-- 1. Contradictions --}}
                    @if(!empty($this->anomaliesReport['contradictions']))
                        @php $hasAnomalies = true; @endphp
                        @foreach($this->anomaliesReport['contradictions'] as $item)
                        <div class="p-3 rounded-lg bg-red-50/50 dark:bg-red-950/10 border border-red-200/30 dark:border-red-900/30 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-red-800 dark:text-red-400">
                                        Contradiction statut
                                    </p>
                                    <p class="text-xs text-gray-755 dark:text-gray-300 font-semibold mt-1">
                                        {{ $item['nom'] }}
                                    </p>
                                    <p class="text-[9px] text-gray-500 mt-1">
                                        Le {{ $item['date'] }} — Statuts : {{ implode(', ', $item['status']) }}
                                    </p>
                                </div>
                                <span class="flex-shrink-0 inline-block px-2 py-0.5 rounded text-[9px] font-bold bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-400 uppercase">
                                    Critique
                                </span>
                            </div>
                        </div>
                        @endforeach
                    @endif

                    {{-- 2. Multiples Pointages --}}
                    @if(!empty($this->anomaliesReport['duplicates']))
                        @php $hasAnomalies = true; @endphp
                        @foreach($this->anomaliesReport['duplicates'] as $item)
                        <div class="p-3 rounded-lg bg-amber-50/50 dark:bg-amber-950/10 border border-amber-200/30 dark:border-amber-900/30 hover:bg-amber-50 dark:hover:bg-amber-950/20 transition-colors">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-amber-800 dark:text-amber-400">
                                        Double pointage
                                    </p>
                                    <p class="text-xs text-gray-755 dark:text-gray-300 font-semibold mt-1">
                                        {{ $item['nom'] }}
                                    </p>
                                    <p class="text-[9px] text-gray-500 mt-1">
                                        Le {{ $item['date'] }} — {{ $item['compte'] }} pointages enregistrés.
                                    </p>
                                </div>
                                <span class="flex-shrink-0 inline-block px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-400 uppercase">
                                    Suspect
                                </span>
            </div>
                        </div>
                        @endforeach
                    @endif

                    {{-- 3. Séries de retards --}}
                    @if(!empty($this->anomaliesReport['late_streaks']))
                        @php $hasAnomalies = true; @endphp
                        @foreach($this->anomaliesReport['late_streaks'] as $item)
                        <div class="p-3 rounded-lg bg-blue-50/50 dark:bg-blue-950/10 border border-blue-200/30 dark:border-blue-900/30 hover:bg-blue-50 dark:hover:bg-blue-950/20 transition-colors">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-blue-800 dark:text-blue-400">
                                        Retards répétitifs
                                    </p>
                                    <p class="text-xs text-gray-755 dark:text-gray-300 font-semibold mt-1">
                                        {{ $item['nom'] }}
                                    </p>
                                    <p class="text-[9px] text-gray-500 mt-1">
                                        {{ $item['retards'] }} retards enregistrés cette semaine.
                                    </p>
                                </div>
                                <span class="flex-shrink-0 inline-block px-2 py-0.5 rounded text-[9px] font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-400 uppercase">
                                    Alerte
                                </span>
                            </div>
                        </div>
                        @endforeach
                    @endif

                    @if(!$hasAnomalies)
                    <div class="flex flex-col items-center justify-center h-full text-center py-8">
                        <p class="text-2xl mb-2">✨</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Aucune anomalie détectée cette semaine !</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Prédiction des Risques d'Absences -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition-shadow duration-300" style="flex: 1.2; min-height: 380px; display: flex; flex-direction: column; justify-content: space-between;">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="text-lg">🔮</span>
                    Risques d'Absences (30j)
                </h3>

                <div class="space-y-2 overflow-y-auto pr-1" style="flex: 1; max-height: 260px;">
                    @forelse($this->absenteeismRisks as $risk)
                        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900/40 border border-gray-150 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-900/80 transition-colors">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-gray-900 dark:text-white truncate">
                                        {{ $risk['nom'] }}
                                    </p>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                        {{ $risk['departement'] }}
                                    </p>
                                    <p class="text-[9px] text-gray-400 dark:text-gray-500 mt-1 italic">
                                        {{ $risk['raisons'] }}
                                    </p>
                                </div>
                                <div class="text-right flex-shrink-0 flex flex-col items-end">
                                    <span class="inline-block px-2 py-0.5 rounded text-[9px] font-bold 
                                        @if($risk['score'] >= 60) bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-400
                                        @elseif($risk['score'] >= 30) bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-400
                                        @else bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-400
                                        @endif uppercase">
                                        {{ $risk['score'] }}%
                                    </span>
                                    <span class="block text-[8px] text-gray-500 dark:text-gray-400 mt-1 uppercase font-semibold">
                                        {{ $risk['niveau'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full text-center py-8">
                            <p class="text-2xl mb-2">✅</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Aucun risque modéré/élevé détecté !</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition-shadow duration-300" style="flex: 0.8; min-height: 380px; display: flex; flex-direction: column; items: center; justify-content: space-between;">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 text-center">
                    <span class="text-lg">📈</span>
                    <span class="block text-sm mt-1">Taux Présence</span>
                </h3>

                <div class="relative w-36 h-36 flex items-center justify-center my-auto">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="currentColor" stroke-width="2.5" class="text-gray-200 dark:text-gray-700" />
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-dasharray="{{ $this->tauxPresenceGlobal }}, 100"
                            stroke-linecap="round" class="text-primary-500 transition-all duration-1000" />
                    </svg>
                    <div class="absolute text-center">
                        <div class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ $this->tauxPresenceGlobal }}%
                        </div>
                    </div>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400 text-center font-medium mt-4">
                    Objectif: <span class="font-bold text-primary-600 dark:text-primary-400">95%</span>
                </p>
            </div>

        </div>
    </div>
    <div class="w-full">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-1 h-6 bg-primary-500 rounded-full"></span>
            Classement Général des Employés
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full mt-6">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm min-h-[320px] flex flex-col">
                <h4 class="text-sm font-bold text-red-600 dark:text-red-400 uppercase tracking-wider mb-5 flex items-center gap-2">
                    🔻 Top 5 Absents
                </h4>
                <ol class="space-y-4 flex-1">
                    @forelse($this->topAbsents as $index => $row)
                    <li class="flex items-center gap-4 py-1">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <span class="text-xs font-bold text-red-700 dark:text-red-400">{{ $index + 1 }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                {{ $row->employe->full_name ?? 'Inconnu' }}
                            </p>
                        </div>
                        <span class="flex-shrink-0 inline-block px-2.5 py-1 rounded-full bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-xs font-bold">
                            {{ $row->total }} j
                        </span>
                    </li>
                    @empty
                    <div class="flex items-center justify-center h-full text-center">
                        <p class="text-sm text-gray-400 py-4">Aucune donnée</p>
                    </div>
                    @endforelse
                </ol>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm min-h-[320px] flex flex-col">
                <h4 class="text-sm font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-5 flex items-center gap-2">
                    ⏰ Top 5 Retards
                </h4>
                <ol class="space-y-4 flex-1">
                    @forelse($this->topRetards as $index => $row)
                    <li class="flex items-center gap-4 py-1">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <span class="text-xs font-bold text-amber-700 dark:text-amber-400">{{ $index + 1 }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                {{ $row->employe->full_name ?? 'Inconnu' }}
                            </p>
                        </div>
                        <span class="flex-shrink-0 inline-block px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 text-xs font-bold">
                            {{ $row->total }} j
                        </span>
                    </li>
                    @empty
                    <div class="flex items-center justify-center h-full text-center">
                        <p class="text-sm text-gray-400 py-4">Aucune donnée</p>
                    </div>
                    @endforelse
                </ol>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm min-h-[320px] flex flex-col">
                <h4 class="text-sm font-bold text-green-600 dark:text-green-400 uppercase tracking-wider mb-5 flex items-center gap-2">
                    🏆 Top 5 Assidus
                </h4>
                <ol class="space-y-4 flex-1">
                    @forelse($this->topAssidus as $index => $row)
                    <li class="flex items-center gap-4 py-1">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <span class="text-xs font-bold text-green-700 dark:text-green-400">{{ $index + 1 }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                {{ $row->employe->full_name ?? 'Inconnu' }}
                            </p>
                        </div>
                        <span class="flex-shrink-0 inline-block px-2.5 py-1 rounded-full bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-xs font-bold">
                            {{ $row->total }} j
                        </span>
                    </li>
                    @empty
                    <div class="flex items-center justify-center h-full text-center">
                        <p class="text-sm text-gray-400 py-4">Aucune donnée</p>
                    </div>
                    @endforelse
                </ol>
            </div>
        </div>
    </div>
</x-filament-panels::page>