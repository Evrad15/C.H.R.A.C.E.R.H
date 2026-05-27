# SYSTÈME DE GESTION DES POINTAGES HOSPITALIERS - RESUMÉ FONCTIONNALITÉS

## VERSION: v1.0 MVP (Stage 12-14 semaines - Solo)

---

## 🏗️ ARCHITECTURE GLOBALE

```
┌─────────────────────────────────────────────────────────────┐
│                       UTILISATEURS                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  EMPLOYÉ (Mobile)      GUÉRITE (Web)       RH (Web)        │
│  - QR Scanner          - Display QR        - Dashboard      │
│  - Facial Enroll       - Facial Fallback   - Rapports       │
│  - Dashboard           - PIN Fallback      - Gestion        │
│  - Offline Mode        - Live Feedback     - Export Excel   │
│  - Absence Mgmt        - Simple UI         - Prédictions    │
│                                            - IA Insights    │
└─────────────────────────┬──────────────────────────────────┘
                          │
           ┌──────────────▼─────────────────┐
           │    BACKEND API (Node.js)       │
           │    Express + TypeScript        │
           │    - JWT Auth                  │
           │    - REST Endpoints            │
           │    - File Upload               │
           │    - Logging                   │
           └──────┬───────────────┬────────┘
                  │               │
         ┌────────▼────┐  ┌──────▼──────────┐
         │ PostgreSQL  │  │  Redis Cache    │
         │ + Audit     │  │  + Sessions     │
         └─────────────┘  └─────────────────┘

         ┌─────────────────────────────────┐
         │  ML Service (Python Flask)      │
         │  - Anomaly Detection            │
         │  - Absence Prediction           │
         │  - Facial Matching              │
         └─────────────────────────────────┘
```

---

## 📱 APP MOBILE (React Native Expo)

### Écrans Principaux

#### 1. LOGIN SCREEN
- Email + Password form
- JWT token storage (SecureStore)
- Remember me option
- Error handling

#### 2. FACIAL ENROLLMENT SCREEN (First Launch)
- Capture 10 selfies (camera prompt)
- Quality check per image (face detection)
- MediaPipe encoding local device
- RGPD consent notice
- Upload to /api/face/enroll
- Confirmation message

#### 3. QR SCANNER SCREEN
- Camera access request
- QR code scanning (expo-camera)
- Validate QR signature (HMAC-SHA256)
- POST /api/pointages (ENTREE)
- Confirmation: "Bienvenue [Name] - [Time]"
- Fallback: Manual PIN if scan fails

#### 4. DASHBOARD EMPLOYÉ
- Stats aujourd'hui (heure arrivée, heure départ)
- Stats mois (jours présents, absences, retards)
- Heatmap simple (jours présents/absents visuels)
- Trending: Heures moyennes par jour semaine
- Quick buttons: Absence today? Report issue

#### 5. DEMANDE CONGÉS SCREEN
- Date picker (début/fin)
- Type dropdown (Congé, Maladie, Sans Solde)
- Justificatif upload (optional)
- Raison text field
- Submit → POST /api/absences
- List demandes précédentes avec statut

#### 6. SETTINGS SCREEN
- Biometric toggle (on/off facial)
- Dark mode toggle (local storage)
- Logout button
- Reset biometric (delete local face embeddings)
- Privacy: See what data is collected

### Technical Details

**Storage:**
- JWT token: SecureStore (encrypted device storage)
- Face embeddings: SQLite encrypted local
- Sync queue: SQLite (pointages non-synced)
- Dark mode preference: AsyncStorage

**Offline Capability:**
- QR scanning: Works offline (local validation)
- Facial enroll: Stored local, synced on internet return
- Dashboard: Show cached data if no internet
- Sync queue: Auto-sync when WiFi returns

**Notifications:**
- Push notifications: expo-notifications
- v1: Simple text alerts (absence pending approval, etc.)
- v2: Rich notifications with actions

**Biometrics:**
- Touch ID / Face ID native (LocalAuthentication)
- Fallback to PIN if biometric fails
- No biometric data sent to backend (OS handles it)

---

## 🖥️ WEB DASHBOARD GUÉRITE

### Purpose
Terminal à l'entrée hôpital. Employé scanne QR depuis son app mobile.

### Features

#### QR Code Display
- GET /qr/{date} generates unique QR per day per employee
- QR contains: employee_id + timestamp + HMAC signature
- Refreshes every 30 seconds
- Large, clear, well-lit display

#### Fallback: Facial Recognition
- Webcam input
- MediaPipe face detection
- Liveness check (simple: eye blink detection)
- Match against database (cosine similarity > 0.65)
- If confidence < 65%: fallback to PIN

#### PIN Entry (Last Resort)
- 6-digit keypad
- Rate limiting (max 3 attempts)
- Lockout 5 minutes after 3 fails

#### Feedback Display
- ✅ Green screen: "Bienvenue [Name] - [HH:MM]"
- ❌ Red screen: Error message + retry option
- Confetti animation on success (v1: simple success message)

### Technical

**Tech Stack:**
- React (no routing, single SPA)
- TailwindCSS for styling
- Webcam: react-webcam lib
- QR Gen: qr-code lib

---

## 📊 WEB DASHBOARD RH

### Overview
Admin/RH dashboard for viewing pointages, managing employees, approving absences, exporting reports.

### Main Screens

#### 1. LOGIN
- Email + Password (JWT)
- Simple (no 2FA v1)

#### 2. POINTAGES VIEW
- Table: Employee | Date | Time In | Time Out | Source | Status
- Filters:
  - Date range picker
  - Employee dropdown (multi-select)
  - Department filter
  - Source filter (QR / Facial / PIN)
- Sort by: Date, Employee, Time
- Real-time: Polling every 5 seconds (not WebSocket)
- Color coding: Late (red), Normal (green), Leave (gray)

#### 3. RAPPORTS ASSIDUITÉ
- Chart 1: Line chart (présence % par jour du mois)
- Chart 2: Bar chart (présence par département)
- Chart 3: Stats table (employé | présence % | absences | retards)
- Export Excel button:
  - GET /reports/export-excel?month=05&year=2026
  - Format: ID | Name | Dept | Présence_Days | Absence_Days | Retards | %
  - Auto-download .xlsx file

#### 4. GESTION EMPLOYÉS
- Table: ID | Name | Email | Dept | Status | Actions
- Add button: Form (Name, Email, Dept, Role)
- Edit button: Modal edit
- Delete button: Soft delete (archived)
- Bulk import: Upload CSV

#### 5. APPROVER ABSENCES
- List: Name | Date Début | Date Fin | Type | Reason | Justificatif | Status
- Filter by status (Pending, Approved, Rejected)
- Approve button: UPDATE /api/absences/{id} status=APPROVED
- Reject button: Modal with reason field
- Notification: Email to employee (TODO v2)

#### 6. AI INSIGHTS (Simple)
- Box 1: "Anomalies Detected This Month: 5"
- Box 2: "Absence Predictions (Tomorrow): Dr. Smith (72% risk)"
- Box 3: "Most absent dept: Cardiologie (12 absences)"
- Charts: Anomaly scores over time

### Technical

**Tech Stack:**
- React 18 + TypeScript
- TailwindCSS
- Recharts (charts)
- React Query (data fetching, caching)
- axios (HTTP)

**Real-time Updates:**
- Polling (GET /pointages every 5 seconds)
- v2: Upgrade to WebSocket (Socket.io)

**Export:**
- npm package: xlsx (write Excel files)
- Server-side: Generate file, stream to client

---

## 🔌 BACKEND API (Node.js Express)

### Authentication

#### POST /auth/login
```
Body: { email, password }
Response: { access_token: JWT, user: { id, name, email, role } }
```

#### POST /auth/logout
```
Headers: Authorization: Bearer {token}
Response: { success: true }
```

#### POST /auth/register (Admin only)
```
Body: { email, password, name, department_id, role }
Response: { user: ... }
```

### Employees

#### GET /employees
```
Query: ?department_id={id}, ?status=active
Response: [{ id, name, email, department_id, role, created_at }]
```

#### POST /employees
```
Admin only
Body: { name, email, department_id, role }
Response: { employee: {...} }
```

#### PUT /employees/{id}
```
Admin only
Body: { name, email, department_id }
Response: { employee: {...} }
```

#### DELETE /employees/{id}
```
Admin only
Soft delete (set deleted_at)
Response: { success: true }
```

### Pointages

#### POST /pointages
```
Headers: Authorization: Bearer {token}
Body: { 
  type: "ENTREE" | "SORTIE",
  source: "QR" | "FACIAL" | "PIN",
  qr_hash: "abc123", (if QR)
  location: { lat, lng },
  face_confidence: 0.95 (if facial)
}
Response: { pointage: { id, employee_id, timestamp, ... }, message: "Bienvenue..." }
```

#### GET /pointages
```
Query: ?employee_id={id}, ?date_from=2026-05-01, ?date_to=2026-05-31, ?source=QR
Response: [{ id, employee_id, type, timestamp, source, ... }]
```

#### GET /pointages/{id}
```
Response: { pointage: {...} }
```

### QR Code

#### GET /qr/{date}
```
Params: date (YYYY-MM-DD)
Auth: Optional (for guérite webcam or mobile)
Response: { 
  qr_image: "data:image/png;base64,...",
  qr_data: { emp_id, timestamp, signature },
  expires_at: "2026-05-20T23:59:59Z"
}
```

### Facial Recognition

#### POST /face/enroll
```
Headers: Authorization: Bearer {token}
Body: FormData with:
  - 10 image files (jpeg/png)
  - consent: true
Response: {
  success: true,
  quality_score: 0.92,
  message: "Face enrolled successfully"
}
```

#### POST /face/match
```
Body: FormData with:
  - image: single face photo
Response: {
  matched: true/false,
  employee_id: 123,
  confidence: 0.87,
  message: "Match found" | "No match"
}
```

#### DELETE /face/{employee_id}
```
Auth: User deleting own face or Admin
Response: { success: true, message: "Face data deleted (GDPR)" }
```

### Absences/Congés

#### POST /absences
```
Headers: Authorization: Bearer {token}
Body: {
  date_debut: "2026-06-01",
  date_fin: "2026-06-05",
  type: "CONGE" | "MALADIE" | "SANS_SOLDE",
  raison: "Vacances",
  justificatif: file (optional)
}
Response: { absence: {...}, status: "DEMANDE" }
```

#### GET /absences
```
Query: ?employee_id={id}, ?status=DEMANDE|APPROUVEE|REJETEE, ?month=05
Response: [{ id, employee_id, date_debut, date_fin, type, status, ... }]
```

#### PUT /absences/{id}
```
Admin only
Body: {
  status: "APPROUVEE" | "REJETEE",
  raison_rejet: "..." (if rejected)
}
Response: { absence: {...} }
```

### Reports

#### GET /reports/attendance/{employee_id}
```
Query: ?month=05&year=2026 OR ?date_from=...&date_to=...
Response: {
  employee: {...},
  period: { month: 5, year: 2026 },
  stats: {
    total_days: 22,
    present_days: 20,
    absent_days: 2,
    late_days: 1,
    presence_rate: 90.9,
    avg_arrival_time: "08:15",
    avg_departure_time: "17:30"
  },
  daily_breakdown: [
    { date: "2026-05-01", time_in: "08:05", time_out: "17:25", status: "normal" },
    ...
  ]
}
```

#### GET /reports/export-excel
```
Query: ?employee_id={id}&month=05&year=2026 OR ?date_from=...&date_to=...
Response: Excel file (xlsx) download
Format: ID | Name | Dept | Date | Arrival | Departure | Duration | Status
```

#### GET /reports/anomalies
```
Query: ?month=05&limit=10
Response: [
  {
    id: "anom_123",
    employee_id: 456,
    anomaly_type: "double_scan" | "unusual_time" | "impossible_location",
    timestamp: "2026-05-19T10:30:00Z",
    confidence: 0.92,
    details: "Deux pointages à 2 minutes d'intervalle"
  },
  ...
]
```

### AI/ML Predictions

#### GET /ml/absence-prediction
```
Query: ?date=2026-05-21
Response: [
  {
    employee_id: 123,
    name: "Dr. Jean",
    absence_probability: 0.72,
    reason: "Historique d'absence élevé les lundis",
    confidence: 0.85
  },
  ...
]
```

#### GET /ml/anomaly-score/{pointage_id}
```
Response: {
  pointage_id: 123,
  anomaly_score: 0.35,
  is_anomaly: false,
  threshold: 0.70
}
```

### Admin/Management

#### GET /health
```
Response: { status: "ok", uptime: 3600, db: "connected" }
```

#### GET /audit-logs
```
Admin only
Query: ?user_id={id}, ?action=login|create|update|delete, ?limit=100
Response: [
  {
    id: "log_123",
    user_id: 456,
    action: "CREATE",
    resource: "POINTAGE",
    resource_id: 789,
    timestamp: "2026-05-19T10:30:00Z",
    ip: "192.168.1.1",
    user_agent: "Mozilla/5.0...",
    status: "SUCCESS" | "FAILURE"
  },
  ...
]
```

---

## 🗄️ DATABASE SCHEMA (PostgreSQL)

### employees
```sql
- id (PK)
- email (UNIQUE)
- name
- phone
- department_id (FK)
- role (ENUM: employee, manager, admin, dpo)
- face_embeddings (BYTEA encrypted, nullable)
- consent_facial_at (TIMESTAMP nullable)
- created_at
- updated_at
- deleted_at (soft delete)
```

### pointages
```sql
- id (PK)
- employee_id (FK)
- type (ENUM: ENTREE, SORTIE)
- source (ENUM: QR, FACIAL, PIN, BIOMETRIC)
- timestamp
- location_lat (FLOAT nullable)
- location_lng (FLOAT nullable)
- face_confidence (FLOAT nullable)
- qr_hash (VARCHAR nullable)
- anomaly_score (FLOAT)
- is_anomaly (BOOLEAN)
- created_at
- updated_at

INDEX: (employee_id, timestamp DESC)
```

### absences
```sql
- id (PK)
- employee_id (FK)
- date_debut (DATE)
- date_fin (DATE)
- type (ENUM: CONGE, MALADIE, SANS_SOLDE)
- status (ENUM: DEMANDE, APPROUVEE, REJETEE)
- raison (TEXT)
- raison_rejet (TEXT nullable)
- justificatif_path (VARCHAR nullable)
- approuvee_par (FK employees, nullable)
- created_at
- updated_at
```

### face_enrollments
```sql
- id (PK UUID)
- employee_id (FK UNIQUE)
- embeddings (BYTEA encrypted)
- quality_score (FLOAT)
- consent_given_at (TIMESTAMP)
- deleted_at (TIMESTAMP nullable, GDPR)
- created_at
```

### audit_logs
```sql
- id (PK)
- user_id (FK)
- action (VARCHAR)
- resource_type (VARCHAR)
- resource_id (INT)
- old_values (JSONB nullable)
- new_values (JSONB nullable)
- timestamp
- ip_address
- user_agent
- status (ENUM: SUCCESS, FAILURE)
```

### departments
```sql
- id (PK)
- name
- manager_id (FK nullable)
- created_at
```

### users_sessions (Redis backup)
```
Key: session:{session_id}
Value: { user_id, email, role, expires_at }
TTL: 7 days
```

---

## 🤖 ML SERVICE (Python Flask)

### Purpose
Separate microservice for ML operations (isolation, scalability, easy updates).

### Endpoints

#### POST /anomaly-detect
```
Body: {
  pointage: {
    timestamp: "2026-05-19T10:30:00Z",
    day_of_week: 1,
    employee_id: 123,
    source: "QR",
    face_confidence: 0.92,
    ...
  }
}
Response: {
  anomaly_score: 0.35,
  is_anomaly: false,
  threshold: 0.70
}
```

#### POST /absence-predict
```
Body: {
  employee_id: 123,
  prediction_date: "2026-05-21"
}
Response: {
  absence_probability: 0.72,
  confidence: 0.85,
  top_reasons: [
    "Historique absence lundi (high)",
    "Pas absence depuis 2 semaines (low)"
  ]
}
```

#### POST /face-encode
```
Body: FormData {
  images: [file1, file2, ...],
  augment: true
}
Response: {
  embeddings: [[float...], [float...], ...],
  quality_scores: [0.92, 0.87, ...],
  mean_quality: 0.89
}
```

#### POST /face-match
```
Body: {
  test_embedding: [float...],
  db_embeddings: [[float...], [float...], ...],
  threshold: 0.65
}
Response: {
  matched: true,
  best_match_idx: 0,
  confidence: 0.87,
  scores: [0.87, 0.45, 0.32, ...]
}
```

#### GET /model-status
```
Response: {
  anomaly_forest: {
    version: "v1",
    trained_at: "2026-05-10T12:00:00Z",
    training_samples: 5000,
    feature_count: 12
  },
  absence_xgboost: {
    version: "v2",
    trained_at: "2026-05-15T08:00:00Z",
    accuracy: 0.82
  }
}
```

### Training Data

**Isolation Forest (Anomalies):**
- Features: timestamp, day_of_week, hour, employee_id, source, face_confidence, gap_since_last
- Training data: 6 months pointages
- Retraining: Weekly (if data drift detected)
- Contamination: 0.05 (expect 5% anomalies)

**XGBoost (Absence Prediction):**
- Target: absent_next_day (binary)
- Features: absence_rate_past_month, day_of_week, month, department, age, day_before_holiday
- Training data: 12 months
- Classes: Balanced (SMOTE if imbalanced)
- Retraining: Weekly

---

## 🔐 SECURITY FEATURES (v1.0)

### Authentication
- JWT (access token 15 min, refresh token 7 days)
- Bcrypt hashing (12 rounds) for passwords
- Token stored in SecureStore (mobile), httpOnly cookie (web)

### Encryption
- Passwords: bcrypt
- PII (names, emails, phones): AES-256 at rest
- Face embeddings: AES-256 + salt per employee
- Transport: TLS 1.3 (HTTPS only)

### Rate Limiting
- Login: 10 attempts/minute per IP
- API general: 100 requests/minute per user
- File upload: 5 files/minute

### Validation
- Joi validation on all endpoints
- Input length limits
- Parameterized queries (prevent SQL injection)
- CORS: Whitelist only allowed origins

### Logging
- All API calls logged (with anonymization)
- Failed logins tracked
- Data modifications logged (for audit)
- Retention: 5 years (GDPR compliance)

### GDPR Compliance (v1 Basic)
- Privacy notice on signup
- Consent collection for facial biometric
- Data access endpoint: GET /user/data-export
- Data deletion endpoint: DELETE /user (soft delete + GDPR purge)
- Facial data auto-delete 90 days after employee leaves

---

## 📋 DATA MODELS SUMMARY

### User/Employee
```
id, email, name, phone, department_id, role, 
face_embeddings (encrypted), consent_facial_at, 
created_at, updated_at, deleted_at
```

### Pointage (Attendance)
```
id, employee_id, type (ENTREE/SORTIE), source (QR/FACIAL/PIN),
timestamp, location (lat, lng), face_confidence, 
anomaly_score, is_anomaly, created_at, updated_at
```

### Absence (Timeoff Request)
```
id, employee_id, date_debut, date_fin, type (CONGE/MALADIE),
status (DEMANDE/APPROUVEE/REJETEE), raison, justificatif_path,
approuvee_par, created_at, updated_at
```

### Facial Enrollment
```
id (UUID), employee_id (unique), embeddings (encrypted BYTEA),
quality_score, consent_given_at, deleted_at, created_at
```

### Audit Log
```
id, user_id, action, resource_type, resource_id,
old_values (JSON), new_values (JSON), timestamp, ip, user_agent, status
```

---

## 🎯 KEY USER FLOWS

### Flow 1: Employee Arrival (QR Scanning)
1. Employee opens mobile app
2. App shows QR scanner screen
3. Employee scans QR at guérite terminal
4. App validates QR signature locally
5. App POSTs /api/pointages { type: "ENTREE", source: "QR" }
6. Backend stores pointage with timestamp
7. App shows confirmation "Bienvenue Jean - 08:05"
8. If internet down: Stored in SQLite, synced on reconnect

### Flow 2: Employee Departure (Manual via Mobile)
1. Employee clicks "Confirmer Départ" in app
2. BiometricPrompt (Touch ID / Face ID) triggered
3. OS validates biometric locally (not sent to backend)
4. App POSTs /api/pointages { type: "SORTIE" }
5. Backend stores pointage
6. App shows "Départ enregistré - 17:30"

### Flow 3: Facial Enrollment (First Launch)
1. App detects first launch (no face_embeddings in DB)
2. Shows RGPD notice + request consent
3. User clicks "Enrolling"
4. Camera opens, prompts "Take 10 selfies"
5. Captures 10 images (different angles)
6. MediaPipe encodes embeddings locally
7. POSTs /api/face/enroll { images: [...], embeddings: [...] }
8. Backend stores encrypted embeddings in DB
9. Stores original images in S3 (backup)
10. Shows "Face enrolled successfully"

### Flow 4: Facial Recognition Fallback (Guérite)
1. QR scan fails (or employee forgot mobile)
2. Terminal shows "Scannez votre visage"
3. Employee looks at webcam
4. Captures 3 frames (liveness check)
5. MediaPipe detects faces + extracts embeddings
6. POSTs /api/face/match { test_embedding }
7. Backend compares cosine similarity vs DB embeddings
8. If confidence > 0.65: Match found, pointage created
9. If confidence < 0.65: Fallback to PIN
10. Shows "Bienvenue [Name]" or "PIN required"

### Flow 5: RH Viewing Attendance Report
1. RH logs in to web dashboard
2. Clicks "Rapports Assiduité"
3. Selects month (May 2026)
4. Dashboard calls GET /reports/attendance
5. Shows line chart (% présence par jour)
6. Shows stats table (20 présents, 2 absents, 1 retard)
7. RH clicks "Exporter Excel"
8. GET /reports/export-excel → xlsx file downloads
9. Opens in Excel, sees formatted report ready for paie

### Flow 6: RH Approving Absence Request
1. RH logs in → clicks "Approver Congés"
2. Table shows pending absence requests
3. RH selects one: "Dr. Jean - 2026-06-01 to 2026-06-05 - Congé"
4. Clicks "Approuver"
5. Backend: PUT /api/absences/{id} { status: "APPROUVEE" }
6. Pointages for those dates auto-excluded from presence calc
7. Email notification sent to employee (v2)
8. Table updates: status now "APPROUVEE"

### Flow 7: AI Anomaly Detection
1. Employee scans QR at 10:30 AM (unusual for them)
2. Backend receives pointage
3. Calls ML service: /anomaly-detect
4. Isolation Forest scores: 0.82 (high anomaly)
5. Backend stores: is_anomaly=true, anomaly_score=0.82
6. RH dashboard shows alert: "Anomalie détectée: Jean - Scan 10:30"
7. RH can click to investigate

### Flow 8: AI Absence Prediction
1. ML service runs daily job (overnight)
2. For each employee: GET /ml/absence-prediction?date=tomorrow
3. XGBoost predicts absence probability
4. If > 70%: Send alert to RH
5. Dashboard tomorrow shows: "Absence Risk: Dr. Smith (72%)"
6. RH can proactively reach out

---

## 🔧 DEVELOPMENT PRIORITIES (Semaine par Semaine)

### MUST HAVE (MVP v1.0)
- ✅ Backend API (JWT, CRUD, QR validation)
- ✅ PostgreSQL + Redis
- ✅ Facial enrollment + basic matching
- ✅ Mobile app (QR scanner, dashboard, offline)
- ✅ Web dashboard RH (reports, export Excel)
- ✅ ML basic (anomalies, absences)
- ✅ Audit logging
- ✅ Docker deployment

### NICE TO HAVE (if time)
- ⚠️ Facial liveness detection (advanced)
- ⚠️ Dark mode UI polish
- ⚠️ Push notifications (real implementation)
- ⚠️ Chatbot simple responses

### FUTURE (v2.0 Post-Stage)
- ❌ Chat WebSocket (real-time)
- ❌ Google Calendar integration
- ❌ 2FA admin
- ❌ Multi-langue (i18n structure)
- ❌ Real-time WebSocket dashboards
- ❌ IA avancée (turnover, causality)
- ❌ Multi-site support

---

## 📦 KEY LIBRARIES

### Frontend Mobile (React Native Expo)
```
expo, react-native, react, redux-toolkit, axios, 
expo-camera, @react-native-ml-kit/face-detection,
expo-local-authentication, sqlite3, async-storage,
react-navigation, react-native-qrcode-svg
```

### Frontend Web (React)
```
react, typescript, tailwindcss, recharts, react-query,
axios, react-icons, date-fns, react-qr-code
```

### Backend (Node.js)
```
express, typescript, passport, passport-jwt, joi, redis,
pg (PostgreSQL), multer, winston, dotenv, cors,
helmet, express-rate-limit, bcryptjs, jsonwebtoken
```

### ML (Python Flask)
```
flask, numpy, pandas, scikit-learn, xgboost, 
opencv-python, mediapipe, matplotlib, 
scipy, joblib (model saving), python-dotenv
```

### DevOps
```
docker, docker-compose, git, github-actions,
postgresql (Docker image), redis (Docker image),
minio (S3 compatible, Docker image)
```

---

## 🎬 QUICK START CHECKLIST FOR YOU

### Week 1
- [ ] Create GitHub repo
- [ ] Setup Docker + docker-compose (PostgreSQL, Redis)
- [ ] Setup Express + TypeScript boilerplate
- [ ] Create database schema
- [ ] Test GET /health endpoint

### Week 2-4 (Backend)
- [ ] Auth endpoints (login, register, logout)
- [ ] CRUD employees
- [ ] Pointage endpoints
- [ ] QR generation + validation
- [ ] Rapports endpoint

### Week 5-7 (Facial)
- [ ] MediaPipe setup (Python)
- [ ] Face enrollment endpoint
- [ ] Face matching endpoint
- [ ] Storage (S3 + embeddings in DB)

### Week 8-10 (Mobile)
- [ ] Expo setup
- [ ] Navigation + screens structure
- [ ] QR scanner integration
- [ ] Facial enrollment flow
- [ ] Dashboard + offline queue

### Week 11 (Web RH)
- [ ] React app setup
- [ ] Login + navigation
- [ ] Pointages table
- [ ] Rapports + Excel export
- [ ] Approve absences

### Week 12 (ML)
- [ ] Flask service setup
- [ ] Isolation Forest training
- [ ] XGBoost training
- [ ] Integration with backend

### Week 13-14 (Tests + Docs)
- [ ] Unit tests (jest backend + frontend)
- [ ] Integration tests
- [ ] README + API docs
- [ ] Deploy Docker
- [ ] Final demo

---

## 💡 NOTES POUR TOI

1. **Start with Backend** - It's foundation for everything else
2. **Use Postman/Insomnia** - Test API as you build
3. **Commit frequently** - Small commits, good commit messages
4. **Write tests early** - Not just at end
5. **Document as you go** - Not at end
6. **Deploy early** - Week 10, even if not everything works
7. **Keep scope tight** - MVP only, v2 is for later
8. **Ask for help** - Mentor, peers, Stack Overflow
9. **Celebrate wins** - Each milestone is progress!

---

## 📞 COMMON ISSUES & QUICK FIXES

**Facial recognition low accuracy?**
- More training images (10 not enough? try 15-20)
- Better lighting, different angles
- Adjust threshold from 0.65 to 0.60

**API slow?**
- Check DB query performance (EXPLAIN ANALYZE)
- Add indexes on frequently queried columns
- Enable Redis caching

**Mobile app crashes?**
- Check Expo logs: `npx expo doctor`
- Test on real device + emulator
- Check permissions in app.json

**Deployment fails?**
- Test Docker build locally first
- Check environment variables
- Read server error logs
- Rollback if needed

---

**Good luck! You've got this! 🚀**
