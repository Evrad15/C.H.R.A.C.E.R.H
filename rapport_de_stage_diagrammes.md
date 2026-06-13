# Recueil des Modèles et Diagrammes pour le Rapport de Stage
## Projet C.H.R.A.C.E.R.H 🚀

Ce document contient l'intégralité des diagrammes et modèles d'analyse, de conception et d'architecture demandés pour votre rapport de stage, structurés suivant vos 16 points clés. Ils sont rédigés en syntaxe **Mermaid** afin d'être rendus directement sous forme d'images PNG.

---

## I. Analyse des besoins et analyse métier

### 1. Diagramme de cas d'utilisation (Use Case Diagram)
Représente les acteurs du système (Employé, Administrateur RH, Manager) et leurs interactions avec les différentes fonctionnalités métier.

```mermaid
graph LR
    Employe["Employé (Acteur)"]
    RH_Admin["Administrateur RH (Acteur)"]
    Manager["Manager de Département (Acteur)"]

    subgraph "Cas d'Utilisation"
        UC_Auth(["S'authentifier - Matricule"])
        UC_Scan(["Scanner le QR Code dynamique"])
        UC_Consult_Perso(["Consulter son historique - Hors-ligne"])
        UC_Gen_Excel(["Générer rapport hebdomadaire - Excel"])
        UC_Chat(["Communiquer via messagerie interne"])
        
        UC_Manage_Emp(["Gérer les employés - CRUD"])
        UC_Manage_Dept(["Gérer les départements"])
        UC_Config_Kiosk(["Configurer la borne Kiosk - QR dynamique"])
        UC_Consult_Global(["Consulter les statistiques globales"])
        UC_Ask_Horizon(["Interroger l'assistant IA Horizon - Gemini"])

        UC_Consult_Dept(["Consulter les stats de son département"])
    end

    %% Employé
    Employe --> UC_Auth
    Employe --> UC_Scan
    Employe --> UC_Consult_Perso
    Employe --> UC_Gen_Excel
    Employe --> UC_Chat

    %% RH Admin
    RH_Admin --> UC_Auth
    RH_Admin --> UC_Manage_Emp
    RH_Admin --> UC_Manage_Dept
    RH_Admin --> UC_Config_Kiosk
    RH_Admin --> UC_Consult_Global
    RH_Admin --> UC_Ask_Horizon
    RH_Admin --> UC_Chat

    %% Manager
    Manager --> UC_Auth
    Manager --> UC_Consult_Dept
    Manager --> UC_Chat
```

---

### 2. Diagramme d'activité (Activity Diagram)
Représente le déroulement logique et séquentiel du flux de pointage quotidien par l'employé sans géolocalisation.

```mermaid
stateDiagram-v2
    [*] --> ArriveDevantBorne : Présentation de l'employé
    ArriveDevantBorne --> LectureQRCode : Ouvre l'app Flutter
    LectureQRCode --> ScanEffectue : Scanne le QR Code dynamique du Kiosk
    ScanEffectue --> EnvoiRequete : Transmet (Matricule + Signature QR)

    EnvoiRequete --> ReponseServeur : Traitement Backend API
    state ReponseServeur {
        [*] --> VerificationSignature : Vérifie validité du Token QR
        VerificationSignature --> SignatureValide : Token valide
        VerificationSignature --> SignatureInvalide : Expiré (> 10s)
    }

    SignatureInvalide --> NotificationErreurQR : Affiche "QR Code expiré"
    NotificationErreurQR --> [*]

    SignatureValide --> EnregistrementPointage : Écrit dans la base de données
    EnregistrementPointage --> DeterminationStatut : Compare l'heure d'arrivée
    
    state DeterminationStatut {
        [*] --> ComparaisonHeure
        ComparaisonHeure --> Avant8h : Heure < 08h00
        ComparaisonHeure --> Apres8h : Heure >= 08h00
    }

    Avant8h --> PointagePresent : Statut = "Présent"
    Apres8h --> PointageRetard : Statut = "En Retard"

    PointagePresent --> Confirmation : Renvoie code 200 OK
    PointageRetard --> Confirmation : Renvoie code 200 OK
    
    Confirmation --> [*] : Notification sur le mobile et Borne Kiosk actualisée
```

---

## II. Analyse détaillée

### 3. Diagramme de séquence (Sequence Diagram)
Montre chronologiquement les échanges détaillés de messages lors du processus de scan et d'enregistrement du pointage.

```mermaid
sequenceDiagram
    autonumber
    actor Employe as Employé
    participant Mobile as Application Flutter (Mobile)
    participant Kiosk as Borne Kiosk (Web/Livewire)
    participant API as Backend (Laravel API)
    participant DB as Base de données (Postgre/MySQL)

    Kiosk->>Kiosk: Génère un QR Code signé avec Timestamp
    Note over Kiosk: Le QR Code change toutes les 10 secondes (Anti-fraude)
    Employe->>Mobile: Ouvre l'application et clique sur "Scanner"
    Mobile->>Kiosk: Scanne le QR Code visible à l'écran
    Kiosk-->>Mobile: Retourne (Signature, Données d'empreinte temporelle)
    Mobile->>API: POST /api/pointages/scan {matricule, qr_data}
    activate API
    API->>API: Décode la signature et vérifie le timestamp (< 10s)
    
    alt Données valides
        API->>DB: Enregistre le pointage (employee_id, type: ENTREE, status)
        activate DB
        DB-->>API: Confirmation SQL
        deactivate DB
        API-->>Mobile: JSON {status: success, message: Pointage validé}
        API->>Kiosk: Diffuse l'événement de mise à jour (WebSockets/Livewire)
        Kiosk->>Kiosk: Actualise la liste des pointages récents
    else QR Code expiré
        API-->>Mobile: JSON {status: error, message: QR Code expiré}
    end
    deactivate API
    Mobile-->>Employe: Affiche le résultat sur l'écran du téléphone
```

---

### 4. Diagramme de collaboration/communication
Illustre l'organisation spatiale des objets communicants et leurs relations dynamiques.

```mermaid
graph TD
    Employe["1. Employé"]
    Mobile["2. App Mobile Flutter"]
    Kiosk["3. Borne Kiosk Web"]
    API["4. Laravel API"]
    DB["5. PostgreSQL DB"]

    Employe -->|1. Ouvre et scanne| Mobile
    Kiosk -->|2. Fournit QR Code dynamique| Mobile
    Mobile -->|3. Soumet scan HTTP POST| API
    API -->|4. Persiste l'enregistrement| DB
    API -.->|5. Actualise via Livewire et WebSockets| Kiosk
```

---

### 5. Diagramme d'état-transition (State Machine Diagram)
Décrit les états successifs d'un employé et d'un pointage au cours de leur cycle de vie.

#### État de l'Employé
```mermaid
stateDiagram-v2
    [*] --> Recrute : Ajout de la fiche RH
    Recrute --> Actif : Signature du consentement RGPD (Reconnaissance Faciale)
    Actif --> EnConge : Demande de congé validée par RH
    EnConge --> Actif : Fin de la période de congé
    Actif --> Suspendu : Mesure disciplinaire ou départ temporaire
    Suspendu --> Actif : Réintégration
    Actif --> Supprime : Archivage (Soft Delete)
    Supprime --> [*]
```

#### État d'un Pointage
```mermaid
stateDiagram-v2
    [*] --> AttenteScan : Borne Kiosk en attente
    AttenteScan --> EnCoursTraitement : Scan détecté par l'API
    EnCoursTraitement --> Rejete : Signature incorrecte
    Rejete --> [*]
    EnCoursTraitement --> Valide : Signature valide
    state Valide {
        [*] --> CheckHeure
        CheckHeure --> Present : < 08h00
        CheckHeure --> Retard : >= 08h00
    }
    Present --> [*]
    Retard --> [*]
```

---

### 6. Diagramme d'activité détaillé
Représente l'activité de l'assistant IA Horizon (intégrant Gemini) lors du traitement et de l'analyse automatique des absences.

```mermaid
stateDiagram-v2
    [*] --> LancementPlanifie : Commande système quotidienne (Cron)
    LancementPlanifie --> CollectePointages : Récupération des données du jour
    CollectePointages --> IdentifierAbsents : Compare les pointages vs liste des employés
    
    state IdentifierAbsents {
        [*] --> AbsenceDetectee : Pas de pointage à 08h30
        AbsenceDetectee --> CreationPointageAbsent : Enregistre statut="Absent" dans DB
    }

    CreationPointageAbsent --> ChargementHorizonService : Instancie HorizonAiService (Gemini)
    ChargementHorizonService --> ConstructionPrompt : Prépare l'historique de l'employé
    ConstructionPrompt --> AppelGemini : Envoie les données d'assiduité (API Gemini)
    
    state AppelGemini {
        [*] --> AnalyseFrequence : Évalue la récurrence des absences
        AnalyseFrequence --> CalculRisque : Calcule le score de risque d'absentéisme
        CalculRisque --> GenerationRecommandations : Rédige des actions correctives
    }

    AppelGemini --> EnregistrementPrediction : Écrit dans la table `ml_predictions`
    EnregistrementPrediction --> GenererAlerteRH : Si risque > 70%
    GenererAlerteRH --> EnvoiNotification : Notification Filament à l'administrateur RH
    EnvoiNotification --> [*]
```

---

## III. Conception architecturale

### 7. Diagramme de composants (Component Diagram)
Montre l'organisation modulaire du système et ses interconnexions via l'API REST et les services tiers.

```mermaid
graph TD
    subgraph Client Mobile [Client Mobile - Flutter]
        VueMobile["Vue Mobile (UI)"]
        SQLite[("Stockage Local (SQLite)")]
        ExcelGen["Générateur Excel"]
        ClientAPI["Client API"]
        VueMobile --> SQLite
        VueMobile --> ExcelGen
        VueMobile --> ClientAPI
    end

    subgraph Interface Web [Interface Web - Laravel/Filament]
        ConsoleAdmin["Console RH Admin"]
        KioskView["Borne Kiosk View (Livewire)"]
        HorizonInterface["Horizon AI Interface"]
        ConsoleAdmin --> HorizonInterface
    end

    subgraph Serveur Backend API [Serveur Backend - Laravel]
        APIControllers["Contrôleurs API (REST)"]
        SanctumAuth["Authentification (Sanctum)"]
        EloquentModels["Modèles Eloquent"]
        HorizonService["HorizonAiService"]
        GeminiClient["Client SDK Gemini"]

        ClientAPI -->|Requêtes HTTPS| APIControllers
        APIControllers --> SanctumAuth
        APIControllers --> EloquentModels
        HorizonInterface --> HorizonService
        HorizonService --> GeminiClient
    end

    subgraph Base de Données
        PostgresDB[("Base PostgreSQL")]
        EloquentModels --> PostgresDB
    end

    subgraph Services Externes
        GeminiAPI["API Google Gemini"]
        GCalendarAPI["API Google Calendar"]
        GeminiClient --> GeminiAPI
        HorizonService --> GCalendarAPI
    end
```

---

### 8. Diagramme de déploiement (Deployment Diagram)
Décrit la topologie matérielle d'exécution physique (smartphones, serveurs, base de données).

```mermaid
graph TD
    subgraph MobileDevice [Smartphone Employé - Android / iOS]
        subgraph FlutterRuntime [Environnement d'Exécution Flutter]
            MobileApp["Artifact: crhacerh-mobile.apk/.ipa"]
        end
    end

    subgraph KioskTerminal [Borne Kiosk - Tablette ou PC]
        subgraph Browser [Navigateur Web - Chrome / Edge]
            KioskApp["Interface Kiosk K1"]
        end
    end

    subgraph ApplicationServer [Serveur d'Application - XAMPP / VPS]
        subgraph WebServer [Serveur Web - Apache / Nginx]
            subgraph LaravelEnv [Laravel PHP 8.2 / 8.3 VM]
                LaravelApp["Code Source Backend"]
            end
        end
    end

    subgraph DatabaseServer [Serveur de Base de Données]
        subgraph PostgresDBMS [PostgreSQL DBMS]
            db[("Database: crhacerh_db")]
        end
    end

    subgraph GeminiCloud [Google Cloud AI]
        GeminiService["API Gemini Engine"]
    end

    MobileApp -->|HTTPS Port 443 / JSON REST API| WebServer
    KioskApp -->|HTTPS Port 443 / Livewire AJAX| WebServer
    LaravelApp -->|Connexion PDO Port 5432| db
    LaravelApp -->|HTTPS REST API SDK| GeminiService
```

---

### 9. Diagramme de paquetages (Package Diagram)
Montre la structuration interne des répertoires du backend Laravel et du client mobile Flutter.

#### Paquetages Laravel (Backend)
```mermaid
graph TD
    subgraph Laravel Framework
        App[App/Http] --> Controllers[Controllers/Api]
        App --> Models[Models]
        App --> Services[Services/HorizonAiService]
        App --> Providers[Providers/Filament]
        Database[Database/Migrations et Seeders] --> Models
    end
```

#### Paquetages Flutter (Mobile)
```mermaid
graph TD
    subgraph Flutter Client
        lib[lib] --> screens[screens/Vues UI]
        lib --> models[models/Entités]
        lib --> services[services/API & Stockage]
        lib --> theme[theme/Style]
    end
```

---

## IV. Conception détaillée

### 10. Diagramme de classes détaillé (Class Diagram)
Affiche les entités avec leurs attributs, types, méthodes, ainsi que leurs relations d'association et multiplicités.

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
        +binary face_embeddings
        +datetime consent_facial_at
        +datetime deleted_at
        +canAccessPanel(panel) bool
        +getRememberToken() string
    }

    class Department {
        +int id
        +string name
        +datetime created_at
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

    class MlPrediction {
        +int id
        +int employee_id
        +string prediction_type
        +float risk_score
        +text details
        +datetime created_at
    }

    class Rapport {
        +int id
        +string title
        +string file_path
        +datetime generated_at
    }

    Department "1" --> "0..*" Employe : contient
    Employe "1" --> "0..*" Pointage : effectue
    Employe "1" --> "0..*" MlPrediction : fait l'objet de
```

---

### 11. Diagramme d'objets (Object Diagram)
Donne un exemple d'instanciation des objets à un instant T pour illustrer le pointage d'un employé.

```mermaid
graph TD
    dept1["deptIT: Department | id = 1 | name = 'Informatique'"]
    emp1["empJean: Employe | id = 101 | matricule = 'chr-2026-0101' | first_name = 'Jean' | last_name = 'Dupont' | role = 'Employés' | department_id = 1"]
    ptg1["ptgEntree: Pointage | id = 999 | employee_id = 101 | type = 'ENTREE' | status = 'Present' | timestamp = '2026-06-13 07:55:00'"]

    emp1 -->|appartient| dept1
    ptg1 -->|créé par| emp1
```

---

## V. Bases de données et Interface

### 12. Dictionnaire de données
Fournit une description rigoureuse de la structure de chaque champ de la base de données.

| Code / Nom du champ | Libellé / Description | Type | Taille | Règles / Contraintes |
| :--- | :--- | :--- | :--- | :--- |
| **EMP_ID** | Identifiant unique de l'employé | Entier (SERIAL) | | Clé primaire, Auto-incrémenté |
| **EMP_MAT** | Matricule unique de l'employé | VARCHAR | 50 | Unique (Format strict: `chr-20xx-xxxx`) |
| **EMP_EMAIL** | Email de connexion | VARCHAR | 150 | Unique, Format email valide |
| **EMP_PWD** | Mot de passe sécurisé | VARCHAR | 255 | Haché en base (Bcrypt) |
| **EMP_FN** | Prénom | VARCHAR | 100 | Obligatoire |
| **EMP_LN** | Nom de famille | VARCHAR | 100 | Obligatoire |
| **EMP_ROLE** | Rôle de l'utilisateur | VARCHAR | 50 | `Employés`, `Manager`, `RH_Admin` |
| **EMP_TEL** | Numéro de téléphone | VARCHAR | 20 | Optionnel |
| **EMP_EMB** | Empreintes biométriques faciales | BYTEA (Binary) | | Vecteur d'embeddings faciaux (128 dimensions) |
| **EMP_RGPD** | Date de consentement RGPD | TIMESTAMP | | Enregistre l'acceptation de la biométrie |
| **DEP_ID** | Identifiant du département | Entier (SERIAL) | | Clé primaire |
| **DEP_NOM** | Nom du département | VARCHAR | 100 | Unique |
| **PTG_ID** | Identifiant unique du pointage | Entier (SERIAL) | | Clé primaire |
| **PTG_TYP** | Type de pointage | VARCHAR | 20 | `ENTREE`, `SORTIE` |
| **PTG_STT** | Statut du pointage | VARCHAR | 20 | `Present`, `Retard`, `Absent` |
| **PTG_SRC** | Source du pointage | VARCHAR | 20 | `QR`, `FACE` (Reconnaissance Faciale) |
| **PTG_TS** | Date et heure du pointage | TIMESTAMP | | Rempli automatiquement à la transaction |
| **PTG_CONF** | Score de confiance faciale | DOUBLE | | Taux de correspondance (entre 0.0 et 1.0) |
| **PTG_ANOM** | Anomalie détectée | BOOLEAN | | `TRUE` si usurpation suspectée |

---

### 13. Schéma relationnel
Traduction logique de la structure de données prête pour l'implémentation.

*   **departments** (<u>id</u>, name, created_at, updated_at)
*   **employees** (<u>id</u>, matricule, email, password, first_name, last_name, role, phone, face_embeddings, consent_facial_at, created_at, updated_at, deleted_at, *department_id#*)
*   **pointages** (<u>id</u>, type, status, source, timestamp, confidence_score, anomaly_detected, created_at, updated_at, *employee_id#*)
*   **ml_predictions** (<u>id</u>, prediction_type, risk_score, details, created_at, *employee_id#*)
*   **rapports** (<u>id</u>, title, file_path, generated_at)

---

### 14. Schéma physique (scripts de création, index, optimisations)
Code SQL DDL optimisé pour PostgreSQL permettant la création physique des tables avec indexations stratégiques.

```sql
-- 1. Table départements
CREATE TABLE departments (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW()
);

-- 2. Table employés
CREATE TABLE employees (
    id SERIAL PRIMARY KEY,
    matricule VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role VARCHAR(50) DEFAULT 'Employés',
    department_id INTEGER REFERENCES departments(id) ON DELETE SET NULL,
    phone VARCHAR(20),
    face_embeddings BYTEA,
    consent_facial_at TIMESTAMP WITHOUT TIME ZONE,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    deleted_at TIMESTAMP WITHOUT TIME ZONE
);

-- 3. Table pointages
CREATE TABLE pointages (
    id SERIAL PRIMARY KEY,
    employee_id INTEGER NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
    type VARCHAR(20) NOT NULL,
    status VARCHAR(20) NOT NULL,
    source VARCHAR(20) DEFAULT 'QR',
    timestamp TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    confidence_score DOUBLE PRECISION,
    anomaly_detected BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW()
);

-- Optimisations & Indexations
CREATE UNIQUE INDEX idx_employees_matricule ON employees(matricule);
CREATE INDEX idx_pointages_employee_ts ON pointages(employee_id, timestamp DESC);
CREATE INDEX idx_pointages_status ON pointages(status);
```

#### Diagramme du Schéma Physique (ERD)
```mermaid
erDiagram
    departments {
        int id PK
        varchar name UK
        timestamp created_at
        timestamp updated_at
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
        varchar phone
        bytea face_embeddings
        timestamp consent_facial_at
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
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
        timestamp created_at
        timestamp updated_at
    }
    ml_predictions {
        int id PK
        int employee_id FK
        varchar prediction_type
        double_precision risk_score
        text details
        timestamp created_at
    }

    departments ||--o{ employees : "contient"
    employees ||--o{ pointages : "enregistre"
    employees ||--o{ ml_predictions : "fait_l_objet_de"
```

---

### 15. Diagrammes d'interface utilisateur (Maquettes/wireframes)
Représente la structure ergonomique de l'application mobile et du panel d'administration Web.

#### Écran d'accueil - Application Mobile Flutter
```mermaid
graph TD
    subgraph "Maquette Écran Mobile Flutter"
        Header["Statut du jour : POINTÉ"]
        QrBtn["Bouton : SCANNER LE QR CODE"]
        StatsBox["Statistiques : Présence 90% | Retard 5% | Absence 5%"]
        HistoryTable["Aujourd'hui : Entrée à 07h55 - Présent"]
        ExcelBtn["Générer Rapport - .xlsx"]
    end
    QrBtn --> HistoryTable
```

#### Écran Dashboard - Panel Administration Filament
```mermaid
graph TD
    subgraph "Maquette Interface Web Filament PHP"
        Nav["Menu : Employés | Pointages | Calendrier | Horizon AI"]
        Widget1["Employés Présents : 42"]
        Widget2["Retards : 3"]
        Widget3["Absents : 5"]
        Chart["Graphiques d'assiduité hebdomadaire"]
        List["Derniers pointages en temps réel"]
    end
```

---

### 16. Architecture de l'application
Schéma d'architecture de type client-serveur suivant le patron MVC (Modèle-Vue-Contrôleur) pour le transfert de données.

```mermaid
graph TD
    subgraph "Client Flutter (Mobile)"
        View["Vue UI Dart"] -->|Lit l'état| Provider["Riverpod State Provider"]
        Provider -->|Appelle| MobileAPI["API Client Service"]
    end

    subgraph "Backend Laravel (Serveur API)"
        MobileAPI -->|1. Requête HTTP JSON| Routes["routes/api.php"]
        Routes -->|2. Route vers| Controller["Http/Controllers/Api/AttendanceController"]
        Controller -->|3. Manipule| Models["Eloquent Models"]
        Models -->|4. Persiste| SQL[("PostgreSQL Database")]
        Controller -->|5. Retourne| MobileAPI
    end
```
