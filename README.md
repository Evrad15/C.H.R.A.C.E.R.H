# C.H.R.A.C.E.R.H 🚀
> **Application Web, Mobile et IA de Gestion de Présence et d'Assiduité des Employés**

---

## 📌 Présentation du Projet
**C.H.R.A.C.E.R.H** est une solution de gestion hospitalière, des temps de présence, et de suivi administratif.

> [!IMPORTANT]
> **Focus 100% Web (`crhacerh-web`)** : L'application mobile (`crhacerh-mobile`) a été isolée et retirée du flux d'exploitation actif. L'ensemble de la gestion (présences, rendez-vous patients, et génération automatique de rapports par mois) est centralisé sur l'application Web Laravel / Filament PHP.

La solution s'articule autour de l'axe principal suivant :
1. **Une interface Web d'administration (Laravel & Filament PHP) :** Permet aux équipes du C.H.R.A.C.E.R.H de gérer les employés et leurs présences, de planifier et suivre les **rendez-vous médicaux des patients**, de configurer une borne de pointage publique (Kiosk) et de consulter les **rapports d'assiduité générés et classés automatiquement chaque mois**.
2. **Application Mobile (Flutter) [ISOLÉE / ARCHIVÉE] :** Conservée à titre d'archive dans `crhacerh-mobile/` mais non active.

---

## 📁 Structure du Projet
```text
APPLICATION WEB ET MOBILE.../
├── README.md                 <- Ce fichier (vue d'ensemble)
├── cahier_des_charges.md     <- Cahier des charges
├── crhacerh-web/             <- Backend Laravel (API, Admin Panel Filament, Rendez-vous Patients & Borne Kiosk)
│   ├── app/                  <- Logique métier & services de génération automatique de rapports
│   └── public/               <- Fichiers publics (dont la borne kiosk)
└── crhacerh-mobile/          <- [ISOLÉ / ARCHIVÉ] Client mobile Flutter (exclu du flux d'exploitation)
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
