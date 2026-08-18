@extends('layouts.dashboard')

@section('title', 'Rendez-vous | C.H.R.A.C.E.R.H')

@section('custom_styles')
<style>
    .page-header-flex {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        justify-content: space-between !important;
        align-items: center !important;
        width: 100% !important;
        margin-bottom: 2.25rem !important;
        padding-top: 0.25rem;
    }

    .page-title {
        font-size: 1.55rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
        white-space: nowrap;
    }

    .btn-create-rdv {
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.55rem !important;
        background: var(--primary) !important;
        color: #FFFFFF !important;
        padding: 0.7rem 1.35rem !important;
        border-radius: 12px !important;
        font-weight: 600 !important;
        font-size: 0.92rem !important;
        text-decoration: none !important;
        border: none !important;
        cursor: pointer !important;
        white-space: nowrap !important;
        box-shadow: 0 4px 14px rgba(58, 91, 240, 0.28) !important;
        transition: background 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease !important;
    }
    .btn-create-rdv:hover {
        background: #2a48d8 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 18px rgba(58, 91, 240, 0.38) !important;
    }

    /* Top Stats Grid */
    .rdv-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 1.75rem;
    }

    .rdv-stat-card {
        background: #FFFFFF;
        border-radius: 14px;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .rdv-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    }

    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }
    .icon-total { background: rgba(58, 91, 240, 0.12); color: var(--primary); }
    .icon-confirme { background: rgba(22, 222, 66, 0.15); color: #0E9F2D; }
    .icon-realise { background: rgba(14, 159, 45, 0.15); color: #0E9F2D; }
    .icon-annule { background: rgba(242, 56, 58, 0.12); color: var(--accent-red); }

    .stat-info .stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-dark);
        line-height: 1.1;
    }
    .stat-info .stat-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-top: 0.2rem;
    }

    /* Toolbar & Search */
    .toolbar-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .left-tools {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .filter-select {
        padding: 0.5rem 0.85rem;
        border-radius: 10px;
        border: 1px solid #E2E7E7;
        background: #FFFFFF;
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--text-dark);
        cursor: pointer;
        outline: none;
        transition: border 0.2s ease;
    }
    .filter-select:focus {
        border-color: var(--primary);
    }

    .inpage-search-box {
        position: relative;
        display: flex;
        align-items: center;
    }
    .inpage-search-box input {
        width: 280px;
        padding: 0.55rem 1rem 0.55rem 2.4rem;
        border-radius: 10px;
        border: 1px solid #E2E7E7;
        background: #FFFFFF;
        font-size: 0.88rem;
        outline: none;
        transition: border 0.2s ease, width 0.2s ease;
    }
    .inpage-search-box input:focus {
        border-color: var(--primary);
        width: 320px;
    }
    .inpage-search-box i.bi-search {
        position: absolute;
        left: 0.85rem;
        color: var(--text-muted);
        font-size: 0.95rem;
    }

    /* Table Container */
    .table-container {
        background: #FFFFFF;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        padding: 1.25rem;
    }

    .rdv-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.4rem;
    }

    .rdv-table th {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.75rem 1rem;
        border-bottom: 2px solid #F1F2F7;
        text-align: left;
    }

    .rdv-table tbody tr {
        background: #FFFFFF;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .rdv-table tbody tr:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    .rdv-table td {
        padding: 0.85rem 1rem;
        font-size: 0.9rem;
        color: var(--text-dark);
        border-top: 1px solid #F6F7FB;
        border-bottom: 1px solid #F6F7FB;
    }
    .rdv-table td:first-child {
        border-left: 1px solid #F6F7FB;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
    }
    .rdv-table td:last-child {
        border-right: 1px solid #F6F7FB;
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }

    .patient-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .patient-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3A5BF0, #6C88FF);
        color: #FFFFFF;
        font-weight: 700;
        font-size: 0.88rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .patient-name {
        font-weight: 700;
        color: var(--text-dark);
    }
    .patient-dossier {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    /* Badges de Statut */
    .badge-statut {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.7rem;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
    }
    .statut-planifie { background: rgba(58, 91, 240, 0.12); color: var(--primary); }
    .statut-confirme { background: rgba(22, 222, 66, 0.15); color: #0E9F2D; }
    .statut-realise  { background: rgba(14, 159, 45, 0.2); color: #07691C; }
    .statut-annule   { background: rgba(242, 56, 58, 0.12); color: var(--accent-red); }
    .statut-reporte  { background: rgba(255, 155, 67, 0.15); color: #D97300; }

    /* Actions buttons */
    .action-btn-group {
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
    .btn-action-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        background: #F1F2F7;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.9rem;
        transition: background 0.2s ease, color 0.2s ease;
    }
    .btn-action-icon:hover {
        background: var(--primary);
        color: #FFFFFF;
    }

    /* Pagination */
    .pagination-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(226, 231, 231, 0.8);
    }
    .pagination-info {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 500;
    }
    .custom-pagination {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .custom-pagination li a,
    .custom-pagination li span {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 0.5rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-dark);
        background: #F1F2F7;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .custom-pagination li.active span {
        background: var(--primary);
        color: #FFFFFF;
    }
    .custom-pagination li.disabled span {
        opacity: 0.4;
        cursor: not-allowed;
    }

    /* Modal Styling */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(31, 56, 76, 0.4);
        backdrop-filter: blur(3px);
        z-index: 1050;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }
    .modal-backdrop.show {
        opacity: 1;
        pointer-events: auto;
    }

    .modal-card {
        background: #FFFFFF;
        border-radius: 18px;
        width: 520px;
        max-width: 90%;
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        transform: translateY(20px);
        transition: transform 0.25s ease;
        overflow: hidden;
    }
    .modal-backdrop.show .modal-card {
        transform: translateY(0);
    }

    .modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #E8EBF2;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }
    .modal-close {
        background: #F1F2F7;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }
    .form-label {
        display: block;
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 0.35rem;
    }
    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 0.6rem 0.85rem;
        border-radius: 10px;
        border: 1px solid #E2E7E7;
        font-size: 0.88rem;
        outline: none;
        transition: border 0.2s ease;
        box-sizing: border-box;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        border-color: var(--primary);
    }
    .form-row {
        display: flex;
        gap: 1rem;
    }
    .form-row .form-group {
        flex: 1;
    }

    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #E8EBF2;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }
    .btn-cancel {
        background: #F1F2F7;
        color: var(--text-dark);
        padding: 0.6rem 1.25rem;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-submit {
        background: var(--primary);
        color: #FFFFFF;
        padding: 0.6rem 1.25rem;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        cursor: pointer;
    }

    /* Drawer Overlay & Panel */
    .drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(31, 56, 76, 0.35);
        backdrop-filter: blur(2px);
        z-index: 999;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    .drawer-overlay.open { opacity: 1; pointer-events: auto; }

    .drawer-panel {
        position: fixed;
        top: 0; right: 0; bottom: 0;
        width: 480px;
        max-width: 92vw;
        background: #FFFFFF;
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: -8px 0 30px rgba(0, 0, 0, 0.12);
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .drawer-panel::-webkit-scrollbar {
        display: none;
    }
    .drawer-panel.open { transform: translateX(0); }

    .drawer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #E8EBF2;
        background: #FFFFFF;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .drawer-close {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: #F1F2F7;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1.1rem;
        color: var(--text-dark);
        transition: background 0.2s;
    }
    .drawer-close:hover {
        background: #DEE1F4;
    }

    .drawer-body {
        padding: 1.5rem !important;
        flex: 1;
        box-sizing: border-box !important;
    }
</style>
@endsection

@section('content')
<!-- Header Page -->
<div class="page-header-flex">
    <h1 class="page-title">Rendez-vous Hospitaliers</h1>
    <button class="btn-create-rdv" id="btnOpenModal">
        <i class="bi bi-plus-lg"></i>
        <span>Nouveau Rendez-vous</span>
    </button>
</div>

<!-- Top Stats Cards -->
<div class="rdv-stats-grid">
    <div class="rdv-stat-card">
        <div class="stat-icon-wrapper icon-total">
            <i class="bi bi-calendar-event"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $statsToday['total'] }}</div>
            <div class="stat-label">Aujourd'hui (Total)</div>
        </div>
    </div>
    <div class="rdv-stat-card">
        <div class="stat-icon-wrapper icon-confirme">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $statsToday['confirme'] }}</div>
            <div class="stat-label">Confirmés</div>
        </div>
    </div>
    <div class="rdv-stat-card">
        <div class="stat-icon-wrapper icon-realise">
            <i class="bi bi-check-all"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $statsToday['realise'] }}</div>
            <div class="stat-label">Réalisés (Honorés)</div>
        </div>
    </div>
    <div class="rdv-stat-card">
        <div class="stat-icon-wrapper icon-annule">
            <i class="bi bi-x-circle"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $statsToday['annule'] }}</div>
            <div class="stat-label">Annulés / Reportés</div>
        </div>
    </div>
</div>

<!-- Toolbar (Filtres et Recherche) -->
<div class="toolbar-flex">
    <div class="left-tools">
        <!-- Filtre par Statut -->
        <select class="filter-select" onchange="applyFilters()" id="filterStatut">
            <option value="">Tous les statuts</option>
            <option value="Planifié" {{ request('statut') == 'Planifié' ? 'selected' : '' }}>Planifié</option>
            <option value="Confirmé" {{ request('statut') == 'Confirmé' ? 'selected' : '' }}>Confirmé</option>
            <option value="Réalisé" {{ request('statut') == 'Réalisé' ? 'selected' : '' }}>Réalisé</option>
            <option value="Annulé" {{ request('statut') == 'Annulé' ? 'selected' : '' }}>Annulé</option>
            <option value="Reporté" {{ request('statut') == 'Reporté' ? 'selected' : '' }}>Reporté</option>
        </select>

        <!-- Filtre par Service -->
        <select class="filter-select" onchange="applyFilters()" id="filterService">
            <option value="">Tous les services</option>
            @foreach($services as $serv)
            <option value="{{ $serv }}" {{ request('service') == $serv ? 'selected' : '' }}>{{ $serv }}</option>
            @endforeach
        </select>

        <!-- Filtre par Période -->
        <select class="filter-select" onchange="applyFilters()" id="filterPeriode">
            <option value="">Toutes les dates</option>
            <option value="today" {{ request('periode') == 'today' ? 'selected' : '' }}>Aujourd'hui</option>
            <option value="upcoming" {{ request('periode') == 'upcoming' ? 'selected' : '' }}>À venir</option>
            <option value="past" {{ request('periode') == 'past' ? 'selected' : '' }}>Passés</option>
        </select>
    </div>

    <!-- Recherche Textuelle -->
    <form action="{{ route('rendezvous.index') }}" method="GET" class="inpage-search-box" id="searchForm">
        @if(request('statut')) <input type="hidden" name="statut" value="{{ request('statut') }}"> @endif
        @if(request('service')) <input type="hidden" name="service" value="{{ request('service') }}"> @endif
        @if(request('periode')) <input type="hidden" name="periode" value="{{ request('periode') }}"> @endif
        
        <i class="bi bi-search"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher patient, médecin...">
    </form>
</div>

<!-- Tableau des Rendez-vous -->
<div class="table-container">
    <table class="rdv-table">
        <thead>
            <tr>
                <th style="width: 25%;">Patient</th>
                <th style="width: 22%;">Service / Motif</th>
                <th style="width: 18%;">Date & Heure</th>
                <th style="width: 18%;">Médecin</th>
                <th style="width: 17%;">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rendezvous as $rdv)
            @php
                $patientName = $rdv->patient ? $rdv->patient->nom_complet : ($rdv->nom_patient ?? 'Patient Inconnu');
                $patientDossier = $rdv->patient->numero_dossier ?? 'N/A';
                $rdvPayload = [
                    'id' => $rdv->id,
                    'nom_patient' => $patientName,
                    'telephone_patient' => $rdv->telephone_patient ?? ($rdv->patient->telephone ?? ''),
                    'email_patient' => $rdv->email_patient ?? '',
                    'service' => $rdv->service ?? 'Général',
                    'motif' => $rdv->motif ?? '',
                    'date_rendez_vous' => $rdv->date_rendez_vous ? \Carbon\Carbon::parse($rdv->date_rendez_vous)->toIso8601String() : '',
                    'medecin' => $rdv->medecin ?? 'Généraliste',
                    'statut' => $rdv->statut ?? 'Planifié',
                    'pointage_at' => $rdv->pointage_at ? \Carbon\Carbon::parse($rdv->pointage_at)->toIso8601String() : null,
                    'notes' => $rdv->notes ?? '',
                    'patient' => [
                        'nom_complet' => $patientName,
                        'numero_dossier' => $patientDossier,
                    ],
                ];
            @endphp
            <tr class="rdv-clickable-row" data-rdv="{{ base64_encode(json_encode($rdvPayload)) }}" style="cursor: pointer;">
                <td>
                    <div class="patient-info">
                        <div class="patient-avatar">
                            {{ strtoupper(substr($patientName, 0, 1)) }}
                        </div>
                        <div>
                            <div class="patient-name">{{ $patientName }}</div>
                            <div class="patient-dossier">{{ $patientDossier }} | {{ $rdv->telephone_patient ?? '—' }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="font-weight:600; color:var(--text-dark);">{{ $rdv->service }}</div>
                    <div style="font-size:0.78rem; color:var(--text-muted);">{{ Str::limit($rdv->motif, 35) }}</div>
                </td>
                <td>
                    <div style="font-weight:600; color:var(--text-dark);">
                        <i class="bi bi-calendar3" style="color:var(--primary); margin-right:0.3rem;"></i>
                        {{ \Carbon\Carbon::parse($rdv->date_rendez_vous)->format('d/m/Y') }}
                    </div>
                    <div style="font-size:0.8rem; color:var(--text-muted); margin-top:0.1rem;">
                        <i class="bi bi-clock" style="margin-right:0.2rem;"></i>
                        {{ \Carbon\Carbon::parse($rdv->date_rendez_vous)->format('H:i') }}
                    </div>
                </td>
                <td>
                    <div style="font-weight:600; color:var(--text-dark);">{{ $rdv->medecin ?? '—' }}</div>
                </td>
                <td>
                    @php
                        $badgeClass = match($rdv->statut) {
                            'Planifié' => 'statut-planifie',
                            'Confirmé' => 'statut-confirme',
                            'Réalisé'  => 'statut-realise',
                            'Annulé'   => 'statut-annule',
                            'Reporté'  => 'statut-reporte',
                            default    => 'statut-planifie',
                        };
                    @endphp
                    <span class="badge-statut {{ $badgeClass }}">
                        <i class="bi bi-circle-fill" style="font-size:0.45rem;"></i>
                        {{ $rdv->statut }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; color:var(--text-muted); padding:2.5rem;">
                    <i class="bi bi-calendar-x" style="font-size:2rem; display:block; margin-bottom:0.5rem; opacity:0.5;"></i>
                    Aucun rendez-vous trouvé.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination sur-mesure -->
    @if($rendezvous->hasPages())
    <div class="pagination-wrapper">
        <div class="pagination-info">
            Affichage de <strong>{{ $rendezvous->firstItem() }}</strong> à <strong>{{ $rendezvous->lastItem() }}</strong> sur <strong>{{ $rendezvous->total() }}</strong> rendez-vous
        </div>

        <ul class="custom-pagination">
            @if ($rendezvous->onFirstPage())
                <li class="disabled"><span><i class="bi bi-chevron-left"></i></span></li>
            @else
                <li><a href="{{ $rendezvous->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a></li>
            @endif

            @foreach ($rendezvous->getUrlRange(1, $rendezvous->lastPage()) as $page => $url)
                @if ($page == $rendezvous->currentPage())
                    <li class="active"><span>{{ $page }}</span></li>
                @else
                    <li><a href="{{ $url }}">{{ $page }}</a></li>
                @endif
            @endforeach

            @if ($rendezvous->hasMorePages())
                <li><a href="{{ $rendezvous->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a></li>
            @else
                <li class="disabled"><span><i class="bi bi-chevron-right"></i></span></li>
            @endif
        </ul>
    </div>
    @endif
</div>

<!-- DRAWER COULISSANT : NOUVEAU RENDEZ-VOUS -->
<div class="drawer-overlay" id="createRdvDrawerOverlay"></div>
<div class="drawer-panel" id="createRdvDrawerPanel" style="width:480px;">
    <div class="drawer-header">
        <span style="font-weight: 700; color: var(--text-dark); font-size:1.1rem;">Créer un Nouveau Rendez-vous</span>
        <button type="button" class="drawer-close" id="btnCloseCreateRdvDrawer">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <form action="{{ route('rendezvous.store') }}" method="POST" class="drawer-body">
        @csrf
        <div style="display:flex; flex-direction:column; gap:1.1rem;">

            <div>
                <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Nom et Prénom du Patient *</label>
                <input type="text" name="nom_patient" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" placeholder="ex: BILOUNGA Chantal" required>
            </div>

            <div style="display:flex; gap:0.85rem;">
                <div style="flex:1;">
                    <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Téléphone</label>
                    <input type="text" name="telephone_patient" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" placeholder="+237 699 00 11 22">
                </div>
                <div style="flex:1;">
                    <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Email</label>
                    <input type="email" name="email_patient" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" placeholder="patient@gmail.com">
                </div>
            </div>

            <div style="display:flex; gap:0.85rem;">
                <div style="flex:1;">
                    <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Service Médical *</label>
                    <select name="service" class="form-select" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" required>
                        @foreach($services as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1;">
                    <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Médecin Attribué</label>
                    <select name="medecin" class="form-select" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;">
                        @foreach($medecins as $m)
                        <option value="{{ $m }}">{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display:flex; gap:0.85rem;">
                <div style="flex:1;">
                    <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Date du Rendez-vous *</label>
                    <input type="date" name="date_rendez_vous" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" value="{{ date('Y-m-d') }}" required>
                </div>
                <div style="flex:1;">
                    <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Heure *</label>
                    <input type="time" name="heure_rendez_vous" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" value="09:00" required>
                </div>
            </div>

            <div>
                <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Motif de la Consultation *</label>
                <input type="text" name="motif" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" placeholder="ex: Échographie de contrôle T2" required>
            </div>

            <!-- MODES D'ENREGISTREMENT BIOMÉTRIQUE PATIENT -->
            <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:14px; padding:1.1rem; margin-top:0.3rem;">
                <div style="font-size:0.88rem; font-weight:700; color:var(--text-dark); margin-bottom:0.85rem; display:flex; align-items:center; gap:0.5rem;">
                    <i class="bi bi-shield-lock-fill" style="color:var(--primary);"></i>
                    <span>Modes d'Enregistrement / Pointage Biométrique (Patient)</span>
                </div>

                <!-- Mode 1: Empreinte Digitale Patient -->
                <div style="background:#FFFFFF; border:1px solid #E2E7E7; border-radius:10px; padding:0.85rem; margin-bottom:0.75rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                        <div style="font-weight:700; font-size:0.84rem; color:var(--text-dark); display:flex; align-items:center; gap:0.4rem;">
                            <i class="bi bi-fingerprint" style="color:var(--primary); font-size:1.1rem;"></i>
                            <span>Mode 1 : Empreinte Digitale</span>
                        </div>
                        <span id="badgeRdvFp" style="font-size:0.72rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:6px; background:#F1F2F7; color:var(--text-muted);">Non enregistrée</span>
                    </div>
                    <input type="hidden" name="register_fingerprint" id="inputRdvFp" value="">
                    <button type="button" id="btnToggleRdvFp" style="width:100%; padding:0.5rem; border-radius:8px; border:1px solid var(--primary); background:#EBF3FF; color:var(--primary); font-weight:700; font-size:0.8rem; cursor:pointer; transition:all 0.2s ease;">
                        <i class="bi bi-plus-circle"></i> Capturer l'Empreinte (Lecteur USB)
                    </button>
                </div>

                <!-- Mode 2: Reconnaissance Faciale Patient -->
                <div style="background:#FFFFFF; border:1px solid #E2E7E7; border-radius:10px; padding:0.85rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                        <div style="font-weight:700; font-size:0.84rem; color:var(--text-dark); display:flex; align-items:center; gap:0.4rem;">
                            <i class="bi bi-person-bounding-box" style="color:#0E9F2D; font-size:1.1rem;"></i>
                            <span>Mode 2 : Reconnaissance Faciale</span>
                        </div>
                        <span id="badgeRdvFace" style="font-size:0.72rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:6px; background:#F1F2F7; color:var(--text-muted);">Non enregistrée</span>
                    </div>
                    <input type="hidden" name="register_face" id="inputRdvFace" value="">
                    <button type="button" id="btnToggleRdvFace" style="width:100%; padding:0.5rem; border-radius:8px; border:1px solid #0E9F2D; background:#EBF9EE; color:#0E9F2D; font-weight:700; font-size:0.8rem; cursor:pointer; transition:all 0.2s ease;">
                        <i class="bi bi-camera-video"></i> Capturer le Visage (Caméra HD)
                    </button>
                </div>
            </div>

            <div>
                <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Notes complémentaires</label>
                <textarea name="notes" class="form-textarea" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" rows="2" placeholder="Remarques particulières..."></textarea>
            </div>

            <div style="margin-top:0.5rem; display:flex; gap:0.75rem; justify-content:flex-end;">
                <button type="button" class="btn-cancel" id="btnCancelCreateRdvDrawer" style="background:#F1F2F7; color:var(--text-dark); padding:0.65rem 1.25rem; border-radius:10px; border:none; font-weight:600; cursor:pointer;">Annuler</button>
                <button type="submit" class="btn-submit" style="background:var(--primary); color:#FFFFFF; padding:0.65rem 1.35rem; border-radius:10px; border:none; font-weight:600; cursor:pointer; box-shadow:0 4px 12px rgba(58, 91, 240, 0.28);">Enregistrer le RDV</button>
            </div>
        </div>
    </form>
</div>

<!-- Drawer Fiche Rendez-vous -->
<div class="drawer-overlay" id="drawerOverlay"></div>
<div class="drawer-panel" id="drawerPanel">
    <div class="drawer-header" style="display:flex; justify-content:space-between; align-items:center; padding:1.25rem 1.5rem; border-bottom:1px solid #E8EBF2;">
        <span style="font-weight: 700; color: var(--text-dark);">Détails du Rendez-vous</span>
        <button class="modal-close" id="drawerCloseBtn">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="drawer-body" id="drawerBody" style="padding:1.5rem;">
        <div id="drawerContent"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ── Nouveau Rendez-vous Sliding Drawer ──
        const btnOpenRdvDrawer   = document.getElementById('btnOpenModal');
        const createRdvOverlay   = document.getElementById('createRdvDrawerOverlay');
        const createRdvPanel     = document.getElementById('createRdvDrawerPanel');
        const btnCloseRdvDrawer  = document.getElementById('btnCloseCreateRdvDrawer');
        const btnCancelRdvDrawer = document.getElementById('btnCancelCreateRdvDrawer');

        function openCreateRdvDrawer() {
            if (createRdvOverlay && createRdvPanel) {
                createRdvOverlay.classList.add('open');
                createRdvPanel.classList.add('open');
            }
        }

        function closeCreateRdvDrawer() {
            if (createRdvOverlay && createRdvPanel) {
                createRdvOverlay.classList.remove('open');
                createRdvPanel.classList.remove('open');
            }
        }

        if (btnOpenRdvDrawer) btnOpenRdvDrawer.addEventListener('click', openCreateRdvDrawer);
        if (btnCloseRdvDrawer) btnCloseRdvDrawer.addEventListener('click', closeCreateRdvDrawer);
        if (btnCancelRdvDrawer) btnCancelRdvDrawer.addEventListener('click', closeCreateRdvDrawer);
        if (createRdvOverlay) createRdvOverlay.addEventListener('click', closeCreateRdvDrawer);

        // ── Biométrie Nouveau Rendez-vous Handlers ──
        const btnCreateRdvFp = document.getElementById('btnToggleRdvFp');
        if (btnCreateRdvFp) {
            btnCreateRdvFp.addEventListener('click', function () {
                window.openFingerprintScanner('inputRdvFp', 'badgeRdvFp', 'btnToggleRdvFp');
            });
        }

        const btnCreateRdvFace = document.getElementById('btnToggleRdvFace');
        if (btnCreateRdvFace) {
            btnCreateRdvFace.addEventListener('click', function () {
                window.openFaceCameraScanner('inputRdvFace', 'badgeRdvFace', 'btnToggleRdvFace');
            });
        }

        // ── Drawer Détails Rendez-vous Handlers ──
        const overlay = document.getElementById('drawerOverlay');
        const panel   = document.getElementById('drawerPanel');
        const drawerClose = document.getElementById('drawerCloseBtn');

        function closeDrawer() {
            if (overlay && panel) {
                overlay.classList.remove('open');
                panel.classList.remove('open');
            }
        }

        if (overlay) overlay.addEventListener('click', closeDrawer);
        if (drawerClose) drawerClose.addEventListener('click', closeDrawer);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeDrawer();
                closeCreateRdvDrawer();
            }
        });

        // ── Click Handlers sur les lignes du tableau ──
        document.querySelectorAll('.rdv-clickable-row').forEach(row => {
            row.addEventListener('click', function () {
                try {
                    const rawB64 = this.getAttribute('data-rdv');
                    if (!rawB64) return;
                    const decoded = decodeURIComponent(escape(window.atob(rawB64)));
                    const rdv = JSON.parse(decoded);
                    openRdvDetails(rdv);
                } catch (e) {
                    console.error('Erreur ouverture détails RDV :', e);
                }
            });
        });
    });

    function applyFilters() {
        const statut  = document.getElementById('filterStatut').value;
        const service = document.getElementById('filterService').value;
        const periode = document.getElementById('filterPeriode').value;
        const searchInput = document.querySelector('.inpage-search-box input[name="search"]');
        const search  = searchInput ? searchInput.value : '';

        const params = new URLSearchParams();
        if (statut)  params.set('statut', statut);
        if (service) params.set('service', service);
        if (periode) params.set('periode', periode);
        if (search)  params.set('search', search);

        window.location.href = "{{ route('rendezvous.index') }}?" + params.toString();
    }

    const allServices = @json($services);
    const allMedecins = @json($medecins);
    const csrfToken   = "{{ csrf_token() }}";
    let currentRdvData = null;

    function openRdvDetails(rdv) {
        currentRdvData = rdv;
        const overlay = document.getElementById('drawerOverlay');
        const panel   = document.getElementById('drawerPanel');
        const content = document.getElementById('drawerContent');

        if (content) {
            content.innerHTML = buildRdvDrawerHTML(rdv);
        }
        if (overlay && panel) {
            overlay.classList.add('open');
            panel.classList.add('open');
        }
    }

    function showEditRdvForm() {
        if (!currentRdvData) return;
        const content = document.getElementById('drawerContent');
        if (content) {
            content.innerHTML = buildEditRdvHTML(currentRdvData);
            initEditRdvBiometricToggles(currentRdvData);
        }
    }

    function cancelEditRdvForm() {
        if (!currentRdvData) return;
        const content = document.getElementById('drawerContent');
        if (content) {
            content.innerHTML = buildRdvDrawerHTML(currentRdvData);
        }
    }

    function initEditRdvBiometricToggles(rdv) {
        const btnFp = document.getElementById('btnToggleEditRdvFp');
        if (btnFp) {
            btnFp.addEventListener('click', function () {
                window.openFingerprintScanner('inputEditRdvFp', 'badgeEditRdvFp', 'btnToggleEditRdvFp');
            });
        }

        const btnFace = document.getElementById('btnToggleEditRdvFace');
        if (btnFace) {
            btnFace.addEventListener('click', function () {
                window.openFaceCameraScanner('inputEditRdvFace', 'badgeEditRdvFace', 'btnToggleEditRdvFace');
            });
        }
    }

    function buildEditRdvHTML(rdv) {
        const patientName = rdv.patient ? rdv.patient.nom_complet : (rdv.nom_patient || '');
        let dateStr = '';
        let hoursStr = '';
        try {
            const rdvDateObj = new Date(rdv.date_rendez_vous);
            if (!isNaN(rdvDateObj.getTime())) {
                const year = rdvDateObj.getFullYear();
                const month = String(rdvDateObj.getMonth() + 1).padStart(2, '0');
                const day = String(rdvDateObj.getDate()).padStart(2, '0');
                dateStr = `${year}-${month}-${day}`;
                hoursStr = String(rdvDateObj.getHours()).padStart(2, '0') + ':' + String(rdvDateObj.getMinutes()).padStart(2, '0');
            }
        } catch (e) {}

        const serviceOptions = allServices.map(s =>
            `<option value="${s}" ${s === rdv.service ? 'selected' : ''}>${s}</option>`
        ).join('');

        const medecinOptions = allMedecins.map(m =>
            `<option value="${m}" ${m === rdv.medecin ? 'selected' : ''}>${m}</option>`
        ).join('');

        return `
            <form action="/rendez-vous/${rdv.id}" method="POST">
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="_method" value="PUT">

                <div style="display:flex; flex-direction:column; gap:1.1rem;">
                    <div style="font-weight:800; font-size:1.05rem; color:var(--text-dark); margin-bottom:0.2rem; display:flex; align-items:center; gap:0.4rem;">
                        <i class="bi bi-pencil-square" style="color:var(--primary);"></i> Modifier le Rendez-vous
                    </div>

                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Nom et Prénom du Patient *</label>
                        <input type="text" name="nom_patient" value="${patientName}" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" required>
                    </div>

                    <div style="display:flex; gap:0.85rem;">
                        <div style="flex:1;">
                            <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Téléphone</label>
                            <input type="text" name="telephone_patient" value="${rdv.telephone_patient || ''}" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;">
                        </div>
                        <div style="flex:1;">
                            <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Email</label>
                            <input type="email" name="email_patient" value="${rdv.email_patient || ''}" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;">
                        </div>
                    </div>

                    <div style="display:flex; gap:0.85rem;">
                        <div style="flex:1;">
                            <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Service Médical *</label>
                            <select name="service" class="form-select" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" required>
                                ${serviceOptions}
                            </select>
                        </div>
                        <div style="flex:1;">
                            <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Médecin Attribué</label>
                            <select name="medecin" class="form-select" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;">
                                ${medecinOptions}
                            </select>
                        </div>
                    </div>

                    <div style="display:flex; gap:0.85rem;">
                        <div style="flex:1;">
                            <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Date du Rendez-vous *</label>
                            <input type="date" name="date_rendez_vous" value="${dateStr}" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" required>
                        </div>
                        <div style="flex:1;">
                            <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Heure *</label>
                            <input type="time" name="heure_rendez_vous" value="${hoursStr}" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" required>
                        </div>
                    </div>

                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Motif de la Consultation *</label>
                        <input type="text" name="motif" value="${rdv.motif}" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" required>
                    </div>

                    <!-- MODES D'ENREGISTREMENT BIOMÉTRIQUE PATIENT -->
                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:14px; padding:1.1rem; margin-top:0.3rem;">
                        <div style="font-size:0.88rem; font-weight:700; color:var(--text-dark); margin-bottom:0.85rem; display:flex; align-items:center; gap:0.5rem;">
                            <i class="bi bi-shield-lock-fill" style="color:var(--primary);"></i>
                            <span>Modes d'Enregistrement / Pointage Biométrique</span>
                        </div>

                        <!-- Mode 1: Empreinte Digitale Patient -->
                        <div style="background:#FFFFFF; border:1px solid #E2E7E7; border-radius:10px; padding:0.85rem; margin-bottom:0.75rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                                <div style="font-weight:700; font-size:0.84rem; color:var(--text-dark); display:flex; align-items:center; gap:0.4rem;">
                                    <i class="bi bi-fingerprint" style="color:var(--primary); font-size:1.1rem;"></i>
                                    <span>Mode 1 : Empreinte Digitale</span>
                                </div>
                                <span id="badgeEditRdvFp" style="font-size:0.72rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:6px; background:#F1F2F7; color:var(--text-muted);">Non enregistrée</span>
                            </div>
                            <input type="hidden" name="register_fingerprint" id="inputEditRdvFp" value="">
                            <button type="button" id="btnToggleEditRdvFp" style="width:100%; padding:0.5rem; border-radius:8px; border:1px solid var(--primary); background:#EBF3FF; color:var(--primary); font-weight:700; font-size:0.8rem; cursor:pointer;">
                                <i class="bi bi-plus-circle"></i> Capturer l'Empreinte (Lecteur USB)
                            </button>
                        </div>

                        <!-- Mode 2: Reconnaissance Faciale Patient -->
                        <div style="background:#FFFFFF; border:1px solid #E2E7E7; border-radius:10px; padding:0.85rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                                <div style="font-weight:700; font-size:0.84rem; color:var(--text-dark); display:flex; align-items:center; gap:0.4rem;">
                                    <i class="bi bi-person-bounding-box" style="color:#0E9F2D; font-size:1.1rem;"></i>
                                    <span>Mode 2 : Reconnaissance Faciale</span>
                                </div>
                                <span id="badgeEditRdvFace" style="font-size:0.72rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:6px; background:#F1F2F7; color:var(--text-muted);">Non enregistrée</span>
                            </div>
                            <input type="hidden" name="register_face" id="inputEditRdvFace" value="">
                            <button type="button" id="btnToggleEditRdvFace" style="width:100%; padding:0.5rem; border-radius:8px; border:1px solid #0E9F2D; background:#EBF9EE; color:#0E9F2D; font-weight:700; font-size:0.8rem; cursor:pointer;">
                                <i class="bi bi-camera-video"></i> Capturer le Visage (Caméra HD)
                            </button>
                        </div>
                    </div>

                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Notes complémentaires</label>
                        <textarea name="notes" class="form-textarea" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" rows="2">${rdv.notes || ''}</textarea>
                    </div>

                    <div style="margin-top:0.5rem; display:flex; gap:0.75rem; justify-content:flex-end;">
                        <button type="button" onclick="cancelEditRdvForm()" class="btn-cancel" style="background:#F1F2F7; color:var(--text-dark); padding:0.65rem 1.25rem; border-radius:10px; border:none; font-weight:600; cursor:pointer;">Annuler</button>
                        <button type="submit" class="btn-submit" style="background:var(--primary); color:#FFFFFF; padding:0.65rem 1.35rem; border-radius:10px; border:none; font-weight:600; cursor:pointer; box-shadow:0 4px 12px rgba(58, 91, 240, 0.28);">Enregistrer les Modifications</button>
                    </div>
                </div>
            </form>
        `;
    }

    function buildRdvDrawerHTML(rdv) {
        let dateFormatted = 'Date non définie';
        try {
            const dateObj = new Date(rdv.date_rendez_vous);
            if (!isNaN(dateObj.getTime())) {
                dateFormatted = dateObj.toLocaleDateString('fr-FR', {
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
                });
            }
        } catch (e) {}

        const patientName = rdv.patient ? rdv.patient.nom_complet : (rdv.nom_patient || 'Patient Inconnu');
        const patientInitial = (patientName.charAt(0) || 'P').toUpperCase();
        const dossierNum = rdv.patient && rdv.patient.numero_dossier ? rdv.patient.numero_dossier : 'Dossier en cours';

        let statusActionsHTML = '';
        const currentStatut = rdv.statut || 'Planifié';
        const isTerminal = currentStatut === 'Réalisé' || currentStatut === 'Annulé';
        const hasPointage = Boolean(rdv.pointage_at) || currentStatut === 'Présent';

        if (isTerminal) {
            const isCompleted = currentStatut === 'Réalisé';
            statusActionsHTML = `
                <div style="background:${isCompleted ? '#EBF9EE' : '#FDE8E8'}; border:1px solid ${isCompleted ? '#10B981' : '#F87171'}; border-radius:10px; padding:0.75rem 1rem; color:${isCompleted ? '#065F46' : '#991B1B'}; font-size:0.84rem; display:flex; align-items:center; gap:0.5rem;">
                    <i class="bi bi-shield-lock-fill" style="font-size:1.1rem;"></i>
                    <span>Ce rendez-vous est <strong>${currentStatut}</strong> (clôturé). Aucun changement de statut ultérieur n'est possible.</span>
                </div>
            `;
        } else if (hasPointage) {
            // Patient arrivé / présent : On ne peut plus revenir en arrière vers "Confirmé" ou "Reporté"
            statusActionsHTML = `
                <div style="margin-bottom:0.6rem; font-size:0.78rem; color:#059669; font-weight:700; display:flex; align-items:center; gap:0.35rem;">
                    <i class="bi bi-geo-alt-fill"></i> Présence validée à la borne interactive
                </div>
                <form action="/rendez-vous/${rdv.id}/status" method="POST" style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="_method" value="PATCH">
                    <button type="submit" name="statut" value="Réalisé" class="btn-cancel" style="background:#EBF9EE; color:#07691C; font-weight:700; border:1px solid #10B981; padding:0.5rem 0.9rem; border-radius:8px; cursor:pointer;">
                        <i class="bi bi-check2-circle"></i> Marquer Réalisé (Fin Consultation)
                    </button>
                    <button type="submit" name="statut" value="Annulé" class="btn-cancel" style="background:#FDE8E8; color:var(--accent-red); font-weight:700; padding:0.5rem 0.9rem; border-radius:8px; cursor:pointer;">
                        <i class="bi bi-x-circle"></i> Annuler
                    </button>
                </form>
            `;
        } else if (currentStatut === 'Reporté') {
            // Reporté : impossible de revenir à Confirmé sans reprogrammer
            statusActionsHTML = `
                <div style="margin-bottom:0.6rem; font-size:0.78rem; color:#D97300; font-weight:700; display:flex; align-items:center; gap:0.35rem;">
                    <i class="bi bi-clock-history"></i> Statut actuel : Reporté
                </div>
                <form action="/rendez-vous/${rdv.id}/status" method="POST" style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="_method" value="PATCH">
                    <button type="submit" name="statut" value="Annulé" class="btn-cancel" style="background:#FDE8E8; color:var(--accent-red); font-weight:700; padding:0.5rem 0.9rem; border-radius:8px; cursor:pointer;">
                        <i class="bi bi-x-circle"></i> Annuler
                    </button>
                </form>
            `;
        } else if (currentStatut === 'Confirmé') {
            // Confirmé -> Réalisé, Reporté, Annulé (pas de re-confirmation)
            statusActionsHTML = `
                <form action="/rendez-vous/${rdv.id}/status" method="POST" style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="_method" value="PATCH">
                    <button type="submit" name="statut" value="Réalisé" class="btn-cancel" style="background:#EBF9EE; color:#07691C; font-weight:700; padding:0.5rem 0.9rem; border-radius:8px; cursor:pointer;">
                        <i class="bi bi-check2-circle"></i> Réalisé
                    </button>
                    <button type="submit" name="statut" value="Reporté" class="btn-cancel" style="background:#FFF6EB; color:#D97300; font-weight:700; padding:0.5rem 0.9rem; border-radius:8px; cursor:pointer;">
                        <i class="bi bi-calendar-range"></i> Reporter
                    </button>
                    <button type="submit" name="statut" value="Annulé" class="btn-cancel" style="background:#FDE8E8; color:var(--accent-red); font-weight:700; padding:0.5rem 0.9rem; border-radius:8px; cursor:pointer;">
                        <i class="bi bi-x-circle"></i> Annuler
                    </button>
                </form>
            `;
        } else {
            // Planifié (Initial) -> Confirmé, Réalisé, Reporté, Annulé
            statusActionsHTML = `
                <form action="/rendez-vous/${rdv.id}/status" method="POST" style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="_method" value="PATCH">
                    <button type="submit" name="statut" value="Confirmé" class="btn-cancel" style="background:#EBF3FF; color:var(--primary); font-weight:700; padding:0.5rem 0.9rem; border-radius:8px; cursor:pointer;">
                        <i class="bi bi-check-lg"></i> Confirmer
                    </button>
                    <button type="submit" name="statut" value="Réalisé" class="btn-cancel" style="background:#EBF9EE; color:#07691C; font-weight:700; padding:0.5rem 0.9rem; border-radius:8px; cursor:pointer;">
                        <i class="bi bi-check2-circle"></i> Réalisé
                    </button>
                    <button type="submit" name="statut" value="Reporté" class="btn-cancel" style="background:#FFF6EB; color:#D97300; font-weight:700; padding:0.5rem 0.9rem; border-radius:8px; cursor:pointer;">
                        <i class="bi bi-calendar-range"></i> Reporter
                    </button>
                    <button type="submit" name="statut" value="Annulé" class="btn-cancel" style="background:#FDE8E8; color:var(--accent-red); font-weight:700; padding:0.5rem 0.9rem; border-radius:8px; cursor:pointer;">
                        <i class="bi bi-x-circle"></i> Annuler
                    </button>
                </form>
            `;
        }

        return `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem; background:#F8FAFC; padding:0.75rem 1rem; border-radius:12px; border:1px solid #E2E8F0;">
                <button type="button" onclick="showEditRdvForm()" style="background:#EBF3FF; color:var(--primary); border:none; padding:0.45rem 0.9rem; border-radius:8px; font-weight:700; font-size:0.8rem; cursor:pointer; display:inline-flex; align-items:center; gap:0.35rem;">
                    <i class="bi bi-pencil-square"></i> Modifier le RDV
                </button>

                <form action="/rendez-vous/${rdv.id}" method="POST" onsubmit="return confirm('Confirmez-vous la suppression de ce rendez-vous ?');" style="margin:0;">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" style="background:#FDE8E8; color:var(--accent-red); border:none; padding:0.45rem 0.9rem; border-radius:8px; font-weight:700; font-size:0.8rem; cursor:pointer; display:inline-flex; align-items:center; gap:0.35rem;">
                        <i class="bi bi-trash3-fill"></i> Supprimer
                    </button>
                </form>
            </div>

            <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
                <div style="width:52px; height:52px; border-radius:50%; background:linear-gradient(135deg, #3A5BF0, #6C88FF); color:#FFF; font-weight:800; font-size:1.2rem; display:flex; align-items:center; justify-content:center;">
                    ${patientInitial}
                </div>
                <div>
                    <div style="font-size:1.15rem; font-weight:700; color:var(--text-dark);">${patientName}</div>
                    <div style="font-size:0.82rem; color:var(--text-muted); font-weight:500;">${dossierNum}</div>
                </div>
            </div>

            <div style="background:#F6F7FB; border-radius:12px; padding:1rem; margin-bottom:1.25rem;">
                <div style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Statut Actuel</div>
                <div style="font-size:1.1rem; font-weight:800; color:var(--primary); margin-top:0.2rem;">${rdv.statut}</div>
                ${rdv.pointage_at ? `<div style="font-size:0.76rem; color:#059669; font-weight:600; margin-top:0.25rem;"><i class="bi bi-check-circle-fill"></i> Heure d'arrivée  : ${new Date(rdv.pointage_at).toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'})}</div>` : ''}
            </div>

            <div style="display:flex; flex-direction:column; gap:0.85rem; font-size:0.88rem; margin-bottom:1.5rem;">
                <div><strong>Service :</strong> ${rdv.service}</div>
                <div><strong>Médecin :</strong> ${rdv.medecin || 'Non renseigné'}</div>
                <div><strong>Date & Heure :</strong> ${dateFormatted}</div>
                <div><strong>Téléphone :</strong> ${rdv.telephone_patient || '—'}</div>
                <div><strong>Email :</strong> ${rdv.email_patient || '—'}</div>
                <div><strong>Motif :</strong> ${rdv.motif}</div>
                ${rdv.notes ? `<div><strong>Notes :</strong> ${rdv.notes}</div>` : ''}
            </div>

            <div style="border-top:1px solid #E8EBF2; padding-top:1rem;">
                <div style="font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.5rem;">Changer le Statut :</div>
                ${statusActionsHTML}
            </div>
        `;
    }
</script>
@endsection
