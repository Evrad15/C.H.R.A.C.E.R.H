<x-filament-panels::page>
<style>
    @keyframes rFadeIn { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
    .r-page { animation:rFadeIn .3s ease-out; display:flex; flex-direction:column; gap:2rem; }

    .r-card {
        background:#fff; border:1px solid #f1f5f9; border-radius:20px;
        box-shadow:0 1px 4px rgba(0,0,0,.04); transition:transform .22s,box-shadow .22s;
        overflow:hidden; position:relative;
    }
    .r-card:hover { transform:translateY(-3px); box-shadow:0 12px 32px rgba(0,0,0,.08) !important; }

    .r-kpi { display:flex; flex-direction:column; padding:1.25rem 1.5rem; justify-content:space-between; flex:1; min-height:140px; }
    .r-kpi-icon { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; }
    .r-kpi-title { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; }
    .r-kpi-val { font-size:2rem; font-weight:900; line-height:1; margin-top:.5rem; }
    
    .r-section-title { font-size:1.1rem; font-weight:800; color:#111827; display:flex; align-items:center; gap:.5rem; margin-bottom:1.25rem; }
    .r-section-title span { display:inline-block; width:6px; height:20px; border-radius:3px; }

    .r-list-item { 
        display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem; 
        padding:.75rem 1rem; border-radius:12px; border:1px solid transparent; 
        transition:background .2s, border .2s; margin-bottom:.5rem;
    }
    .r-list-item:hover { background:#f8fafc; border-color:#f1f5f9; }

    .r-badge { display:inline-block; padding:.2rem .6rem; border-radius:6px; font-size:.65rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; }

    /* Dark mode overrides (optional if user doesn't use it much, but good practice) */
    .dark .r-card { background:#1f2937; border-color:#374151; box-shadow:none; }
    .dark .r-section-title { color:#f9fafb; }
    .dark .r-kpi-title { color:#9ca3af; }
</style>

<div class="r-page">
    {{-- HEADER DATE --}}
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <div style="font-size:1.5rem; font-weight:900; color:#111827;" class="dark:text-white">Tableau de bord</div>
            <p style="font-size:.85rem; color:#6b7280; margin-top:.2rem;">
                Semaine {{ now()->weekOfYear }} • {{ now()->safeTranslatedFormat('l j F Y') }}
            </p>
        </div>
    </div>

    {{-- KPI ROW --}}
    <div style="display:flex; flex-wrap:wrap; gap:1.25rem;">
        {{-- KPI: Employés --}}
        <div class="r-card r-kpi">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div class="r-kpi-title">Total Employés</div>
                <div class="r-kpi-icon" style="background:#e0e7ff; color:#4338ca;">👥</div>
            </div>
            <div>
                <div class="r-kpi-val text-gray-900 dark:text-white">{{ $this->totalEmployes }}</div>
                <div style="font-size:.7rem; color:#6b7280; margin-top:.4rem; font-weight:500;">Effectif global actif</div>
            </div>
        </div>

        {{-- KPI: Absents --}}
        <div class="r-card r-kpi">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div class="r-kpi-title">Absents (Jour)</div>
                <div class="r-kpi-icon" style="background:#fee2e2; color:#b91c1c;">❌</div>
            </div>
            <div>
                <div style="display:flex; align-items:baseline; gap:.75rem;">
                    <div class="r-kpi-val" style="color:#ef4444;">{{ $this->absentsSemaine }}</div>
                    @if($this->tendanceAbsents >= 0)
                        <span style="font-size:.7rem; font-weight:800; color:#ef4444; background:#fef2f2; padding:2px 6px; border-radius:4px;">↑ +{{ $this->tendanceAbsents }}</span>
                    @else
                        <span style="font-size:.7rem; font-weight:800; color:#22c55e; background:#f0fdf4; padding:2px 6px; border-radius:4px;">↓ {{ abs($this->tendanceAbsents) }}</span>
                    @endif
                </div>
                <div style="font-size:.7rem; color:#6b7280; margin-top:.4rem; font-weight:500;">vs jour précédent</div>
            </div>
            <div style="position:absolute; bottom:0; left:0; right:0; height:4px; background:#ef4444;"></div>
        </div>

        {{-- KPI: Retards --}}
        <div class="r-card r-kpi">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div class="r-kpi-title">En Retard (Jour)</div>
                <div class="r-kpi-icon" style="background:#fef3c7; color:#b45309;">⏰</div>
            </div>
            <div>
                <div style="display:flex; align-items:baseline; gap:.75rem;">
                    <div class="r-kpi-val" style="color:#f59e0b;">{{ $this->retardsSemaine }}</div>
                    @if($this->tendanceRetards >= 0)
                        <span style="font-size:.7rem; font-weight:800; color:#f59e0b; background:#fffbeb; padding:2px 6px; border-radius:4px;">↑ +{{ $this->tendanceRetards }}</span>
                    @else
                        <span style="font-size:.7rem; font-weight:800; color:#22c55e; background:#f0fdf4; padding:2px 6px; border-radius:4px;">↓ {{ abs($this->tendanceRetards) }}</span>
                    @endif
                </div>
                <div style="font-size:.7rem; color:#6b7280; margin-top:.4rem; font-weight:500;">vs jour précédent</div>
            </div>
            <div style="position:absolute; bottom:0; left:0; right:0; height:4px; background:#f59e0b;"></div>
        </div>

        {{-- KPI: A l'heure --}}
        <div class="r-card r-kpi">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div class="r-kpi-title">À l'Heure (Jour)</div>
                <div class="r-kpi-icon" style="background:#dcfce7; color:#15803d;">✅</div>
            </div>
            <div>
                <div style="display:flex; align-items:baseline; gap:.75rem;">
                    <div class="r-kpi-val" style="color:#22c55e;">{{ $this->aLHeureSemaine }}</div>
                    @if($this->tendanceALHeure >= 0)
                        <span style="font-size:.7rem; font-weight:800; color:#22c55e; background:#f0fdf4; padding:2px 6px; border-radius:4px;">↑ +{{ $this->tendanceALHeure }}</span>
                    @else
                        <span style="font-size:.7rem; font-weight:800; color:#ef4444; background:#fef2f2; padding:2px 6px; border-radius:4px;">↓ {{ abs($this->tendanceALHeure) }}</span>
                    @endif
                </div>
                <div style="font-size:.7rem; color:#6b7280; margin-top:.4rem; font-weight:500;">vs jour précédent</div>
            </div>
            <div style="position:absolute; bottom:0; left:0; right:0; height:4px; background:#22c55e;"></div>
        </div>
    </div>

    {{-- GRAPHIQUE --}}
    <div class="r-card" style="padding:1.5rem;">
        <h3 class="r-section-title">
            <span style="background:#6366f1;"></span> Évolution des Présences (Semaine en cours)
        </h3>
        
        <div style="display:flex; gap:1.5rem; margin-bottom:1rem; font-size:.75rem; font-weight:600;">
            <div style="display:flex; align-items:center; gap:.4rem;"><div style="width:10px;height:10px;border-radius:3px;background:#22c55e;"></div> Présents</div>
            <div style="display:flex; align-items:center; gap:.4rem;"><div style="width:10px;height:10px;border-radius:3px;background:#f59e0b;"></div> Retards</div>
            <div style="display:flex; align-items:center; gap:.4rem;"><div style="width:10px;height:10px;border-radius:3px;background:#ef4444;"></div> Absents</div>
        </div>

        <div style="height:260px; position:relative; width:100%;">
            <canvas id="presenceWeekChart"></canvas>
        </div>
    </div>

    {{-- CHART SCRIPT --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" defer></script>
    <script>
        (function () {
            var chartData = @json($this->chartData);
            function initChart() {
                var canvas = document.getElementById('presenceWeekChart');
                if (!canvas) { return; }
                if (typeof Chart === 'undefined') { setTimeout(initChart, 100); return; }

                var isDark = document.documentElement.classList.contains('dark');
                var gridColor  = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.04)';
                var labelColor = isDark ? '#9ca3af' : '#6b7280';

                new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: chartData.labels || [],
                        datasets: [
                            {
                                label: 'Présents', data: chartData.assidus || [],
                                borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.05)',
                                tension: 0.4, fill: true, borderWidth: 3, pointRadius: 4, pointHoverRadius: 6, pointBackgroundColor: '#fff', pointBorderColor: '#22c55e', pointBorderWidth: 2
                            },
                            {
                                label: 'Retards', data: chartData.retards || [],
                                borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.05)',
                                tension: 0.4, fill: true, borderWidth: 3, pointRadius: 4, pointHoverRadius: 6, pointBackgroundColor: '#fff', pointBorderColor: '#f59e0b', pointBorderWidth: 2
                            },
                            {
                                label: 'Absents', data: chartData.absences || [],
                                borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.05)',
                                tension: 0.4, fill: true, borderWidth: 3, pointRadius: 4, pointHoverRadius: 6, pointBackgroundColor: '#fff', pointBorderColor: '#ef4444', pointBorderWidth: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { display: false }, tooltip: { padding: 12, cornerRadius: 8 } },
                        scales: {
                            x: { grid: { color: gridColor, drawBorder: false }, ticks: { color: labelColor, font: { size: 11, family: 'Inter' } } },
                            y: { beginAtZero: true, grid: { color: gridColor, drawBorder: false }, ticks: { color: labelColor, font: { size: 11, family: 'Inter' }, stepSize: 1 } }
                        }
                    }
                });
            }
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initChart);
            else initChart();
        })();
    </script>

    {{-- ROW 3: Anomalies, Risques, Taux --}}
    <div style="display:flex; flex-wrap:wrap; gap:1.25rem; align-items:stretch;">
        
        {{-- ANOMALIES --}}
        <div class="r-card" style="flex:1; min-width:280px; display:flex; flex-direction:column; padding:1.5rem;">
            <h3 class="r-section-title"><span style="background:#ef4444; animation:rFadeIn 1s infinite alternate;"></span> Anomalies de la Semaine</h3>
            
            <div style="flex:1; overflow-y:auto; max-height:300px; padding-right:.5rem;">
                @php $hasAnomalies = false; @endphp

                @if(!empty($this->anomaliesReport['contradictions']))
                    @php $hasAnomalies = true; @endphp
                    @foreach($this->anomaliesReport['contradictions'] as $item)
                        <div class="r-list-item" style="background:#fef2f2;">
                            <div>
                                <div style="font-size:.75rem; font-weight:800; color:#b91c1c;">Contradiction statut</div>
                                <div style="font-size:.85rem; font-weight:700; color:#111827; margin:.2rem 0;">{{ $item['nom'] }}</div>
                                <div style="font-size:.65rem; color:#6b7280;">Le {{ $item['date'] }} — {{ implode(', ', $item['status']) }}</div>
                            </div>
                            <div class="r-badge" style="background:#fee2e2; color:#991b1b;">Critique</div>
                        </div>
                    @endforeach
                @endif

                @if(!empty($this->anomaliesReport['duplicates']))
                    @php $hasAnomalies = true; @endphp
                    @foreach($this->anomaliesReport['duplicates'] as $item)
                        <div class="r-list-item" style="background:#fffbeb;">
                            <div>
                                <div style="font-size:.75rem; font-weight:800; color:#b45309;">Double pointage</div>
                                <div style="font-size:.85rem; font-weight:700; color:#111827; margin:.2rem 0;">{{ $item['nom'] }}</div>
                                <div style="font-size:.65rem; color:#6b7280;">Le {{ $item['date'] }} — {{ $item['compte'] }} pointages</div>
                            </div>
                            <div class="r-badge" style="background:#fef3c7; color:#92400e;">Suspect</div>
                        </div>
                    @endforeach
                @endif

                @if(!empty($this->anomaliesReport['late_streaks']))
                    @php $hasAnomalies = true; @endphp
                    @foreach($this->anomaliesReport['late_streaks'] as $item)
                        <div class="r-list-item" style="background:#eff6ff;">
                            <div>
                                <div style="font-size:.75rem; font-weight:800; color:#1d4ed8;">Retards répétitifs</div>
                                <div style="font-size:.85rem; font-weight:700; color:#111827; margin:.2rem 0;">{{ $item['nom'] }}</div>
                                <div style="font-size:.65rem; color:#6b7280;">{{ $item['retards'] }} retards cette semaine</div>
                            </div>
                            <div class="r-badge" style="background:#dbeafe; color:#1e40af;">Alerte</div>
                        </div>
                    @endforeach
                @endif

                @if(!$hasAnomalies)
                    <div style="padding:3rem 1rem; text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%;">
                        <div style="font-size:2.5rem; margin-bottom:.5rem;">✨</div>
                        <div style="font-weight:700; font-size:.85rem; color:#111827;">Aucune anomalie !</div>
                        <div style="font-size:.7rem; color:#6b7280; margin-top:.2rem;">Tout est parfait cette semaine.</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- RISQUES --}}
        <div class="r-card" style="flex:1.2; min-width:300px; display:flex; flex-direction:column; padding:1.5rem;">
            <h3 class="r-section-title"><span style="background:#8b5cf6;"></span> Risques d'Absences (30j)</h3>
            
            <div style="flex:1; overflow-y:auto; max-height:300px; padding-right:.5rem;">
                @forelse($this->absenteeismRisks as $risk)
                    @php
                        $bg = 'bg-gray-50'; $bdgBg = '#f3f4f6'; $bdgCol = '#374151';
                        if($risk['score'] >= 60) { $bg = 'bg-red-50'; $bdgBg = '#fee2e2'; $bdgCol = '#991b1b'; }
                        elseif($risk['score'] >= 30) { $bg = 'bg-orange-50'; $bdgBg = '#ffedd5'; $bdgCol = '#c2410c'; }
                    @endphp
                    <div class="r-list-item {{ $bg }}">
                        <div>
                            <div style="font-size:.85rem; font-weight:800; color:#111827;">{{ $risk['nom'] }}</div>
                            <div style="font-size:.7rem; font-weight:600; color:#6b7280; margin-top:.15rem;">{{ $risk['departement'] }}</div>
                            <div style="font-size:.65rem; color:#9ca3af; margin-top:.25rem; font-style:italic;">{{ $risk['raisons'] }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div class="r-badge" style="background:{{ $bdgBg }}; color:{{ $bdgCol }};">{{ $risk['score'] }}%</div>
                            <div style="font-size:.6rem; font-weight:700; text-transform:uppercase; color:#9ca3af; margin-top:.3rem;">{{ $risk['niveau'] }}</div>
                        </div>
                    </div>
                @empty
                    <div style="padding:3rem 1rem; text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%;">
                        <div style="font-size:2.5rem; margin-bottom:.5rem;">✅</div>
                        <div style="font-weight:700; font-size:.85rem; color:#111827;">Aucun risque majeur détecté</div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- TAUX --}}
        <div class="r-card" style="flex:0.8; min-width:240px; display:flex; flex-direction:column; align-items:center; padding:1.5rem; justify-content:center;">
            <h3 class="r-section-title" style="margin-bottom:0;"><span style="background:#14b8a6;"></span> Taux Présence</h3>
            
            <div style="position:relative; width:140px; height:140px; display:flex; align-items:center; justify-content:center; margin:2rem 0;">
                <svg style="width:100%; height:100%; transform:rotate(-90deg);" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#f1f5f9" stroke-width="2.5"></circle>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#14b8a6" stroke-width="2.5"
                        stroke-dasharray="{{ $this->tauxPresenceGlobal }}, 100" stroke-linecap="round"
                        style="transition:stroke-dasharray 1s ease-out;"></circle>
                </svg>
                <div style="position:absolute; text-align:center;">
                    <div style="font-size:2rem; font-weight:900; color:#111827;">{{ $this->tauxPresenceGlobal }}<span style="font-size:1.2rem;">%</span></div>
                </div>
            </div>
            
            <div style="font-size:.75rem; font-weight:600; color:#6b7280; background:#f8fafc; padding:.4rem 1rem; border-radius:10px;">
                Objectif : <span style="font-weight:800; color:#14b8a6;">95%</span>
            </div>
        </div>
    </div>

    {{-- CLASSEMENTS --}}
    <h3 class="r-section-title" style="margin-top:1rem; margin-bottom:0;"><span style="background:#3b82f6;"></span> Classement Général des Employés</h3>
    
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:1.25rem;">
        
        {{-- Top Absents --}}
        <div class="r-card" style="padding:1.5rem;">
            <div style="font-size:.75rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#b91c1c; display:flex; align-items:center; gap:.5rem; margin-bottom:1.25rem;">
                <div style="width:24px;height:24px;background:#fee2e2;border-radius:6px;display:flex;align-items:center;justify-content:center;">🔻</div> Top 5 Absents
            </div>
            <div style="display:flex; flex-direction:column; gap:.75rem;">
                @forelse($this->topAbsents as $index => $row)
                    <div style="display:flex; align-items:center; gap:.75rem;">
                        <div style="width:24px; height:24px; border-radius:50%; background:#fef2f2; color:#ef4444; font-size:.65rem; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            {{ $index + 1 }}
                        </div>
                        <div style="flex:1; font-size:.85rem; font-weight:700; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            {{ $row->employe->full_name ?? 'Inconnu' }}
                        </div>
                        <div class="r-badge" style="background:#fee2e2; color:#991b1b;">{{ $row->total }} j</div>
                    </div>
                @empty
                    <div style="font-size:.75rem; color:#9ca3af; text-align:center; padding:1rem 0;">Aucune donnée</div>
                @endforelse
            </div>
        </div>

        {{-- Top Retards --}}
        <div class="r-card" style="padding:1.5rem;">
            <div style="font-size:.75rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#b45309; display:flex; align-items:center; gap:.5rem; margin-bottom:1.25rem;">
                <div style="width:24px;height:24px;background:#fef3c7;border-radius:6px;display:flex;align-items:center;justify-content:center;">⏰</div> Top 5 Retards
            </div>
            <div style="display:flex; flex-direction:column; gap:.75rem;">
                @forelse($this->topRetards as $index => $row)
                    <div style="display:flex; align-items:center; gap:.75rem;">
                        <div style="width:24px; height:24px; border-radius:50%; background:#fffbeb; color:#f59e0b; font-size:.65rem; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            {{ $index + 1 }}
                        </div>
                        <div style="flex:1; font-size:.85rem; font-weight:700; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            {{ $row->employe->full_name ?? 'Inconnu' }}
                        </div>
                        <div class="r-badge" style="background:#fef3c7; color:#92400e;">{{ $row->total }} j</div>
                    </div>
                @empty
                    <div style="font-size:.75rem; color:#9ca3af; text-align:center; padding:1rem 0;">Aucune donnée</div>
                @endforelse
            </div>
        </div>

        {{-- Top Assidus --}}
        <div class="r-card" style="padding:1.5rem;">
            <div style="font-size:.75rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#15803d; display:flex; align-items:center; gap:.5rem; margin-bottom:1.25rem;">
                <div style="width:24px;height:24px;background:#dcfce7;border-radius:6px;display:flex;align-items:center;justify-content:center;">🏆</div> Top 5 Assidus
            </div>
            <div style="display:flex; flex-direction:column; gap:.75rem;">
                @forelse($this->topAssidus as $index => $row)
                    <div style="display:flex; align-items:center; gap:.75rem;">
                        <div style="width:24px; height:24px; border-radius:50%; background:#f0fdf4; color:#22c55e; font-size:.65rem; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            {{ $index + 1 }}
                        </div>
                        <div style="flex:1; font-size:.85rem; font-weight:700; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            {{ $row->employe->full_name ?? 'Inconnu' }}
                        </div>
                        <div class="r-badge" style="background:#dcfce7; color:#166534;">{{ $row->total }} j</div>
                    </div>
                @empty
                    <div style="font-size:.75rem; color:#9ca3af; text-align:center; padding:1rem 0;">Aucune donnée</div>
                @endforelse
            </div>
        </div>

    </div>

</div>
</x-filament-panels::page>