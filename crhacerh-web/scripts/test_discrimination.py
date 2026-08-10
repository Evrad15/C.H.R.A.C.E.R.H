"""
Test de discrimination faciale inter-personnes.
Compare chaque photo enregistrée contre toutes les autres
pour voir si le moteur SFace peut les distinguer clairement.
"""
import sys
import os
import json

base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
models_dir = os.path.join(base_dir, 'models')
faces_dir  = os.path.join(base_dir, 'storage', 'app', 'public', 'faces')
yunet_path = os.path.join(models_dir, 'face_detection_yunet_2023mar.onnx')
sface_path = os.path.join(models_dir, 'face_recognition_sface_2021dec.onnx')

MATCH_THRESHOLD = 0.55   # nouveau seuil anti-jumeaux

try:
    import cv2
    import numpy as np
except ImportError:
    print("ERREUR: OpenCV non disponible.")
    sys.exit(1)

try:
    detector   = cv2.FaceDetectorYN.create(yunet_path, "", (320, 320), 0.6, 0.3, 5000)
    recognizer = cv2.FaceRecognizerSF.create(sface_path, "")
except Exception as e:
    print(f"ERREUR chargement modèles: {e}")
    sys.exit(1)


def get_embedding(path):
    with open(path, 'rb') as f:
        img_bytes = f.read()
    img = cv2.imdecode(np.frombuffer(img_bytes, np.uint8), cv2.IMREAD_COLOR)
    if img is None:
        return None
    try:
        lab = cv2.cvtColor(img, cv2.COLOR_BGR2LAB)
        l, a, b = cv2.split(lab)
        clahe = cv2.createCLAHE(clipLimit=2.5, tileGridSize=(8, 8))
        cl = clahe.apply(l)
        img = cv2.cvtColor(cv2.merge((cl, a, b)), cv2.COLOR_LAB2BGR)
    except Exception:
        pass
    h, w, _ = img.shape
    detector.setInputSize((w, h))
    _, faces = detector.detect(img)
    if faces is None or len(faces) == 0:
        return None
    best_face = max(faces, key=lambda f: f[14])
    aligned  = recognizer.alignCrop(img, best_face)
    feature  = recognizer.feature(aligned)
    return feature[0]


def cosine(v1, v2):
    d = float(np.dot(v1, v2))
    n = float(np.linalg.norm(v1) * np.linalg.norm(v2))
    return d / n if n > 0 else 0.0


# Charger tous les embeddings
photos = {}
for fname in sorted(os.listdir(faces_dir)):
    if fname.startswith('_temp') or not fname.endswith('.jpg'):
        continue
    emp_id = fname.replace('.jpg', '')
    path   = os.path.join(faces_dir, fname)
    vec    = get_embedding(path)
    if vec is not None:
        photos[emp_id] = vec
        print(f"  [OK] Employé #{emp_id} — embedding extrait ({len(vec)} dims)")
    else:
        print(f"  [FAIL] Employé #{emp_id} — aucun visage détecté dans la photo")

print()
print("=" * 65)
print(f"  MATRICE DE SIMILARITÉ COSINUS (seuil validation : {MATCH_THRESHOLD})")
print("=" * 65)

ids = list(photos.keys())
header = "         | " + " | ".join(f"Emp#{i:>4}" for i in ids)
print(header)
print("-" * len(header))

results = []
for id_a in ids:
    row = f"Emp#{id_a:>4}  | "
    for id_b in ids:
        sim = cosine(photos[id_a], photos[id_b])
        tag = "MATCH  " if (id_a != id_b and sim >= MATCH_THRESHOLD) else ("SELF   " if id_a == id_b else "DIFF   ")
        row += f"{sim:.4f} {tag} | "
        if id_a != id_b:
            results.append((id_a, id_b, sim))
    print(row)

print()
print("=" * 65)
print("  VERDICT PAR PAIRE")
print("=" * 65)

seen = set()
for id_a, id_b, sim in results:
    pair = tuple(sorted([id_a, id_b]))
    if pair in seen:
        continue
    seen.add(pair)
    verdict = "[!] RISQUE CONFUSION" if sim >= MATCH_THRESHOLD else "[OK] DISCRIMINATION CLAIRE"
    ambig   = "[~] ZONE AMBIGUITE JUMEAUX" if (MATCH_THRESHOLD - 0.05) <= sim < MATCH_THRESHOLD else ""
    print(f"  Emp#{id_a} vs Emp#{id_b} : cos={sim:.4f}  ->  {verdict} {ambig}")

print()
print(f"  Nb personnes testees : {len(ids)}")
print(f"  Seuil de validation  : {MATCH_THRESHOLD} (cosinus SFace)")
print(f"  Seuil ambiguite      : {MATCH_THRESHOLD - 0.05:.2f} - {MATCH_THRESHOLD:.2f}")
