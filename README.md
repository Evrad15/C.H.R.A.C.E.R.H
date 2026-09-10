# Système Intégré de Gestion des Présences et d'Accueil Biométrique - CHRACERH 🚀

> **Application Web pour le Suivi d'Assiduité et l'Accueil Biométrique par Reconnaissance Faciale & Empreinte Digitale des Employés et Patients**

---

## 📌 Présentation du Projet

Le **Système Intégré de Gestion des Présences et d'Accueil Biométrique** est une solution conçue spécifiquement pour les besoins du **C.H.R.A.C.E.R.H** (*Centre Hospitalier de Recherche et d'Application en Chirurgie Endoscopique et Reproduction Humaine*). Ce système simplifie, sécurise et automatise le suivi d'assiduité du personnel hospitalier tout en fluidifiant l'accueil et l'orientation des patients lors de leur arrivée en consultation.

> [!IMPORTANT]
> **Focus 100% Web (`crhacerh-web`) :** L'application est développée sur une architecture web moderne basée sur **Laravel 11, Breeze, Blade & Vanilla CSS** avec une base de données **PostgreSQL 15**. L'ensemble des fonctionnalités (gestion des présences, borne Kiosk biométrique, suivi des rendez-vous et rapports mensuels) est centralisé sur cette application web.

La solution s'articule autour des deux composants actifs suivants :
1. **L'interface Web d'administration RH (Laravel 11 & Blade) :** Permet aux équipes du C.H.R.A.C.E.R.H de gérer les fiches des employés, de planifier et de suivre les rendez-vous médicaux des patients, de valider les demandes de congés/permissions et d'exporter des rapports d'assiduité réguliers (Excel & PDF).
2. **La Borne de Pointage Publique Interactive Kiosk (`/kiosk`) :** Une interface Web publique biométrique accessible depuis un écran tactile ou une tablette à l'entrée de l'établissement. Elle permet le pointage en temps réel par **Reconnaissance Faciale**, **Empreinte Digitale** ou via un **QR code dynamique**, et sert également de point d'accueil pour le pointage des patients ayant un rendez-vous planifié.

---

## ⚙️ Fonctionnalités Majeures (Composant Web)

### 💻 Portail Web & Administration RH (Blade & Laravel Breeze)
* **Tableau de Bord RH (Dashboard) :** Indicateurs visuels dynamiques sur l'état des présences quotidiennes (présents, absents, retards) et graphiques statistiques par département.
* **Sécurité Conforme OWASP Top 10 :** Authentification robuste via Laravel Breeze, chiffrement des mots de passe par Bcrypt, protection contre le *Brute-Force* (Rate Limiting à 5 essais/min), jetons CSRF et requêtes préparées anti-injection SQL.
* **Gestion des Congés & Permissions :** Flux de validation des demandes d'absences neutralisant automatiquement les périodes de congés validées pour éviter de marquer injustement un agent absent.
* **Gestion des Rendez-vous Patients :** Module complet de planification, d'assignation des médecins spécialistes et de suivi du statut des consultations.
* **Génération de Rapports d'Assiduité :** 
  * Exportation de rapports d'assiduité détaillés au format Excel (`.xlsx`) via **PhpSpreadsheet** et PDF via **Dompdf**.
  * Espace de stockage structuré classant automatiquement les rapports dans des sous-dossiers mensuels.

### 🏢 Borne Interactive Kiosk (`/kiosk`)
* **Pointage Bimodal Biométrique :** Enregistrement et vérification instantanée des employés et patients par **Reconnaissance Faciale** ou **Empreinte Digitale** (temps de réponse < 300 ms).
* **Accueil des Patients :** Pointage d'arrivée autonome pour les consultations du jour, mettant à jour en temps réel le statut du patient en "Présent" dans l'administration.
* **Pointage par QR Code Dynamique :** Affichage d'un code QR temporaire mis à jour en continu pour sécuriser l'enregistrement.
* **Flux des Pointages Récents :** Confirmation visuelle en temps réel avec horodatage de la prise en compte du pointage.

---

## 📁 Structure du Projet

```text
Système Intégré de Gestion des Présences.../
├── README.md                           <- Ce fichier (vue d'ensemble)
├── dictionnaire_de_donnees.html         <- Modèle relationnel et dictionnaire des tables SQL
├── images_rapport_stage/                <- Diagrammes UML (Cas d'utilisation, Composants, Paquetages, Architecture)
│
├── crhacerh-web/                       <- [COMPOSANT ACTIF] Application Web Laravel 11
│   ├── app/                            <- Contrôleurs API & Web, Modèles Eloquent, Middleware et ReportService
│   ├── config/                         <- Configuration applicative (auth, database, etc.)
│   ├── database/                       <- Migrations PostgreSQL et Seeders de démonstration
│   ├── public/                         <- Actifs publics, favicon, logo CHRACERH, scripts Kiosk
│   ├── resources/                      <- Vues Blade (auth/login.blade.php, layouts/dashboard.blade.php, kiosk.blade.php)
│   └── routes/                         <- Définitions des routes Web (`web.php`) & API (`api.php`)
│
└── crhacerh-mobile/                    <- [ARCHIVÉ] Client mobile Flutter (exclu du flux d'exploitation)
```

---

## ⚙️ Installation et Démarrage rapide

### 1. Prérequis techniques
* **PHP :** Version `>= 8.2` avec extensions `pdo_pgsql`, `gd`, `zip`, `xml`, `mbstring`.
* **SGBD :** PostgreSQL version `>= 15`.
* **Gestionnaires :** Composer `>= 2.6` & Node.js `>= 18` (npm).
* **Python :** Version `3.10` à `3.12` avec `pip` (pour le moteur biométrique InsightFace ArcFace).

---

### 2. Procédure d'installation pas à pas

1. Accédez au répertoire de l'application Web :
   ```bash
   cd crhacerh-web
   ```

2. Installez les dépendances PHP et Node.js :
   ```bash
   composer install
   npm install
   npm run build
   ```

3. Installez l'environnement et les dépendances Python (Biométrie InsightFace) :
   ```bash
   # Création et activation du venv (optionnel mais recommandé)
   python -m venv venv
   # Windows PowerShell :
   .\venv\Scripts\Activate.ps1
   # Linux/macOS :
   source venv/bin/activate

   # Mise à jour des outils de build et installation des bibliothèques IA
   pip install --upgrade pip setuptools wheel
   pip install -r requirements.txt
   ```

4. Téléchargez les modèles d'IA pré-entraînés (InsightFace `buffalo_l` 512D) :
   ```bash
   python -c "import insightface; app = insightface.app.FaceAnalysis(name='buffalo_l', root='models/insightface'); app.prepare(ctx_id=0, det_size=(320, 320)); print('Modèles buffalo_l opérationnels !')"
   ```

5. Créez la base de données PostgreSQL :
   ```sql
   CREATE DATABASE chracerh_db;
   ```

6. Configurez le fichier d'environnement :
   ```bash
   cp .env.example .env
   ```
   Renseignez la configuration de la base de données dans le fichier `.env` :
   ```ini
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=chracerh_db
   DB_USERNAME=postgres
   DB_PASSWORD=votre_mot_de_passe

   # Chemin absolu de Python si non présent dans le PATH global (optionnel)
   PYTHON_BINARY="C:\chemin\vers\python.exe"
   ```

7. Générez la clé d'application et exécutez les migrations :
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   ```

8. Lancez les serveurs :
   ```bash
   # 1. Démon Biométrique IA (Port 5001)
   python scripts/face_daemon.py

   # 2. Serveur Web Laravel (Port 8000)
   php artisan serve --port=8000

   # 3. Serveur Assets Vite (Port 5173 - Hot Module Replacement)
   npm run dev
   ```

---

### 🌐 Liens d'accès aux interfaces :

* **Formulaire de Connexion RH :** `http://localhost:8000/login`
* **Tableau de Bord Administration RH :** `http://localhost:8000/dashboard`
* **Borne Interactive Kiosk :** `http://localhost:8000/kiosk`

---

## 📖 Documentation & Diagrammes UML

* **Dictionnaire de Données SQL :** [dictionnaire_de_donnees.html](file:///c:/Users/SOLUTION%20TECH/Documents/Syst%C3%A8me%20Int%C3%A9gr%C3%A9%20de%20Gestion%20des%20Pr%C3%A9sences%20et%20d%27Accueil%20Biom%C3%A9trique%20-%20CHRACERH/dictionnaire_de_donnees.html)
* **Diagrammes d'Architecture & UML :**
  * **Cas d'Utilisation :** [`images_rapport_stage/1_1_diagramme_de_cas_d_utilisation_use_case_diagram.png`](file:///c:/Users/SOLUTION%20TECH/Documents/Syst%C3%A8me%20Int%C3%A9gr%C3%A9%20de%20Gestion%20des%20Pr%C3%A9sences%20et%20d%27Accueil%20Biom%C3%A9trique%20-%20CHRACERH/images_rapport_stage/1_1_diagramme_de_cas_d_utilisation_use_case_diagram.png)
  * **Composants System :** [`images_rapport_stage/8_7_diagramme_de_composants_component_diagram.png`](file:///c:/Users/SOLUTION%20TECH/Documents/Syst%C3%A8me%20Int%C3%A9gr%C3%A9%20de%20Gestion%20des%20Pr%C3%A9sences%20et%20d%27Accueil%20Biom%C3%A9trique%20-%20CHRACERH/images_rapport_stage/8_7_diagramme_de_composants_component_diagram.png)
  * **Architecture Applicative :** [`images_rapport_stage/17_16_architecture_de_l_application.png`](file:///c:/Users/SOLUTION%20TECH/Documents/Syst%C3%A8me%20Int%C3%A9gr%C3%A9%20de%20Gestion%20des%20Pr%C3%A9sences%20et%20d%27Accueil%20Biom%C3%A9trique%20-%20CHRACERH/images_rapport_stage/17_16_architecture_de_l_application.png)
