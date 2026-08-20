@extends('layouts.dashboard')

@section('title', 'Rapports & Archives | C.H.R.A.C.E.R.H')

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

    .btn-create-rapport {
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
    .btn-create-rapport:hover {
        background: #2a48d8 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 18px rgba(58, 91, 240, 0.38) !important;
    }

    /* Top Stats Grid */
    .rapport-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 1.75rem;
    }

    .rapport-stat-card {
        background: #FFFFFF;
        border-radius: 14px;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .rapport-stat-card:hover {
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
    .icon-files { background: rgba(58, 91, 240, 0.12); color: var(--primary); }
    .icon-month { background: rgba(22, 222, 66, 0.15); color: #0E9F2D; }
    .icon-db { background: rgba(14, 159, 45, 0.15); color: #07691C; }
    .icon-date { background: rgba(255, 155, 67, 0.15); color: #D97300; }

    .stat-info .stat-value {
        font-size: 1.4rem;
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

    /* Quick Action Generation Cards */
    .quick-gen-section {
        margin-bottom: 2rem;
    }
    .section-subtitle {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .quick-gen-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
    }
    .quick-card {
        background: #FFFFFF;
        border-radius: 14px;
        padding: 1.25rem;
        border: 1px solid #E8EBF2;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.2s ease;
    }
    .quick-card:hover {
        border-color: var(--primary);
        box-shadow: 0 8px 20px rgba(58, 91, 240, 0.12);
        transform: translateY(-2px);
    }
    .quick-card-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--text-dark);
        margin-bottom: 0.35rem;
    }
    .quick-card-desc {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-bottom: 1.25rem;
    }
    .btn-quick-gen {
        width: 100%;
        padding: 0.55rem;
        border-radius: 8px;
        border: none;
        background: var(--primary-light);
        color: var(--primary);
        font-weight: 700;
        font-size: 0.82rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        transition: all 0.2s ease;
    }
    .quick-card:hover .btn-quick-gen {
        background: var(--primary);
        color: #FFFFFF;
    }

    /* Library Accordion Folders */
    .library-section {
        background: #FFFFFF;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .folder-card {
        border: 1px solid #E8EBF2;
        border-radius: 12px;
        margin-bottom: 1rem;
        overflow: hidden;
        transition: border-color 0.2s ease;
    }
    .folder-header {
        background: #F8FAFC;
        padding: 1rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        user-select: none;
    }
    .folder-header:hover {
        background: #F1F5F9;
    }
    .folder-title-flex {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 700;
        color: var(--text-dark);
        font-size: 0.98rem;
    }
    .folder-count-badge {
        background: rgba(58, 91, 240, 0.1);
        color: var(--primary);
        padding: 0.2rem 0.65rem;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .folder-files-list {
        padding: 0.75rem 1.25rem 1.25rem 1.25rem;
        border-top: 1px solid #E8EBF2;
        background: #FFFFFF;
        display: block;
    }

    .file-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.65rem 0.85rem;
        border-radius: 8px;
        transition: background 0.15s ease;
    }
    .file-row:hover {
        background: #F8FAFC;
    }
    .file-info-flex {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .file-icon {
        font-size: 1.3rem;
    }
    .icon-excel { color: #107C41; }
    .icon-pdf   { color: #E81123; }

    .file-name {
        font-weight: 600;
        font-size: 0.88rem;
        color: var(--text-dark);
    }
    .file-meta {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .file-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-file-action {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-open { background: #EBF3FF; color: var(--primary); }
    .btn-open:hover { background: var(--primary); color: #FFFFFF; }
    
    .btn-dl { background: #EBF9EE; color: #0E9F2D; }
    .btn-dl:hover { background: #0E9F2D; color: #FFFFFF; }

    .btn-del { background: #FDE8E8; color: var(--accent-red); }
    .btn-del:hover { background: var(--accent-red); color: #FFFFFF; }

    /* Modal Styling */
    .rapport-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(31, 56, 76, 0.6);
        backdrop-filter: blur(4px);
        z-index: 999999;
        display: none;
        align-items: center;
        justify-content: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }
    .rapport-modal-overlay.open {
        display: flex !important;
        opacity: 1 !important;
        pointer-events: auto !important;
    }

    .modal-card {
        background: #FFFFFF;
        border-radius: 18px;
        width: 500px;
        max-width: 92vw;
        box-shadow: 0 25px 60px rgba(0,0,0,0.22);
        transform: translateY(20px);
        transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        overflow: hidden;
    }
    .rapport-modal-overlay.open .modal-card {
        transform: translateY(0);
    }

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
    .form-label { display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.35rem; }
    .form-input, .form-select {
        width: 100%;
        padding: 0.6rem 0.85rem;
        border-radius: 10px;
        border: 1px solid #E2E7E7;
        font-size: 0.88rem;
        outline: none;
        transition: border 0.2s ease;
        box-sizing: border-box;
    }
    .form-input:focus, .form-select:focus { border-color: var(--primary); }
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
    .btn-submit {
        background: var(--primary);
        color: #FFFFFF;
        padding: 0.6rem 1.35rem;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(58, 91, 240, 0.28);
        transition: background 0.2s ease, transform 0.15s ease;
    }
    .btn-submit:hover { background: #2a48d8; transform: translateY(-1px); }

    /* Searchable Employee Autocomplete */
    .emp-search-wrapper { position: relative; }
    .emp-search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #FFFFFF;
        border: 1px solid #CBD5E1;
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
        border-bottom: 1px solid #F1F5F9;
    }
    .emp-search-item:last-child { border-bottom: none; }
    .emp-search-item:hover { background: #F8FAFC; }
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
    .btn-change-emp:hover { background: var(--primary); color: #FFF; }
</style>
@endsection

@section('content')
<!-- Header Page -->
<div class="page-header-flex">
    <h1 class="page-title">Génération & Bibliothèque des Rapports</h1>
    <button type="button" class="btn-create-rapport" id="btnOpenModal" onclick="openRapportModal()">
        <i class="bi bi-file-earmark-plus-fill"></i>
        <span>Générer un Rapport</span>
    </button>
</div>

@if(session('success'))
<div style="background: #EBF9EE; color: #0E9F2D; padding: 0.85rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; border: 1px solid #C3E9C8;">
    <i class="bi bi-check-circle-fill" style="font-size: 1.1rem;"></i>
    <span>{{ session('success') }}</span>
</div>
@endif

@if(session('error'))
<div style="background: #FDE8E8; color: #E81123; padding: 0.85rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; border: 1px solid #F8B4B4;">
    <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.1rem;"></i>
    <span>{{ session('error') }}</span>
</div>
@endif

<!-- Top Stats Cards -->
<div class="rapport-stats-grid">
    <div class="rapport-stat-card">
        <div class="stat-icon-wrapper icon-files">
            <i class="bi bi-folder-fill"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['total_files'] }}</div>
            <div class="stat-label">Fichiers Générés</div>
        </div>
    </div>
    <div class="rapport-stat-card">
        <div class="stat-icon-wrapper icon-month">
            <i class="bi bi-calendar-check-fill"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['current_month'] }}</div>
            <div class="stat-label">Ce Mois-ci</div>
        </div>
    </div>
    <div class="rapport-stat-card">
        <div class="stat-icon-wrapper icon-db">
            <i class="bi bi-database-fill"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $stats['db_total'] }}</div>
            <div class="stat-label">Synthèses BDD</div>
        </div>
    </div>
    <div class="rapport-stat-card">
        <div class="stat-icon-wrapper icon-date">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value" style="font-size:1rem;">{{ Str::limit($stats['latest_file'], 14) }}</div>
            <div class="stat-label">Dernier Rapport</div>
        </div>
    </div>
</div>

<!-- Library Section (Dossiers et Fichiers) -->
<div class="library-section">
    <div class="section-subtitle" style="margin-bottom:1.25rem;">
        <i class="bi bi-folder2-open" style="color:var(--primary);"></i>
        <span>Bibliothèque des Archives par Mois</span>
    </div>

    @forelse($foldersInfo as $folder)
    <div class="folder-card">
        <div class="folder-header" onclick="toggleFolder('folder-{{ Str::slug($folder['name']) }}')">
            <div class="folder-title-flex">
                <i class="bi bi-folder-fill" style="color:var(--primary); font-size:1.2rem;"></i>
                <span>Dossier : {{ $folder['name'] }}</span>
            </div>
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <span class="folder-count-badge">{{ $folder['count'] }} fichier(s)</span>
                <i class="bi bi-chevron-down" style="font-size:0.85rem; color:var(--text-muted);"></i>
            </div>
        </div>

        <div class="folder-files-list" id="folder-{{ Str::slug($folder['name']) }}" style="display: block;">
            @foreach($folder['files'] as $f)
            <div class="file-row">
                <div class="file-info-flex">
                    @if($f['extension'] === 'xlsx' || $f['extension'] === 'xls')
                        <i class="bi bi-file-earmark-excel-fill file-icon icon-excel"></i>
                    @else
                        <i class="bi bi-file-earmark-pdf-fill file-icon icon-pdf"></i>
                    @endif
                    <div>
                        <div class="file-name">{{ $f['name'] }}</div>
                        <div class="file-meta">{{ $f['size_formatted'] }} • Modifié le {{ $f['modified_at'] }}</div>
                    </div>
                </div>

                <div class="file-actions">
                    <button type="button" onclick="openDocumentPreview('{{ $f['extension'] }}', '{{ addslashes($f['name']) }}', '{{ $f['preview_url'] }}', '{{ $f['open_url'] }}', '{{ $f['download_url'] }}')" class="btn-file-action btn-open">
                        <i class="bi bi-eye"></i> Ouvrir
                    </button>
                    <a href="{{ $f['download_url'] }}" download class="btn-file-action btn-dl">
                        <i class="bi bi-download"></i> Télécharger
                    </a>
                    <form action="{{ route('rapports.deleteFile') }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Voulez-vous vraiment supprimer définitivement ce rapport ?');">
                        @csrf
                        <input type="hidden" name="path" value="{{ $f['path'] }}">
                        <button type="submit" class="btn-file-action btn-del">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @empty
    <div style="text-align:center; color:var(--text-muted); padding:3rem 1rem;">
        <i class="bi bi-folder-x" style="font-size:2.5rem; opacity:0.4; display:block; margin-bottom:0.5rem;"></i>
        Aucun dossier ou rapport archivé dans la bibliothèque.
    </div>
    @endforelse
</div>

<!-- Modal Générer un Rapport Personnalisé -->
<div class="rapport-modal-overlay" id="rapportModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 class="modal-title">Générer un Rapport Sur-Mesure</h3>
            <button type="button" class="modal-close" id="btnCloseModal" onclick="closeRapportModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form action="{{ route('rapports.generate') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Type de Période *</label>
                    <select name="period" class="form-select" required>
                        <option value="week">Hebdomadaire (Semaine en cours)</option>
                        <option value="month">Mensuel (Mois complet)</option>
                        <option value="day">Journalier (Jour spécifique)</option>
                        <option value="year">Annuel (Bilan annuel)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Format de Fichier *</label>
                    <select name="format" class="form-select" required>
                        <option value="excel">Tableur Excel (.xlsx)</option>
                        <option value="pdf">Document PDF (.pdf)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Employé spécifique (Optionnel)</label>
                    <input type="hidden" name="employee_id" id="rapportSelectedEmpId" value="">

                    <div id="rapportEmpSearchContainer" class="emp-search-wrapper">
                        <div style="position:relative;">
                            <input type="text" id="rapportEmpSearchInput" class="form-input" placeholder="🔍 Taper un nom, prénom ou département..." autocomplete="off">
                        </div>
                        <div id="rapportEmpSearchResults" class="emp-search-results"></div>
                    </div>

                    <div id="rapportSelectedEmpCard" class="selected-emp-card" style="display:none; margin-top:0.35rem;">
                        <div class="selected-emp-info">
                            <div class="emp-avatar" id="rapportSelectedEmpAvatar" style="width:34px; height:34px; border-radius:50%; background:var(--primary); color:#fff; font-weight:700; font-size:0.8rem; display:flex; align-items:center; justify-content:center;">EM</div>
                            <div>
                                <div class="emp-name" id="rapportSelectedEmpName" style="font-weight:700; font-size:0.88rem; color:var(--text-dark);">NOM Prénom</div>
                                <div class="emp-dept" id="rapportSelectedEmpDept" style="font-size:0.75rem; color:var(--text-muted);">Département</div>
                            </div>
                        </div>
                        <button type="button" class="btn-change-emp" id="btnRapportChangeEmp">
                            <i class="bi bi-arrow-repeat"></i> Changer
                        </button>
                    </div>

                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.35rem;">Laissez vide pour générer un rapport global sur tous les employés.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Date Cible (Optionnel)</label>
                    <input type="date" name="date" class="form-input" value="{{ date('Y-m-d') }}">
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">Laissez aujourd'hui pour calculer par rapport à la date actuelle.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="btnCancelModal" onclick="closeRapportModal()">Annuler</button>
                <button type="submit" class="btn-submit">Générer & Archiver</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Visionneuse de Document (Aperçu Sans Téléchargement) -->
<div class="rapport-modal-overlay" id="previewModal">
    <div class="modal-card" style="width: 960px; max-width: 95vw; height: 85vh; display: flex; flex-direction: column;">
        <div class="modal-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:1rem 1.5rem;">
            <div style="display:flex; align-items:center; gap:0.6rem; overflow:hidden;">
                <i class="bi bi-file-earmark-text-fill" style="color:var(--primary); font-size:1.3rem;"></i>
                <h3 class="modal-title" id="previewModalTitle" style="font-size:1.05rem; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Aperçu du Rapport</h3>
            </div>
            <div style="display:flex; align-items:center; gap:0.65rem;">
                <a href="#" id="previewDownloadBtn" class="btn-file-action btn-dl" download style="padding:0.4rem 0.85rem; font-size:0.8rem;">
                    <i class="bi bi-download"></i> Télécharger le Fichier
                </a>
                <button type="button" class="modal-close" id="btnClosePreviewModal" onclick="closePreviewModal()" style="width:34px; height:34px; border-radius:8px; background:#F1F2F7; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
        <div class="modal-body" id="previewModalBody" style="flex:1; overflow-y:auto; padding:1.25rem; background:#F1F5F9; display:flex; flex-direction:column;">
            <!-- Contenu dynamique de l'aperçu -->
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openRapportModal() {
        const modal = document.getElementById('rapportModal');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('open'), 10);
        }
    }

    function closeRapportModal() {
        const modal = document.getElementById('rapportModal');
        if (modal) {
            modal.classList.remove('open');
            setTimeout(() => { modal.style.display = 'none'; }, 250);
        }
    }

    function closePreviewModal() {
        const previewModal = document.getElementById('previewModal');
        if (previewModal) {
            previewModal.classList.remove('open');
            setTimeout(() => { previewModal.style.display = 'none'; }, 250);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('rapportModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeRapportModal();
            });
        }

        const previewModal = document.getElementById('previewModal');
        if (previewModal) {
            previewModal.addEventListener('click', (e) => {
                if (e.target === previewModal) closePreviewModal();
            });
        }
    });

    function toggleFolder(folderId) {
        const element = document.getElementById(folderId);
        if (element) {
            const currentDisplay = window.getComputedStyle(element).display;
            if (currentDisplay === 'none' || element.style.display === 'none') {
                element.style.display = 'block';
            } else {
                element.style.display = 'none';
            }
        }
    }

    function openDocumentPreview(ext, filename, previewUrl, openUrl, downloadUrl) {
        const modal = document.getElementById('previewModal');
        const modalTitle = document.getElementById('previewModalTitle');
        const modalBody = document.getElementById('previewModalBody');
        const downloadBtn = document.getElementById('previewDownloadBtn');

        if (!modal || !modalBody) return;

        modalTitle.textContent = 'Aperçu : ' + filename;
        downloadBtn.href = downloadUrl;

        modalBody.innerHTML = `
            <div style="text-align:center; padding:4rem 1rem; color:var(--text-muted); margin:auto;">
                <div class="spinner-border text-primary" role="status" style="width:2.5rem; height:2.5rem; margin-bottom:1rem;"></div>
                <p style="font-weight:700; font-size:0.95rem; color:var(--text-dark);">Chargement de l'aperçu du document...</p>
                <p style="font-size:0.8rem;">Veuillez patienter pendant la préparation du rapport.</p>
            </div>
        `;

        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('open'), 10);

        if (ext === 'pdf') {
            modalBody.innerHTML = `
                <iframe src="${openUrl}" style="width:100%; height:100%; min-height:68vh; border:none; border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,0.08); background:#FFF;"></iframe>
            `;
        } else {
            fetch(previewUrl, { headers: { 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(data => {
                    if (data.html) {
                        modalBody.innerHTML = `
                            <div class="excel-preview-wrapper" style="background:#FFF; padding:1.5rem; border-radius:12px; border:1px solid #CBD5E1; overflow-x:auto; box-shadow:0 4px 12px rgba(0,0,0,0.04);">
                                <style>
                                    .excel-preview-wrapper table { border-collapse: collapse; width: 100%; font-size: 0.85rem; color: #1E293B; }
                                    .excel-preview-wrapper td, .excel-preview-wrapper th { border: 1px solid #CBD5E1; padding: 0.55rem 0.75rem; text-align: left; }
                                    .excel-preview-wrapper tr:nth-child(even) { background-color: #F8FAFC; }
                                    .excel-preview-wrapper tr:hover { background-color: #F1F5F9; }
                                </style>
                                ${data.html}
                            </div>
                        `;
                    } else {
                        modalBody.innerHTML = `
                            <div style="text-align:center; padding:3rem 1rem; color:var(--accent-red); margin:auto;">
                                <i class="bi bi-exclamation-triangle-fill" style="font-size:2.5rem;"></i>
                                <p style="margin-top:0.75rem; font-weight:700;">${data.message || 'Impossible d\'afficher cet aperçu.'}</p>
                            </div>
                        `;
                    }
                })
                .catch(err => {
                    console.error(err);
                    modalBody.innerHTML = `
                        <div style="text-align:center; padding:3rem 1rem; color:var(--accent-red); margin:auto;">
                            <i class="bi bi-exclamation-circle-fill" style="font-size:2.5rem;"></i>
                            <p style="margin-top:0.75rem; font-weight:700;">Erreur lors du chargement de l'aperçu.</p>
                        </div>
                    `;
                });
        }
    }

    // ── GESTIONNAIRE DE RECHERCHE D'EMPLOYÉ POUR RAPPORT SUR-MESURE ──
    const rapportEmployesData = @json($employees);
    const rSearchInput     = document.getElementById('rapportEmpSearchInput');
    const rResultsBox      = document.getElementById('rapportEmpSearchResults');
    const rHiddenId        = document.getElementById('rapportSelectedEmpId');
    const rSearchContainer = document.getElementById('rapportEmpSearchContainer');
    const rSelectedCard    = document.getElementById('rapportSelectedEmpCard');
    const rBtnChange       = document.getElementById('btnRapportChangeEmp');
    const rAvatarBox       = document.getElementById('rapportSelectedEmpAvatar');
    const rNameBox         = document.getElementById('rapportSelectedEmpName');
    const rDeptBox         = document.getElementById('rapportSelectedEmpDept');

    function renderRapportEmpResults(query = '') {
        const cleanQuery = query.toLowerCase().trim();
        const filtered = rapportEmployesData.filter(emp => {
            const fullName = `${emp.last_name || ''} ${emp.first_name || ''}`.toLowerCase();
            const deptName = emp.department ? emp.department.name.toLowerCase() : '';
            return fullName.includes(cleanQuery) || deptName.includes(cleanQuery);
        });

        let html = '';
        if (!cleanQuery) {
            html += `
                <div class="emp-search-item" data-id="" data-name="Tous les employés" data-dept="Rapport d'ensemble" data-initials="ALL" style="background:#F8FAFC; border-bottom:1.5px solid #E2E8F0;">
                    <div class="item-avatar" style="background:var(--text-muted);"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <div class="item-name" style="color:var(--primary);">Tous les employés</div>
                        <div class="item-dept">Générer un rapport global sur l'ensemble du personnel</div>
                    </div>
                </div>
            `;
        }

        if (filtered.length === 0) {
            html += '<div style="padding:0.85rem; text-align:center; color:var(--text-muted); font-size:0.82rem;">Aucun employé trouvé.</div>';
        } else {
            html += filtered.map(emp => {
                const initials = ((emp.last_name ? emp.last_name[0] : 'E') + (emp.first_name ? emp.first_name[0] : 'M')).toUpperCase();
                const dept = emp.department ? emp.department.name : 'Général';
                const fullName = `${(emp.last_name || '').toUpperCase()} ${emp.first_name || ''}`;
                return `
                    <div class="emp-search-item" data-id="${emp.id}" data-name="${fullName}" data-dept="${dept}" data-initials="${initials}">
                        <div class="item-avatar">${initials}</div>
                        <div>
                            <div class="item-name">${fullName}</div>
                            <div class="item-dept">${dept}</div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        rResultsBox.innerHTML = html;
        rResultsBox.style.display = 'block';
    }

    if (rSearchInput) {
        rSearchInput.addEventListener('focus', () => renderRapportEmpResults(rSearchInput.value));
        rSearchInput.addEventListener('input', () => renderRapportEmpResults(rSearchInput.value));

        document.addEventListener('click', (e) => {
            if (rSearchContainer && !rSearchContainer.contains(e.target)) {
                rResultsBox.style.display = 'none';
            }
        });

        rResultsBox.addEventListener('click', (e) => {
            const item = e.target.closest('.emp-search-item');
            if (item) {
                const id = item.getAttribute('data-id');
                const name = item.getAttribute('data-name');
                const dept = item.getAttribute('data-dept');
                const initials = item.getAttribute('data-initials');

                rHiddenId.value = id;

                if (id) {
                    rNameBox.textContent = name;
                    rDeptBox.textContent = dept;
                    rAvatarBox.textContent = initials;
                    rSearchContainer.style.display = 'none';
                    rSelectedCard.style.display = 'flex';
                } else {
                    rSearchInput.value = '';
                }
                rResultsBox.style.display = 'none';
            }
        });

        if (rBtnChange) {
            rBtnChange.addEventListener('click', () => {
                rHiddenId.value = '';
                rSearchInput.value = '';
                rSelectedCard.style.display = 'none';
                rSearchContainer.style.display = 'block';
                rSearchInput.focus();
            });
        }
    }
</script>
@endsection
