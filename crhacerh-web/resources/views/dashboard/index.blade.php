@extends('layouts.dashboard')

@section('title', 'Tableau de bord | C.H.R.A.C.E.R.H')

@section('custom_styles')
<style>
    .page-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 1.25rem;
    }

    .card-box {
        background: #FFFFFF;
        border-radius: 20px;
        padding: 1.5rem;
        border: 1px solid rgba(226, 231, 231, 0.6);
        box-shadow: var(--shadow-subtle);
    }

    .card-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .card-heading {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .badge-stat {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.8rem;
        font-weight: 700;
        margin-top: 0.3rem;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
    }

    .badge-stat.trend-up {
        color: #0E9F2D;
        background: rgba(22, 222, 66, 0.12);
    }

    .badge-stat.trend-down {
        color: var(--accent-red);
        background: rgba(242, 56, 58, 0.12);
    }

    .btn-rapport {
        background: #FBFCFE;
        border: 1px solid #DDE4F0;
        color: var(--primary);
        padding: 0.45rem 1.25rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-rapport:hover {
        background: var(--primary);
        color: #FFFFFF;
        border-color: var(--primary);
    }

    .chart-subtitle {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-bottom: 0.8rem;
    }

    .chart-container {
        position: relative;
        height: 220px;
        width: 100%;
    }

    /* Grille d'alignement horizontal inférieur (Pointages du jour + Présences du jour côte à côte) */
    .bottom-aligned-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 1.75rem;
        align-items: stretch;
    }

    /* Section Pointages du jour avec défilement (5 lignes visibles) */
    .table-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.8rem;
    }

    .table-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .table-scroll-body {
        max-height: 275px; /* Affiche 5 lignes et permet le défilement */
        overflow-y: auto;
        padding-right: 0.25rem;
    }

    /* Scrollbar personnalisée */
    .table-scroll-body::-webkit-scrollbar {
        width: 5px;
    }
    .table-scroll-body::-webkit-scrollbar-track {
        background: #F1F2F7;
        border-radius: 8px;
    }
    .table-scroll-body::-webkit-scrollbar-thumb {
        background: #C8CBD9;
        border-radius: 8px;
    }

    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.5rem;
    }

    .custom-table th {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--text-dark);
        text-align: left;
        padding: 0.5rem 0.75rem;
        border-bottom: 2px solid var(--text-dark);
        position: sticky;
        top: 0;
        background: #FFFFFF;
        z-index: 10;
    }

    .custom-table td {
        background: #F1F2F7;
        padding: 0.65rem 0.75rem;
        font-size: 0.85rem;
        color: var(--text-dark);
        font-weight: 500;
    }

    .custom-table tr td:first-child {
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }

    .custom-table tr td:last-child {
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }

    .employee-cell {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .emp-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #DEE1F4;
        color: var(--primary);
        font-weight: 700;
        font-size: 0.78rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .badge-type {
        display: inline-block;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .badge-entree {
        background: rgba(22, 222, 66, 0.15);
        color: #0E9F2D;
    }

    .badge-sortie {
        background: rgba(242, 56, 58, 0.15);
        color: var(--accent-red);
    }

    .badge-status {
        display: inline-block;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .status-ok {
        background: rgba(22, 222, 66, 0.12);
        color: #0E9F2D;
    }

    .status-retard {
        background: rgba(255, 155, 67, 0.15);
        color: #D97300;
    }

    .status-absent {
        background: rgba(242, 56, 58, 0.15);
        color: var(--accent-red);
    }

    /* Donut Card */
    .date-sub {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 0.2rem;
    }

    .icon-btn-square {
        width: 32px;
        height: 32px;
        background: #F1F2F7;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-dark);
        cursor: pointer;
        font-size: 0.9rem;
    }

    .donut-wrapper {
        position: relative;
        height: 190px;
        width: 190px;
        margin: 1rem auto;
    }

    .donut-center-box {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    .donut-center-text {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--text-dark);
        line-height: 1;
    }

    .donut-center-label {
        font-size: 0.72rem;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        margin-top: 0.25rem;
    }

    .legend-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
        margin-top: 1rem;
        text-align: center;
    }

    .legend-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.2rem;
    }

    .legend-label-row {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.78rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .dot-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .dot-green { background: var(--accent-green); }
    .dot-orange { background: var(--accent-orange); }
    .dot-red { background: var(--accent-red); }

    .legend-val {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-dark);
    }
</style>
@endsection

@section('content')
<h1 class="page-title">Tableau de bord</h1>

<!-- Rangée Supérieure : Graphique Hebdomadaire (7 Jours Lundi à Dimanche) -->
<div class="card-box" style="margin-bottom: 1.75rem;">
    <div class="card-header-flex">
        <div>
            <h2 class="card-heading">Présences (Semaine en cours)</h2>
            <div class="badge-stat {{ $isTrendPositive ? 'trend-up' : 'trend-down' }}">
                <i class="bi {{ $isTrendPositive ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                <span>{{ $trendPercent >= 0 ? '+' : '' }}{{ $trendPercent }}% vs semaine dernière</span>
            </div>
        </div>
        <a href="/ressources-humaines/rapports" class="btn-rapport">Rapport</a>
    </div>

    <div class="chart-subtitle">Données hebdomadaires d'assiduité</div>

    <div class="chart-container">
        <canvas id="weeklyPresenceChart"></canvas>
    </div>
</div>

<!-- Rangée Inférieure Alignée Horizontalement : Pointages du jour + Présences du jour -->
<div class="bottom-aligned-grid">
    <!-- Gauche : Carte Tableau "Pointages du jour" -->
    <div class="card-box">
        <div class="table-header-flex">
            <h2 class="table-title">Pointages du jour</h2>
            <span style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600;">
                <i class="bi bi-arrows-vertical"></i> Défiler pour voir la suite
            </span>
        </div>

        <div class="table-scroll-body">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Employés</th>
                        <th>Départements</th>
                        <th>Heure</th>
                        <th>Entrée/Sortie</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPointages as $p)
                    <tr>
                        <td>
                            <div class="employee-cell">
                                <div class="emp-avatar">
                                    {{ strtoupper(substr($p->employee->first_name ?? 'E', 0, 1) . substr($p->employee->last_name ?? 'M', 0, 1)) }}
                                </div>
                                <span>{{ $p->employee->first_name ?? 'Agent' }} {{ $p->employee->last_name ?? '' }}</span>
                            </div>
                        </td>
                        <td>{{ $p->employee->department->name ?? 'Général' }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->timestamp)->format('H:i:s') }}</td>
                        <td>
                            <span class="badge-type {{ $p->type === 'ENTREE' ? 'badge-entree' : 'badge-sortie' }}">
                                {{ $p->type }}
                            </span>
                        </td>
                        <td>
                            @php
                                $st = strtolower($p->status ?? '');
                                $isRetard = $st === 'retard' || $st === 'en retard';
                                $isAbsent = $st === 'absent';
                            @endphp
                            <span class="badge-status {{ $isRetard ? 'status-retard' : ($isAbsent ? 'status-absent' : 'status-ok') }}">
                                {{ $isRetard ? 'En retard' : ($isAbsent ? 'Absent' : 'A l\'heure') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted);">
                            Aucun pointage enregistré aujourd'hui.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Droite : Carte Graphique Circulaire "Présences du jour" -->
    <div class="card-box">
        <div class="card-header-flex" style="margin-bottom: 0;">
            <div>
                <h2 class="card-heading">Présences du jour</h2>
                <div class="date-sub">{{ $analysisDate->locale('fr')->isoFormat('DD MMMM YYYY') }}</div>
            </div>
            <div class="icon-btn-square" title="Détails">
                <i class="bi bi-eye-fill"></i>
            </div>
        </div>

        <div class="donut-wrapper">
            <canvas id="dailyDonutChart"></canvas>
            <div class="donut-center-box">
                <div class="donut-center-text">{{ $totalEmployes }}</div>
                <div class="donut-center-label">Total Employés</div>
            </div>
        </div>

        <div class="legend-grid">
            <div class="legend-item">
                <div class="legend-label-row">
                    <span class="dot-indicator dot-green"></span>
                    <span>Présents</span>
                </div>
                <span class="legend-val">{{ $presentsCount }}</span>
            </div>
            <div class="legend-item">
                <div class="legend-label-row">
                    <span class="dot-indicator dot-orange"></span>
                    <span>Retard</span>
                </div>
                <span class="legend-val">{{ $retardsCount }}</span>
            </div>
            <div class="legend-item">
                <div class="legend-label-row">
                    <span class="dot-indicator dot-red"></span>
                    <span>Absences</span>
                </div>
                <span class="legend-val">{{ $absentsCount }}</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // --- 1. Multi-Line Chart (Présences Semaine en cours 7 jours incluant week-end) ---
        const weeklyCtx = document.getElementById('weeklyPresenceChart').getContext('2d');

        const weeklyLabels = {!! json_encode(array_column($weeklyData, 'day')) !!};
        const presentsData = {!! json_encode(array_column($weeklyData, 'presents')) !!};
        const retardsData = {!! json_encode(array_column($weeklyData, 'retards')) !!};
        const absentsData = {!! json_encode(array_column($weeklyData, 'absents')) !!};

        new Chart(weeklyCtx, {
            type: 'line',
            data: {
                labels: weeklyLabels,
                datasets: [
                    {
                        label: 'Présents',
                        data: presentsData,
                        borderColor: '#16DE42',
                        backgroundColor: 'transparent',
                        borderWidth: 2.5,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Retard',
                        data: retardsData,
                        borderColor: '#FF9B43',
                        backgroundColor: 'transparent',
                        borderWidth: 2.5,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Absences',
                        data: absentsData,
                        borderColor: '#F2383A',
                        backgroundColor: 'transparent',
                        borderWidth: 2.5,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#1F384C',
                        titleFont: { family: 'Outfit', size: 12 },
                        bodyFont: { family: 'Outfit', size: 12 },
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: '#737B8B',
                            font: { family: 'Outfit', size: 11, weight: '600' }
                        }
                    },
                    y: {
                        display: true,
                        grid: { color: 'rgba(200, 203, 217, 0.35)', borderDash: [4, 4] },
                        border: { display: false },
                        title: {
                            display: true,
                            text: "Nombre d'employés",
                            color: '#737B8B',
                            font: { family: 'Outfit', size: 11, weight: '600' }
                        },
                        ticks: {
                            color: '#737B8B',
                            font: { family: 'Outfit', size: 11, weight: '600' },
                            stepSize: 10,
                            beginAtZero: true
                        }
                    }
                }
            }
        });


        // --- 2. Donut Chart (Présences du jour avec Total Employés au centre) ---
        const donutCtx = document.getElementById('dailyDonutChart').getContext('2d');

        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Présents', 'Retard', 'Absences'],
                datasets: [{
                    data: [{{ $presentsCount }}, {{ $retardsCount }}, {{ $absentsCount }}],
                    backgroundColor: ['#16DE42', '#FF9B43', '#F2383A'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '76%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1F384C',
                        bodyFont: { family: 'Outfit', size: 12 }
                    }
                }
            }
        });

    });
</script>
@endsection
