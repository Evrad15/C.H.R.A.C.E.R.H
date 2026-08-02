@extends('layouts.dashboard')

@section('title', 'Rendez-vous | C.H.R.A.C.E.R.H')

@section('custom_styles')
<style>
    .page-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2.25rem;
    }

    .page-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }

    .btn-create-rdv {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--primary);
        color: #FFFFFF;
        padding: 0.65rem 1.25rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        border: none;
        cursor: pointer;
        margin-left: auto;
        box-shadow: 0 4px 12px rgba(58, 91, 240, 0.25);
        transition: background 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
    }
    .btn-create-rdv:hover {
        background: #2a48d8;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(58, 91, 240, 0.35);
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
        width: 420px;
        background: #FFFFFF;
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: -8px 0 30px rgba(0, 0, 0, 0.12);
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        scrollbar-width: none;
    }
    .drawer-panel.open { transform: translateX(0); }
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
            <tr onclick="openRdvDetails({{ json_encode($rdv) }})">
                <td>
                    <div class="patient-info">
                        <div class="patient-avatar">
                            {{ strtoupper(substr($rdv->patient->nom_complet ?? 'P', 0, 1)) }}
                        </div>
                        <div>
                            <div class="patient-name">{{ $rdv->patient->nom_complet ?? 'Patient Inconnu' }}</div>
                            <div class="patient-dossier">{{ $rdv->patient->numero_dossier ?? 'N/A' }} | {{ $rdv->telephone_patient ?? '—' }}</div>
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

<!-- Modal Nouveau Rendez-vous -->
<div class="modal-backdrop" id="rdvModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 class="modal-title">Créer un Nouveau Rendez-vous</h3>
            <button class="modal-close" id="btnCloseModal">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form action="{{ route('rendezvous.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nom et Prénom du Patient *</label>
                    <input type="text" name="nom_patient" class="form-input" placeholder="ex: BILOUNGA Chantal" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone_patient" class="form-input" placeholder="+237 699 00 11 22">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email_patient" class="form-input" placeholder="patient@gmail.com">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Service Médical *</label>
                        <select name="service" class="form-select" required>
                            @foreach($services as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Médecin Attribué</label>
                        <select name="medecin" class="form-select">
                            @foreach($medecins as $m)
                            <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Date du Rendez-vous *</label>
                        <input type="date" name="date_rendez_vous" class="form-input" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Heure *</label>
                        <input type="time" name="heure_rendez_vous" class="form-input" value="09:00" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Motif de la Consultation *</label>
                    <input type="text" name="motif" class="form-input" placeholder="ex: Échographie de contrôle T2" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes complémentaires</label>
                    <textarea name="notes" class="form-textarea" rows="2" placeholder="Remarques particulières..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="btnCancelModal">Annuler</button>
                <button type="submit" class="btn-submit">Enregistrer le RDV</button>
            </div>
        </form>
    </div>
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
        // Modal Handlers
        const modal = document.getElementById('rdvModal');
        const btnOpen = document.getElementById('btnOpenModal');
        const btnClose = document.getElementById('btnCloseModal');
        const btnCancel = document.getElementById('btnCancelModal');

        if (btnOpen && modal) {
            btnOpen.addEventListener('click', () => modal.classList.add('show'));
            btnClose.addEventListener('click', () => modal.classList.remove('show'));
            btnCancel.addEventListener('click', () => modal.classList.remove('show'));
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.classList.remove('show');
            });
        }

        // Drawer Close Handlers
        const overlay = document.getElementById('drawerOverlay');
        const panel   = document.getElementById('drawerPanel');
        const drawerClose = document.getElementById('drawerCloseBtn');

        function closeDrawer() {
            overlay.classList.remove('open');
            panel.classList.remove('open');
        }
        if (overlay && panel) {
            overlay.addEventListener('click', closeDrawer);
            drawerClose.addEventListener('click', closeDrawer);
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeDrawer();
            });
        }
    });

    function applyFilters() {
        const statut  = document.getElementById('filterStatut').value;
        const service = document.getElementById('filterService').value;
        const periode = document.getElementById('filterPeriode').value;
        const search  = document.querySelector('.inpage-search-box input[name="search"]').value;

        const params = new URLSearchParams();
        if (statut)  params.set('statut', statut);
        if (service) params.set('service', service);
        if (periode) params.set('periode', periode);
        if (search)  params.set('search', search);

        window.location.href = "{{ route('rendezvous.index') }}?" + params.toString();
    }

    function openRdvDetails(rdv) {
        const overlay = document.getElementById('drawerOverlay');
        const panel   = document.getElementById('drawerPanel');
        const content = document.getElementById('drawerContent');

        overlay.classList.add('open');
        panel.classList.add('open');

        const dateFormatted = new Date(rdv.date_rendez_vous).toLocaleDateString('fr-FR', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
        });

        content.innerHTML = `
            <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
                <div style="width:52px; height:52px; border-radius:50%; background:linear-gradient(135deg, #3A5BF0, #6C88FF); color:#FFF; font-weight:800; font-size:1.2rem; display:flex; align-items:center; justify-content:center;">
                    ${rdv.patient ? rdv.patient.nom_complet.charAt(0).toUpperCase() : 'P'}
                </div>
                <div>
                    <div style="font-size:1.15rem; font-weight:700; color:var(--text-dark);">${rdv.patient ? rdv.patient.nom_complet : 'Patient'}</div>
                    <div style="font-size:0.82rem; color:var(--text-muted); font-weight:500;">${rdv.patient ? rdv.patient.numero_dossier : ''}</div>
                </div>
            </div>

            <div style="background:#F6F7FB; border-radius:12px; padding:1rem; margin-bottom:1.25rem;">
                <div style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Statut Actuel</div>
                <div style="font-size:1.1rem; font-weight:800; color:var(--primary); margin-top:0.2rem;">${rdv.statut}</div>
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
                <form action="/rendez-vous/${rdv.id}/status" method="POST" style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="PATCH">
                    <button type="submit" name="statut" value="Confirmé" class="btn-cancel" style="background:#EBF9EE; color:#0E9F2D;">Confirmé</button>
                    <button type="submit" name="statut" value="Réalisé" class="btn-cancel" style="background:#EBF9EE; color:#07691C;">Réalisé</button>
                    <button type="submit" name="statut" value="Reporté" class="btn-cancel" style="background:#FFF6EB; color:#D97300;">Reporté</button>
                    <button type="submit" name="statut" value="Annulé" class="btn-cancel" style="background:#FDE8E8; color:var(--accent-red);">Annulé</button>
                </form>
            </div>
        `;
    }
</script>
@endsection
