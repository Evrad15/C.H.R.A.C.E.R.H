@extends('layouts.dashboard')

@section('title', 'Employés | C.H.R.A.C.E.R.H')

@section('custom_styles')
<style>
    .page-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 1.25rem;
    }

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
        gap: 1.25rem;
    }

    /* Bouton et Menu Filtre (Style Figma) */
    .filter-dropdown-container {
        position: relative;
        display: inline-block;
    }

    .btn-filter {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        background: transparent;
        border: none;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-dark);
        cursor: pointer;
        padding: 0.4rem 0.6rem;
        border-radius: 8px;
        transition: background 0.2s ease;
    }

    .btn-filter:hover {
        background: rgba(0, 0, 0, 0.04);
    }

    .btn-filter i.bi-funnel {
        font-size: 1.3rem;
    }

    .btn-filter i.bi-chevron-down {
        font-size: 0.85rem;
        margin-left: 0.2rem;
    }

    .filter-menu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        margin-top: 0.4rem;
        background: #FFFFFF;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(226, 231, 231, 0.8);
        min-width: 230px;
        z-index: 100;
        padding: 0.5rem 0;
    }

    .filter-menu.show {
        display: block;
    }

    .filter-item {
        display: block;
        padding: 0.6rem 1rem;
        font-size: 0.88rem;
        color: var(--text-dark);
        text-decoration: none;
        font-weight: 500;
        transition: background 0.2s ease;
    }

    .filter-item:hover, .filter-item.active {
        background: #F1F2F7;
        color: var(--primary);
        font-weight: 700;
    }

    /* Input Recherche locale */
    .inpage-search-box {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #FFFFFF;
        border: 1px solid rgba(226, 231, 231, 0.9);
        border-radius: 10px;
        padding: 0.4rem 0.85rem;
        width: 300px;
        box-shadow: var(--shadow-subtle);
    }

    .inpage-search-box input {
        border: none;
        outline: none;
        background: transparent;
        font-size: 0.88rem;
        width: 100%;
        color: var(--text-dark);
    }

    .inpage-search-box i {
        color: var(--text-muted);
    }

    /* Tableau des employés (Figma Style) */
    .table-container {
        background: #FFFFFF;
        border-radius: 20px;
        padding: 1.5rem;
        border: 1px solid rgba(226, 231, 231, 0.6);
        box-shadow: var(--shadow-subtle);
    }

    .employes-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.65rem;
    }

    .employes-table th {
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--text-dark);
        text-align: left;
        padding: 0.5rem 1rem;
        border-bottom: 2px solid var(--text-dark);
    }

    .employes-table td {
        background: #F1F2F7;
        padding: 0.85rem 1rem;
        font-size: 0.88rem;
        color: var(--text-dark);
        font-weight: 500;
    }

    .employes-table tr td:first-child {
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
    }

    .employes-table tr td:last-child {
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }

    .emp-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .emp-avatar-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #DEE1F4;
        color: var(--primary);
        font-weight: 700;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .emp-name {
        font-weight: 700;
        color: var(--text-dark);
        letter-spacing: 0.2px;
    }

    /* Badges de compteurs (Absences, Retards, A l'heure) */
    .counter-val {
        font-weight: 700;
        font-size: 0.92rem;
    }

    .val-absences { color: var(--accent-red); }
    .val-retards { color: #D97300; }
    .val-alheure { color: #0E9F2D; }

    /* Style de la Pagination (20 par page) */
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

    .custom-pagination li a:hover:not(.active) {
        background: #DEE1F4;
        color: var(--primary);
    }

    /* Table row clickable */
    .employes-table tbody tr {
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .employes-table tbody tr:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    /* ========== DRAWER OVERLAY + PANEL ========== */
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
    .drawer-overlay.open {
        opacity: 1;
        pointer-events: auto;
    }

    .drawer-panel {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: 440px;
        background: #FFFFFF;
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: -8px 0 30px rgba(0, 0, 0, 0.12);
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE/Edge */
    }
    .drawer-panel::-webkit-scrollbar {
        display: none; /* Chrome/Safari */
    }
    .drawer-panel.open {
        transform: translateX(0);
    }

    .drawer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #E8EBF2;
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
        padding: 1.5rem;
        flex: 1;
    }

    /* Profile Header */
    .drawer-profile {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .drawer-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3A5BF0, #6C88FF);
        color: #FFFFFF;
        font-weight: 800;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .drawer-name {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-dark);
    }
    .drawer-dept {
        font-size: 0.82rem;
        color: var(--text-muted);
        font-weight: 500;
        margin-top: 0.15rem;
    }
    .drawer-role-badge {
        display: inline-block;
        padding: 0.15rem 0.5rem;
        border-radius: 5px;
        font-size: 0.72rem;
        font-weight: 600;
        background: rgba(58, 91, 240, 0.1);
        color: var(--primary);
        margin-top: 0.3rem;
    }

    /* Info Row */
    .drawer-info-row {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }
    .drawer-info-item {
        flex: 1;
        background: #F6F7FB;
        border-radius: 10px;
        padding: 0.6rem 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .drawer-info-item i {
        color: var(--primary);
        font-size: 1rem;
    }
    .drawer-info-item span {
        font-size: 0.8rem;
        color: var(--text-dark);
        font-weight: 500;
        word-break: break-all;
    }

    /* Stat Cards Row */
    .drawer-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    .stat-card {
        background: #F6F7FB;
        border-radius: 12px;
        padding: 0.85rem;
        text-align: center;
    }
    .stat-card .stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1.1;
    }
    .stat-card .stat-label {
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-top: 0.2rem;
        text-transform: uppercase;
    }
    .stat-card .stat-pct {
        font-size: 0.72rem;
        font-weight: 700;
        margin-top: 0.15rem;
    }
    .stat-green .stat-value { color: #0E9F2D; }
    .stat-green .stat-pct { color: #0E9F2D; }
    .stat-orange .stat-value { color: #D97300; }
    .stat-orange .stat-pct { color: #D97300; }
    .stat-red .stat-value { color: var(--accent-red); }
    .stat-red .stat-pct { color: var(--accent-red); }

    /* Section Title */
    .drawer-section-title {
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    /* Weekly Timeline */
    .weekly-timeline {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        margin-bottom: 1.5rem;
    }
    .timeline-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.35rem 0.5rem;
        background: #F6F7FB;
        border-radius: 8px;
    }
    .timeline-day {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text-dark);
        min-width: 80px;
    }
    .timeline-badge {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.15rem 0.5rem;
        border-radius: 5px;
    }
    .timeline-badge.Present { background: rgba(22,222,66,0.12); color: #0E9F2D; }
    .timeline-badge.Retard  { background: rgba(255,155,67,0.15); color: #D97300; }
    .timeline-badge.Absent  { background: rgba(242,56,58,0.12); color: var(--accent-red); }

    /* Last Pointages Mini-table */
    .drawer-mini-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.35rem;
    }
    .drawer-mini-table th {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text-muted);
        text-align: left;
        padding: 0.3rem 0.5rem;
        border-bottom: 1px solid #E8EBF2;
    }
    .drawer-mini-table td {
        font-size: 0.78rem;
        font-weight: 500;
        color: var(--text-dark);
        padding: 0.35rem 0.5rem;
        background: #F6F7FB;
    }
    .drawer-mini-table tr td:first-child { border-radius: 6px 0 0 6px; }
    .drawer-mini-table tr td:last-child  { border-radius: 0 6px 6px 0; }

    /* Loading skeleton */
    .drawer-loading {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--text-muted);
    }
    .drawer-loading i {
        font-size: 2rem;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
    <h1 class="page-title" style="margin:0;">Employés</h1>
    <button type="button" class="btn-create-conge" id="btnOpenCreateEmpDrawer" style="display:inline-flex; align-items:center; gap:0.55rem; background:var(--primary); color:#FFFFFF; padding:0.7rem 1.35rem; border-radius:12px; font-weight:600; font-size:0.92rem; border:none; cursor:pointer; box-shadow:0 4px 14px rgba(58, 91, 240, 0.28);">
        <i class="bi bi-person-plus-fill"></i>
        <span>Nouvel Employé</span>
    </button>
</div>

@if(session('success'))
<div style="background: #EBF9EE; color: #0E9F2D; padding: 0.85rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; border: 1px solid #C3E9C8;">
    <i class="bi bi-check-circle-fill" style="font-size: 1.1rem;"></i>
    <span>{{ session('success') }}</span>
</div>
@endif

<!-- Barre d'outils et Filtre + Recherche -->
<div class="toolbar-flex">
    <div class="left-tools">
        <div class="filter-dropdown-container">
            <button type="button" class="btn-filter" id="filterDropdownBtn">
                <i class="bi bi-funnel"></i>
                <span>Filtre</span>
                <i class="bi bi-chevron-down"></i>
            </button>

            <div class="filter-menu" id="filterMenu">
                <a href="{{ route('employes.index', ['search' => request('search')]) }}" 
                   class="filter-item {{ !request('department_id') ? 'active' : '' }}">
                    Tous les départements
                </a>
                @foreach($departments as $dept)
                <a href="{{ route('employes.index', ['department_id' => $dept->id, 'search' => request('search')]) }}" 
                   class="filter-item {{ request('department_id') == $dept->id ? 'active' : '' }}">
                    {{ $dept->name }}
                </a>
                @endforeach
            </div>
        </div>

        <!-- Formulaire de recherche intégrée -->
    
    </div>

    @if(request('department_id') || request('search'))
    <div style="font-size: 0.85rem; color: var(--text-muted);">
        Filtre actif : 
        <strong>{{ request('department_id') ? ($departments->firstWhere('id', request('department_id'))->name ?? 'Département') : '' }}</strong>
        {{ request('search') ? '"' . request('search') . '"' : '' }}
        <a href="{{ route('employes.index') }}" style="margin-left: 0.5rem; color: var(--accent-red); font-weight: 600; text-decoration: none;">
            <i class="bi bi-x-circle"></i> Réinitialiser
        </a>
    </div>
    @endif
</div>

<!-- Tableau des employés (Figma SVG - Ordre Alphabétique par NOM Prénom) -->
<div class="table-container">
    <table class="employes-table">
        <thead>
            <tr>
                <th style="width: 34%;">Nom et Prénoms</th>
                <th style="width: 26%;">Départements</th>
                <th style="width: 13%;">Absences</th>
                <th style="width: 13%;">Retards</th>
                <th style="width: 14%;">A l'heure</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employes as $emp)
            <tr onclick="openEmployeeDrawer({{ $emp->id }})">
                <td>
                    <div class="emp-info">
                        <div class="emp-avatar-circle">
                            {{ $emp->initials }}
                        </div>
                        <span class="emp-name">{{ $emp->full_name }}</span>
                    </div>
                </td>
                <td>{{ $emp->department_name }}</td>
                <td>
                    <span class="counter-val val-absences">{{ $emp->absences_count }}</span>
                </td>
                <td>
                    <span class="counter-val val-retards">{{ $emp->retards_count }}</span>
                </td>
                <td>
                    <span class="counter-val val-alheure">{{ $emp->alheure_count }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                    Aucun employé trouvé.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination sur-mesure (20 par page) -->
    @if($employes->hasPages())
    <div class="pagination-wrapper">
        <div class="pagination-info">
            Affichage de <strong>{{ $employes->firstItem() }}</strong> à <strong>{{ $employes->lastItem() }}</strong> sur <strong>{{ $employes->total() }}</strong> employés
        </div>

        <ul class="custom-pagination">
            {{-- Lien Précédent --}}
            @if ($employes->onFirstPage())
                <li class="disabled"><span><i class="bi bi-chevron-left"></i></span></li>
            @else
                <li><a href="{{ $employes->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a></li>
            @endif

            {{-- Numéros de page --}}
            @foreach ($employes->getUrlRange(1, $employes->lastPage()) as $page => $url)
                @if ($page == $employes->currentPage())
                    <li class="active"><span>{{ $page }}</span></li>
                @else
                    <li><a href="{{ $url }}">{{ $page }}</a></li>
                @endif
            @endforeach

            {{-- Lien Suivant --}}
            @if ($employes->hasMorePages())
                <li><a href="{{ $employes->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a></li>
            @else
                <li class="disabled"><span><i class="bi bi-chevron-right"></i></span></li>
            @endif
        </ul>
    </div>
    @endif
</div>
<!-- ========== DRAWER OVERLAY + PANEL ========== -->
<div class="drawer-overlay" id="drawerOverlay"></div>
<div class="drawer-panel" id="drawerPanel">
    <div class="drawer-header">
        <span style="font-weight: 700; color: var(--text-dark);">Fiche Employé</span>
        <button class="drawer-close" id="drawerCloseBtn">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="drawer-body" id="drawerBody">
        <div class="drawer-loading" id="drawerLoading">
            <i class="bi bi-arrow-repeat"></i>
            <p>Chargement des données…</p>
        </div>
        <div id="drawerContent" style="display:none;"></div>
    </div>
</div>

<!-- ========== DRAWER COULISSANT : NOUVEL EMPLOYÉ ========== -->
<div class="drawer-overlay" id="createEmpOverlay"></div>
<div class="drawer-panel" id="createEmpPanel" style="width:480px;">
    <div class="drawer-header">
        <span style="font-weight: 700; color: var(--text-dark); font-size:1.1rem;">Enregistrer un Nouvel Employé</span>
        <button type="button" class="drawer-close" id="createEmpCloseBtn"><i class="bi bi-x-lg"></i></button>
    </div>
    <form action="{{ route('employes.store') }}" method="POST" class="drawer-body">
        @csrf
        <div style="display:flex; flex-direction:column; gap:1.1rem;">
            <div style="display:flex; gap:0.85rem;">
                <div style="flex:1;">
                    <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Nom *</label>
                    <input type="text" name="last_name" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" placeholder="ex: BILOUNGA" required>
                </div>
                <div style="flex:1;">
                    <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Prénom *</label>
                    <input type="text" name="first_name" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" placeholder="ex: Chantal" required>
                </div>
            </div>

            <div>
                <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Adresse E-mail *</label>
                <input type="email" name="email" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" placeholder="ex: c.bilounga@chracerh.cm" required>
            </div>

            <div style="display:flex; gap:0.85rem;">
                <div style="flex:1;">
                    <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Téléphone</label>
                    <input type="text" name="phone" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" placeholder="+237 699 00 11 22">
                </div>
                <div style="flex:1;">
                    <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Rôle *</label>
                    <select name="role" class="form-select" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;">
                        <option value="Employés">Employé</option>
                        <option value="Manager">Manager / Chef Service</option>
                        <option value="RH_Admin">Administrateur RH</option>
                    </select>
                </div>
            </div>

            <div>
                <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Département *</label>
                <select name="department_id" class="form-select" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" required>
                    <option value="">Sélectionner un département...</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- MODES D'ENREGISTREMENT BIOMÉTRIQUE -->
            <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:14px; padding:1.1rem; margin-top:0.3rem;">
                <div style="font-size:0.88rem; font-weight:700; color:var(--text-dark); margin-bottom:0.85rem; display:flex; align-items:center; gap:0.5rem;">
                    <i class="bi bi-shield-lock-fill" style="color:var(--primary);"></i>
                    <span>Modes d'Enregistrement Biométrique (Pointages)</span>
                </div>

                <!-- Mode 1: Empreinte Digitale -->
                <div style="background:#FFFFFF; border:1px solid #E2E7E7; border-radius:10px; padding:0.85rem; margin-bottom:0.75rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                        <div style="font-weight:700; font-size:0.84rem; color:var(--text-dark); display:flex; align-items:center; gap:0.4rem;">
                            <i class="bi bi-fingerprint" style="color:var(--primary); font-size:1.1rem;"></i>
                            <span>Mode 1 : Empreinte Digitale</span>
                        </div>
                        <span id="badgeFpStatus" style="font-size:0.72rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:6px; background:#F1F2F7; color:var(--text-muted);">Non enregistrée</span>
                    </div>
                    <input type="hidden" name="register_fingerprint" id="inputRegisterFp" value="">
                    <button type="button" id="btnToggleFp" style="width:100%; padding:0.5rem; border-radius:8px; border:1px solid var(--primary); background:#EBF3FF; color:var(--primary); font-weight:700; font-size:0.8rem; cursor:pointer; transition:all 0.2s ease;">
                        <i class="bi bi-plus-circle"></i> Capturer l'Empreinte (Lecteur USB)
                    </button>
                </div>

                <!-- Mode 2: Reconnaissance Faciale -->
                <div style="background:#FFFFFF; border:1px solid #E2E7E7; border-radius:10px; padding:0.85rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                        <div style="font-weight:700; font-size:0.84rem; color:var(--text-dark); display:flex; align-items:center; gap:0.4rem;">
                            <i class="bi bi-person-bounding-box" style="color:#0E9F2D; font-size:1.1rem;"></i>
                            <span>Mode 2 : Reconnaissance Faciale</span>
                        </div>
                        <span id="badgeFaceStatus" style="font-size:0.72rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:6px; background:#F1F2F7; color:var(--text-muted);">Non enregistrée</span>
                    </div>
                    <input type="hidden" name="register_face" id="inputRegisterFace" value="">
                    <button type="button" id="btnToggleFace" style="width:100%; padding:0.5rem; border-radius:8px; border:1px solid #0E9F2D; background:#EBF9EE; color:#0E9F2D; font-weight:700; font-size:0.8rem; cursor:pointer; transition:all 0.2s ease;">
                        <i class="bi bi-camera-video"></i> Capturer le Visage (Caméra HD)
                    </button>
                </div>
            </div>

            <div style="margin-top:0.5rem; display:flex; gap:0.75rem; justify-content:flex-end;">
                <button type="button" class="btn-cancel" id="btnCancelCreateEmp" style="background:#F1F2F7; color:var(--text-dark); padding:0.65rem 1.25rem; border-radius:10px; border:none; font-weight:600; cursor:pointer;">Annuler</button>
                <button type="submit" class="btn-submit" style="background:var(--primary); color:#FFFFFF; padding:0.65rem 1.35rem; border-radius:10px; border:none; font-weight:600; cursor:pointer; box-shadow:0 4px 12px rgba(58, 91, 240, 0.28);">Enregistrer l'Employé</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Filter dropdown
        const filterBtn = document.getElementById('filterDropdownBtn');
        const filterMenu = document.getElementById('filterMenu');
        if (filterBtn && filterMenu) {
            filterBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                filterMenu.classList.toggle('show');
            });
            document.addEventListener('click', function (e) {
                if (!filterMenu.contains(e.target) && !filterBtn.contains(e.target)) {
                    filterMenu.classList.remove('show');
                }
            });
        }

        // Nouvel Employé Sliding Drawer Handlers
        const btnOpenCreate = document.getElementById('btnOpenCreateEmpDrawer');
        const createOverlay = document.getElementById('createEmpOverlay');
        const createPanel   = document.getElementById('createEmpPanel');
        const createClose   = document.getElementById('createEmpCloseBtn');
        const createCancel  = document.getElementById('btnCancelCreateEmp');

        function openCreateEmpDrawer() {
            if (createOverlay && createPanel) {
                createOverlay.classList.add('open');
                createPanel.classList.add('open');
            }
        }

        function closeCreateEmpDrawer() {
            if (createOverlay && createPanel) {
                createOverlay.classList.remove('open');
                createPanel.classList.remove('open');
            }
        }

        if (btnOpenCreate) btnOpenCreate.addEventListener('click', openCreateEmpDrawer);
        if (createClose) createClose.addEventListener('click', closeCreateEmpDrawer);
        if (createCancel) createCancel.addEventListener('click', closeCreateEmpDrawer);
        if (createOverlay) createOverlay.addEventListener('click', closeCreateEmpDrawer);

        // Biometric modes interactive scanner triggers
        const btnFp = document.getElementById('btnToggleFp');
        if (btnFp) {
            btnFp.addEventListener('click', function () {
                window.openFingerprintScanner('inputRegisterFp', 'badgeFpStatus', 'btnToggleFp');
            });
        }

        const btnFace = document.getElementById('btnToggleFace');
        if (btnFace) {
            btnFace.addEventListener('click', function () {
                window.openFaceCameraScanner('inputRegisterFace', 'badgeFaceStatus', 'btnToggleFace');
            });
        }

        // Drawer close
        const overlay = document.getElementById('drawerOverlay');
        const panel   = document.getElementById('drawerPanel');
        const closeBtn = document.getElementById('drawerCloseBtn');

        function closeDrawer() {
            overlay.classList.remove('open');
            panel.classList.remove('open');
        }
        overlay.addEventListener('click', closeDrawer);
        closeBtn.addEventListener('click', closeDrawer);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDrawer();
        });
    });

    const allDepartments = @json($departments);
    const csrfToken = "{{ csrf_token() }}";
    let currentEmpData = null;

    function openEmployeeDrawer(id) {
        const overlay = document.getElementById('drawerOverlay');
        const panel   = document.getElementById('drawerPanel');
        const loading = document.getElementById('drawerLoading');
        const content = document.getElementById('drawerContent');

        overlay.classList.add('open');
        panel.classList.add('open');
        loading.style.display = 'block';
        content.style.display = 'none';

        fetch('/employes/' + id, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                loading.style.display = 'none';
                content.style.display = 'block';
                content.innerHTML = buildDrawerHTML(data);
            })
            .catch(err => {
                loading.innerHTML = '<p style="color:var(--accent-red); padding:1rem;">Erreur de chargement.</p>';
                console.error(err);
            });
    }

    function showEditEmpForm() {
        if (!currentEmpData) return;
        const content = document.getElementById('drawerContent');
        content.innerHTML = buildEditDrawerHTML(currentEmpData);
        initEditBiometricToggles(currentEmpData);
    }

    function cancelEditEmpForm() {
        if (!currentEmpData) return;
        const content = document.getElementById('drawerContent');
        content.innerHTML = buildDrawerHTML(currentEmpData);
    }

    function initEditBiometricToggles(d) {
        const btnFp = document.getElementById('btnToggleEditFp');
        if (btnFp) {
            btnFp.addEventListener('click', function () {
                window.openFingerprintScanner('inputEditFp', 'badgeEditFpStatus', 'btnToggleEditFp');
            });
        }

        const btnFace = document.getElementById('btnToggleEditFace');
        if (btnFace) {
            btnFace.addEventListener('click', function () {
                window.openFaceCameraScanner('inputEditFace', 'badgeEditFaceStatus', 'btnToggleEditFace');
            });
        }
    }

    function buildEditDrawerHTML(d) {
        const deptOptions = allDepartments.map(dept => 
            `<option value="${dept.id}" ${dept.id == d.department_id ? 'selected' : ''}>${dept.name}</option>`
        ).join('');

        return `
            <form action="/employes/${d.id}" method="POST">
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="_method" value="PUT">

                <div style="display:flex; flex-direction:column; gap:1.1rem;">
                    <div style="font-weight:800; font-size:1.05rem; color:var(--text-dark); margin-bottom:0.2rem; display:flex; align-items:center; gap:0.4rem;">
                        <i class="bi bi-pencil-square" style="color:var(--primary);"></i> Modifier les informations
                    </div>

                    <div style="display:flex; gap:0.85rem;">
                        <div style="flex:1;">
                            <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Nom *</label>
                            <input type="text" name="last_name" value="${d.last_name}" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" required>
                        </div>
                        <div style="flex:1;">
                            <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Prénom *</label>
                            <input type="text" name="first_name" value="${d.first_name}" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" required>
                        </div>
                    </div>

                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Adresse E-mail *</label>
                        <input type="email" name="email" value="${d.email !== '—' ? d.email : ''}" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" required>
                    </div>

                    <div style="display:flex; gap:0.85rem;">
                        <div style="flex:1;">
                            <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Téléphone</label>
                            <input type="text" name="phone" value="${d.phone !== '—' ? d.phone : ''}" class="form-input" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;">
                        </div>
                        <div style="flex:1;">
                            <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Rôle *</label>
                            <select name="role" class="form-select" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;">
                                <option value="Employés" ${d.role === 'Employés' || d.role === 'Employé' ? 'selected' : ''}>Employé</option>
                                <option value="Manager" ${d.role === 'Manager' ? 'selected' : ''}>Manager / Chef Service</option>
                                <option value="RH_Admin" ${d.role === 'RH_Admin' ? 'selected' : ''}>Administrateur RH</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.35rem;">Département *</label>
                        <select name="department_id" class="form-select" style="width:100%; padding:0.6rem 0.85rem; border-radius:10px; border:1px solid #E2E7E7; font-size:0.88rem; box-sizing:border-box;" required>
                            <option value="">Sélectionner un département...</option>
                            ${deptOptions}
                        </select>
                    </div>

                    <!-- MODES D'ENREGISTREMENT BIOMÉTRIQUE -->
                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:14px; padding:1.1rem; margin-top:0.3rem;">
                        <div style="font-size:0.88rem; font-weight:700; color:var(--text-dark); margin-bottom:0.85rem; display:flex; align-items:center; gap:0.5rem;">
                            <i class="bi bi-shield-lock-fill" style="color:var(--primary);"></i>
                            <span>Modes d'Enregistrement Biométrique</span>
                        </div>

                        <!-- Mode 1: Empreinte Digitale -->
                        <div style="background:#FFFFFF; border:1px solid #E2E7E7; border-radius:10px; padding:0.85rem; margin-bottom:0.75rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                                <div style="font-weight:700; font-size:0.84rem; color:var(--text-dark); display:flex; align-items:center; gap:0.4rem;">
                                    <i class="bi bi-fingerprint" style="color:var(--primary); font-size:1.1rem;"></i>
                                    <span>Mode 1 : Empreinte Digitale</span>
                                </div>
                                <span id="badgeEditFpStatus" style="font-size:0.72rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:6px; background:${d.fingerprint_registered ? '#EBF9EE' : '#F1F2F7'}; color:${d.fingerprint_registered ? '#0E9F2D' : 'var(--text-muted)'};">${d.fingerprint_registered ? 'Enregistrée ✓' : 'Non enregistrée'}</span>
                            </div>
                            <input type="hidden" name="register_fingerprint" id="inputEditFp" value="${d.fingerprint_registered ? '1' : ''}">
                            <button type="button" id="btnToggleEditFp" style="width:100%; padding:0.5rem; border-radius:8px; border:1px solid ${d.fingerprint_registered ? '#0E9F2D' : 'var(--primary)'}; background:${d.fingerprint_registered ? '#0E9F2D' : '#EBF3FF'}; color:${d.fingerprint_registered ? '#FFFFFF' : 'var(--primary)'}; font-weight:700; font-size:0.8rem; cursor:pointer;">
                                ${d.fingerprint_registered ? '<i class="bi bi-check-circle-fill"></i> Empreinte Digitale Capturée' : '<i class="bi bi-plus-circle"></i> Capturer l\'Empreinte (Lecteur USB)'}
                            </button>
                        </div>

                        <!-- Mode 2: Reconnaissance Faciale -->
                        <div style="background:#FFFFFF; border:1px solid #E2E7E7; border-radius:10px; padding:0.85rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                                <div style="font-weight:700; font-size:0.84rem; color:var(--text-dark); display:flex; align-items:center; gap:0.4rem;">
                                    <i class="bi bi-person-bounding-box" style="color:#0E9F2D; font-size:1.1rem;"></i>
                                    <span>Mode 2 : Reconnaissance Faciale</span>
                                </div>
                                <span id="badgeEditFaceStatus" style="font-size:0.72rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:6px; background:${d.face_registered ? '#EBF9EE' : '#F1F2F7'}; color:${d.face_registered ? '#0E9F2D' : 'var(--text-muted)'};">${d.face_registered ? 'Enregistrée ✓' : 'Non enregistrée'}</span>
                            </div>
                            <input type="hidden" name="register_face" id="inputEditFace" value="${d.face_registered ? '1' : ''}">
                            <button type="button" id="btnToggleEditFace" style="width:100%; padding:0.5rem; border-radius:8px; border:1px solid #0E9F2D; background:${d.face_registered ? '#0E9F2D' : '#EBF9EE'}; color:${d.face_registered ? '#FFFFFF' : '#0E9F2D'}; font-weight:700; font-size:0.8rem; cursor:pointer;">
                                ${d.face_registered ? '<i class="bi bi-check-circle-fill"></i> Visage Capturé' : '<i class="bi bi-camera-video"></i> Capturer le Visage (Caméra HD)'}
                            </button>
                        </div>
                    </div>

                    <div style="margin-top:0.5rem; display:flex; gap:0.75rem; justify-content:flex-end;">
                        <button type="button" onclick="cancelEditEmpForm()" class="btn-cancel" style="background:#F1F2F7; color:var(--text-dark); padding:0.65rem 1.25rem; border-radius:10px; border:none; font-weight:600; cursor:pointer;">Annuler</button>
                        <button type="submit" class="btn-submit" style="background:var(--primary); color:#FFFFFF; padding:0.65rem 1.35rem; border-radius:10px; border:none; font-weight:600; cursor:pointer; box-shadow:0 4px 12px rgba(58, 91, 240, 0.28);">Enregistrer les Modifications</button>
                    </div>
                </div>
            </form>
        `;
    }

    function buildDrawerHTML(d) {
        currentEmpData = d;

        // Weekly timeline
        let weeklyHtml = '';
        if (d.weekly_stats && d.weekly_stats.length) {
            d.weekly_stats.forEach(ws => {
                weeklyHtml += `
                    <div class="timeline-row">
                        <span class="timeline-day">${ws.jour}</span>
                        <span class="timeline-badge ${ws.status}">${ws.status === 'Present' ? 'A l\'heure' : (ws.status === 'Retard' ? 'En retard' : 'Absent')}</span>
                    </div>`;
            });
        } else {
            weeklyHtml = '<p style="color:var(--text-muted);font-size:0.82rem;">Aucune donnée</p>';
        }

        // Last pointages table
        let ptgsHtml = '';
        if (d.last_pointages && d.last_pointages.length) {
            d.last_pointages.forEach(p => {
                const cls = p.status === 'Present' ? 'status-ok' : (p.status === 'Retard' ? 'status-retard' : 'status-absent');
                const label = p.status === 'Present' ? 'A l\'heure' : (p.status === 'Retard' ? 'En retard' : 'Absent');
                ptgsHtml += `
                    <tr>
                        <td>${p.date}</td>
                        <td>${p.heure}</td>
                        <td><span class="badge-type ${p.type === 'ENTREE' ? 'badge-entree' : 'badge-sortie'}">${p.type}</span></td>
                        <td><span class="badge-status ${cls}">${label}</span></td>
                    </tr>`;
            });
        }

        return `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem; background:#F8FAFC; padding:0.75rem 1rem; border-radius:12px; border:1px solid #E2E8F0;">
                <button type="button" onclick="showEditEmpForm()" style="background:#EBF3FF; color:var(--primary); border:none; padding:0.45rem 0.9rem; border-radius:8px; font-weight:700; font-size:0.8rem; cursor:pointer; display:inline-flex; align-items:center; gap:0.35rem;">
                    <i class="bi bi-pencil-square"></i> Modifier l'Employé
                </button>

                <form action="/employes/${d.id}" method="POST" onsubmit="return confirm('Confirmez-vous la suppression définitve de cet employé ?');" style="margin:0;">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" style="background:#FDE8E8; color:var(--accent-red); border:none; padding:0.45rem 0.9rem; border-radius:8px; font-weight:700; font-size:0.8rem; cursor:pointer; display:inline-flex; align-items:center; gap:0.35rem;">
                        <i class="bi bi-trash3-fill"></i> Supprimer
                    </button>
                </form>
            </div>

            <div class="drawer-profile">
                <div class="drawer-avatar">${d.initials}</div>
                <div>
                    <div class="drawer-name">${d.full_name}</div>
                    <div class="drawer-dept">${d.department}</div>
                    <div style="display:flex;align-items:center;gap:0.4rem;margin-top:0.2rem;">
                        <span class="drawer-role-badge">${d.role}</span>
                        <span style="font-size:0.72rem;font-weight:600;color:var(--text-muted);">N° ${d.matricule}</span>
                    </div>
                </div>
            </div>

            <div class="drawer-info-row">
                <div class="drawer-info-item">
                    <i class="bi bi-envelope-fill"></i>
                    <span>${d.email}</span>
                </div>
                <div class="drawer-info-item">
                    <i class="bi bi-telephone-fill"></i>
                    <span>${d.phone}</span>
                </div>
            </div>

            <!-- MODES DE POINTAGE BIOMÉTRIQUE ENREGISTRÉS -->
            <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:14px; padding:0.85rem 1rem; margin-bottom:1.1rem;">
                <div style="font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.6rem; display:flex; align-items:center; gap:0.4rem;">
                    <i class="bi bi-shield-lock-fill" style="color:var(--primary);"></i>
                    <span>Modes de Pointage Biométrique d'Accueil Enregistrés</span>
                </div>
                <div style="display:flex; gap:0.65rem;">
                    <div style="flex:1; background:#FFFFFF; border:1px solid #E2E7E7; border-radius:10px; padding:0.6rem 0.75rem; display:flex; align-items:center; justify-content:space-between;">
                        <div style="font-size:0.78rem; font-weight:700; color:var(--text-dark); display:flex; align-items:center; gap:0.35rem;">
                            <i class="bi bi-fingerprint" style="color:var(--primary); font-size:1.05rem;"></i>
                            <span>Empreinte USB</span>
                        </div>
                        <span style="font-size:0.72rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:6px; background:${d.fingerprint_registered ? '#EBF9EE' : '#F1F2F7'}; color:${d.fingerprint_registered ? '#0E9F2D' : 'var(--text-muted)'};">
                            ${d.fingerprint_registered ? 'Actif ✓' : 'Inactif'}
                        </span>
                    </div>

                    <div style="flex:1; background:#FFFFFF; border:1px solid #E2E7E7; border-radius:10px; padding:0.6rem 0.75rem; display:flex; align-items:center; justify-content:space-between;">
                        <div style="font-size:0.78rem; font-weight:700; color:var(--text-dark); display:flex; align-items:center; gap:0.35rem;">
                            <i class="bi bi-person-bounding-box" style="color:#0E9F2D; font-size:1.05rem;"></i>
                            <span>Visage HD</span>
                        </div>
                        <span style="font-size:0.72rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:6px; background:${d.face_registered ? '#EBF9EE' : '#F1F2F7'}; color:${d.face_registered ? '#0E9F2D' : 'var(--text-muted)'};">
                            ${d.face_registered ? 'Actif ✓' : 'Inactif'}
                        </span>
                    </div>
                </div>
            </div>

            <div class="drawer-stats-grid">
                <div class="stat-card stat-green">
                    <div class="stat-value">${d.presents}</div>
                    <div class="stat-label">Présents</div>
                    <div class="stat-pct">${d.taux_presence}%</div>
                </div>
                <div class="stat-card stat-orange">
                    <div class="stat-value">${d.retards}</div>
                    <div class="stat-label">Retards</div>
                    <div class="stat-pct">${d.taux_retard}%</div>
                </div>
                <div class="stat-card stat-red">
                    <div class="stat-value">${d.absences}</div>
                    <div class="stat-label">Absences</div>
                    <div class="stat-pct">${d.taux_absence}%</div>
                </div>
            </div>

            <div class="drawer-section-title"><i class="bi bi-calendar-week"></i> Assiduité Récente (7 jours)</div>
            <div class="weekly-timeline">${weeklyHtml}</div>

            <div class="drawer-section-title"><i class="bi bi-clock-history"></i> Derniers Pointages</div>
            <table class="drawer-mini-table">
                <thead><tr><th>Date</th><th>Heure</th><th>Type</th><th>Statut</th></tr></thead>
                <tbody>${ptgsHtml || '<tr><td colspan="4" style="text-align:center;color:var(--text-muted);">—</td></tr>'}</tbody>
            </table>
        `;
    }
</script>
@endsection
