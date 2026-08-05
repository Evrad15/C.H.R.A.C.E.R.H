@extends('layouts.dashboard')

@section('title', 'Congés et Permissions | C.H.R.A.C.E.R.H')

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

    .btn-create-conge {
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
    .btn-create-conge:hover {
        background: #2a48d8 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 18px rgba(58, 91, 240, 0.38) !important;
    }

    /* Top Stats Grid */
    .conge-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 1.75rem;
    }

    .conge-stat-card {
        background: #FFFFFF;
        border-radius: 14px;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .conge-stat-card:hover {
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
    .icon-attente { background: rgba(255, 155, 67, 0.15); color: #D97300; }
    .icon-approuve { background: rgba(22, 222, 66, 0.15); color: #0E9F2D; }
    .icon-refuse { background: rgba(242, 56, 58, 0.12); color: var(--accent-red); }

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

    .conge-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.4rem;
    }

    .conge-table th {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.75rem 1rem;
        border-bottom: 2px solid #F1F2F7;
        text-align: left;
    }

    .conge-table tbody tr {
        background: #FFFFFF;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .conge-table tbody tr:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    .conge-table td {
        padding: 0.85rem 1rem;
        font-size: 0.9rem;
        color: var(--text-dark);
        border-top: 1px solid #F6F7FB;
        border-bottom: 1px solid #F6F7FB;
    }
    .conge-table td:first-child {
        border-left: 1px solid #F6F7FB;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
    }
    .conge-table td:last-child {
        border-right: 1px solid #F6F7FB;
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }

    .emp-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .emp-avatar {
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
    .emp-name {
        font-weight: 700;
        color: var(--text-dark);
    }
    .emp-dept {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    /* Badges de Statut et Types */
    .badge-type {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.76rem;
        font-weight: 600;
        background: #F1F2F7;
        color: var(--text-dark);
    }

    .badge-statut {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.7rem;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
    }
    .statut-attente { background: rgba(255, 155, 67, 0.15); color: #D97300; }
    .statut-approuve { background: rgba(22, 222, 66, 0.15); color: #0E9F2D; }
    .statut-refuse   { background: rgba(242, 56, 58, 0.12); color: var(--accent-red); }

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
    .modal-backdrop.show { opacity: 1; pointer-events: auto; }

    .modal-card {
        background: #FFFFFF;
        border-radius: 18px;
        width: 500px;
        max-width: 90%;
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        transform: translateY(20px);
        transition: transform 0.25s ease;
        overflow: hidden;
    }
    .modal-backdrop.show .modal-card { transform: translateY(0); }

    .modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #E8EBF2;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-title { font-size: 1.15rem; font-weight: 700; color: var(--text-dark); margin: 0; }
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

    .modal-body { padding: 1.5rem; }

    .form-group { margin-bottom: 1rem; }

    /* Searchable Employee Component */
    .emp-search-wrapper {
        position: relative;
    }
    .emp-search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #FFFFFF;
        border: 1px solid #E2E7E7;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        max-height: 220px;
        overflow-y: auto;
        z-index: 1060;
        margin-top: 4px;
        display: none;
    }
    .emp-search-item {
        padding: 0.65rem 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        transition: background 0.15s ease;
        border-bottom: 1px solid #F6F7FB;
    }
    .emp-search-item:last-child { border-bottom: none; }
    .emp-search-item:hover {
        background: #F1F5F9;
    }
    .emp-search-item .item-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--primary);
        color: #fff;
        font-weight: 700;
        font-size: 0.78rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .emp-search-item .item-name {
        font-weight: 700;
        font-size: 0.86rem;
        color: var(--text-dark);
    }
    .emp-search-item .item-dept {
        font-size: 0.75rem;
        color: var(--text-muted);
    }
    .selected-emp-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #F8FAFC;
        border: 1.5px solid var(--primary);
        padding: 0.65rem 0.85rem;
        border-radius: 12px;
    }
    .selected-emp-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .btn-change-emp {
        background: #EBF3FF;
        color: var(--primary);
        border: none;
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-change-emp:hover {
        background: var(--primary);
        color: #fff;
    }
    .form-label { display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.35rem; }
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
    .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--primary); }
    .form-row { display: flex; gap: 1rem; }
    .form-row .form-group { flex: 1; }

    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #E8EBF2;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }
    .btn-cancel { background: #F1F2F7; color: var(--text-dark); padding: 0.6rem 1.25rem; border-radius: 10px; border: none; font-weight: 600; cursor: pointer; }
    .btn-submit { background: var(--primary); color: #FFFFFF; padding: 0.6rem 1.25rem; border-radius: 10px; border: none; font-weight: 600; cursor: pointer; }

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
    <h1 class="page-title">Congés et Permissions</h1>
    <button class="btn-create-conge" id="btnOpenModal">
        <i class="bi bi-plus-lg"></i>
        <span>Nouvelle Demande</span>
    </button>
</div>

<!-- Top Stats Cards -->
<div class="conge-stats-grid">
    <div class="conge-stat-card">
        <div class="stat-icon-wrapper icon-total">
            <i class="bi bi-file-earmark-text"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Demandes</div>
        </div>
    </div>
    <div class="conge-stat-card">
        <div class="stat-icon-wrapper icon-attente">
            <i class="bi bi-hourglass-split"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['en_attente'] }}</div>
            <div class="stat-label">En Attente</div>
        </div>
    </div>
    <div class="conge-stat-card">
        <div class="stat-icon-wrapper icon-approuve">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['approuve'] }}</div>
            <div class="stat-label">Approuvées</div>
        </div>
    </div>
    <div class="conge-stat-card">
        <div class="stat-icon-wrapper icon-refuse">
            <i class="bi bi-x-circle"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['refuse'] }}</div>
            <div class="stat-label">Refusées</div>
        </div>
    </div>
</div>

<!-- Toolbar (Filtres et Recherche) -->
<div class="toolbar-flex">
    <div class="left-tools">
        <!-- Filtre par Statut -->
        <select class="filter-select" onchange="applyFilters()" id="filterStatut">
            <option value="">Tous les statuts</option>
            <option value="En attente" {{ request('statut') == 'En attente' ? 'selected' : '' }}>En attente</option>
            <option value="Approuvé" {{ request('statut') == 'Approuvé' ? 'selected' : '' }}>Approuvé</option>
            <option value="Refusé" {{ request('statut') == 'Refusé' ? 'selected' : '' }}>Refusé</option>
        </select>

        <!-- Filtre par Type -->
        <select class="filter-select" onchange="applyFilters()" id="filterType">
            <option value="">Tous les types</option>
            @foreach($typesConges as $tc)
            <option value="{{ $tc }}" {{ request('type') == $tc ? 'selected' : '' }}>{{ $tc }}</option>
            @endforeach
        </select>
    </div>

    <!-- Recherche Textuelle -->
    <form action="{{ route('conges.index') }}" method="GET" class="inpage-search-box" id="searchForm">
        @if(request('statut')) <input type="hidden" name="statut" value="{{ request('statut') }}"> @endif
        @if(request('type')) <input type="hidden" name="type" value="{{ request('type') }}"> @endif
        
        <i class="bi bi-search"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher employé, motif...">
    </form>
</div>

<!-- Tableau des Congés et Permissions -->
<div class="table-container">
    <table class="conge-table">
        <thead>
            <tr>
                <th style="width: 25%;">Employé</th>
                <th style="width: 20%;">Type de Demande</th>
                <th style="width: 22%;">Période</th>
                <th style="width: 18%;">Motif</th>
                <th style="width: 15%;">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($conges as $c)
            <tr onclick="openCongeDetails({{ json_encode($c) }})">
                <td>
                    <div class="emp-info">
                        <div class="emp-avatar">
                            {{ strtoupper(substr($c->employe->last_name ?? 'E', 0, 1) . substr($c->employe->first_name ?? 'M', 0, 1)) }}
                        </div>
                        <div>
                            <div class="emp-name">{{ strtoupper($c->employe->last_name ?? '') }} {{ $c->employe->first_name ?? 'Employé' }}</div>
                            <div class="emp-dept">{{ $c->employe->department->name ?? 'Général' }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge-type">{{ $c->type }}</span>
                </td>
                <td>
                    @php
                        $start = \Carbon\Carbon::parse($c->date_debut);
                        $end   = \Carbon\Carbon::parse($c->date_fin);
                        $days  = $start->diffInDays($end) + 1;
                    @endphp
                    <div style="font-weight:600; color:var(--text-dark);">
                        <i class="bi bi-calendar-range" style="color:var(--primary); margin-right:0.3rem;"></i>
                        {{ $start->format('d/m/Y') }} → {{ $end->format('d/m/Y') }}
                    </div>
                    <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.15rem;">
                        Durée : <strong>{{ $days }} jour(s)</strong>
                    </div>
                </td>
                <td>
                    <div style="font-weight:500; color:var(--text-dark);">{{ Str::limit($c->motif, 35) }}</div>
                </td>
                <td>
                    @php
                        $badgeClass = match($c->statut) {
                            'En attente' => 'statut-attente',
                            'Approuvé'   => 'statut-approuve',
                            'Refusé'     => 'statut-refuse',
                            default      => 'statut-attente',
                        };
                    @endphp
                    <span class="badge-statut {{ $badgeClass }}">
                        <i class="bi bi-circle-fill" style="font-size:0.45rem;"></i>
                        {{ $c->statut }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; color:var(--text-muted); padding:2.5rem;">
                    <i class="bi bi-file-earmark-x" style="font-size:2rem; display:block; margin-bottom:0.5rem; opacity:0.5;"></i>
                    Aucune demande de congé trouvée.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination sur-mesure -->
    @if($conges->hasPages())
    <div class="pagination-wrapper">
        <div class="pagination-info">
            Affichage de <strong>{{ $conges->firstItem() }}</strong> à <strong>{{ $conges->lastItem() }}</strong> sur <strong>{{ $conges->total() }}</strong> demandes
        </div>

        <ul class="custom-pagination">
            @if ($conges->onFirstPage())
                <li class="disabled"><span><i class="bi bi-chevron-left"></i></span></li>
            @else
                <li><a href="{{ $conges->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a></li>
            @endif

            @foreach ($conges->getUrlRange(1, $conges->lastPage()) as $page => $url)
                @if ($page == $conges->currentPage())
                    <li class="active"><span>{{ $page }}</span></li>
                @else
                    <li><a href="{{ $url }}">{{ $page }}</a></li>
                @endif
            @endforeach

            @if ($conges->hasMorePages())
                <li><a href="{{ $conges->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a></li>
            @else
                <li class="disabled"><span><i class="bi bi-chevron-right"></i></span></li>
            @endif
        </ul>
    </div>
    @endif
</div>

<!-- Modal Nouvelle Demande -->
<div class="modal-backdrop" id="congeModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 class="modal-title">Nouvelle Demande de Congé / Permission</h3>
            <button class="modal-close" id="btnCloseModal">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form action="{{ route('conges.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Employé concerné *</label>
                    <input type="hidden" name="employe_id" id="selectedEmployeId" required>

                    <div id="empSearchContainer" class="emp-search-wrapper">
                        <div style="position:relative;">
                            <input type="text" id="empSearchInput" class="form-input" placeholder="🔍 Saisissez un nom, prénom ou département..." autocomplete="off">
                        </div>
                        <div id="empSearchResults" class="emp-search-results"></div>
                    </div>

                    <div id="selectedEmpCard" class="selected-emp-card" style="display:none;">
                        <div class="selected-emp-info">
                            <div class="emp-avatar" id="selectedEmpAvatar">EM</div>
                            <div>
                                <div class="emp-name" id="selectedEmpName">NOM Prénom</div>
                                <div class="emp-dept" id="selectedEmpDept">Département</div>
                            </div>
                        </div>
                        <button type="button" class="btn-change-emp" id="btnChangeEmp">
                            <i class="bi bi-arrow-repeat"></i> Changer
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Type de Demande *</label>
                    <select name="type" class="form-select" required>
                        @foreach($typesConges as $tc)
                        <option value="{{ $tc }}">{{ $tc }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Date Début *</label>
                        <input type="date" name="date_debut" class="form-input" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date Fin *</label>
                        <input type="date" name="date_fin" class="form-input" value="{{ date('Y-m-d', strtotime('+3 days')) }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Motif de la Demande *</label>
                    <textarea name="motif" class="form-textarea" rows="3" placeholder="Expliquez la raison de la demande..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="btnCancelModal">Annuler</button>
                <button type="submit" class="btn-submit">Soumettre la Demande</button>
            </div>
        </form>
    </div>
</div>

<!-- Drawer Fiche Demande -->
<div class="drawer-overlay" id="drawerOverlay"></div>
<div class="drawer-panel" id="drawerPanel">
    <div class="drawer-header" style="display:flex; justify-content:space-between; align-items:center; padding:1.25rem 1.5rem; border-bottom:1px solid #E8EBF2;">
        <span style="font-weight: 700; color: var(--text-dark);">Fiche de Demande</span>
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
        const modal = document.getElementById('congeModal');
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
        const statut = document.getElementById('filterStatut').value;
        const type   = document.getElementById('filterType').value;
        const search = document.querySelector('.inpage-search-box input[name="search"]').value;

        const params = new URLSearchParams();
        if (statut) params.set('statut', statut);
        if (type)   params.set('type', type);
        if (search) params.set('search', search);

        window.location.href = "{{ route('conges.index') }}?" + params.toString();
    }

    function openCongeDetails(c) {
        const overlay = document.getElementById('drawerOverlay');
        const panel   = document.getElementById('drawerPanel');
        const content = document.getElementById('drawerContent');

        overlay.classList.add('open');
        panel.classList.add('open');

        const empName = c.employe ? (c.employe.last_name.toUpperCase() + ' ' + c.employe.first_name) : 'Employé Inconnu';
        const empDept = c.employe && c.employe.department ? c.employe.department.name : 'Général';
        const initials = c.employe ? (c.employe.last_name.charAt(0) + c.employe.first_name.charAt(0)).toUpperCase() : 'E';

        content.innerHTML = `
            <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
                <div style="width:52px; height:52px; border-radius:50%; background:linear-gradient(135deg, #3A5BF0, #6C88FF); color:#FFF; font-weight:800; font-size:1.2rem; display:flex; align-items:center; justify-content:center;">
                    ${initials}
                </div>
                <div>
                    <div style="font-size:1.15rem; font-weight:700; color:var(--text-dark);">${empName}</div>
                    <div style="font-size:0.82rem; color:var(--text-muted); font-weight:500;">Département : ${empDept}</div>
                </div>
            </div>

            <div style="background:#F6F7FB; border-radius:12px; padding:1rem; margin-bottom:1.25rem;">
                <div style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Statut de la demande</div>
                <div style="font-size:1.1rem; font-weight:800; color:var(--primary); margin-top:0.2rem;">${c.statut}</div>
            </div>

            <div style="display:flex; flex-direction:column; gap:0.85rem; font-size:0.88rem; margin-bottom:1.5rem;">
                <div><strong>Type de Congé :</strong> ${c.type}</div>
                <div><strong>Date Début :</strong> ${new Date(c.date_debut).toLocaleDateString('fr-FR')}</div>
                <div><strong>Date Fin :</strong> ${new Date(c.date_fin).toLocaleDateString('fr-FR')}</div>
                <div><strong>Motif :</strong> ${c.motif}</div>
                ${c.commentaire_rh ? `<div><strong>Commentaire RH :</strong> ${c.commentaire_rh}</div>` : ''}
            </div>

            <div style="border-top:1px solid #E8EBF2; padding-top:1rem;">
                <div style="font-size:0.82rem; font-weight:700; color:var(--text-dark); margin-bottom:0.5rem;">Décision RH :</div>
                <form action="/conges/${c.id}/status" method="POST" style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="PATCH">
                    <button type="submit" name="statut" value="Approuvé" class="btn-cancel" style="background:#EBF9EE; color:#0E9F2D;">Approuver</button>
                    <button type="submit" name="statut" value="Refusé" class="btn-cancel" style="background:#FDE8E8; color:var(--accent-red);">Refuser</button>
                    <button type="submit" name="statut" value="En attente" class="btn-cancel" style="background:#FFF6EB; color:#D97300;">Mettre En Attente</button>
                </form>
            </div>
        `;
    }

    // Live Search Employee Selection Component
    const employesData = @json($employesList);
    const searchInput     = document.getElementById('empSearchInput');
    const resultsBox      = document.getElementById('empSearchResults');
    const hiddenId        = document.getElementById('selectedEmployeId');
    const searchContainer = document.getElementById('empSearchContainer');
    const selectedCard    = document.getElementById('selectedEmpCard');
    const btnChange       = document.getElementById('btnChangeEmp');
    const avatarBox       = document.getElementById('selectedEmpAvatar');
    const nameBox         = document.getElementById('selectedEmpName');
    const deptBox         = document.getElementById('selectedEmpDept');

    function renderResults(query = '') {
        const cleanQuery = query.toLowerCase().trim();
        const filtered = employesData.filter(emp => {
            const fullName = `${emp.last_name} ${emp.first_name}`.toLowerCase();
            const deptName = emp.department ? emp.department.name.toLowerCase() : '';
            return fullName.includes(cleanQuery) || deptName.includes(cleanQuery);
        });

        if (filtered.length === 0) {
            resultsBox.innerHTML = '<div style="padding:0.85rem; text-align:center; color:var(--text-muted); font-size:0.82rem;">Aucun employé trouvé.</div>';
        } else {
            resultsBox.innerHTML = filtered.map(emp => {
                const initials = ((emp.last_name ? emp.last_name[0] : 'E') + (emp.first_name ? emp.first_name[0] : 'M')).toUpperCase();
                const dept = emp.department ? emp.department.name : 'Général';
                return `
                    <div class="emp-search-item" data-id="${emp.id}" data-name="${emp.last_name.toUpperCase()} ${emp.first_name}" data-dept="${dept}" data-initials="${initials}">
                        <div class="item-avatar">${initials}</div>
                        <div>
                            <div class="item-name">${emp.last_name.toUpperCase()} ${emp.first_name}</div>
                            <div class="item-dept">${dept}</div>
                        </div>
                    </div>
                `;
            }).join('');
        }
        resultsBox.style.display = 'block';
    }

    if (searchInput) {
        searchInput.addEventListener('focus', () => renderResults(searchInput.value));
        searchInput.addEventListener('input', () => renderResults(searchInput.value));

        document.addEventListener('click', (e) => {
            if (searchContainer && !searchContainer.contains(e.target)) {
                resultsBox.style.display = 'none';
            }
        });

        resultsBox.addEventListener('click', (e) => {
            const item = e.target.closest('.emp-search-item');
            if (item) {
                const id = item.getAttribute('data-id');
                const name = item.getAttribute('data-name');
                const dept = item.getAttribute('data-dept');
                const initials = item.getAttribute('data-initials');

                hiddenId.value = id;
                nameBox.textContent = name;
                deptBox.textContent = dept;
                avatarBox.textContent = initials;

                searchContainer.style.display = 'none';
                selectedCard.style.display = 'flex';
                resultsBox.style.display = 'none';
            }
        });

        btnChange.addEventListener('click', () => {
            hiddenId.value = '';
            searchInput.value = '';
            selectedCard.style.display = 'none';
            searchContainer.style.display = 'block';
            searchInput.focus();
        });
    }
</script>
@endsection
