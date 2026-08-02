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
        width: 480px;
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
    .btn-submit { background: var(--primary); color: #FFFFFF; padding: 0.6rem 1.25rem; border-radius: 10px; border: none; font-weight: 600; cursor: pointer; }
</style>
@endsection

@section('content')
<!-- Header Page -->
<div class="page-header-flex">
    <h1 class="page-title">Génération & Bibliothèque des Rapports</h1>
    <button class="btn-create-rapport" id="btnOpenModal">
        <i class="bi bi-file-earmark-plus-fill"></i>
        <span>Générer un Rapport</span>
    </button>
</div>

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

<!-- Quick Action Generation Section -->
<div class="quick-gen-section">
    <div class="section-subtitle">
        <i class="bi bi-lightning-charge-fill" style="color:var(--primary);"></i>
        <span>Génération Rapide en 1-Clic</span>
    </div>
    <div class="quick-gen-grid">
        <!-- Card 1: Rapport Hebdomadaire Excel -->
        <div class="quick-card">
            <div>
                <div class="quick-card-title">Rapport Hebdomadaire (Excel)</div>
                <div class="quick-card-desc">Synthèse complète de la semaine en cours avec détails par employé (.xlsx).</div>
            </div>
            <form action="{{ route('rapports.generate') }}" method="POST">
                @csrf
                <input type="hidden" name="period" value="week">
                <input type="hidden" name="format" value="excel">
                <button type="submit" class="btn-quick-gen">
                    <i class="bi bi-file-earmark-excel"></i>
                    <span>Générer Hebdomadaire</span>
                </button>
            </form>
        </div>

        <!-- Card 2: Rapport Mensuel Excel -->
        <div class="quick-card">
            <div>
                <div class="quick-card-title">Rapport Mensuel (Excel)</div>
                <div class="quick-card-desc">Fichier mensuel classé dans le dossier du mois en cours (.xlsx).</div>
            </div>
            <form action="{{ route('rapports.generate') }}" method="POST">
                @csrf
                <input type="hidden" name="period" value="month">
                <input type="hidden" name="format" value="excel">
                <button type="submit" class="btn-quick-gen">
                    <i class="bi bi-file-earmark-excel"></i>
                    <span>Générer Mensuel</span>
                </button>
            </form>
        </div>

        <!-- Card 3: Rapport Journalier PDF -->
        <div class="quick-card">
            <div>
                <div class="quick-card-title">Rapport Journalier (PDF)</div>
                <div class="quick-card-desc">Bilan officiel de la journée prêt à l'impression (.pdf).</div>
            </div>
            <form action="{{ route('rapports.generate') }}" method="POST">
                @csrf
                <input type="hidden" name="period" value="day">
                <input type="hidden" name="format" value="pdf">
                <button type="submit" class="btn-quick-gen">
                    <i class="bi bi-file-earmark-pdf"></i>
                    <span>Générer Journalier PDF</span>
                </button>
            </form>
        </div>

        <!-- Card 4: Rapport Annuel Excel -->
        <div class="quick-card">
            <div>
                <div class="quick-card-title">Bilan Annuel (Excel)</div>
                <div class="quick-card-desc">Statistiques globales et taux d'assiduité annuels (.xlsx).</div>
            </div>
            <form action="{{ route('rapports.generate') }}" method="POST">
                @csrf
                <input type="hidden" name="period" value="year">
                <input type="hidden" name="format" value="excel">
                <button type="submit" class="btn-quick-gen">
                    <i class="bi bi-file-earmark-excel"></i>
                    <span>Générer Bilan Annuel</span>
                </button>
            </form>
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

        <div class="folder-files-list" id="folder-{{ Str::slug($folder['name']) }}">
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
                    <a href="{{ $f['open_url'] }}" target="_blank" class="btn-file-action btn-open">
                        <i class="bi bi-eye"></i> Ouvrir
                    </a>
                    <a href="{{ $f['download_url'] }}" class="btn-file-action btn-dl">
                        <i class="bi bi-download"></i> Télécharger
                    </a>
                    <form action="{{ route('rapports.deleteFile') }}" method="POST" onsubmit="return confirm('Supprimer ce fichier ?');" style="display:inline;">
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
<div class="modal-backdrop" id="rapportModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 class="modal-title">Générer un Rapport Sur-Mesure</h3>
            <button class="modal-close" id="btnCloseModal">
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
                    <label class="form-label">Date Cible (Optionnel)</label>
                    <input type="date" name="date" class="form-input" value="{{ date('Y-m-d') }}">
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">Laissez aujourd'hui pour calculer par rapport à la date actuelle.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="btnCancelModal">Annuler</button>
                <button type="submit" class="btn-submit">Générer & Archiver</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('rapportModal');
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
    });

    function toggleFolder(folderId) {
        const element = document.getElementById(folderId);
        if (element) {
            if (element.style.display === 'none') {
                element.style.display = 'block';
            } else {
                element.style.display = 'none';
            }
        }
    }
</script>
@endsection
