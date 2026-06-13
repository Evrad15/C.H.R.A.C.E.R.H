# C.H.R.A.C.E.R.H 🚀
> **Application Web, Mobile et IA de Gestion de Présence et d'Assiduité des Employés**

---

## 📌 Présentation du Projet
**C.H.R.A.C.E.R.H** est un projet de stage visant à moderniser et automatiser la gestion des temps de présence, des retards et des absences au sein d'une organisation. 

La solution s'articule autour de deux axes principaux :
1. **Une interface Web d'administration (Laravel & Filament PHP) :** Permet aux équipes RH de gérer les employés, de visualiser les statistiques en temps réel, de configurer une borne de pointage publique (Kiosk) et de communiquer via une messagerie interne. Elle intègre également **Horizon**, un assistant RH intelligent alimenté par l'API Gemini pour toutes les tâches analytiques et prédictives.
2. **Une application Mobile (Flutter) :** Permet aux employés de pointer via le scan d'un QR code dynamique sur la borne Kiosk, de consulter leurs statistiques d'assiduité hors-ligne et de générer localement des rapports hebdomadaires au format Excel (.xlsx).

---

## 📁 Structure du Projet
```text
APPLICATION WEB ET MOBILE.../
├── README.md                 <- Ce fichier (vue d'ensemble)
├── cahier_des_charges.md     <- Cahier des charges et spécifications détaillées
├── crhacerh-web/             <- Backend Laravel (API, Admin Panel Filament & Borne Kiosk)
│   ├── app/                  <- Logique métier & HorizonAiService
│   └── public/               <- Fichiers publics (dont la borne kiosk)
└── crhacerh-mobile/          <- Client mobile Flutter
    ├── lib/                  <- Code Dart de l'application (écrans, services de stockage)
    └── assets/               <- Images et ressources du projet mobile
```

---

## ⚙️ Installation et Démarrage rapide

### 1. Backend Web (Laravel & Filament)
Allez dans le répertoire du serveur Web :
```bash
cd crhacerh-web
```

Installez les dépendances PHP et JavaScript :
```bash
composer install
npm install
```

Configurez l'environnement :
1. Copiez le fichier d'exemple : `cp .env.example .env`
2. Configurez votre base de données dans le fichier `.env`.
3. Ajoutez votre clé API Gemini pour l'assistant Horizon :
   ```env
   GEMINI_API_KEY=votre_cle_api_ici
   ```
4. Configurez vos identifiants d'API Google Calendar si nécessaire.

Exécutez les migrations et alimentez la base de données :
```bash
php artisan migrate --seed
```

Lancez le serveur de développement et le compilateur d'assets :
```bash
php artisan serve
# (Dans un autre terminal)
npm run dev
```
Accédez au panel d'administration sur `http://localhost:8000/login` et à la borne de pointage Kiosk sur `http://localhost:8000/kiosk`.

### 2. Application Mobile (Flutter)
Allez dans le répertoire mobile :
```bash
cd crhacerh-mobile
```

Récupérez les paquets Flutter requis :
```bash
flutter pub get
```

Lancez l'application mobile (sur un émulateur ou un appareil physique connecté) :
```bash
flutter run
```

---

## 📖 Liens utiles
* **Spécifications fonctionnelles et techniques :** [cahier_des_charges.md](file:///c:/Users/mopao/Mes%20projets/APPLICATION%20WEB%20ET%20MOBILE%20DE%20GESTION%20DE%20PRESENCES%20DES%20EMPLOYES/cahier_des_charges.md)
