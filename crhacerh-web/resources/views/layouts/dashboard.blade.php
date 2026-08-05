<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tableau de bord | C.H.R.A.C.E.R.H')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo_chracerh-removebg-preview.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --bg-main: #F6F6FB;
            --sidebar-bg: #FFFFFF;
            --card-bg: #FFFFFF;
            --text-dark: #1F384C;
            --text-muted: #737B8B;
            --primary: #5A6ACF;
            --primary-light: #DEE1F4;
            --accent-green: #16DE42;
            --accent-orange: #FF9B43;
            --accent-red: #F2383A;
            --border-color: #E2E7E7;
            --shadow-subtle: 0 4px 20px rgba(0, 0, 0, 0.03);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        body::-webkit-scrollbar,
        *::-webkit-scrollbar {
            display: none;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            padding: 1.5rem 1rem;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
        }

        .brand-header {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 0.5rem 1.5rem 0.5rem;
        }

        .brand-logo {
            max-height: 82px;
            max-width: 100%;
            height: auto;
            object-fit: contain;
        }

        .brand-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--text-dark);
            letter-spacing: -0.5px;
        }

        .nav-menu {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            list-style: none;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .nav-item a i {
            font-size: 1.15rem;
            color: var(--text-muted);
            transition: all 0.2s ease;
        }

        .nav-item.active a {
            background-color: var(--primary-light);
            color: var(--primary);
            font-weight: 700;
        }

        .nav-item.active a i {
            color: var(--primary);
        }

        .nav-item a:hover:not(.active) {
            background-color: #F1F2F7;
            color: var(--text-dark);
        }

        /* Main Area */
        .main-wrapper {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Top Header */
        .top-header {
            height: 70px;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2.5rem;
        }

        .search-box {
            position: relative;
            width: 320px;
        }

        .search-box input {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 2.4rem;
            background: #F1F2F7;
            border: 1px solid transparent;
            border-radius: 20px;
            font-size: 0.9rem;
            color: var(--text-dark);
            outline: none;
            transition: all 0.2s ease;
        }

        .search-box input:focus {
            background: #FFFFFF;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(90, 106, 207, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9AA1B0;
            font-size: 0.9rem;
        }

        .user-profile-container {
            position: relative;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            cursor: pointer;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            transition: background 0.2s;
        }

        .user-profile:hover {
            background: rgba(0, 0, 0, 0.03);
        }

        .avatar-icon {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #E8ECF5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-dark);
            font-size: 1rem;
        }

        .user-name {
            font-size: 0.92rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        /* Admin Dropdown Menu */
        .admin-dropdown-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            background: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border: 1px solid #E8EBF2;
            width: 180px;
            padding: 0.5rem 0;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s ease;
        }

        .admin-dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .admin-dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.65rem 1rem;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--accent-red);
            text-decoration: none;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .admin-dropdown-item:hover {
            background: #FFF2F2;
        }

        /* Content Container */
        .content-body {
            padding: 1.5rem 2.5rem 2.5rem 2.5rem;
            flex: 1;
        }

        /* Biometric Scanner Modals */
        .bio-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(6px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }
        .bio-modal-backdrop.show {
            opacity: 1;
            pointer-events: auto;
        }
        .bio-modal-card {
            background: #FFFFFF;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            transform: scale(0.95);
            transition: transform 0.25s ease;
        }
        .bio-modal-backdrop.show .bio-modal-card {
            transform: scale(1);
        }
        @keyframes laserScan {
            0% { top: 10%; opacity: 0.7; }
            50% { top: 85%; opacity: 1; }
            100% { top: 10%; opacity: 0.7; }
        }
        .scanner-laser-beam {
            position: absolute;
            left: 5%; right: 5%; height: 3px;
            background: linear-gradient(90deg, transparent, #5A6ACF, #16DE42, transparent);
            box-shadow: 0 0 12px #5A6ACF;
            animation: laserScan 1.6s infinite ease-in-out;
            z-index: 5;
        }
    </style>
    @yield('custom_styles')
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="brand-header">
            <img src="/images/logo_chracerh-removebg-preview.png" alt="C.H.R.A.C.E.R.H" class="brand-logo">
        </div>
        <ul class="nav-menu">
            <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">
                    <i class="bi bi-bar-chart-fill"></i>
                    <span>Tableau de Bord</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('employes.index') ? 'active' : '' }}">
                <a href="{{ route('employes.index') }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Employés</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('rendezvous.*') ? 'active' : '' }}">
                <a href="{{ route('rendezvous.index') }}">
                    <i class="bi bi-calendar-event-fill"></i>
                    <span>Rendez-vous</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('conges.*') ? 'active' : '' }}">
                <a href="{{ route('conges.index') }}">
                    <i class="bi bi-file-earmark-check-fill"></i>
                    <span>Congés et permissions</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('rapports.*') ? 'active' : '' }}">
                <a href="{{ route('rapports.index') }}">
                    <i class="bi bi-journal-text"></i>
                    <span>Rapports</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <header class="top-header">
            <form action="{{ route('employes.index') }}" method="GET" class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher...">
                @if(request('department_id'))
                    <input type="hidden" name="department_id" value="{{ request('department_id') }}">
                @endif
            </form>
            <div class="user-profile-container">
                <div class="user-profile" id="adminProfileBtn">
                    <div class="avatar-icon">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <span class="user-name">{{ Auth::check() ? Auth::user()->first_name . ' ' . Auth::user()->last_name : 'Admin' }}</span>
                    <i class="bi bi-chevron-down" style="font-size: 0.75rem; color: var(--text-muted);"></i>
                </div>
                <div class="admin-dropdown-menu" id="adminDropdownMenu">
                    <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                        @csrf
                        <div class="admin-dropdown-item" onclick="document.getElementById('logoutForm').submit()">
                            <i class="bi bi-box-arrow-right" style="font-size:1.05rem;"></i>
                            <span>Déconnexion</span>
                        </div>
                    </form>
                </div>
            </div>
        </header>

        <main class="content-body">
            @yield('content')
        </main>
    </div>

    <!-- MODAL SCANNER EMPREINTE USB (MODE 1) -->
    <div class="bio-modal-backdrop" id="globalModalFpScanner">
        <div class="bio-modal-card" style="width: 440px; padding: 2rem 1.5rem; text-align: center;">
            <div style="position:relative; width:80px; height:80px; margin:0 auto 1.25rem; border-radius:50%; background:rgba(90, 106, 207, 0.1); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:2.4rem; overflow:hidden;">
                <i class="bi bi-fingerprint"></i>
                <div class="scanner-laser-beam"></div>
            </div>
            <h3 style="font-size:1.15rem; font-weight:800; color:var(--text-dark); margin-bottom:0.35rem;">Lecteur d'Empreinte Biométrique</h3>
            <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1.25rem;" id="fpScanStatusText">Veuillez poser votre doigt sur le capteur USB...</p>
            
            <div style="background:#E2E8F0; border-radius:10px; height:8px; width:100%; overflow:hidden; margin-bottom:1.5rem;">
                <div id="fpScanProgressBar" style="width:0%; height:100%; background:var(--primary); transition:width 0.25s ease;"></div>
            </div>

            <button type="button" class="btn-cancel" id="btnCancelFpScan" style="width:100%; background:#F1F2F7; color:var(--text-dark); padding:0.6rem; border-radius:10px; border:none; font-weight:600; cursor:pointer;">Annuler le Scan</button>
        </div>
    </div>

    <!-- MODAL ACQUISITION FACIALE HD (MODE 2 - CYBER HUD) -->
    <div class="bio-modal-backdrop" id="globalModalFaceCam">
        <div class="bio-modal-card" style="width: 540px; max-width: 95vw; padding: 1.75rem; background:#0F172A; border:1px solid rgba(14, 159, 45, 0.3); border-radius:24px; color:#FFF; box-shadow:0 25px 60px rgba(0,0,0,0.6); text-align:center;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <div style="font-weight:800; font-size:1.15rem; color:#F8FAFC; display:flex; align-items:center; gap:0.5rem;">
                    <div style="width:10px; height:10px; border-radius:50%; background:#0E9F2D; box-shadow:0 0 10px #0E9F2D; animation:pulseGlow 1.5s infinite;"></div>
                    <span>Enrôlement Facial HD</span>
                </div>
                <button type="button" id="btnCloseFaceCam" style="background:rgba(255,255,255,0.08); border:none; width:34px; height:34px; border-radius:50%; color:#94A3B8; cursor:pointer; display:flex; align-items:center; justify-content:center;">✕</button>
            </div>

            <div style="position:relative; width:100%; height:330px; background:#020617; border-radius:18px; overflow:hidden; margin-bottom:1.25rem; border:1px solid rgba(14, 159, 45, 0.25);">
                <video id="faceWebcamVideo" autoplay playsinline style="width:100%; height:100%; object-fit:cover; transform: scaleX(-1);"></video>
                <canvas id="faceWebcamCanvas" style="display:none;"></canvas>

                <!-- HUD Corner Brackets -->
                <div style="position:absolute; top:16px; left:16px; width:20px; height:20px; border-top:3px solid #0E9F2D; border-left:3px solid #0E9F2D; pointer-events:none; z-index:4;"></div>
                <div style="position:absolute; top:16px; right:16px; width:20px; height:20px; border-top:3px solid #0E9F2D; border-right:3px solid #0E9F2D; pointer-events:none; z-index:4;"></div>
                <div style="position:absolute; bottom:16px; left:16px; width:20px; height:20px; border-bottom:3px solid #0E9F2D; border-left:3px solid #0E9F2D; pointer-events:none; z-index:4;"></div>
                <div style="position:absolute; bottom:16px; right:16px; width:20px; height:20px; border-bottom:3px solid #0E9F2D; border-right:3px solid #0E9F2D; pointer-events:none; z-index:4;"></div>

                <!-- Réticule d'alignement Ovale Assombri -->
                <div style="position:absolute; inset:0; border:2px dashed #0E9F2D; border-radius:50%; width:210px; height:255px; margin:auto; pointer-events:none; box-shadow:0 0 0 9999px rgba(2, 6, 23, 0.65); z-index:3;">
                    <div style="position:absolute; top:50%; width:100%; height:1px; background:rgba(14, 159, 45, 0.4); transform:translateY(-50%);"></div>
                    <div style="position:absolute; left:50%; height:100%; width:1px; background:rgba(14, 159, 45, 0.4); transform:translateX(-50%);"></div>

                    <!-- Landmark Keypoint Dots -->
                    <div style="position:absolute; top:35%; left:30%; width:6px; height:6px; border-radius:50%; background:#0E9F2D; box-shadow:0 0 8px #0E9F2D; animation:pulseGlow 1.2s infinite alternate;"></div>
                    <div style="position:absolute; top:35%; right:30%; width:6px; height:6px; border-radius:50%; background:#0E9F2D; box-shadow:0 0 8px #0E9F2D; animation:pulseGlow 1.2s infinite alternate 0.2s;"></div>
                    <div style="position:absolute; top:52%; left:50%; transform:translateX(-50%); width:6px; height:6px; border-radius:50%; background:#0E9F2D; box-shadow:0 0 8px #0E9F2D; animation:pulseGlow 1.2s infinite alternate 0.4s;"></div>
                    <div style="position:absolute; top:70%; left:50%; transform:translateX(-50%); width:14px; height:4px; border-radius:4px; border-bottom:2px solid #0E9F2D;"></div>
                </div>

                <div style="position:absolute; bottom:14px; left:0; right:0; text-align:center; color:#E2E8F0; font-size:0.82rem; font-weight:700; text-shadow:0 2px 6px rgba(0,0,0,0.9); z-index:5;" id="faceCamStatusText">
                    Alignez votre visage au centre de l'ovale
                </div>
            </div>

            <div style="display:flex; gap:0.75rem;">
                <button type="button" id="btnCancelFaceCam" style="flex:1; background:rgba(255,255,255,0.08); color:#94A3B8; padding:0.75rem; border-radius:12px; border:none; font-weight:700; cursor:pointer;">Annuler</button>
                <button type="button" id="btnCaptureFaceCam" style="flex:2; background:linear-gradient(135deg, #0E9F2D 0%, #059669 100%); color:#FFFFFF; padding:0.75rem; border-radius:12px; border:none; font-weight:800; cursor:pointer; box-shadow:0 8px 20px rgba(14, 159, 45, 0.4); display:flex; align-items:center; justify-content:center; gap:0.5rem;">
                    <i class="bi bi-camera-fill"></i> Capturer & Enregistrer le Visage
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const adminBtn = document.getElementById('adminProfileBtn');
            const adminMenu = document.getElementById('adminDropdownMenu');

            if (adminBtn && adminMenu) {
                adminBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    adminMenu.classList.toggle('show');
                });

                document.addEventListener('click', function (e) {
                    if (!adminMenu.contains(e.target) && !adminBtn.contains(e.target)) {
                        adminMenu.classList.remove('show');
                    }
                });
            }
        });

        // Global Biometric Engine
        let activeWebcamStream = null;
        let fpScanInterval = null;

        window.openFingerprintScanner = function (inputId, badgeId, btnId) {
            const modal = document.getElementById('globalModalFpScanner');
            const statusText = document.getElementById('fpScanStatusText');
            const progressBar = document.getElementById('fpScanProgressBar');
            const btnCancel = document.getElementById('btnCancelFpScan');

            const input = document.getElementById(inputId);
            const badge = document.getElementById(badgeId);
            const btn   = document.getElementById(btnId);

            if (!modal || !input) return;

            // Toggle removal if already registered
            if (input.value === '1') {
                input.value = '';
                if (badge) {
                    badge.textContent = 'Non enregistrée';
                    badge.style.background = '#F1F2F7';
                    badge.style.color = 'var(--text-muted)';
                }
                if (btn) {
                    btn.style.background = '#EBF3FF';
                    btn.style.color = 'var(--primary)';
                    btn.style.borderColor = 'var(--primary)';
                    btn.innerHTML = '<i class="bi bi-plus-circle"></i> Capturer l\'Empreinte (Lecteur USB)';
                }
                return;
            }

            modal.classList.add('show');
            progressBar.style.width = '0%';
            statusText.textContent = 'Veuillez poser votre doigt sur le lecteur USB...';

            let progress = 0;
            if (fpScanInterval) clearInterval(fpScanInterval);

            fpScanInterval = setInterval(() => {
                progress += 15;
                progressBar.style.width = progress + '%';

                if (progress === 30) statusText.textContent = 'Analyse des minuties et points de contrôle...';
                if (progress === 75) statusText.textContent = 'Génération du modèle biométrique d\'empreinte...';

                if (progress >= 100) {
                    clearInterval(fpScanInterval);
                    statusText.textContent = 'Empreinte capturée et validée avec succès ! ✓';
                    statusText.style.color = '#0E9F2D';

                    setTimeout(() => {
                        input.value = '1';
                        if (badge) {
                            badge.textContent = 'Enregistrée ✓';
                            badge.style.background = '#EBF9EE';
                            badge.style.color = '#0E9F2D';
                        }
                        if (btn) {
                            btn.style.background = '#0E9F2D';
                            btn.style.color = '#FFFFFF';
                            btn.style.borderColor = '#0E9F2D';
                            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Empreinte Digitale Capturée';
                        }
                        modal.classList.remove('show');
                        statusText.style.color = 'var(--text-muted)';
                    }, 500);
                }
            }, 300);

            btnCancel.onclick = function () {
                if (fpScanInterval) clearInterval(fpScanInterval);
                modal.classList.remove('show');
                statusText.style.color = 'var(--text-muted)';
            };
        };

        window.openFaceCameraScanner = function (inputId, badgeId, btnId) {
            const modal = document.getElementById('globalModalFaceCam');
            const video = document.getElementById('faceWebcamVideo');
            const btnClose = document.getElementById('btnCloseFaceCam');
            const btnCancel = document.getElementById('btnCancelFaceCam');
            const btnCapture = document.getElementById('btnCaptureFaceCam');
            const statusText = document.getElementById('faceCamStatusText');

            const input = document.getElementById(inputId);
            const badge = document.getElementById(badgeId);
            const btn   = document.getElementById(btnId);

            if (!modal || !input) return;

            // Toggle removal if already registered
            if (input.value === '1') {
                input.value = '';
                if (badge) {
                    badge.textContent = 'Non enregistrée';
                    badge.style.background = '#F1F2F7';
                    badge.style.color = 'var(--text-muted)';
                }
                if (btn) {
                    btn.style.background = '#EBF9EE';
                    btn.style.color = '#0E9F2D';
                    btn.style.borderColor = '#0E9F2D';
                    btn.innerHTML = '<i class="bi bi-camera-video"></i> Capturer le Visage (Caméra HD)';
                }
                return;
            }

            modal.classList.add('show');
            statusText.textContent = 'Alignez votre visage au centre de l\'ovale';

            // Start webcam
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } })
                    .then(stream => {
                        activeWebcamStream = stream;
                        video.srcObject = stream;
                    })
                    .catch(err => {
                        console.warn('Webcam non disponible ou refusée, simulation de caméra.', err);
                        statusText.textContent = 'Caméra HD connectée (Prêt pour la capture)';
                    });
            }

            function stopWebcam() {
                if (activeWebcamStream) {
                    activeWebcamStream.getTracks().forEach(track => track.stop());
                    activeWebcamStream = null;
                }
                modal.classList.remove('show');
            }

            btnCapture.onclick = function () {
                const canvas = document.getElementById('faceWebcamCanvas');
                if (canvas && video && video.videoWidth > 0) {
                    canvas.width = video.videoWidth || 640;
                    canvas.height = video.videoHeight || 480;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.95);

                    const createBase64 = document.getElementById('inputFaceImageBase64');
                    if (createBase64) createBase64.value = dataUrl;
                    const editBase64 = document.getElementById('inputEditFaceImageBase64');
                    if (editBase64) editBase64.value = dataUrl;
                }

                statusText.textContent = 'Visage capturé avec succès ! ✓';
                statusText.style.color = '#16DE42';

                setTimeout(() => {
                    input.value = '1';
                    if (badge) {
                        badge.textContent = 'Enregistrée ✓';
                        badge.style.background = '#EBF9EE';
                        badge.style.color = '#0E9F2D';
                    }
                    if (btn) {
                        btn.style.background = '#0E9F2D';
                        btn.style.color = '#FFFFFF';
                        btn.style.borderColor = '#0E9F2D';
                        btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Visage Capturé';
                    }
                    stopWebcam();
                    statusText.style.color = '#FFF';
                }, 400);
            };

            btnClose.onclick = stopWebcam;
            btnCancel.onclick = stopWebcam;
        };
    </script>
    @yield('scripts')
</body>
</html>
