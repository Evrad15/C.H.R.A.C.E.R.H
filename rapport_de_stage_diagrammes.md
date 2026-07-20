# Recueil des Modèles et Diagrammes pour le Rapport de Stage - CHRACERH

## Projet : Système Intégré de Gestion des Présences et d'Accueil Biométrique - CHRACERH 🚀

Ce document contient l'ensemble des diagrammes et modèles d'analyse, de conception et d'architecture pour votre rapport de stage, mis à jour pour refléter une architecture **100% Web et Borne Kiosk, axée sur les empreintes digitales et QR codes** (sans intelligence artificielle ni reconnaissance faciale Gemini).

---

## I. Analyse des besoins et analyse métier

### 1. Diagramme de cas d'utilisation (Use Case Diagram)
Représente les acteurs du système (Employé, Patient, Administrateur RH, Manager) et leurs interactions avec les composants logiciels.

```mermaid
graph LR
    Employe["Employé (Acteur)"]
    Patient["Patient (Acteur)"]
    RH_Admin["Administrateur RH (Acteur)"]
    Manager["Manager de Département (Acteur)"]

    subgraph "Système Web & Borne Kiosk"
        UC_Auth(["S'authentifier sur le Portail Web"])
        UC_Consult_Perso(["Consulter ses statistiques de présence"])
        UC_Ask_Leave(["Soumettre une demande de congé/permission"])
        UC_Chat_RH(["Discuter avec le bureau des RH (Livewire Chat)"])
        
        UC_Point_Biometric(["Effectuer son pointage (Kiosk - Empreinte/QR)"])
        UC_Patient_Checkin(["Valider son arrivée au rendez-vous (Kiosk)"])
        
        UC_Manage_Emp(["Gérer les employés (CRUD Filament)"])
        UC_Manage_Rdv(["Gérer les rendez-vous patients"])
        UC_Manage_Leaves(["Valider/Rejeter les demandes de congés"])
        UC_Export_Reports(["Générer et exporter les rapports mensuels (.xlsx)"])
    end

    %% Employé
    Employe --> UC_Auth
    Employe --> UC_Consult_Perso
    Employe --> UC_Ask_Leave
    Employe --> UC_Chat_RH
    Employe --> UC_Point_Biometric

    %% Patient
    Patient --> UC_Patient_Checkin

    %% RH Admin
    RH_Admin --> UC_Auth
    RH_Admin --> UC_Manage_Emp
    RH_Admin --> UC_Manage_Rdv
    RH_Admin --> UC_Manage_Leaves
    RH_Admin --> UC_Export_Reports
    RH_Admin --> UC_Chat_RH

    %% Manager
    Manager --> UC_Auth
    Manager --> UC_Chat_RH
```

---

### 2. Diagramme d'activité (Activity Diagram)
Représente le flux de pointage quotidien d'un employé sur la borne interactive Kiosk par empreinte digitale ou par scan de code QR.

```mermaid
stateDiagram-v2
    [*] --> ArriveDevantKiosk : Présentation de l'employé devant la borne
    
    state ModeSelection {
        [*] --> ChoixMethode
        ChoixMethode --> CapteurEmpreinte : Poser le doigt sur le lecteur
        ChoixMethode --> ScannerQRCode : Scanner le code QR dynamique
    }

    CapteurEmpreinte --> LireEmpreinteDigitale : Lecture du doigt
    LireEmpreinteDigitale --> ComparerTemplateLocal : Comparaison du template biométrique
    ComparerTemplateLocal --> CorrespondanceTrouvee : Empreinte validée
    ComparerTemplateLocal --> EchecVerification : Empreinte non reconnue

    ScannerQRCode --> LireQRCode : Capture du QR Code
    LireQRCode --> VerifierValiditeToken : Vérification du timestamp (< 10s)
    VerifierValiditeToken --> CorrespondanceTrouvee : Jeton valide
    VerifierValiditeToken --> EchecVerification : Jeton expiré ou incorrect

    EchecVerification --> NotificationRejet : Affiche "Erreur de validation"
    NotificationRejet --> ArriveDevantKiosk

    CorrespondanceTrouvee --> DeterminerStatut : Identification réussie
    
    state DeterminerStatut {
        [*] --> ComparerHeureArrivee
        ComparerHeureArrivee --> AvantHeureLimite : Heure <= 08h15
        ComparerHeureArrivee --> ApresHeureLimite : Heure > 08h15
    }

    AvantHeureLimite --> EnregistrerPresent : Statut = "Présent"
    ApresHeureLimite --> EnregistrerRetard : Statut = "Retard"

    EnregistrerPresent --> EnregistrerDB : Envoi en base de données
    EnregistrerRetard --> EnregistrerDB : Envoi en base de données
    
    EnregistrerDB --> AffichageSucces : Confirme le pointage sur l'écran du Kiosk
    AffichageSucces --> [*]
```

---

## II. Analyse détaillée

### 3. Diagramme de séquence (Sequence Diagram)
Montre le processus chronologique d'authentification biométrique par empreinte digitale sur la borne Kiosk.

```mermaid
sequenceDiagram
    autonumber
    actor Employe as Employé / Patient
    participant Kiosk as Borne Kiosk (Navigateur Web)
    participant API as Contrôleur (KioskController)
    participant DB as Base de données SQL

    Employe->>Kiosk: Pose son doigt sur le capteur biométrique
    Kiosk->>Kiosk: Capture le template biométrique de l'empreinte
    Kiosk->>API: POST /api/kiosk/biometric-verify { category: "EMPLOYE", id: ID }
    activate API

    API->>DB: Requête d'authentification (Vérification de l'existence du profil)
    activate DB
    DB-->>API: Profil employé et template de référence
    deactivate DB

    API->>API: Compare le template reçu avec le template enregistré
    
    alt Correspondance positive
        API->>DB: Enregistre le pointage (type: ENTREE/SORTIE, status: Present/Retard, source: EMPREINTE)
        activate DB
        DB-->>API: Confirmation SQL
        deactivate DB
        API-->>Kiosk: HTTP 200 { status: success, name: "Jean Dupont", time: "07:58" }
        Kiosk->>Kiosk: Actualise l'écran avec message de bienvenue et met à jour les pointages récents
    else Correspondance non concluante
        API-->>Kiosk: HTTP 401 { status: error, message: "Empreinte digitale non reconnue" }
        Kiosk->>Kiosk: Affiche un message d'erreur et demande de réessayer
    end
    deactivate API
    Kiosk-->>Employe: Affiche le résultat visuel
```

---

### 4. Diagramme de collaboration/communication
Illustre l'échange de messages structurels entre les composants lors d'une vérification d'empreinte digitale sur le Kiosk.

```mermaid
graph TD
    Employe["1. Employé"]
    Kiosk["2. Borne Kiosk Web"]
    API["3. FaceApiController / KioskController"]
    DB["4. Base de Données SQL"]

    Employe -->|1. Pose le doigt| Kiosk
    Kiosk -->|2. Transmet category id fingerprint| API
    API -->|3. Vérifie la concordance et enregistre| DB
    DB -.->|4. Confirme l'écriture| API
    API -.->|5. Renvoie le statut de pointage| Kiosk
```

---

### 5. Diagramme d'état-transition (State Machine Diagram)

#### État de l'Employé au sein de l'hôpital
```mermaid
stateDiagram-v2
    [*] --> Recrute : Ajout de la fiche par les RH
    Recrute --> Actif : Enregistrement de l'empreinte digitale (consentement biométrique)
    Actif --> EnConge : Demande de congé acceptée
    EnConge --> Actif : Fin du congé (réactivation automatique)
    Actif --> Suspendu : Mesure disciplinaire
    Suspendu --> Actif : Réintégration
    Actif --> Archive : Archivage / Soft Delete
    Archive --> [*]
```

#### État d'un Pointage
```mermaid
stateDiagram-v2
    [*] --> AttenteVerification : Capture de l'empreinte ou scan QR effectué
    AttenteVerification --> Rejete : Jeton invalide ou empreinte non reconnue
    Rejete --> [*]
    AttenteVerification --> Valide : Authentifié positivement
    
    state Valide {
        [*] --> CalculHeure
        CalculHeure --> Present : Heure <= 08h15
        CalculHeure --> Retard : Heure > 08h15
    }

    Present --> Enregistre : Écrit dans la DB
    Retard --> Enregistre : Écrit dans la DB
    Enregistre --> [*]
```

---

### 6. Diagramme d'activité de validation des congés (Workflow Diagram)
Représente le flux de traitement administratif d'une demande de congé ou d'autorisation d'absence par le service RH.

```mermaid
stateDiagram-v2
    [*] --> SoumissionDemande : Employé soumet une demande (avec motif & dates)
    SoumissionDemande --> NotificationRH : Alerte dans le panel d'administration Filament
    
    state EvaluerDemande {
        [*] --> ExamenMotif
        ExamenMotif --> VerifierSolde : Analyse de la validité et des pièces jointes
        VerifierSolde --> SoldeSuffisant : Droits ouverts
        VerifierSolde --> SoldeInsuffisant : Congés non imputables
    }

    SoldeInsuffisant --> RejetDemande : Statut = "Rejetée"
    SoldeSuffisant --> ApprobationDemande : Statut = "Approuvée"
    
    RejetDemande --> AjouterCommentaire : Écrit la raison du rejet
    ApprobationDemande --> EnregistrerCalendrier : Ajoute au calendrier d'équipe (Google Calendar)
    
    AjouterCommentaire --> EnregistrerDB : Mise à jour SQL
    EnregistrerCalendrier --> EnregistrerDB : Mise à jour SQL
    
    EnregistrerDB --> EnvoyerNotification : Notification par chat de la réponse
    EnvoyerNotification --> [*]
```

---

## III. Conception architecturale

### 7. Diagramme de composants (Component Diagram)
Montre l'organisation modulaire de l'application et les points d'intégration avec les APIs tierces.

```mermaid
graph TD
    subgraph KioskTerminal [Borne de Pointage Kiosk - Navigateur Web]
        KioskUI["Interface Kiosk JS"]
        FingerprintSDK["Pilote de Capteur d'Empreinte (JS WebUSB)"]
        KioskUI --> FingerprintSDK
    end

    subgraph ApplicationWeb [Portail d'Administration & Employé - Laravel / Filament]
        AdminPanel["Console Administration RH"]
        EmployeePortal["Espace Portail Employés"]
        LivewireMessenger["Composant Livewire Chat"]
        LivewireMessenger --> AdminPanel
        LivewireMessenger --> EmployeePortal
    end

    subgraph ServiceServeur [Serveur d'Application - Laravel Kernel]
        Routes["Gestionnaire de Routes (routes/api.php & web.php)"]
        Controllers["Contrôleurs (KioskController, PointageController)"]
        Models["Modèles Eloquent (Employe, Pointage, RendezVous)"]
        ReportEngine["ReportService (PhpSpreadsheet)"]

        KioskUI -->|Requêtes REST JSON| Routes
        AdminPanel --> Routes
        EmployeePortal --> Routes
        Routes --> Controllers
        Controllers --> Models
        AdminPanel --> ReportEngine
    end

    subgraph BaseDonnees [Base de Données]
        SQLDatabase[("Base SQL (MySQL/Postgres)")]
        Models --> SQLDatabase
        ReportEngine --> SQLDatabase
    end

    subgraph APIExternes [APIs Tierces]
        GCalendarAPI["API Google Calendar"]
        Controllers --> GCalendarAPI
    end
```

---

### 8. Diagramme de déploiement (Deployment Diagram)
Décrit l'architecture physique du système (tablette Kiosk, serveur Web et APIs Cloud).

```mermaid
graph TD
    subgraph Borne ["Borne Kiosk - Tablette Murale / PC Accueil"]
        Navigateur["Navigateur Web (Chrome/Edge)"]
        FingerprintHardware["Lecteur d'Empreinte USB"]
        Navigateur --> FingerprintHardware
    end

    subgraph PC_RH ["Poste Ressources Humaines - Ordinateur de Bureau"]
        NavigateurRH["Navigateur Web (Filament Admin Panel)"]
    end

    subgraph WebServer ["Serveur Web - VPS Hospitalier (Linux / XAMPP)"]
        WebServerEngine["Serveur Web (Apache / Nginx)"]
        PHP_Runtime["PHP 8.2 / 8.3 Runtime"]
        LaravelApp["Code Source Application (Laravel 11.2)"]
        
        WebServerEngine --> PHP_Runtime
        PHP_Runtime --> LaravelApp
    end

    subgraph DB_Server ["Serveur Base de Données SQL"]
        SQL_Engine["SGBD (MySQL / PostgreSQL)"]
        SQL_Data[(Base : crhacerh_db)]
        SQL_Engine --> SQL_Data
    end

    subgraph GoogleCalendarCloud ["Google Workspace Cloud"]
        CalendarService["API Google Calendar"]
    end

    Navigateur -->|HTTP/HTTPS Port 443 / API REST| WebServerEngine
    NavigateurRH -->|HTTP/HTTPS Port 443 / Filament views| WebServerEngine
    LaravelApp -->|Connexion PDO Port 3306 / 5432| SQL_Engine
    LaravelApp -->|OAuth 2.0 HTTPS Sync| CalendarService
```

---

### 9. Diagramme de paquetages (Package Diagram)
Montre le découpage logique des modules de l'application Laravel/Filament.

```mermaid
graph TD
    subgraph "App (Couche Métier)"
        Http[Http/Controllers] --> Api[Api/AttendanceController]
        Http --> Livewire[Livewire/Chat]
        Models[Models/Eloquent]
        Services[Services/ReportService]
        Providers[Providers/Filament]
    end

    subgraph "Database (Couche Persistance)"
        Migrations[Migrations & Seeders]
    end

    subgraph "Resources (Couche Présentation)"
        Views[Views/Blade/Livewire]
        PublicAssets[Public/KioskAssets]
    end

    Api --> Models
    Livewire --> Models
    Services --> Models
    Migrations --> Models
    Views --> Http
```

---

## IV. Conception détaillée

### 10. Diagramme de classes détaillé (Class Diagram)
Contient la structure des données avec leurs attributs, leurs types et les relations d'association.

```mermaid
classDiagram
    class Employe {
        +int id
        +string matricule
        +string email
        +string password
        +string first_name
        +string last_name
        +string role
        +int department_id
        +string phone
        +text fingerprint_data
        +datetime fingerprint_registered_at
        +canAccessPanel(panel) bool
        +getFullNameAttribute() string
    }

    class Department {
        +int id
        +string name
        +employees() HasMany
    }

    class Pointage {
        +int id
        +int employee_id
        +string type
        +string status
        +string source
        +datetime timestamp
        +float confidence_score
        +boolean anomaly_detected
    }

    class RendezVous {
        +int id
        +string nom_patient
        +string telephone_patient
        +string email_patient
        +string service
        +string motif
        +datetime date_rendez_vous
        +string medecin
        +string statut
        +text fingerprint_data
        +datetime fingerprint_registered_at
        +datetime pointage_at
    }

    class Conge {
        +int id
        +int employe_id
        +string type
        +datetime date_debut
        +datetime date_fin
        +text motif
        +string statut
        +text commentaire_rh
    }

    class Rapport {
        +int id
        +string titre
        +text contenu
        +date date_rapport
        +int employe_id
    }

    class MlPrediction {
        +int id
        +int employee_id
        +string prediction_type
        +float risk_score
        +text details
    }

    Department "1" --> "0..*" Employe : contient
    Employe "1" --> "0..*" Pointage : effectue
    Employe "1" --> "0..*" Conge : demande
    Employe "1" --> "0..*" Rapport : genere
    Employe "1" --> "0..*" MlPrediction : fait l'objet de
```

---

### 11. Diagramme d'objets (Object Diagram)
Instance concrète représentant le pointage biométrique d'un employé et l'arrivée d'un patient à un instant T.

```mermaid
graph TD
    subgraph "Objets Instanciés - 20/07/2026 à 08h05"
        dept1["deptGyn: Department | id = 2 | name = 'Gynécologie'"]
        emp1["empMarc: Employe | id = 45 | matricule = 'chr-45' | first_name = 'Marc' | last_name = 'Kassa' | role = 'Employés'"]
        ptg1["ptgMarc: Pointage | id = 1205 | employee_id = 45 | type = 'ENTREE' | status = 'Present' | source = 'EMPREINTE' | timestamp = '2026-07-20 07:58:12'"]
        
        rdv1["rdvPatient: RendezVous | id = 89 | nom_patient = 'Amina Diop' | service = 'FIV' | statut = 'Présent' | pointage_at = '2026-07-20 08:04:15'"]
    end

    emp1 -->|appartient| dept1
    ptg1 -->|pointage de| emp1
```

---

## V. Base de données et Interface

### 12. Dictionnaire de données

| Nom du champ | Description | Type | Contraintes |
| :--- | :--- | :--- | :--- |
| **employés.id** | Identifiant de l'employé | INT | Primary Key, Auto-incrément |
| **employés.matricule** | Matricule unique de l'employé | VARCHAR(50) | Unique, Obligatoire |
| **employés.email** | Email professionnel de l'employé | VARCHAR(150) | Unique, Obligatoire |
| **employés.password** | Mot de passe sécurisé | VARCHAR(255) | Haché en base |
| **employés.first_name** | Prénom de l'employé | VARCHAR(100) | Obligatoire |
| **employés.last_name** | Nom de famille de l'employé | VARCHAR(100) | Obligatoire |
| **employés.role** | Rôle d'accès | VARCHAR(50) | `RH_Admin`, `Manager`, `Employés` |
| **employés.fingerprint_data**| Template de l'empreinte digitale | TEXT | Optionnel |
| **employés.fingerprint_registered_at**| Date d'enregistrement d'empreinte | TIMESTAMP | Optionnel |
| **pointages.id** | Identifiant unique du pointage | INT | Primary Key |
| **pointages.employee_id** | Clé étrangère vers l'employé | INT | Foreign Key, Cascade |
| **pointages.type** | Type de pointage | VARCHAR(20) | `ENTREE`, `SORTIE` |
| **pointages.status** | Statut calculé | VARCHAR(20) | `Present`, `Retard`, `Absent` |
| **pointages.source** | Source d'enregistrement | VARCHAR(20) | `EMPREINTE`, `QR` |
| **rendez_vous.id** | Identifiant unique du rendez-vous | INT | Primary Key |
| **rendez_vous.nom_patient** | Nom et prénom du patient | VARCHAR(150) | Obligatoire |
| **rendez_vous.statut** | Statut du rendez-vous | VARCHAR(50) | `Planifié`, `Présent`, `Réalisé`, etc. |
| **conges.id** | Identifiant unique de la demande | INT | Primary Key |
| **conges.statut** | Statut de validation | VARCHAR(50) | `En attente`, `Approuvée`, `Rejetée` |

---

### 13. Schéma relationnel (MCL)
*   **departments** (<u>id</u>, name, created_at, updated_at)
*   **employés** (<u>id</u>, matricule, email, password, first_name, last_name, role, phone, fingerprint_data, fingerprint_registered_at, created_at, updated_at, deleted_at, *department_id#*)
*   **pointages** (<u>id</u>, type, status, source, timestamp, confidence_score, anomaly_detected, created_at, updated_at, *employee_id#*)
*   **rendez_vous** (<u>id</u>, nom_patient, telephone_patient, email_patient, service, motif, date_rendez_vous, medecin, statut, fingerprint_data, fingerprint_registered_at, pointage_at, created_at, updated_at)
*   **conges** (<u>id</u>, type, date_debut, date_fin, motif, statut, commentaire_rh, created_at, updated_at, *employe_id#*)
*   **rapports** (<u>id</u>, titre, contenu, date_rapport, created_at, updated_at, *employe_id#*)
*   **ml_predictions** (<u>id</u>, prediction_type, risk_score, details, created_at, *employee_id#*)

---

### 14. Schéma physique (ERD)

```mermaid
erDiagram
    departments {
        int id PK
        varchar name UK
    }
    employees {
        int id PK
        varchar matricule UK
        varchar email UK
        varchar password
        varchar first_name
        varchar last_name
        varchar role
        int department_id FK
        text fingerprint_data
        timestamp fingerprint_registered_at
    }
    pointages {
        int id PK
        int employee_id FK
        varchar type
        varchar status
        varchar source
        timestamp timestamp
        double_precision confidence_score
        boolean anomaly_detected
    }
    rendez_vous {
        int id PK
        varchar nom_patient
        varchar statut
        text fingerprint_data
        timestamp pointage_at
    }
    conges {
        int id PK
        int employe_id FK
        varchar type
        timestamp date_debut
        timestamp date_fin
        varchar statut
    }

    departments ||--o{ employees : "contient"
    employees ||--o{ pointages : "enregistre"
    employees ||--o{ conges : "soumet"
    employees ||--o{ rendez_vous : "recoit (optionnel)"
```

---

### 15. Diagrammes d'interface utilisateur (Maquettes/wireframes)

#### Borne Interactive Kiosk (Écran Public d'accueil)
```text
+-------------------------------------------------------------------------------+
| [Logo CHRACERH]              BORNE DE POINTAGE             20/07/2026 12:00   |
+-------------------------------------------------------------------------------+
|  [ Onglet 1: Employés ]   |   [ Onglet 2: Patients ]   |  [ Onglet 3: Biométrie ]|
+---------------------------+----------------------------+----------------------+
|                           |  +----------------------+  |  +-----------------+ |
|  Posez votre doigt sur    |  | Rendez-vous du Jour  |  |  | Enregistrement  | |
|  le capteur biométrique   |  |                      |  |  | d'Empreinte     | |
|  pour enregistrer votre   |  | - 08:30 Amina Diop   |  |  |                 | |
|  pointage d'arrivée       |  | - 09:15 Paul Kassa   |  |  | [Formulaire]    | |
|                           |  |                      |  |  |                 | |
|  [Lecteur d'Empreinte]    |  +----------------------+  |  +-----------------+ |
|                           |                            |                      |
|  Pointages Récents :      |  Arrivés récents :         |                      |
|  - Marc Kassa (07:58)     |  - Amina Diop (08:04)      |                      |
+-------------------------------------------------------------------------------+
```

#### Portail RH Admin (Filament Dashboard)
```text
+-------------------------------------------------------------------------------+
|  Menu: (x) Tableau de bord  |  ( ) Employés  |  ( ) Rendez-vous  | ( ) Congés |
+-------------------------------------------------------------------------------+
|  +--------------------+  +--------------------+  +-------------------------+  |
|  | Présents Jour : 42  |  | Retards Jour : 3   |  | Absents Jour : 5        |  |
|  +--------------------+  +--------------------+  +-------------------------+  |
|                                                                               |
|  Graphique d'Assiduité de la Semaine (Presents, Retards, Absents)             |
|  [====================== Chart.js Line Chart ===========================]    |
|                                                                               |
|  Activités Récentes :                                                         |
|  - Marc Kassa (07:58 - Entrée par Empreinte Digitale)                         |
+-------------------------------------------------------------------------------+
```

---

### 16. Architecture MVC de l'Application Web

```mermaid
graph TD
    subgraph NavigateurWeb ["Navigateur Web (Borne Kiosk ou PC RH Admin)"]
        ViewHTML["Vues Blade / Composants Livewire (HTML/CSS)"]
    end

    subgraph LaravelBackend ["Serveur Application - Laravel Backend"]
        Routes["Gestionnaire de Routes (routes/api.php et web.php)"]
        Controller["KioskController / PointageController"]
        EloquentModel["Modèles Eloquent (Employe, Pointage, RendezVous)"]
    end

    subgraph Database ["Données"]
        SQLDatabase[(Base SQL - MySQL/PostgreSQL)]
    end

    ViewHTML -->|1. Actions Utilisateur / Pointage| Routes
    Routes --> Controller
    Controller --> EloquentModel
    EloquentModel -->|2. Persistance SQL| SQLDatabase
    SQLDatabase --> EloquentModel
    Controller -->|3. Retourne JSON ou Vue HTML| ViewHTML
```
