<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borne de Pointage | C.H.R.A.C.E.R.H</title>
    <meta name="description" content="Kiosk de pointage dynamique">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        :root {
            --primary: #D4B895;
            --primary-light: #E8D0B3;
            --bg-gradient-start: #FDFBF7;
            --bg-gradient-end: #EFE4D3;
            --card-bg: rgba(255, 255, 255, 0.5);
            --card-border: rgba(212, 184, 149, 0.3);
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated Background Orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.5;
            animation: float 10s infinite ease-in-out alternate;
            z-index: -1;
        }
        .orb-1 {
            width: 400px;
            height: 400px;
            background: #D4B895;
            top: -10%;
            left: -10%;
        }
        .orb-2 {
            width: 500px;
            height: 500px;
            background: #E8D0B3;
            bottom: -20%;
            right: -10%;
            animation-delay: -5s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(50px, 50px) scale(1.1); }
        }

        .container {
            width: 100%;
            max-width: 1400px;
            padding: 2rem;
            display: grid;
            grid-template-columns: 1fr 450px;
            gap: 3rem;
            height: 90vh;
        }

        .r-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid var(--card-border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Left Column: History */
        .history-section {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            height: 100%;
        }

        .header {
            margin-bottom: 1rem;
        }
        .header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            background: linear-gradient(to right, #8C7B6B, #4A4036);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .header p {
            font-size: 1.25rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
        }

        .history-card {
            flex: 1;
            padding: 2rem;
        }
        .history-card h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-main);
        }

        .pointages-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            overflow-y: auto;
            max-height: calc(100% - 3rem);
            padding-right: 1rem;
        }

        /* Custom Scrollbar */
        .pointages-list::-webkit-scrollbar {
            width: 8px;
        }
        .pointages-list::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.02);
            border-radius: 4px;
        }
        .pointages-list::-webkit-scrollbar-thumb {
            background: rgba(0,0,0,0.1);
            border-radius: 4px;
        }

        .pointage-item {
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 16px;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
        }
        .pointage-item:hover {
            transform: translateX(5px);
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        .pointage-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #D4B895, #C2A17A);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 2px solid rgba(255,255,255,0.8);
        }
        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .employee-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-main);
        }
        .time-badge {
            padding: 0.5rem 1rem;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1rem;
        }
        .badge-entree {
            background: rgba(16, 185, 129, 0.1);
            color: #2E7D32;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        .badge-depart {
            background: rgba(245, 158, 11, 0.1);
            color: #D84315;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        /* Right Column: QR Code */
        .qr-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 2rem;
        }

        .qr-card {
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
            width: 100%;
        }

        .qr-wrapper {
            background: white;
            padding: 1.5rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .qr-wrapper:hover {
            transform: scale(1.05);
        }

        /* Progress Bar */
        .progress-container {
            width: 100%;
            height: 8px;
            background: rgba(0,0,0,0.05);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 1rem;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #D4B895, #C2A17A);
            width: 100%;
            border-radius: 10px;
            transform-origin: left;
        }

        .instruction {
            text-align: center;
            font-size: 1.1rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
                height: auto;
                gap: 2rem;
            }
            .qr-section {
                order: -1; /* QR Code on top for smaller screens */
            }
        }

        /* Mode Selector */
        .mode-selector {
            display: flex;
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
            border-radius: 30px;
            padding: 4px;
            gap: 4px;
            width: fit-content;
            box-shadow: 0 8px 32px rgba(212, 184, 149, 0.1);
        }
        .mode-btn {
            background: transparent;
            border: none;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .mode-btn:hover {
            color: var(--text-main);
        }
        .mode-btn.active {
            background: linear-gradient(135deg, #D4B895, #C2A17A);
            color: white;
            box-shadow: 0 4px 15px rgba(212, 184, 149, 0.4);
        }

        /* Content Cards transition */
        .content-card {
            display: none !important;
            opacity: 0;
            transform: scale(0.95);
            transition: all 0.4s ease;
        }
        .content-card.active {
            display: flex !important;
            opacity: 1;
            transform: scale(1);
        }

        /* Disclaimer */
        .kiosk-disclaimer {
            background: rgba(245, 158, 11, 0.1);
            color: #D84315;
            border: 1px solid rgba(245, 158, 11, 0.2);
            padding: 10px 16px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            width: 100%;
        }

        /* Webcam wrapper */
        .webcam-wrapper {
            position: relative;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid var(--primary);
            box-shadow: 0 20px 45px rgba(0,0,0,0.08);
            background: #000;
        }
        #webcam {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1); /* Mirror effect */
        }

        /* Scanning HUD */
        .face-scanner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .scanner-laser {
            position: absolute;
            width: 100%;
            height: 3px;
            background: linear-gradient(to right, transparent, var(--primary-light), var(--primary), var(--primary-light), transparent);
            top: 0;
            animation: scan-vertical 3s infinite linear;
            box-shadow: 0 0 8px var(--primary);
        }
        .scanner-brackets {
            width: 80%;
            height: 80%;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            position: relative;
            animation: pulse-border 2s infinite alternate ease-in-out;
        }
        .scanner-brackets::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            border: 2px solid var(--primary);
            border-radius: 50%;
            clip-path: polygon(0 0, 30% 0, 30% 100%, 0 100%);
            animation: rotate-brackets 8s infinite linear;
        }

        @keyframes scan-vertical {
            0% { top: 0%; }
            50% { top: 100%; }
            100% { top: 0%; }
        }
        @keyframes pulse-border {
            0% { transform: scale(0.96); opacity: 0.4; }
            100% { transform: scale(1.02); opacity: 1; }
        }
        @keyframes rotate-brackets {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Success & Error styles */
        .feedback-success {
            color: #2E7D32 !important;
            font-weight: 600;
            animation: text-pulse 1s infinite alternate;
        }
        .feedback-error {
            color: #C62828 !important;
            font-weight: 600;
        }
        @keyframes text-pulse {
            0% { transform: scale(1); }
            100% { transform: scale(1.03); }
        }
    </style>
</head>
<body>

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="container">
        
        <!-- Section Historique -->
        <section class="history-section">
            <div class="header">
                <h1>C.H.R.A.C.E.R.H</h1>
                <p>Système de Pointage Express Automatisé</p>
            </div>

            <div class="r-card history-card">
                <h2>
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Historique des pointages
                </h2>
                
                <div id="pointages-list" class="pointages-list">
                    <div style="text-align:center; padding: 2rem; color: var(--text-muted);">
                        Chargement du flux en direct...
                    </div>
                </div>
            </div>
        </section>

        <!-- Section Pointage (QR ou Reconnaissance Faciale) -->
        <section class="qr-section">
            <!-- Sélecteur de Mode de Pointage -->
            <div class="mode-selector">
                <button id="mode-qr-btn" class="mode-btn active" onclick="switchMode('qr')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 00-1 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    Code QR
                </button>
                <button id="mode-face-btn" class="mode-btn" onclick="switchMode('face')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Visage
                </button>
            </div>

            <!-- Conteneur QR Code -->
            <div id="qr-container" class="r-card qr-card content-card active">
                <div class="qr-wrapper" id="qr-wrapper">
                    <div id="qrcode"></div>
                </div>
                
                <div style="width: 100%;">
                    <div class="progress-container">
                        <div id="progress-bar-fill" class="progress-bar"></div>
                    </div>
                    <p style="text-align: center; font-size: 0.9rem; margin-top: 0.5rem; color: var(--text-muted);">Actualisation du code...</p>
                </div>

                <div class="instruction">
                    Ouvrez l'application mobile et scannez ce code QR pour valider votre arrivée ou votre départ.
                </div>
            </div>

            <!-- Conteneur Reconnaissance Faciale -->
            <div id="face-container" class="r-card qr-card content-card">
                <!-- Message explicatif d'oubli/perte -->
                <div class="kiosk-disclaimer">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right: 6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    À utiliser uniquement en cas de perte ou d'oubli de votre téléphone.
                </div>

                <div class="webcam-wrapper">
                    <video id="webcam" autoplay playsinline muted width="320" height="320"></video>
                    <!-- Pulsing face detection overlay -->
                    <div class="face-scanner-overlay">
                        <div class="scanner-laser"></div>
                        <div class="scanner-brackets"></div>
                    </div>
                </div>
                
                <canvas id="hidden-canvas" style="display: none;" width="640" height="640"></canvas>

                <div id="face-feedback" class="face-feedback instruction">
                    Placez votre visage au centre du cadre pour enregistrer votre présence.
                </div>
            </div>
        </section>

    </div>

    <script>
        // 1. Gestion du QR Code
        const QR_DURATION = 30000; // 30 secondes
        let startTime = Date.now();
        const progressBar = document.getElementById('progress-bar-fill');
        const qrcodeContainer = document.getElementById('qrcode');

        const qrcode = new QRCode(qrcodeContainer, {
            width: 280,
            height: 280,
            colorDark: "#4A4036", // Dark brown for QR
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        function generateNewQR() {
            // Animation bump on refresh
            const wrapper = document.getElementById('qr-wrapper');
            wrapper.style.transform = 'scale(0.95)';
            setTimeout(() => { wrapper.style.transform = 'scale(1)'; }, 150);

            const token = `CHRACERH-KIOSK-01-${Date.now()}`;
            qrcode.makeCode(token);
        }

        function updateCycle() {
            const now = Date.now();
            const elapsed = now - startTime;
            const progress = Math.max(0, 100 - (elapsed / QR_DURATION) * 100);

            progressBar.style.width = progress + '%';

            if (elapsed >= QR_DURATION) {
                startTime = now;
                generateNewQR();
            }
            requestAnimationFrame(updateCycle);
        }

        // 2. Gestion de l'historique
        async function fetchRecentPointages() {
            try {
                const response = await fetch('/api/kiosk/recent-pointages');
                const data = await response.json();
                const container = document.getElementById('pointages-list');

                if (data.length === 0) {
                    container.innerHTML = `
                        <div style="text-align:center; padding: 3rem; color: var(--text-muted);">
                            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin: 0 auto 1rem; opacity: 0.5;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4M12 20V4"></path>
                            </svg>
                            Aucun pointage aujourd'hui.
                        </div>`;
                    return;
                }

                const htmlContent = data.map(p => {
                    const isEntree = p.type === 'ENTREE';
                    const badgeClass = isEntree ? 'badge-entree' : 'badge-depart';
                    const label = isEntree ? 'Arrivé' : 'Départ';
                    
                    return `
                    <div class="pointage-item">
                        <div class="pointage-info">
                            <div class="avatar">
                                <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(p.first_name + ' ' + p.last_name)}&background=random" alt="Avatar" />
                            </div>
                            <div class="employee-name">
                                ${p.first_name} <span style="opacity: 0.8">${p.last_name}</span>
                            </div>
                        </div>
                        <div class="time-badge ${badgeClass}">
                            ${label} à ${p.time}
                        </div>
                    </div>
                    `;
                }).join('');

                if (container.innerHTML !== htmlContent) {
                    container.innerHTML = htmlContent;
                }
            } catch (error) {
                console.error("Erreur API :", error);
            }
        }

        // 3. Gestion de la Reconnaissance Faciale
        let webcamStream = null;
        let faceIntervalId = null;
        const webcamElement = document.getElementById('webcam');
        const faceFeedback = document.getElementById('face-feedback');
        const hiddenCanvas = document.getElementById('hidden-canvas');
        const canvasCtx = hiddenCanvas.getContext('2d');
        let isProcessingFace = false;

        function switchMode(mode) {
            const qrBtn = document.getElementById('mode-qr-btn');
            const faceBtn = document.getElementById('mode-face-btn');
            const qrContainer = document.getElementById('qr-container');
            const faceContainer = document.getElementById('face-container');

            if (mode === 'qr') {
                qrBtn.classList.add('active');
                faceBtn.classList.remove('active');
                qrContainer.classList.add('active');
                faceContainer.classList.remove('active');
                stopWebcam();
            } else {
                faceBtn.classList.add('active');
                qrBtn.classList.remove('active');
                faceContainer.classList.add('active');
                qrContainer.classList.remove('active');
                startWebcam();
            }
        }

        async function startWebcam() {
            faceFeedback.innerText = "Démarrage de la caméra...";
            faceFeedback.className = "face-feedback instruction";
            
            try {
                webcamStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { width: 320, height: 320, facingMode: 'user' } 
                });
                webcamElement.srcObject = webcamStream;
                webcamElement.onloadedmetadata = () => {
                    webcamElement.play();
                    faceFeedback.innerText = "Alignez votre visage au centre pour pointer.";
                    setTimeout(() => {
                        if (webcamStream) {
                            startFaceScanning();
                        }
                    }, 2000);
                };
            } catch (err) {
                console.error("Erreur d'accès à la webcam: ", err);
                faceFeedback.innerText = "Erreur : Impossible d'accéder à la webcam. Vérifiez vos autorisations.";
                faceFeedback.className = "face-feedback instruction feedback-error";
            }
        }

        function stopWebcam() {
            stopFaceScanning();
            if (webcamStream) {
                webcamStream.getTracks().forEach(track => track.stop());
                webcamStream = null;
            }
            webcamElement.srcObject = null;
        }

        function startFaceScanning() {
            stopFaceScanning();
            faceIntervalId = setInterval(captureAndVerifyFace, 3000);
        }

        function stopFaceScanning() {
            if (faceIntervalId) {
                clearInterval(faceIntervalId);
                faceIntervalId = null;
            }
        }

        async function captureAndVerifyFace() {
            if (isProcessingFace || !webcamStream) return;
            isProcessingFace = true;
            
            faceFeedback.innerText = "Analyse faciale en cours...";
            faceFeedback.className = "face-feedback instruction";

            try {
                canvasCtx.drawImage(webcamElement, 0, 0, hiddenCanvas.width, hiddenCanvas.height);
                const imageDataUrl = hiddenCanvas.toDataURL('image/jpeg', 0.85);

                const response = await fetch('/api/kiosk/face-verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ image: imageDataUrl })
                });

                const result = await response.json();
                
                if (response.ok && result.status === 'success') {
                    const user = result.data.employee;
                    const typeLabel = result.data.type === 'ENTREE' ? 'Arrivée' : 'Départ';
                    faceFeedback.innerText = `Reconnu : ${user} (${typeLabel} à ${result.data.time})`;
                    faceFeedback.className = "face-feedback instruction feedback-success";
                    
                    fetchRecentPointages();

                    stopFaceScanning();
                    setTimeout(() => {
                        if (webcamStream) {
                            faceFeedback.innerText = "Alignez votre visage au centre pour pointer.";
                            faceFeedback.className = "face-feedback instruction";
                            startFaceScanning();
                        }
                    }, 5000);
                } else {
                    faceFeedback.innerText = result.message || "Visage non reconnu. Réessayez.";
                    faceFeedback.className = "face-feedback instruction feedback-error";
                }
            } catch (error) {
                console.error("Erreur reconnaissance faciale:", error);
                faceFeedback.innerText = "Erreur de connexion avec le serveur.";
                faceFeedback.className = "face-feedback instruction feedback-error";
            } finally {
                isProcessingFace = false;
            }
        }

        // Lancement initial
        generateNewQR();
        requestAnimationFrame(updateCycle);
        fetchRecentPointages();
        setInterval(fetchRecentPointages, 3000);
    </script>
</body>
</html>