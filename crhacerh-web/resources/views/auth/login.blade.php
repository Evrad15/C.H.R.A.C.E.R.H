<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | C.H.R.A.C.E.R.H</title>
    <meta name="description" content="Connectez-vous au Système Intégré de Gestion des Présences et d'Accueil Biométrique du C.H.R.A.C.E.R.H">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg-main: #F6F6FB;
            --card-bg: #FFFFFF;
            --text-dark: #1F384C;
            --text-muted: #737B8B;
            --primary: #5A6ACF;
            --primary-dark: #4556B8;
            --primary-light: #DEE1F4;
            --accent-green: #16DE42;
            --border-color: #E2E7E7;
            --error: #F2383A;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            min-height: 100vh;
            background: #0D1B2A;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* ── Fond animé ── */
        .bg-canvas {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .bg-gradient {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 10%, rgba(90,106,207,0.35) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 90%, rgba(22,222,66,0.12) 0%, transparent 55%),
                radial-gradient(ellipse 50% 40% at 70% 20%, rgba(90,106,207,0.18) 0%, transparent 50%);
            animation: gradientShift 12s ease-in-out infinite alternate;
        }

        @keyframes gradientShift {
            0%   { opacity: 1; transform: scale(1); }
            100% { opacity: 0.8; transform: scale(1.08); }
        }

        .bg-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        /* Particules flottantes */
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(90, 106, 207, 0.3);
            animation: float linear infinite;
        }
        .particle:nth-child(1)  { width: 6px;  height: 6px;  top: 15%; left: 10%; animation-duration: 18s; animation-delay: 0s; }
        .particle:nth-child(2)  { width: 4px;  height: 4px;  top: 70%; left: 20%; animation-duration: 14s; animation-delay: -5s; }
        .particle:nth-child(3)  { width: 8px;  height: 8px;  top: 40%; left: 80%; animation-duration: 20s; animation-delay: -3s; background: rgba(22,222,66,0.25); }
        .particle:nth-child(4)  { width: 3px;  height: 3px;  top: 85%; left: 70%; animation-duration: 16s; animation-delay: -8s; }
        .particle:nth-child(5)  { width: 5px;  height: 5px;  top: 25%; left: 55%; animation-duration: 22s; animation-delay: -2s; }
        .particle:nth-child(6)  { width: 7px;  height: 7px;  top: 60%; left: 45%; animation-duration: 19s; animation-delay: -10s; background: rgba(90,106,207,0.2); }

        @keyframes float {
            0%   { transform: translateY(0px) rotate(0deg); opacity: 0.6; }
            50%  { opacity: 1; }
            100% { transform: translateY(-120px) rotate(360deg); opacity: 0; }
        }

        /* ── Card principale ── */
        .login-wrapper {
            position: relative;
            z-index: 10;
            display: flex;
            width: 100%;
            max-width: 1040px;
            min-height: 580px;
            margin: 1.5rem;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 40px 100px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.06);
        }

        /* ── Panneau gauche (visuel) ── */
        .login-visual {
            flex: 1;
            background: linear-gradient(145deg, #1a2a4a 0%, #0f1e3a 40%, #131f35 100%);
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .visual-deco {
            position: absolute;
            inset: 0;
            pointer-events: none;
        }
        .visual-deco::before {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(90,106,207,0.25) 0%, transparent 70%);
            top: -100px; left: -100px;
        }
        .visual-deco::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(22,222,66,0.12) 0%, transparent 70%);
            bottom: -80px; right: -80px;
        }

        .visual-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-badge {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #5A6ACF, #7B89E8);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            color: #fff;
            box-shadow: 0 4px 15px rgba(90,106,207,0.4);
        }

        .logo-text {
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.05em;
        }
        .logo-text span {
            display: block;
            font-size: 0.7rem;
            font-weight: 400;
            color: rgba(255,255,255,0.5);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .visual-content {
            position: relative;
            z-index: 1;
        }

        .visual-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(22,222,66,0.12);
            border: 1px solid rgba(22,222,66,0.3);
            color: #16DE42;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35rem 0.85rem;
            border-radius: 50px;
            margin-bottom: 1.5rem;
            letter-spacing: 0.05em;
        }

        .visual-badge .dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #16DE42;
            animation: blink 2s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.3; }
        }

        .visual-title {
            color: #fff;
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.25;
            margin-bottom: 1rem;
        }

        .visual-title span {
            background: linear-gradient(90deg, #5A6ACF, #7B89E8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .visual-desc {
            color: rgba(255,255,255,0.5);
            font-size: 0.875rem;
            line-height: 1.7;
            max-width: 280px;
            margin-bottom: 2rem;
        }

        .visual-stats {
            display: flex;
            gap: 1.25rem;
        }

        .stat-chip {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            backdrop-filter: blur(10px);
        }

        .stat-chip .icon {
            width: 34px; height: 34px;
            border-radius: 9px;
            background: rgba(90,106,207,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.95rem;
            color: #7B89E8;
        }

        .stat-chip .info .value {
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
        }

        .stat-chip .info .label {
            color: rgba(255,255,255,0.4);
            font-size: 0.7rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* ── Panneau droit (formulaire) ── */
        .login-form-panel {
            width: 440px;
            background: #ffffff;
            padding: 3rem 2.75rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 2.25rem;
        }

        .form-header h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 0.4rem;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        /* Alertes */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            padding: 0.85rem 1rem;
            border-radius: 12px;
            font-size: 0.83rem;
            margin-bottom: 1.5rem;
            animation: slideDown 0.3s ease;
        }
        .alert-error   { background: #FFF1F1; border: 1px solid #FECACA; color: var(--error); }
        .alert-success { background: #F0FDF4; border: 1px solid #BBF7D0; color: #15803D; }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Champs */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            letter-spacing: 0.02em;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1rem;
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-input {
            width: 100%;
            height: 50px;
            background: var(--bg-main);
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            padding: 0 1rem 0 2.85rem;
            font-size: 0.9rem;
            color: var(--text-dark);
            outline: none;
            transition: all 0.25s ease;
            font-family: 'Outfit', sans-serif;
        }

        .form-input::placeholder { color: var(--text-muted); }

        .form-input:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(90,106,207,0.1);
        }

        .form-input:focus + .input-icon,
        .input-wrap:focus-within .input-icon {
            color: var(--primary);
        }

        .form-input.is-error {
            border-color: var(--error);
            box-shadow: 0 0 0 4px rgba(242,56,58,0.1);
        }

        .field-error {
            font-size: 0.775rem;
            color: var(--error);
            margin-top: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Toggle mot de passe */
        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            font-size: 1rem;
            padding: 0.2rem;
            transition: color 0.2s;
        }
        .toggle-password:hover { color: var(--primary); }

        /* Options */
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.75rem;
            font-size: 0.83rem;
        }

        .remember-wrap {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            color: var(--text-muted);
            user-select: none;
        }

        .remember-wrap input[type="checkbox"] {
            width: 16px; height: 16px;
            border-radius: 5px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .forgot-link {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .forgot-link:hover { opacity: 0.75; }

        /* Bouton connexion */
        .btn-login {
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, var(--primary) 0%, #7B89E8 100%);
            color: #fff;
            border: none;
            border-radius: 14px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            letter-spacing: 0.03em;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(90,106,207,0.35);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #7B89E8 0%, var(--primary) 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(90,106,207,0.45);
        }

        .btn-login:hover::before { opacity: 1; }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login span, .btn-login i {
            position: relative;
            z-index: 1;
        }

        .btn-login .spinner {
            display: none;
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .btn-login.loading .btn-text { display: none; }
        .btn-login.loading .spinner  { display: block; }

        /* Footer */
        .form-footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .footer-badge {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .footer-badge i {
            color: var(--accent-green);
            font-size: 0.85rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
                max-width: 440px;
                min-height: auto;
            }
            .login-visual {
                padding: 2rem;
                min-height: 200px;
            }
            .visual-title { font-size: 1.5rem; }
            .visual-stats { display: none; }
            .login-form-panel {
                width: 100%;
                padding: 2rem 1.75rem;
            }
        }
    </style>
</head>
<body>

<!-- Fond animé -->
<div class="bg-canvas">
    <div class="bg-gradient"></div>
    <div class="bg-grid"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
</div>

<div class="login-wrapper">

    <!-- ── Panneau gauche ── -->
    <div class="login-visual">
        <div class="visual-deco"></div>

        <!-- Logo -->
        <div class="visual-logo">
            <div class="logo-badge">
                <i class="bi bi-hospital"></i>
            </div>
            <div class="logo-text">
                C.H.R.A.C.E.R.H
                <span>Système de Gestion RH</span>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="visual-content">
            <div class="visual-badge">
                <span class="dot"></span>
                Système actif
            </div>
            <h2 class="visual-title">
                Gestion Biométrique<br>
                des <span>Présences</span>
            </h2>
            <p class="visual-desc">
                Plateforme centralisée de suivi des pointages, gestion des congés,
                rendez-vous et génération de rapports pour le personnel du CHRACERH.
            </p>
            <div class="visual-stats">
                <div class="stat-chip">
                    <div class="icon"><i class="bi bi-fingerprint"></i></div>
                    <div class="info">
                        <div class="value">Biométrique</div>
                        <div class="label">Pointage</div>
                    </div>
                </div>
                <div class="stat-chip">
                    <div class="icon"><i class="bi bi-shield-check"></i></div>
                    <div class="info">
                        <div class="value">Sécurisé</div>
                        <div class="label">Accès</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Panneau droit (formulaire) ── -->
    <div class="login-form-panel">

        <div class="form-header">
            <h1>Bon retour 👋</h1>
            <p>Connectez-vous à votre espace de gestion</p>
        </div>

        {{-- Messages d'erreur globaux --}}
        @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
            <div class="alert alert-error" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        {{-- Message d'erreur de connexion --}}
        @if (session('error'))
            <div class="alert alert-error" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form id="loginForm" method="POST" action="{{ route('auth.login') }}" novalidate>
            @csrf

            <!-- Email -->
            <div class="form-group">
                <label class="form-label" for="email">Adresse e-mail</label>
                <div class="input-wrap">
                    <input
                        id="email"
                        type="email"
                        name="email"
                        class="form-input {{ $errors->has('email') ? 'is-error' : '' }}"
                        placeholder="prénom.nom@chracerh.cm"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                    >
                    <i class="bi bi-envelope input-icon"></i>
                </div>
                @error('email')
                    <div class="field-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Mot de passe -->
            <div class="form-group">
                <label class="form-label" for="password">Mot de passe</label>
                <div class="input-wrap">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-input {{ $errors->has('password') ? 'is-error' : '' }}"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                    <i class="bi bi-lock input-icon"></i>
                    <button type="button" class="toggle-password" id="togglePwd" aria-label="Afficher le mot de passe">
                        <i class="bi bi-eye" id="togglePwdIcon"></i>
                    </button>
                </div>
                @error('password')
                    <div class="field-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Se souvenir + Mot de passe oublié -->
            <div class="form-options">
                <label class="remember-wrap">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    Se souvenir de moi
                </label>
                <a href="#" class="forgot-link">Mot de passe oublié ?</a>
            </div>

            <!-- Bouton connexion -->
            <button type="submit" class="btn-login" id="btnLogin">
                <span class="btn-text">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Se connecter</span>
                </span>
                <div class="spinner"></div>
            </button>
        </form>

        <!-- Footer -->
        <div class="form-footer">
            <span class="footer-badge">
                <i class="bi bi-shield-lock-fill"></i>
                Connexion chiffrée et sécurisée
            </span>
        </div>

    </div>
</div>

<script>
    // Toggle affichage mot de passe
    const toggleBtn = document.getElementById('togglePwd');
    const pwdInput  = document.getElementById('password');
    const pwdIcon   = document.getElementById('togglePwdIcon');

    toggleBtn.addEventListener('click', () => {
        if (pwdInput.type === 'password') {
            pwdInput.type = 'text';
            pwdIcon.className = 'bi bi-eye-slash';
            toggleBtn.setAttribute('aria-label', 'Masquer le mot de passe');
        } else {
            pwdInput.type = 'password';
            pwdIcon.className = 'bi bi-eye';
            toggleBtn.setAttribute('aria-label', 'Afficher le mot de passe');
        }
    });

    // Animation du bouton au submit
    const loginForm = document.getElementById('loginForm');
    const loginBtn  = document.getElementById('btnLogin');

    loginForm.addEventListener('submit', (e) => {
        const email    = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;

        if (!email || !password) return; // laisse HTML5 gérer

        loginBtn.classList.add('loading');
        loginBtn.disabled = true;
    });

    // Focus automatique sur l'email
    document.getElementById('email').focus();
</script>
</body>
</html>
