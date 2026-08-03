<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'C.H.R.A.C.E.R.H') }} — Connexion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Outfit', system-ui, sans-serif;
            background: #F6F6FB;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1F384C;
        }

        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07), 0 1px 4px rgba(0,0,0,0.04);
            padding: 2.5rem 2.25rem;
            width: 100%;
            max-width: 420px;
        }

        /* En-tête */
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 52px; height: 52px;
            background: #5A6ACF;
            border-radius: 14px;
            margin-bottom: 1rem;
            color: #fff;
            font-size: 1.5rem;
        }

        .login-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1F384C;
            margin-bottom: 0.2rem;
        }

        .login-subtitle {
            font-size: 0.83rem;
            color: #737B8B;
        }

        /* Alerte statut de session */
        .session-status {
            background: #F0FDF4;
            border: 1px solid #BBF7D0;
            color: #15803D;
            border-radius: 8px;
            padding: 0.65rem 0.875rem;
            font-size: 0.83rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Champs */
        .form-group { margin-bottom: 1.1rem; }

        .form-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #1F384C;
            margin-bottom: 0.4rem;
        }

        .form-input {
            width: 100%;
            height: 44px;
            border: 1.5px solid #E2E7E7;
            border-radius: 10px;
            padding: 0 0.875rem;
            font-size: 0.875rem;
            color: #1F384C;
            background: #F6F6FB;
            outline: none;
            font-family: 'Outfit', sans-serif;
            transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
        }

        .form-input:focus {
            border-color: #5A6ACF;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(90,106,207,0.12);
        }

        .form-input[aria-invalid="true"],
        .form-input.is-error {
            border-color: #F2383A;
            box-shadow: 0 0 0 3px rgba(242,56,58,0.1);
        }

        /* Wrapper mot de passe */
        .input-password-wrap {
            position: relative;
        }

        .input-password-wrap .form-input {
            padding-right: 2.75rem;
        }

        .btn-show-password {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #737B8B;
            font-size: 1rem;
            padding: 0.15rem;
            line-height: 1;
            transition: color 0.15s;
        }
        .btn-show-password:hover { color: #5A6ACF; }

        /* Messages d'erreur */
        .field-error {
            font-size: 0.78rem;
            color: #F2383A;
            margin-top: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Options */
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            font-size: 0.82rem;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            color: #737B8B;
            cursor: pointer;
            user-select: none;
        }

        .remember-label input[type="checkbox"] {
            width: 15px; height: 15px;
            border-radius: 4px;
            accent-color: #5A6ACF;
            cursor: pointer;
        }

        .forgot-link {
            color: #5A6ACF;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.82rem;
        }
        .forgot-link:hover { text-decoration: underline; }

        /* Bouton */
        .btn-submit {
            width: 100%;
            height: 46px;
            background: #5A6ACF;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: 'Outfit', sans-serif;
            letter-spacing: 0.02em;
            transition: background 0.18s, transform 0.12s;
        }

        .btn-submit:hover  { background: #4A5ABF; }
        .btn-submit:active { transform: scale(0.98); }

        .btn-submit:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        /* Pied de carte */
        .login-footer {
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid #E2E7E7;
            text-align: center;
            font-size: 0.75rem;
            color: #B0B7C3;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }

        .login-footer i { color: #16DE42; }
    </style>
</head>
<body>
    <div class="login-card" role="main">

        <!-- En-tête -->
        <div class="login-header">
            <div class="login-logo" aria-hidden="true">
                <i class="bi bi-hospital"></i>
            </div>
            <h1 class="login-title">C.H.R.A.C.E.R.H</h1>
            <p class="login-subtitle">Système de Gestion des Présences — Espace RH</p>
        </div>

        <!-- Statut de session (ex: déconnexion réussie) -->
        @if (session('status'))
            <div class="session-status" role="status">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('status') }}
            </div>
        @endif

        <!-- Formulaire -->
        <form method="POST" action="{{ route('login') }}" autocomplete="on" novalidate>
            @csrf

            <!-- Email -->
            <div class="form-group">
                <label class="form-label" for="email">Adresse e-mail</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    class="form-input {{ $errors->has('email') ? 'is-error' : '' }}"
                    value="{{ old('email') }}"
                    autocomplete="username"
                    autofocus
                    required
                    aria-describedby="{{ $errors->has('email') ? 'email-error' : '' }}"
                    aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    placeholder="votre.email@chracerh.cm"
                >
                @error('email')
                    <p class="field-error" id="email-error" role="alert">
                        <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Mot de passe -->
            <div class="form-group">
                <label class="form-label" for="password">Mot de passe</label>
                <div class="input-password-wrap">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-input {{ $errors->has('password') ? 'is-error' : '' }}"
                        autocomplete="current-password"
                        required
                        aria-describedby="{{ $errors->has('password') ? 'password-error' : '' }}"
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                        placeholder="••••••••"
                    >
                    <button
                        type="button"
                        class="btn-show-password"
                        id="togglePassword"
                        aria-label="Afficher le mot de passe"
                        aria-pressed="false"
                    >
                        <i class="bi bi-eye" id="togglePasswordIcon" aria-hidden="true"></i>
                    </button>
                </div>
                @error('password')
                    <p class="field-error" id="password-error" role="alert">
                        <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Se souvenir / Mot de passe oublié -->
            <div class="form-options">
                <label class="remember-label">
                    <input
                        type="checkbox"
                        name="remember"
                        id="remember_me"
                        {{ old('remember') ? 'checked' : '' }}
                    >
                    Se souvenir de moi
                </label>

                @if (Route::has('password.request'))
                    <a class="forgot-link" href="{{ route('password.request') }}">
                        Mot de passe oublié ?
                    </a>
                @endif
            </div>

            <!-- Soumettre -->
            <button type="submit" class="btn-submit" id="submitBtn">
                <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                Se connecter
            </button>
        </form>

        <!-- Pied de page -->
        <div class="login-footer">
            <i class="bi bi-shield-lock-fill"></i>
            Connexion sécurisée — CHRACERH &copy; {{ date('Y') }}
        </div>

    </div>

    <script>
        // ── Toggle affichage mot de passe ──
        (function () {
            const btn   = document.getElementById('togglePassword');
            const input = document.getElementById('password');
            const icon  = document.getElementById('togglePasswordIcon');

            if (!btn) return;

            btn.addEventListener('click', function () {
                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
                btn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                btn.setAttribute('aria-label',   isHidden ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
            });
        }());

        // ── Désactiver le bouton pendant la soumission (anti double-clic) ──
        (function () {
            const form = document.querySelector('form');
            const btn  = document.getElementById('submitBtn');

            if (!form || !btn) return;

            form.addEventListener('submit', function () {
                // Vérification HTML5 avant désactivation
                if (form.checkValidity()) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="bi bi-hourglass-split" aria-hidden="true"></i> Connexion…';
                }
            });
        }());
    </script>
</body>
</html>
