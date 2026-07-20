# Système Intégré de Gestion des Présences et d'Accueil Biométrique - CHRACERH 🚀

> **Application Web pour le Suivi d'Assiduité et l'Accueil Biométrique par Empreinte Digitale des Employés et Patients**

---

## 📌 Présentation du Projet

Le **Système Intégré de Gestion des Présences et d'Accueil Biométrique** est une solution conçue spécifiquement pour les besoins du **C.H.R.A.C.E.R.H** (Centre Hospitalier de Recherche et d'Application en Chirurgie Endoscopique et Reproduction Humaine). Ce système simplifie, sécurise et automatise le suivi d'assiduité du personnel hospitalier tout en fluidifiant l'accueil et l'orientation des patients lors de leur arrivée en consultation.

> [!IMPORTANT]
> **Focus 100% Web (`crhacerh-web`) :** L'application mobile (`crhacerh-mobile`) a été officiellement isolée et retirée du flux actif d'exploitation. L'ensemble des fonctionnalités (gestion des présences, borne Kiosk de pointage, suivi des rendez-vous et rapports mensuels) est centralisé sur l'application Web Laravel / Filament PHP.

La solution s'articule autour des deux composants actifs suivants :
1. **L'interface Web d'administration (Laravel & Filament PHP) :** Permet aux équipes du C.H.R.A.C.E.R.H de gérer les fiches des employés, de planifier et de suivre les rendez-vous médicaux des patients, de valider les demandes de congés/permissions et d'exporter des rapports d'assiduité réguliers.
2. **La Borne de Pointage Publique Interactive Kiosk (`/kiosk`) :** Une interface Web publique accessible depuis un écran ou une tablette à l'entrée de l'établissement. Elle permet le pointage en temps réel par **Empreinte Digitale** ou via un **QR code dynamique**, et sert également de point d'accueil pour le pointage de présence des patients ayant un rendez-vous planifié.

---

## ⚙️ Fonctionnalités Majeures (Composant Web)

### 💻 Portail Web & Administration (Filament PHP)
* **Tableau de Bord RH (Dashboard) :** Indicateurs visuels dynamiques (via Chart.js) sur l'état des présences quotidiennes (présents, absents, retards) et graphiques comparatifs entre départements.
* **Gestion des Congés & Permissions :** Flux de validation et de suivi des demandes d'absences (Maladie, Congé, Permission) soumises par le personnel.
* **Gestion des Rendez-vous Patients :** Module complet de planification, d'assignation des médecins spécialistes et de suivi du statut des consultations.
* **Calendrier RH Synchronisé :** Intégration bidirectionnelle avec **Google Calendar** et affichage interactif (FullCalendar) pour coordonner les plannings.
* **Génération de Rapports d'Assiduité :** 
  * Exportation de rapports détaillés au format Excel (`.xlsx`) via PhpSpreadsheet.
  * Espace de stockage structuré classant automatiquement les rapports dans des sous-dossiers par mois.
* **Messagerie de Support Interne :** Chat en direct (Livewire) permettant aux agents de communiquer avec les RH et de transmettre leurs pièces justificatives numérisées.

### 🏢 Borne Interactive Kiosk
* **Pointage par Empreinte Digitale :** Enregistrement et vérification instantanée des empreintes digitales des employés et des patients.
* **Accueil des Patients :** Pointage d'arrivée autonome pour les consultations du jour, mettant à jour en temps réel le statut du patient en "Présent" dans l'administration.
* **Pointage par QR Code Dynamique :** Affichage d'un code QR temporaire mis à jour en continu pour sécuriser l'enregistrement et éviter les fraudes.
* **Pointages Récents :** Flux visuel en temps réel confirmant la bonne prise en compte du pointage des utilisateurs.

---

## 📁 Structure du Projet

```text
Système Intégré de Gestion des Présences.../
├── README.md                           <- Ce fichier (vue d'ensemble)
├── cahier_des_charges.md               <- Spécifications fonctionnelles et techniques détaillées
├── dictionnaire_de_donnees.html         <- Modèle relationnel et dictionnaire des tables SQL
├── rapport_de_stage_diagrammes.md      <- Modèles UML & diagrammes d'architecture (Mermaid)
│
├── crhacerh-web/                       <- [COMPOSANT ACTIF] Backend Laravel & Filament Admin
│   ├── app/                            <- Contrôleurs API, Modèles Eloquent et logique de génération des rapports
│   ├── config/                         <- Configuration de l'application (Google Calendar, etc.)
│   ├── database/                       <- Migrations de base de données et Seeders de test
│   ├── public/                         <- Actifs publics, scripts de la borne Kiosk (lecteur d'empreinte/QR)
│   ├── resources/                      <- Vues Blade, fichiers de styles et composants Livewire (chat)
│   └── routes/                         <- Définitions des routes (Web & API)
│
└── crhacerh-mobile/                    <- [ARCHIVÉ] Client mobile Flutter (exclu du flux d'exploitation)
```

---

## ⚙️ Installation et Démarrage rapide

### Configuration du Serveur Web (Laravel & Filament)

1. Accédez au répertoire de l'application Web :
   ```bash
   cd crhacerh-web
   ```

2. Installez les dépendances PHP (Composer) :
   ```bash
   composer install
   ```

3. Installez les dépendances Node et compilez les actifs JavaScript/CSS :
   ```bash
   npm install
   npm run build
   ```

4. Configurez l'environnement :
   * Créez votre fichier d'environnement : `cp .env.example .env`
   * Configurez la base de données dans le fichier `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
   * Renseignez vos identifiants d'API Google Calendar si nécessaire.

5. Exécutez les migrations de base de données et peuplez les tables :
   ```bash
   php artisan migrate --seed
   ```

6. Lancez le serveur de développement et le compilateur d'actifs :
   ```bash
   # Lance le serveur Laravel, les écouteurs de files d'attente et Vite simultanément :
   npm run dev
   ```

* Le panel d'administration est disponible sur : `http://localhost:8000/ressources-humaines` (ou `/login`).
* La borne de pointage Kiosk est accessible sur : `http://localhost:8000/kiosk`.

---

## 📖 Liens Utiles et Documentation

* **Spécifications Fonctionnelles & Techniques :** [cahier_des_charges.md](file:///c:/Users/SOLUTION%20TECH/Documents/Syst%C3%A8me%20Int%C3%A9gr%C3%A9%20de%20Gestion%20des%20Pr%C3%A9sences%20et%20d%27Accueil%20Biom%C3%A9trique%20-%20CHRACERH/cahier_des_charges.md)
* **Diagrammes d'Architecture et Modèles UML :** [rapport_de_stage_diagrammes.md](file:///c:/Users/SOLUTION%20TECH/Documents/Syst%C3%A8me%20Int%C3%A9gr%C3%A9%20de%20Gestion%20des%20Pr%C3%A9sences%20et%20d%27Accueil%20Biom%C3%A9trique%20-%20CHRACERH/rapport_de_stage_diagrammes.md)
* **Dictionnaire de Données SQL :** [dictionnaire_de_donnees.html](file:///c:/Users/SOLUTION%20TECH/Documents/Syst%C3%A8me%20Int%C3%A9gr%C3%A9%20de%20Gestion%20des%20Pr%C3%A9sences%20et%20d%27Accueil%20Biom%C3%A9trique%20-%20CHRACERH/dictionnaire_de_donnees.html)
