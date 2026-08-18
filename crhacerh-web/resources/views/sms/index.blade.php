@extends('layouts.dashboard')

@section('title', 'Journal des SMS Automatiques | C.H.R.A.C.E.R.H')

@section('custom_styles')
<style>
    /* ── Header ── */
    .page-header-flex {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2rem;
    }
    .page-title {
        font-size: 1.55rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }
    .page-subtitle {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
    }

    /* ── Badges Auto System ── */
    .auto-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        background: #EBF9EE;
        color: #0E9F2D;
        padding: 0.45rem 0.95rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        border: 1px solid rgba(14, 159, 45, 0.2);
    }
    .auto-pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #0E9F2D;
        animation: pulseDot 1.8s infinite;
    }
    @keyframes pulseDot {
        0% { transform: scale(0.9); opacity: 0.7; box-shadow: 0 0 0 0 rgba(14,159,45,0.6); }
        70% { transform: scale(1.1); opacity: 1; box-shadow: 0 0 0 6px rgba(14,159,45,0); }
        100% { transform: scale(0.9); opacity: 0.7; box-shadow: 0 0 0 0 rgba(14,159,45,0); }
    }

    /* ── Stats Grid ── */
    .sms-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 1.75rem;
    }
    .sms-stat-card {
        background: #fff;
        border-radius: 14px;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .sms-stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.08); }
    .stat-icon-wrap {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.35rem; flex-shrink: 0;
    }
    .icon-today   { background: #EBF3FF; color: var(--primary); }
    .icon-conf    { background: #EBF9EE; color: #0E9F2D; }
    .icon-reminder{ background: #FFF6EB; color: #D97300; }
    .icon-failed  { background: #FDE8E8; color: var(--accent-red); }
    .stat-value { font-size: 1.6rem; font-weight: 800; color: var(--text-dark); line-height: 1; }
    .stat-label { font-size: 0.78rem; color: var(--text-muted); font-weight: 500; margin-top: 0.2rem; }

    /* ── Toolbar ── */
    .toolbar-flex {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
    }
    .left-tools { display: flex; gap: 0.75rem; flex-wrap: wrap; }
    .filter-select {
        padding: 0.5rem 0.85rem;
        border-radius: 10px;
        border: 1px solid var(--border-color);
        background: #fff;
        font-size: 0.85rem;
        color: var(--text-dark);
        cursor: pointer;
        outline: none;
        transition: border 0.2s;
    }
    .filter-select:focus { border-color: var(--primary); }
    .search-box {
        display: flex; align-items: center; gap: 0.5rem;
        background: #fff; border: 1px solid var(--border-color);
        border-radius: 10px; padding: 0.45rem 0.85rem;
        font-size: 0.85rem; color: var(--text-muted);
    }
    .search-box input { border: none; outline: none; font-size: 0.85rem; color: var(--text-dark); background: transparent; width: 220px; }

    /* ── Table ── */
    .table-container {
        background: #fff;
        border-radius: 16px;
        box-shadow: var(--shadow-subtle);
        overflow: hidden;
    }
    .sms-table { width: 100%; border-collapse: collapse; }
    .sms-table thead { background: #F6F7FB; }
    .sms-table th {
        padding: 0.85rem 1rem;
        text-align: left;
        font-size: 0.76rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1px solid var(--border-color);
    }
    .sms-table td {
        padding: 0.95rem 1rem;
        border-bottom: 1px solid #F0F2F8;
        font-size: 0.88rem;
        color: var(--text-dark);
        vertical-align: middle;
    }
    .sms-table tbody tr { transition: background 0.15s ease; cursor: pointer; }
    .sms-table tbody tr:hover { background: #FAFBFF; }
    .sms-table tbody tr:last-child td { border-bottom: none; }

    .patient-avatar {
        width: 36px; height: 36px; border-radius: 50%;
        background: linear-gradient(135deg, #3A5BF0, #6C88FF);
        color: #fff; font-weight: 800; font-size: 0.88rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .patient-info { display: flex; align-items: center; gap: 0.75rem; }
    .patient-name { font-weight: 600; color: var(--text-dark); }
    .patient-sub  { font-size: 0.76rem; color: var(--text-muted); margin-top: 0.1rem; }

    /* ── Type Badges ── */
    .badge-type {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.25rem 0.65rem; border-radius: 6px;
        font-size: 0.74rem; font-weight: 700;
    }
    .type-confirmation { background: #EBF9EE; color: #0E9F2D; }
    .type-rappel_j1    { background: #FFF6EB; color: #D97300; }
    .type-annulation   { background: #FDE8E8; color: var(--accent-red); }
    .type-reporte      { background: #F3E8FF; color: #7C3AED; }
    .type-manuel       { background: #EBF3FF; color: var(--primary); }

    /* ── Status Badges ── */
    .badge-status {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.25rem 0.65rem; border-radius: 6px;
        font-size: 0.74rem; font-weight: 700;
    }
    .status-sent       { background: #EBF9EE; color: #0E9F2D; }
    .status-simulated  { background: #EBF3FF; color: var(--primary); }
    .status-failed     { background: #FDE8E8; color: var(--accent-red); }

    /* Message snippet */
    .msg-snippet {
        font-size: 0.82rem;
        color: var(--text-dark);
        line-height: 1.4;
        max-width: 320px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Action buttons */
    .btn-action-icon {
        background: #F1F2F7;
        color: var(--text-dark);
        border: none;
        width: 32px; height: 32px;
        border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.15s ease;
    }
    .btn-action-icon:hover { background: var(--primary-light); color: var(--primary); }

    .btn-retry-sms {
        background: #FDE8E8;
        color: var(--accent-red);
        border: none;
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        display: inline-flex; align-items: center; gap: 0.3rem;
        cursor: pointer; transition: all 0.15s ease;
    }
    .btn-retry-sms:hover { background: var(--accent-red); color: #fff; }

    /* ── Modal Détails ── */
    .modal-overlay {
        position: fixed; inset: 0;
        background: rgba(31,56,76,0.4); backdrop-filter: blur(3px);
        z-index: 1200; opacity: 0; pointer-events: none;
        transition: opacity 0.3s ease; display: flex;
        align-items: center; justify-content: center;
    }
    .modal-overlay.open { opacity: 1; pointer-events: auto; }
    .log-modal {
        background: #fff; border-radius: 20px;
        width: 520px; max-width: 94vw; max-height: 90vh;
        overflow-y: auto; scrollbar-width: none;
        box-shadow: 0 25px 60px rgba(0,0,0,0.18);
        transform: scale(0.94) translateY(12px);
        transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1);
    }
    .modal-overlay.open .log-modal { transform: scale(1) translateY(0); }

    .log-modal-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #E8EBF2;
        background: #F8FAFC;
        border-radius: 20px 20px 0 0;
    }
    .log-modal-title { font-size: 1.05rem; font-weight: 700; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem; }
    .modal-close-btn {
        width: 32px; height: 32px; border-radius: 8px;
        background: #E2E8F0; border: none;
        color: var(--text-dark); font-size: 0.95rem; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: background 0.2s;
    }
    .modal-close-btn:hover { background: #CBD5E1; }

    .log-modal-body { padding: 1.5rem; }

    /* Bulle SMS style */
    .sms-bubble {
        background: #F1F5F9;
        border-radius: 14px 14px 14px 2px;
        padding: 1rem 1.15rem;
        font-size: 0.88rem;
        color: var(--text-dark);
        line-height: 1.55;
        white-space: pre-wrap;
        word-break: break-word;
        border: 1px solid #E2E8F0;
        margin-top: 0.5rem;
        margin-bottom: 1.25rem;
    }

    .log-meta-row {
        display: flex;
        justify-content: space-between;
        padding: 0.6rem 0;
        border-bottom: 1px solid #F1F5F9;
        font-size: 0.82rem;
    }
    .log-meta-row:last-child { border-bottom: none; }
    .log-meta-label { color: var(--text-muted); font-weight: 600; }
    .log-meta-val { color: var(--text-dark); font-weight: 700; text-align: right; }

    /* Toast */
    .toast {
        position: fixed; bottom: 1.5rem; right: 1.5rem;
        background: #1F384C; color: #fff; padding: 0.85rem 1.25rem;
        border-radius: 12px; font-size: 0.88rem; font-weight: 600;
        display: flex; align-items: center; gap: 0.5rem;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        z-index: 9999; transform: translateY(80px); opacity: 0;
        transition: all 0.35s cubic-bezier(0.34,1.56,0.64,1);
    }
    .toast.show { transform: translateY(0); opacity: 1; }
    .toast.success { border-left: 4px solid #0E9F2D; }
    .toast.error   { border-left: 4px solid var(--accent-red); }

    /* Pagination */
    .pagination-wrapper {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.25rem; border-top: 1px solid var(--border-color);
        flex-wrap: wrap; gap: 0.75rem;
    }
    .pagination-info { font-size: 0.82rem; color: var(--text-muted); }
    .custom-pagination { display: flex; list-style: none; gap: 0.35rem; }
    .custom-pagination li a,
    .custom-pagination li span {
        display: flex; align-items: center; justify-content: center;
        width: 34px; height: 34px; border-radius: 8px;
        font-size: 0.82rem; font-weight: 600; text-decoration: none;
        border: 1px solid var(--border-color);
        color: var(--text-dark); background: #fff; transition: all 0.15s;
    }
    .custom-pagination li.active span { background: var(--primary); color: #fff; border-color: var(--primary); }
    .custom-pagination li a:hover { background: var(--primary-light); border-color: var(--primary); color: var(--primary); }
    .custom-pagination li.disabled span { opacity: 0.4; cursor: not-allowed; }
</style>
@endsection

@section('content')

{{-- Header --}}
<div class="page-header-flex">
    <div>
        <h1 class="page-title">📱 Journal des Notifications SMS</h1>
        <p class="page-subtitle">
            Suivi et audit en temps réel des SMS envoyés automatiquement aux patientes (Confirmations, Rappels J-1, Annulations, Reports).
        </p>
    </div>
    <div class="auto-badge-pill">
        <div class="auto-pulse-dot"></div>
        <span>Système Automatisé Actif</span>
    </div>
</div>

{{-- Stats Grid --}}
<div class="sms-stats-grid">
    <div class="sms-stat-card">
        <div class="stat-icon-wrap icon-today"><i class="bi bi-send-check-fill"></i></div>
        <div>
            <div class="stat-value">{{ $stats['total_today'] }}</div>
            <div class="stat-label">SMS envoyés aujourd'hui</div>
        </div>
    </div>
    <div class="sms-stat-card">
        <div class="stat-icon-wrap icon-conf"><i class="bi bi-calendar-check-fill"></i></div>
        <div>
            <div class="stat-value" style="color:#0E9F2D;">{{ $stats['confirmations'] }}</div>
            <div class="stat-label">Confirmations auto</div>
        </div>
    </div>
    <div class="sms-stat-card">
        <div class="stat-icon-wrap icon-reminder"><i class="bi bi-alarm-fill"></i></div>
        <div>
            <div class="stat-value" style="color:#D97300;">{{ $stats['rappels_j1'] }}</div>
            <div class="stat-label">Rappels J-1 (Cron 18h)</div>
        </div>
    </div>
    <div class="sms-stat-card">
        <div class="stat-icon-wrap icon-failed"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div>
            <div class="stat-value" style="color:var(--accent-red);">{{ $stats['echecs'] }}</div>
            <div class="stat-label">Échecs de livraison</div>
        </div>
    </div>
</div>

{{-- Toolbar / Filters --}}
<div class="toolbar-flex">
    <div class="left-tools">
        <select class="filter-select" id="filterPeriode" onchange="applyFilters()">
            <option value="today"     {{ request('periode') == 'today' || !request('periode') ? 'selected' : '' }}>Aujourd'hui</option>
            <option value="yesterday" {{ request('periode') == 'yesterday' ? 'selected' : '' }}>Hier</option>
            <option value="week"      {{ request('periode') == 'week' ? 'selected' : '' }}>Cette semaine</option>
            <option value="month"     {{ request('periode') == 'month' ? 'selected' : '' }}>Ce mois</option>
            <option value="all"       {{ request('periode') == 'all' ? 'selected' : '' }}>Tout l'historique</option>
        </select>

        <select class="filter-select" id="filterType" onchange="applyFilters()">
            <option value="">Tous les types</option>
            <option value="CONFIRMATION" {{ request('type') == 'CONFIRMATION' ? 'selected' : '' }}>Confirmation</option>
            <option value="RAPPEL_J1"    {{ request('type') == 'RAPPEL_J1' ? 'selected' : '' }}>Rappel J-1</option>
            <option value="ANNULATION"   {{ request('type') == 'ANNULATION' ? 'selected' : '' }}>Annulation</option>
            <option value="REPORTE"      {{ request('type') == 'REPORTE' ? 'selected' : '' }}>Reporté</option>
            <option value="MANUEL"       {{ request('type') == 'MANUEL' ? 'selected' : '' }}>Manuel</option>
        </select>

        <select class="filter-select" id="filterStatus" onchange="applyFilters()">
            <option value="">Tous les statuts</option>
            <option value="SENT"      {{ request('status') == 'SENT' ? 'selected' : '' }}>Délivré (Opérateur)</option>
            <option value="SIMULATED" {{ request('status') == 'SIMULATED' ? 'selected' : '' }}>Simulé (Sandbox)</option>
            <option value="FAILED"    {{ request('status') == 'FAILED' ? 'selected' : '' }}>Échec</option>
        </select>
    </div>

    <form method="GET" action="{{ route('sms.index') }}" class="search-box">
        @if(request('periode')) <input type="hidden" name="periode" value="{{ request('periode') }}"> @endif
        @if(request('type'))    <input type="hidden" name="type" value="{{ request('type') }}"> @endif
        @if(request('status'))  <input type="hidden" name="status" value="{{ request('status') }}"> @endif
        <i class="bi bi-search"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher patient, tel, message...">
    </form>
</div>

{{-- Audit Table --}}
<div class="table-container">
    <table class="sms-table">
        <thead>
            <tr>
                <th style="width:14%;">Date &amp; Heure</th>
                <th style="width:13%;">Type</th>
                <th style="width:23%;">Patiente</th>
                <th style="width:30%;">Message Envoyé</th>
                <th style="width:12%;">Statut</th>
                <th style="width:8%; text-align:center;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            @php
                $typeClass = match($log->type) {
                    'CONFIRMATION' => 'type-confirmation',
                    'RAPPEL_J1'    => 'type-rappel_j1',
                    'ANNULATION'   => 'type-annulation',
                    'REPORTE'      => 'type-reporte',
                    default        => 'type-manuel',
                };
                $typeLabel = match($log->type) {
                    'CONFIRMATION' => 'Confirmation',
                    'RAPPEL_J1'    => 'Rappel J-1',
                    'ANNULATION'   => 'Annulation',
                    'REPORTE'      => 'Reporté',
                    default        => 'Manuel',
                };

                $statusClass = match($log->status) {
                    'SENT'      => 'status-sent',
                    'SIMULATED' => 'status-simulated',
                    'FAILED'    => 'status-failed',
                    default     => 'status-simulated',
                };
                $statusLabel = match($log->status) {
                    'SENT'      => 'Délivré',
                    'SIMULATED' => 'Sandbox',
                    'FAILED'    => 'Échec',
                    default     => $log->status,
                };
            @endphp
            <tr onclick="openLogDetails({{ json_encode($log) }})">
                <td>
                    <div style="font-weight:600;">
                        {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y') }}
                    </div>
                    <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.1rem;">
                        <i class="bi bi-clock" style="margin-right:0.2rem;"></i>
                        {{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}
                    </div>
                </td>
                <td>
                    <span class="badge-type {{ $typeClass }}">
                        {{ $typeLabel }}
                    </span>
                </td>
                <td>
                    <div class="patient-info">
                        <div class="patient-avatar">
                            {{ strtoupper(substr($log->patient_name ?? 'P', 0, 1)) }}
                        </div>
                        <div>
                            <div class="patient-name">{{ $log->patient_name ?? 'Patient' }}</div>
                            <div class="patient-sub">
                                <i class="bi bi-telephone-fill" style="color:var(--primary); font-size:0.68rem;"></i>
                                {{ $log->phone }}
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="msg-snippet" title="{{ $log->message }}">
                        {{ $log->message }}
                    </div>
                    @if($log->rendezVous)
                    <div style="font-size:0.74rem; color:var(--text-muted); margin-top:0.15rem;">
                        RDV : {{ \Carbon\Carbon::parse($log->rendezVous->date_rendez_vous)->format('d/m/Y H:i') }} ({{ $log->rendezVous->service }})
                    </div>
                    @endif
                </td>
                <td>
                    <span class="badge-status {{ $statusClass }}">
                        @if($log->status === 'SENT')
                            <i class="bi bi-check-all"></i>
                        @elseif($log->status === 'SIMULATED')
                            <i class="bi bi-shield-check"></i>
                        @else
                            <i class="bi bi-x-circle-fill"></i>
                        @endif
                        {{ $statusLabel }}
                    </span>
                </td>
                <td onclick="event.stopPropagation()" style="text-align:center;">
                    @if($log->status === 'FAILED')
                    <button class="btn-retry-sms" onclick="retrySms({{ $log->id }})" title="Réessayer l'envoi">
                        <i class="bi bi-arrow-clockwise"></i> Réessayer
                    </button>
                    @else
                    <button class="btn-action-icon" onclick="openLogDetails({{ json_encode($log) }})" title="Voir détails">
                        <i class="bi bi-eye-fill"></i>
                    </button>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:3.5rem; color:var(--text-muted);">
                    <i class="bi bi-inbox" style="font-size:2.5rem; display:block; margin-bottom:0.75rem; opacity:0.35;"></i>
                    Aucun SMS enregistré dans le journal pour cette période.
                    <div style="font-size:0.8rem; margin-top:0.35rem; color:var(--text-muted);">
                        Les SMS seront automatiquement consignés ici dès qu'un rendez-vous est créé ou rappelé.
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div class="pagination-wrapper">
        <div class="pagination-info">
            Affichage de <strong>{{ $logs->firstItem() }}</strong> à <strong>{{ $logs->lastItem() }}</strong> sur <strong>{{ $logs->total() }}</strong> notifications
        </div>
        <ul class="custom-pagination">
            @if($logs->onFirstPage())
                <li class="disabled"><span><i class="bi bi-chevron-left"></i></span></li>
            @else
                <li><a href="{{ $logs->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a></li>
            @endif
            @foreach($logs->getUrlRange(1, $logs->lastPage()) as $page => $url)
                <li class="{{ $page == $logs->currentPage() ? 'active' : '' }}">
                    @if($page == $logs->currentPage())
                        <span>{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                </li>
            @endforeach
            @if($logs->hasMorePages())
                <li><a href="{{ $logs->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a></li>
            @else
                <li class="disabled"><span><i class="bi bi-chevron-right"></i></span></li>
            @endif
        </ul>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════ --}}
{{-- MODAL DÉTAILS SMS                                  --}}
{{-- ══════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="logModalOverlay">
    <div class="log-modal" id="logModal">
        <div class="log-modal-header">
            <div class="log-modal-title">
                <i class="bi bi-chat-left-text-fill" style="color:var(--primary);"></i>
                <span>Détails de la Notification SMS</span>
            </div>
            <button class="modal-close-btn" onclick="closeLogModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="log-modal-body">
            <div style="font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Message délivré à la patiente :</div>
            <div class="sms-bubble" id="modalMsgBody"></div>

            <div class="log-meta-row">
                <span class="log-meta-label">Patiente</span>
                <span class="log-meta-val" id="modalPatient"></span>
            </div>
            <div class="log-meta-row">
                <span class="log-meta-label">Numéro de téléphone</span>
                <span class="log-meta-val" id="modalPhone"></span>
            </div>
            <div class="log-meta-row">
                <span class="log-meta-label">Type d'automatisme</span>
                <span class="log-meta-val" id="modalType"></span>
            </div>
            <div class="log-meta-row">
                <span class="log-meta-label">Date &amp; Heure d'envoi</span>
                <span class="log-meta-val" id="modalDate"></span>
            </div>
            <div class="log-meta-row">
                <span class="log-meta-label">Statut de remise</span>
                <span class="log-meta-val" id="modalStatus"></span>
            </div>
            <div class="log-meta-row">
                <span class="log-meta-label">Détails techniques / Opérateur</span>
                <span class="log-meta-val" id="modalDetails" style="font-size:0.78rem; font-weight:500; color:var(--text-muted);"></span>
            </div>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="toast" id="toast">
    <i class="bi bi-info-circle-fill" id="toastIcon"></i>
    <span id="toastMsg"></span>
</div>

@endsection

@section('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function openLogDetails(log) {
    document.getElementById('modalMsgBody').textContent = log.message;
    document.getElementById('modalPatient').textContent = log.patient_name || 'Patient Inconnu';
    document.getElementById('modalPhone').textContent   = log.phone || 'N/A';
    document.getElementById('modalType').textContent    = log.type;
    document.getElementById('modalDate').textContent    = new Date(log.created_at).toLocaleString('fr-FR');
    document.getElementById('modalStatus').textContent  = log.status === 'SENT' ? '✅ Délivré' : log.status === 'SIMULATED' ? '🔵 Sandbox' : '❌ Échec';
    document.getElementById('modalDetails').textContent = log.response_details || 'Aucun détail supplémentaire.';

    document.getElementById('logModalOverlay').classList.add('open');
}

function closeLogModal() {
    document.getElementById('logModalOverlay').classList.remove('open');
}

// ── Réessayer un SMS en échec ──────────────────────────
async function retrySms(logId) {
    showToast('info', 'Tentative de réexpédition du SMS...');
    try {
        const res = await fetch(`/sms/${logId}/retry`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();
        if (data.status === 'success') {
            showToast('success', data.message);
            setTimeout(() => location.reload(), 1200);
        } else {
            showToast('error', data.message || 'Échec lors de la réexpédition.');
        }
    } catch (e) {
        showToast('error', 'Erreur réseau lors de la réexpédition.');
    }
}

// ── Filtres ───────────────────────────────────────────
function applyFilters() {
    const periode = document.getElementById('filterPeriode').value;
    const type    = document.getElementById('filterType').value;
    const status  = document.getElementById('filterStatus').value;
    const params  = new URLSearchParams();

    if (periode) params.set('periode', periode);
    if (type)    params.set('type', type);
    if (status)  params.set('status', status);

    window.location.href = '{{ route("sms.index") }}?' + params.toString();
}

// ── Toast Notification ────────────────────────────────
function showToast(type, msg) {
    const toast = document.getElementById('toast');
    const icon  = document.getElementById('toastIcon');
    document.getElementById('toastMsg').textContent = msg;
    icon.className = type === 'success' ? 'bi bi-check-circle-fill' : type === 'error' ? 'bi bi-x-circle-fill' : 'bi bi-info-circle-fill';
    toast.className = `toast ${type} show`;
    setTimeout(() => { toast.classList.remove('show'); }, 4000);
}

document.getElementById('logModalOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeLogModal();
});
</script>
@endsection
