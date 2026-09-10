# C.H.R.A.C.E.R.H — Plateforme de Gestion RH Hospitalière

> **Centre Hospitalier — Ressources humAines, Contrôle d'accès, Empreinte, Reconnaissance faciale & Historique**

Plateforme web de gestion des ressources humaines et de pointage biométrique pour le secteur hospitalier, développée avec Laravel 11 + Vite + PostgreSQL.

---

## Stack Technique

| Couche | Technologie |
|---|---|
| Backend | Laravel 11 (PHP 8.2+) |
| Frontend | Vite + Vanilla JS |
| Base de données | PostgreSQL |
| Reconnaissance faciale | InsightFace ArcFace R100 — `buffalo_l` (512D, ONNX) |
| Runtime ML | Python 3.11+ · ONNX Runtime · OpenCV |
| Authentification | Laravel Sanctum (tokens API) |

---

## Fonctionnalités

### Borne Kiosque Biométrique
- **Pointage par reconnaissance faciale** — moteur ArcFace 512D, 100 % hors-ligne (intranet)
- **Détection d'ambiguïté** — signalement automatique si deux profils sont trop proches (delta cosinus < 0.04)
- **Vérification 1:N** — comparaison contre tous les embeddings en base en une seule passe
- **Pointage manuel sécurisé** — sélection par nom sur la borne, suivie d'une vraie vérification ArcFace (plus de score fictif)
- **Anti-doublon biométrique** — refus d'enregistrement si le visage correspond déjà à un autre employé (cosinus ≥ 0.55)
- **Normalisation CLAHE** — correction automatique des variations d'éclairage avant extraction

### Plateforme Web de Gestion
- Gestion des employés, départements, congés et rapports
- Tableau de bord pointages en temps réel
- Gestion des rendez-vous patients avec pointage biométrique
- Consultation de l'historique des pointages

---

## Prérequis

- PHP 8.2+
- PostgreSQL 14+
- Node.js 20+
- Python 3.11+
- Composer 2+

---

## Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/Evrad15/C.H.R.A.C.E.R.H.git
cd C.H.R.A.C.E.R.H/crhacerh-web
```

### 2. Dépendances PHP & JS

```bash
composer install
npm install
```

### 3. Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Éditer `.env` et configurer :
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=chracerh
DB_USERNAME=votre_user
DB_PASSWORD=votre_mdp
```

### 4. Base de données

```bash
php artisan migrate
php artisan db:seed   # optionnel — données de démonstration
```

### 5. Environnement & Dépendances Python (Moteur Biométrique InsightFace)

Le moteur de reconnaissance faciale repose sur **InsightFace (modèle ArcFace `buffalo_l` 512D)**, **ONNX Runtime** et **OpenCV**.

#### A. Création et activation de l'environnement virtuel (Recommandé)

Sur Windows (PowerShell) :
```powershell
python -m venv venv
.\venv\Scripts\Activate.ps1
```

Sur Linux / macOS (Bash) :
```bash
python3 -m venv venv
source venv/bin/activate
```

#### B. Mise à niveau des outils de packaging
```bash
pip install --upgrade pip setuptools wheel
```

#### C. Installation des dépendances via `requirements.txt`
```bash
pip install -r requirements.txt
```

> **Note GPU (Optionnelle) :** Si vous disposez d'une carte graphique NVIDIA avec CUDA et cuDNN configurés, remplacez `onnxruntime` par la version GPU pour accélérer les inférences :
> ```bash
> pip uninstall -y onnxruntime
> pip install onnxruntime-gpu
> ```

#### D. Configuration du chemin Python dans Laravel (Optionnel)
Si votre binaire Python n'est pas dans le `PATH` global, définissez son chemin absolu dans votre fichier `.env` :
```env
PYTHON_BINARY="C:\chemin\vers\votre\python.exe"
```

---

### 6. Téléchargement des Modèles Biométriques

Les modèles neuronaux ONNX ne sont pas versionnés dans Git en raison de leur taille (~250 Mo). Ils sont automatiquement téléchargés au premier démarrage ou manuellement :

```bash
python -c "import insightface; app = insightface.app.FaceAnalysis(name='buffalo_l', root='models/insightface'); app.prepare(ctx_id=0, det_size=(320, 320)); print('Modèles buffalo_l prêts !')"
```

Les modèles seront stockés dans le dossier local : `models/insightface/models/buffalo_l/`.

---

### 7. Lancer les Serveurs de l'Application

Pour un fonctionnement optimal de la borne et du portail RH, démarrez les 3 services :

```bash
# Terminal 1 : Démon Biométrique IA (Reconnaissance faciale ultra-rapide < 200 ms)
python scripts/face_daemon.py

# Terminal 2 : Serveur Web Laravel RH & Kiosk
php artisan serve --port=8000

# Terminal 3 : Serveur Frontend Vite (Compilation live du CSS / JS)
npm run dev
```

---

## Architecture Biométrique

```
Image (webcam borne)
        │
        ▼
  [RetinaFace det_10g.onnx]  ← détection & alignement du visage
        │
        ▼
  [ArcFace w600k_r50.onnx]   ← extraction embedding 512D normalisé
        │
        ▼
  Similarité cosinus 1:N      ← comparaison contre tous les embeddings DB
        │
   ┌────┴────┐
   │         │
cos ≥ 0.40  cos < 0.40
   │         │
MATCH     REJETÉ
   │
delta top1-top2 < 0.04 ?
   │
AMBIGU → sélection manuelle
```

### Seuils ArcFace

| Paramètre | Valeur | Rôle |
|---|---|---|
| `MATCH_THRESHOLD` | `0.40` | Cosinus minimum pour valider une identité |
| `AMBIGUITY_DELTA` | `0.04` | Delta max entre Top-1 et Top-2 avant alerte jumeaux |
| Anti-doublon enregistrement | `0.55` | Rejet si visage déjà associé à un autre employé |

---

## Structure des Scripts Python

```
scripts/
├── face_recognize.py      # Moteur principal ArcFace (extract / compare / compare_vectors)
├── download_models.py     # Téléchargement automatique buffalo_l
├── migrate_embeddings.php # Migration SFace 128D → ArcFace 512D
└── test_discrimination.py # Tests de discrimination inter-personnes
```

---

## API Endpoints Biométriques

| Méthode | Route | Description |
|---|---|---|
| `POST` | `/api/face/register/{id}` | Enregistre le visage d'un employé (ArcFace 512D) |
| `POST` | `/api/face/verify` | Pointage par reconnaissance faciale 1:N |
| `GET` | `/api/face/status/{id}` | Vérifie si un employé a un profil facial enregistré |

---

## Base de Données — Schéma Biométrique

### Table `employés`
| Colonne | Type | Description |
|---|---|---|
| `face_embeddings` | `bytea` | Vecteur ArcFace 512D (binaire, base64 en DB) |
| `consent_facial_at` | `timestamp` | Date du consentement RGPD |
| `face_embedding_updated_at` | `timestamp` | Dernière mise à jour de l'embedding |
| `fingerprint_data` | `text` | Données empreinte digitale |

### Table `pointages`
| Colonne | Type | Description |
|---|---|---|
| `source` | `varchar(20)` | `FACE`, `BIO`, `QR`, `PIN` |
| `confidence_score` | `decimal(8,6)` | Score cosinus ArcFace brut |
| `anomaly_detected` | `boolean` | Détection d'anomalie |

---

## Notes RGPD

- Les embeddings faciaux sont des **données biométriques sensibles** (Art. 9 RGPD)
- Le consentement est tracé via `consent_facial_at`
- La suppression d'un employé efface en cascade ses pointages (`onDelete cascade`)
- Le droit à l'oubli est implémenté via `softDeletes` sur la table `employés`

---

## Licence

Projet interne — usage hospitalier exclusif. Tous droits réservés.
