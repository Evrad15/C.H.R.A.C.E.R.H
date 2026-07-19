<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borne de Pointage | C.H.R.A.C.E.R.H</title>
    <meta name="description" content="Borne interactive de pointage et accueil des patients CHRACERH">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #D4B895;
            --primary-light: #E8D0B3;
            --bg-gradient-start: #FDFBF7;
            --bg-gradient-end: #EFE4D3;
            --card-bg: rgba(255, 255, 255, 0.65);
            --card-border: rgba(212, 184, 149, 0.4);
            --text-main: #4A4036;
            --text-muted: #8C7B6B;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            color: var(--text-main);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            /* Fixe sur l'écran, aucun défilement */
            position: relative;
        }

        /* Animated Background Orbs (Ancien design) */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.55;
            animation: float 10s infinite ease-in-out alternate;
            z-index: -1;
        }

        .orb-1 {
            width: 420px;
            height: 420px;
            background: #D4B895;
            top: -10%;
            left: -10%;
        }

        .orb-2 {
            width: 520px;
            height: 520px;
            background: #E8D0B3;
            bottom: -20%;
            right: -10%;
            animation-delay: -5s;
        }

        @keyframes float {
            0% {
                transform: translate(0, 0) scale(1);
            }

            100% {
                transform: translate(50px, 50px) scale(1.1);
            }
        }

        /* Grille principale : Gauche (Tableau/Historique) + Droite (Borne Biométrique 450px) */
        .container {
            width: 100%;
            max-width: 1440px;
            padding: 1.5rem 2rem;
            display: grid;
            grid-template-columns: 1fr 450px;
            gap: 2.5rem;
            height: 92vh;
        }

        .r-card {
            background: var(--card-bg);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-radius: 24px;
            border: 1px solid var(--card-border);
            box-shadow: 0 25px 50px -12px rgba(74, 64, 54, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Colonne Gauche : Historique et RDV */
        .history-section {
            display: flex;
            flex-direction: column;
            padding: 2rem;
            height: 100%;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid rgba(212, 184, 149, 0.25);
        }

        .brand-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-box {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #D4B895 0%, #A88860 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.35rem;
            box-shadow: 0 8px 16px rgba(212, 184, 149, 0.4);
        }

        .brand-info h1 {
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--text-main);
            line-height: 1.1;
        }

        .brand-info p {
            font-size: 0.82rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 0.15rem;
        }

        .clock-box {
            text-align: right;
        }

        #clock-time {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--text-main);
            font-variant-numeric: tabular-nums;
            line-height: 1;
        }

        #clock-date {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: capitalize;
            margin-top: 0.25rem;
        }

        /* Onglets */
        .tabs-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            gap: 1rem;
        }

        .tabs-nav {
            display: flex;
            gap: 0.5rem;
            background: rgba(212, 184, 149, 0.2);
            padding: 0.35rem;
            border-radius: 14px;
            border: 1px solid rgba(212, 184, 149, 0.3);
        }

        .tab-btn {
            background: transparent;
            border: none;
            color: var(--text-main);
            padding: 0.65rem 1.25rem;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tab-btn:hover {
            background: rgba(255, 255, 255, 0.4);
        }

        .tab-btn.active {
            background: #fff;
            color: var(--text-main);
            box-shadow: 0 4px 10px rgba(74, 64, 54, 0.08);
        }

        .tab-badge {
            background: var(--primary);
            color: #fff;
            padding: 0.12rem 0.55rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 800;
        }

        .tab-btn.active .tab-badge {
            background: #4A4036;
            color: #fff;
        }

        /* Tableau et Conteneur avec Scroll propre si beaucoup de lignes */
        .table-wrapper {
            flex: 1;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .table-wrapper::-webkit-scrollbar {
            width: 6px;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: var(--primary-light);
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        th {
            background: rgba(255, 255, 255, 0.5);
            color: var(--text-muted);
            font-weight: 800;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.85rem 1rem;
            text-align: left;
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 1px solid rgba(212, 184, 149, 0.3);
        }

        td {
            padding: 0.95rem 1rem;
            font-size: 0.92rem;
            color: var(--text-main);
            border-bottom: 1px solid rgba(212, 184, 149, 0.15);
            vertical-align: middle;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.35);
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .badge-entree {
            background: rgba(16, 185, 129, 0.15);
            color: #059669;
        }

        .badge-sortie {
            background: rgba(239, 68, 68, 0.15);
            color: #dc2626;
        }

        .badge-retard {
            background: rgba(245, 158, 11, 0.15);
            color: #d97706;
        }

        .badge-rdv-planifie {
            background: rgba(59, 130, 246, 0.15);
            color: #2563eb;
        }

        .badge-rdv-present {
            background: rgba(16, 185, 129, 0.2);
            color: #059669;
            font-weight: 800;
            border: 1px solid #10b981;
        }

        /* Colonne Droite (Borne Biométrique - remplace l'ancien encadré QR) */
        .bio-station-section {
            padding: 2.25rem 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            text-align: center;
            height: 100%;
        }

        .station-header {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .station-icon {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background: rgba(212, 184, 149, 0.25);
            border: 2px solid var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin-bottom: 1rem;
            box-shadow: 0 0 25px rgba(212, 184, 149, 0.3);
        }

        .station-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.35rem;
        }

        .station-subtitle {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
            max-width: 320px;
            line-height: 1.4;
        }

        /* Cartes de bouton d'action biométrique */
        .action-cards {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            margin: 1.5rem 0;
        }

        .action-btn-card {
            width: 100%;
            background: #fff;
            border: 1.5px solid rgba(212, 184, 149, 0.4);
            padding: 1.25rem 1.5rem;
            border-radius: 18px;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 10px 20px -5px rgba(74, 64, 54, 0.06);
            text-align: left;
        }

        .action-btn-card:hover {
            transform: translateY(-3px);
            border-color: var(--text-main);
            box-shadow: 0 15px 30px -5px rgba(74, 64, 54, 0.15);
            background: #FDFBF7;
        }

        .action-icon-box {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            flex-shrink: 0;
        }

        .box-face {
            background: rgba(59, 130, 246, 0.12);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .box-finger {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .action-text h4 {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--text-main);
        }

        .action-text p {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 0.2rem;
        }

        .kiosk-footer {
            width: 100%;
            padding-top: 1rem;
            border-top: 1px solid rgba(212, 184, 149, 0.25);
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        /* Modals pour le pointage facial/empreinte */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(74, 64, 54, 0.85);
            backdrop-filter: blur(12px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-box {
            background: #FDFBF7;
            border: 1px solid var(--card-border);
            border-radius: 24px;
            width: 100%;
            max-width: 520px;
            padding: 2.25rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            transform: scale(0.95);
            transition: transform 0.3s ease;
            max-height: 90vh;
        }

        .modal-overlay.active .modal-box {
            transform: scale(1);
        }

        .modal-close {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
            background: rgba(74, 64, 54, 0.1);
            border: none;
            color: var(--text-main);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            background: rgba(239, 68, 68, 0.15);
            color: #dc2626;
        }

        .video-container {
            width: 100%;
            height: 280px;
            border-radius: 16px;
            overflow: hidden;
            background: #1e293b;
            position: relative;
            margin: 1.25rem 0;
            border: 2px solid var(--card-border);
        }

        #webcam-stream {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .finger-anim-box {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(16, 185, 129, 0.12);
            border: 3px dashed #10b981;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4.5rem;
            margin: 1.5rem 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .finger-anim-box:hover {
            background: rgba(16, 185, 129, 0.22);
            transform: scale(1.05);
        }

        .finger-anim-box.scanning {
            animation: rotateDash 2s linear infinite;
        }

        @keyframes rotateDash {
            0% {
                border-color: #10b981;
            }

            50% {
                border-color: #2563eb;
                transform: scale(1.08);
            }

            100% {
                border-color: #10b981;
            }
        }

        .candidates-list {
            width: 100%;
            max-height: 180px;
            overflow-y: auto;
            text-align: left;
            margin-top: 0.75rem;
            border: 1px solid rgba(212, 184, 149, 0.4);
            border-radius: 12px;
            background: #fff;
        }

        .candidate-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(212, 184, 149, 0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .candidate-item:hover {
            background: #FDFBF7;
        }

        .candidate-item:last-child {
            border-bottom: none;
        }

        .empty-table {
            padding: 3.5rem 1rem;
            text-align: center;
            color: var(--text-muted);
            font-style: italic;
        }
    </style>
</head>

<body>

    <!-- Orbes animés de l'ancien design -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="container">

        <!-- Colonne Gauche : Historique de Pointages & RDV du jour -->
        <div class="r-card history-section">

            <!-- En-tête avec Logo et Horloge -->
            <div class="header-top">
                <div class="brand-info">
                    <div class="logo-box">CH</div>
                    <div>
                        <h1>BORNE INTERACTIVE DE POINTAGE</h1>
                        <p>C.H.R.A.C.E.R.H — Centre Hospitalier</p>
                    </div>
                </div>
                <div class="clock-box">
                    <div id="clock-time">00:00:00</div>
                    <div id="clock-date">Chargement...</div>
                </div>
            </div>

            <!-- Navigation 2 Onglets -->
            <div class="tabs-header">
                <div class="tabs-nav">
                    <button class="tab-btn active" id="tab-btn-pointages" onclick="switchTab('pointages')">
                        <span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
                                <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-.964 1.205q.183-.183.35-.378l.758.653a8 8 0 0 1-.401.432z" />
                                <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z" />
                                <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5" />
                            </svg></span> Historique de Pointages
                        <span class="tab-badge" id="count-pointages">0</span>
                    </button>
                    <button class="tab-btn" id="tab-btn-rdvs" onclick="switchTab('rdvs')">
                        <span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-floppy-fill" viewBox="0 0 16 16">
                                <path d="M0 1.5A1.5 1.5 0 0 1 1.5 0H3v5.5A1.5 1.5 0 0 0 4.5 7h7A1.5 1.5 0 0 0 13 5.5V0h.086a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5H14v-5.5A1.5 1.5 0 0 0 12.5 9h-9A1.5 1.5 0 0 0 2 10.5V16h-.5A1.5 1.5 0 0 1 0 14.5z" />
                                <path d="M3 16h10v-5.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5zm9-16H4v5.5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5zM9 1h2v4H9z" />
                            </svg></span> Rendez-vous du Jour
                        <span class="tab-badge" id="count-rdvs">0</span>
                    </button>
                </div>
                <div style="font-size:0.78rem; color:var(--text-muted); display:flex; align-items:center; gap:0.4rem; font-weight:600;">
                    <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#10b981; box-shadow:0 0 6px #10b981;"></span>
                    Synchronisation auto
                </div>
            </div>

            <!-- Tableau Onglet 1 : Pointages Employés -->
            <div class="table-wrapper" id="panel-pointages">
                <table>
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Département</th>
                            <th>Type</th>
                            <th>Mode</th>
                            <th>Heure</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-pointages">
                        <tr>
                            <td colspan="6" class="empty-table">Chargement de l'historique...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Tableau Onglet 2 : Rendez-vous Patients -->
            <div class="table-wrapper" id="panel-rdvs" style="display: none;">
                <table>
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Service / Consultation</th>
                            <th>Médecin</th>
                            <th>Heure RDV</th>
                            <th>Arrivée</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-rdvs">
                        <tr>
                            <td colspan="7" class="empty-table">Chargement des rendez-vous...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Colonne Droite : Borne Biométrique (Remplace l'encadré QR du mobile) -->
        <div class="r-card bio-station-section">

            <div class="station-header">
                <div class="station-icon"><svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-shield-fill-check" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263 63 63 0 0 0-2.887-.87C9.843.266 8.69 0 8 0m2.146 5.146a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793z" />
                    </svg></div>
                <h3 class="station-title">Authentification Biométrique</h3>
                <p class="station-subtitle">
                    Identifiez-vous à la borne pour pointer votre entrée/sortie ou valider votre arrivée à un rendez-vous.
                </p>
            </div>

            <!-- 2 boutons d'action interactifs -->
            <div class="action-cards">

                <div class="action-btn-card" onclick="openFaceModal()">
                    <div class="action-icon-box box-face"><svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-camera-fill" viewBox="0 0 16 16">
                            <path d="M10.5 8.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0" />
                            <path d="M2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4zm.5 2a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1m9 2.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0" />
                        </svg></div>
                    <div class="action-text">
                        <h4>Reconnaissance Faciale</h4>
                        <p>Identification par caméra</p>
                    </div>
                </div>

                <div class="action-btn-card" onclick="openFingerModal()">
                    <div class="action-icon-box box-finger"><svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-fingerprint" viewBox="0 0 16 16">
                            <path d="M8.06 6.5a.5.5 0 0 1 .5.5v.776a11.5 11.5 0 0 1-.552 3.519l-1.331 4.14a.5.5 0 0 1-.952-.305l1.33-4.141a10.5 10.5 0 0 0 .504-3.213V7a.5.5 0 0 1 .5-.5Z" />
                            <path d="M6.06 7a2 2 0 1 1 4 0 .5.5 0 1 1-1 0 1 1 0 1 0-2 0v.332q0 .613-.066 1.221A.5.5 0 0 1 6 8.447q.06-.555.06-1.115zm3.509 1a.5.5 0 0 1 .487.513 11.5 11.5 0 0 1-.587 3.339l-1.266 3.8a.5.5 0 0 1-.949-.317l1.267-3.8a10.5 10.5 0 0 0 .535-3.048A.5.5 0 0 1 9.569 8m-3.356 2.115a.5.5 0 0 1 .33.626L5.24 14.939a.5.5 0 1 1-.955-.296l1.303-4.199a.5.5 0 0 1 .625-.329" />
                            <path d="M4.759 5.833A3.501 3.501 0 0 1 11.559 7a.5.5 0 0 1-1 0 2.5 2.5 0 0 0-4.857-.833.5.5 0 1 1-.943-.334m.3 1.67a.5.5 0 0 1 .449.546 10.7 10.7 0 0 1-.4 2.031l-1.222 4.072a.5.5 0 1 1-.958-.287L4.15 9.793a9.7 9.7 0 0 0 .363-1.842.5.5 0 0 1 .546-.449Zm6 .647a.5.5 0 0 1 .5.5c0 1.28-.213 2.552-.632 3.762l-1.09 3.145a.5.5 0 0 1-.944-.327l1.089-3.145c.382-1.105.578-2.266.578-3.435a.5.5 0 0 1 .5-.5Z" />
                            <path d="M3.902 4.222a5 5 0 0 1 5.202-2.113.5.5 0 0 1-.208.979 4 4 0 0 0-4.163 1.69.5.5 0 0 1-.831-.556m6.72-.955a.5.5 0 0 1 .705-.052A4.99 4.99 0 0 1 13.059 7v1.5a.5.5 0 1 1-1 0V7a3.99 3.99 0 0 0-1.386-3.028.5.5 0 0 1-.051-.705M3.68 5.842a.5.5 0 0 1 .422.568q-.044.289-.044.59c0 .71-.1 1.417-.298 2.1l-1.14 3.923a.5.5 0 1 1-.96-.279L2.8 8.821A6.5 6.5 0 0 0 3.058 7q0-.375.054-.736a.5.5 0 0 1 .568-.422m8.882 3.66a.5.5 0 0 1 .456.54c-.084 1-.298 1.986-.64 2.934l-.744 2.068a.5.5 0 0 1-.941-.338l.745-2.07a10.5 10.5 0 0 0 .584-2.678.5.5 0 0 1 .54-.456" />
                            <path d="M4.81 1.37A6.5 6.5 0 0 1 14.56 7a.5.5 0 1 1-1 0 5.5 5.5 0 0 0-8.25-4.765.5.5 0 0 1-.5-.865m-.89 1.257a.5.5 0 0 1 .04.706A5.48 5.48 0 0 0 2.56 7a.5.5 0 0 1-1 0c0-1.664.626-3.184 1.655-4.333a.5.5 0 0 1 .706-.04ZM1.915 8.02a.5.5 0 0 1 .346.616l-.779 2.767a.5.5 0 1 1-.962-.27l.778-2.767a.5.5 0 0 1 .617-.346m12.15.481a.5.5 0 0 1 .49.51c-.03 1.499-.161 3.025-.727 4.533l-.07.187a.5.5 0 0 1-.936-.351l.07-.187c.506-1.35.634-2.74.663-4.202a.5.5 0 0 1 .51-.49" />
                        </svg></div>
                    <div class="action-text">
                        <h4>Empreinte Digitale</h4>
                        <p>Scan tactile ou sélection du profil</p>
                    </div>
                </div>

            </div>

            <div class="kiosk-footer">
                <span>🔒</span> Système de Pointage Sécurisé & Intégré CHRACERH
            </div>

        </div>

    </div>

    <!-- MODAL RECONNAISSANCE FACIALE -->
    <div class="modal-overlay" id="modal-face">
        <div class="modal-box">
            <button class="modal-close" onclick="closeFaceModal()">✕</button>
            <h3 style="font-size:1.35rem; font-weight:800; color:var(--text-main);">Reconnaissance Faciale</h3>
            <p style="font-size:0.85rem; color:var(--text-muted); margin-top:0.3rem;">
                Regardez la caméra pendant la capture ou sélectionnez directement votre nom dans la liste de la borne.
            </p>

            <div class="video-container">
                <video id="webcam-stream" autoplay playsinline></video>
                <div id="face-scan-line" style="position:absolute;top:0;left:0;width:100%;height:3px;background:#2563eb;box-shadow:0 0 10px #2563eb;animation:scanLine 2s infinite linear;display:none;"></div>
            </div>

            <button onclick="captureAndVerifyFace()" style="background:#2563eb; color:#fff; border:none; padding:0.85rem 1.75rem; border-radius:12px; font-weight:800; font-size:0.95rem; cursor:pointer; width:100%; box-shadow:0 8px 16px rgba(37,99,235,0.25);">
                📸 Analyser le visage maintenant
            </button>

            <!-- Sélection directe pour la borne -->
            <div style="width:100%; margin-top:1.25rem; text-align:left;">
                <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.35rem; font-weight:700; text-transform:uppercase;">
                    Ou sélection rapide (Simulation tactile Borne) :
                </div>
                <div class="candidates-list" id="face-candidates-list">
                    <div style="padding:1rem; text-align:center; color:var(--text-muted);">Chargement...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EMPREINTE DIGITALE -->
    <div class="modal-overlay" id="modal-finger">
        <div class="modal-box">
            <button class="modal-close" onclick="closeFingerModal()">✕</button>
            <h3 style="font-size:1.35rem; font-weight:800; color:var(--text-main);">Capteur d'Empreintes</h3>
            <p style="font-size:0.85rem; color:var(--text-muted); margin-top:0.3rem;">
                Posez votre doigt sur le lecteur biométrique ou cliquez sur le capteur ci-dessous.
            </p>

            <div class="finger-anim-box" id="finger-sensor-box" onclick="triggerSimulatedSensor()">
                👆
            </div>
            <div id="finger-status-text" style="font-size:0.88rem; font-weight:700; color:#059669; margin-bottom:1.25rem;">
                Prêt à scanner... Cliquez sur le capteur ou sélectionnez dans la liste ci-dessous
            </div>

            <div style="width:100%; text-align:left;">
                <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.35rem; font-weight:700; text-transform:uppercase;">
                    Sélectionner l'Employé ou le Patient :
                </div>
                <div class="candidates-list" id="finger-candidates-list">
                    <div style="padding:1rem; text-align:center; color:var(--text-muted);">Chargement...</div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes scanLine {
            0% {
                top: 0%;
            }

            50% {
                top: 96%;
            }

            100% {
                top: 0%;
            }
        }
    </style>

    <script>
        // HORLOGE
        function updateClock() {
            const now = new Date();
            document.getElementById('clock-time').textContent = now.toLocaleTimeString('fr-FR');
            document.getElementById('clock-date').textContent = now.toLocaleDateString('fr-FR', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // ONGLETS
        function switchTab(tab) {
            const btnPointages = document.getElementById('tab-btn-pointages');
            const btnRdvs = document.getElementById('tab-btn-rdvs');
            const panelPointages = document.getElementById('panel-pointages');
            const panelRdvs = document.getElementById('panel-rdvs');

            if (tab === 'pointages') {
                btnPointages.classList.add('active');
                btnRdvs.classList.remove('active');
                panelPointages.style.display = 'block';
                panelRdvs.style.display = 'none';
            } else {
                btnRdvs.classList.add('active');
                btnPointages.classList.remove('active');
                panelRdvs.style.display = 'block';
                panelPointages.style.display = 'none';
            }
        }

        // CHARGEMENT DONNÉES EN DIRECT
        async function fetchAllData() {
            try {
                const resPointages = await fetch('/api/kiosk/recent-pointages');
                if (resPointages.ok) {
                    const dataPointages = await resPointages.json();
                    renderPointagesTable(dataPointages);
                    document.getElementById('count-pointages').textContent = dataPointages.length;
                }

                const resRdvs = await fetch('/api/kiosk/daily-rdv');
                if (resRdvs.ok) {
                    const dataRdvs = await resRdvs.json();
                    renderRdvsTable(dataRdvs);
                    document.getElementById('count-rdvs').textContent = dataRdvs.length;
                }
            } catch (err) {
                console.error("Erreur auto-rafraîchissement:", err);
            }
        }

        function renderPointagesTable(items) {
            const tbody = document.getElementById('tbody-pointages');
            if (!items || items.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="empty-table">Aucun pointage enregistré aujourd'hui pour l'instant.</td></tr>`;
                return;
            }

            tbody.innerHTML = items.map(item => {
                let badgeType = item.type === 'ENTREE' ? 'badge-entree' : 'badge-sortie';
                let sourceIcon = item.source === 'FACE' ? '📸 Facial' : (item.source === 'EMPREINTE' ? '👆 Empreinte' : '🏷️ Borne');
                let statusBadge = item.status === 'Retard' ?
                    `<span class="badge-status badge-retard">⚠️ Retard</span>` :
                    `<span class="badge-status badge-entree">✔ Présent</span>`;

                return `
                    <tr>
                        <td style="font-weight:700; color:var(--text-main);">${item.full_name}</td>
                        <td style="color:var(--text-muted); font-weight:600;">${item.department}</td>
                        <td><span class="badge-status ${badgeType}">${item.type}</span></td>
                        <td style="font-weight:700; color:var(--text-main);">${sourceIcon}</td>
                        <td style="font-weight:800; font-variant-numeric:tabular-nums;">${item.time}</td>
                        <td>${statusBadge}</td>
                    </tr>
                `;
            }).join('');
        }

        function renderRdvsTable(items) {
            const tbody = document.getElementById('tbody-rdvs');
            if (!items || items.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="empty-table">Aucun rendez-vous patient programmé pour aujourd'hui.</td></tr>`;
                return;
            }

            tbody.innerHTML = items.map(item => {
                let isPresent = item.statut === 'Présent' || item.statut === 'Réalisé' || item.pointage_at !== null;
                let statusBadge = isPresent ?
                    `<span class="badge-status badge-rdv-present">✔ Présent / Arrivé</span>` :
                    `<span class="badge-status badge-rdv-planifie">🕒 ${item.statut}</span>`;

                let timeArrival = item.pointage_at ? `<span style="color:#059669; font-weight:800;">${item.pointage_at}</span>` : `<span style="color:var(--text-muted);">-</span>`;

                let actionBtn = isPresent ?
                    `<span style="font-size:0.78rem; color:#059669; font-weight:700;">✔ En attente</span>` :
                    `<button onclick="quickCheckInPatient(${item.id})" style="background:#059669; color:#fff; border:none; padding:0.4rem 0.85rem; border-radius:8px; font-weight:700; cursor:pointer; font-size:0.8rem; transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">✔ Valider</button>`;

                return `
                    <tr>
                        <td style="font-weight:800; color:var(--text-main); font-size:1rem;">${item.nom_patient}</td>
                        <td style="font-weight:600; color:#2563eb;">${item.service}</td>
                        <td style="color:var(--text-muted); font-weight:500;">${item.medecin}</td>
                        <td style="font-weight:800;">${item.heure}</td>
                        <td>${timeArrival}</td>
                        <td>${statusBadge}</td>
                        <td>${actionBtn}</td>
                    </tr>
                `;
            }).join('');
        }

        async function quickCheckInPatient(id) {
            try {
                const res = await fetch('/api/kiosk/biometric-verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        category: 'PATIENT',
                        id: id
                    })
                });
                if (res.ok) {
                    const result = await res.json();
                    alert(`✔ ${result.message}\nPatient : ${result.data.name}`);
                    fetchAllData();
                }
            } catch (e) {
                console.error(e);
            }
        }

        setInterval(fetchAllData, 5000);
        fetchAllData();

        // CHARGEMENT DES CANDIDATS (SIMULATION BIO)
        async function loadBiometricCandidates() {
            try {
                const res = await fetch('/api/kiosk/biometric-candidates');
                if (!res.ok) return;
                const data = await res.json();

                const faceList = document.getElementById('face-candidates-list');
                let htmlFace = '';
                data.employees.forEach(emp => {
                    htmlFace += `
                        <div class="candidate-item" onclick="verifyCandidateBio('EMPLOYE', ${emp.id}, 'FACE')">
                            <div>
                                <div style="font-weight:800; color:var(--text-main);">👤 ${emp.name}</div>
                                <div style="font-size:0.75rem; color:var(--text-muted);">${emp.role_info}</div>
                            </div>
                            <span style="background:rgba(37,99,235,0.12); color:#2563eb; padding:0.22rem 0.55rem; border-radius:8px; font-size:0.75rem; font-weight:700;">Pointer ➔</span>
                        </div>
                    `;
                });
                data.patients.forEach(pt => {
                    htmlFace += `
                        <div class="candidate-item" onclick="verifyCandidateBio('PATIENT', ${pt.id}, 'FACE')">
                            <div>
                                <div style="font-weight:800; color:var(--text-main);">🏥 ${pt.name}</div>
                                <div style="font-size:0.75rem; color:#2563eb;">${pt.role_info}</div>
                            </div>
                            <span style="background:rgba(16,185,129,0.15); color:#059669; padding:0.22rem 0.55rem; border-radius:8px; font-size:0.75rem; font-weight:700;">Arrivée ➔</span>
                        </div>
                    `;
                });
                faceList.innerHTML = htmlFace || '<div style="padding:1rem; text-align:center;">Aucun profil disponible</div>';

                const fingerList = document.getElementById('finger-candidates-list');
                let htmlFinger = '';
                data.employees.forEach(emp => {
                    htmlFinger += `
                        <div class="candidate-item" onclick="verifyCandidateBio('EMPLOYE', ${emp.id}, 'EMPREINTE')">
                            <div>
                                <div style="font-weight:800; color:var(--text-main);">👤 ${emp.name}</div>
                                <div style="font-size:0.75rem; color:var(--text-muted);">${emp.role_info}</div>
                            </div>
                            <span style="background:rgba(16,185,129,0.15); color:#059669; padding:0.22rem 0.55rem; border-radius:8px; font-size:0.75rem; font-weight:700;">👆 Empreinte ➔</span>
                        </div>
                    `;
                });
                data.patients.forEach(pt => {
                    htmlFinger += `
                        <div class="candidate-item" onclick="verifyCandidateBio('PATIENT', ${pt.id}, 'EMPREINTE')">
                            <div>
                                <div style="font-weight:800; color:var(--text-main);">🏥 ${pt.name}</div>
                                <div style="font-size:0.75rem; color:#2563eb;">${pt.role_info}</div>
                            </div>
                            <span style="background:rgba(16,185,129,0.15); color:#059669; padding:0.22rem 0.55rem; border-radius:8px; font-size:0.75rem; font-weight:700;">👆 Empreinte ➔</span>
                        </div>
                    `;
                });
                fingerList.innerHTML = htmlFinger || '<div style="padding:1rem; text-align:center;">Aucun profil disponible</div>';

            } catch (e) {
                console.error(e);
            }
        }

        async function verifyCandidateBio(category, id, source) {
            try {
                const endpoint = source === 'FACE' ? '/api/kiosk/face-verify' : '/api/kiosk/biometric-verify';
                const payload = source === 'FACE' ? {
                    image: 'data:image/jpeg;base64,SIMULATED_FACE',
                    category: category,
                    id: id
                } : {
                    category: category,
                    id: id
                };

                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await res.json();
                if (res.ok && result.status === 'success') {
                    if (source === 'FACE') closeFaceModal();
                    else closeFingerModal();

                    alert(`✔ ${result.message}\nNom : ${result.data.name || ''}\nHeure : ${result.data.time || ''}`);
                    fetchAllData();
                } else {
                    alert('Erreur : ' + (result.message || 'Impossible de valider le pointage.'));
                }
            } catch (e) {
                alert('Erreur réseau.');
            }
        }

        // MODALS FACIAL & EMPREINTE
        let videoStream = null;

        function openFaceModal() {
            document.getElementById('modal-face').classList.add('active');
            loadBiometricCandidates();
            initCamera();
        }

        function closeFaceModal() {
            document.getElementById('modal-face').classList.remove('active');
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
        }

        async function initCamera() {
            const video = document.getElementById('webcam-stream');
            try {
                videoStream = await navigator.mediaDevices.getUserMedia({
                    video: true
                });
                video.srcObject = videoStream;
                document.getElementById('face-scan-line').style.display = 'block';
            } catch (e) {
                document.getElementById('face-scan-line').style.display = 'none';
            }
        }

        async function captureAndVerifyFace() {
            const video = document.getElementById('webcam-stream');
            if (!video.srcObject) {
                alert("Caméra inactive. Veuillez cliquer sur votre nom dans la liste de sélection ci-dessous.");
                return;
            }

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            const dataUrl = canvas.toDataURL('image/jpeg');

            try {
                const res = await fetch('/api/kiosk/face-verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        image: dataUrl
                    })
                });
                const result = await res.json();
                if (res.ok && result.status === 'success') {
                    closeFaceModal();
                    alert(`✔ ${result.message}\nNom : ${result.data.name || result.data.employee}`);
                    fetchAllData();
                } else {
                    alert(result.message || "Visage non reconnu. Sélectionnez votre profil ci-dessous.");
                }
            } catch (e) {
                alert("Erreur réseau.");
            }
        }

        function openFingerModal() {
            document.getElementById('modal-finger').classList.add('active');
            loadBiometricCandidates();
        }

        function closeFingerModal() {
            document.getElementById('modal-finger').classList.remove('active');
            document.getElementById('finger-sensor-box').classList.remove('scanning');
        }

        function triggerSimulatedSensor() {
            const box = document.getElementById('finger-sensor-box');
            const statusText = document.getElementById('finger-status-text');
            box.classList.add('scanning');
            statusText.textContent = "👆 Lecture biométrique du capteur en cours...";
            statusText.style.color = "#2563eb";

            setTimeout(() => {
                box.classList.remove('scanning');
                statusText.textContent = "Veuillez cliquer sur votre nom ci-dessous pour confirmer l'empreinte détectée.";
                statusText.style.color = "#059669";
            }, 1500);
        }
    </script>
</body>

</html>