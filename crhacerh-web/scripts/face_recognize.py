import sys
import os
import json
import base64
import numpy as np

# =============================================================================
#  Moteur de reconnaissance faciale — InsightFace ArcFace
#  Modèle : buffalo_l (ArcFace R100, 512D embeddings)
#
#  ArcFace est significativement plus précis que SFace pour distinguer des
#  personnes similaires. Il produit des embeddings de 512 dimensions contre
#  128 pour SFace, avec une marge inter-classes bien supérieure.
#
#  Seuil cosinus recommandé ArcFace : >= 0.40 pour une correspondance
#  (par opposition à 0.55 pour SFace qui était déjà très permissif)
# =============================================================================

# Seuil cosinus ArcFace pour valider une correspondance
MATCH_THRESHOLD = 0.40

# Delta cosinus en dessous duquel on déclare une ambiguïté (jumeaux potentiels)
AMBIGUITY_DELTA = 0.04

# Répertoire de téléchargement/cache des modèles InsightFace
BASE_DIR    = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MODELS_DIR  = os.path.join(BASE_DIR, 'models', 'insightface')
TEMP_DIR    = os.path.join(BASE_DIR, 'temp_faces')


def get_app():
    """Charge et retourne l'instance InsightFace FaceAnalysis (ArcFace buffalo_l)."""
    try:
        import io
        import contextlib
        import logging
        logging.getLogger('insightface').setLevel(logging.ERROR)
        import insightface
        from insightface.app import FaceAnalysis
    except ImportError:
        return None, "InsightFace non disponible. Installez-le avec : pip install insightface onnxruntime"

    os.makedirs(MODELS_DIR, exist_ok=True)
    try:
        with contextlib.redirect_stdout(io.StringIO()), contextlib.redirect_stderr(io.StringIO()):
            app = FaceAnalysis(
                name='buffalo_l',              # ArcFace R100 — le plus précis
                root=MODELS_DIR,
                allowed_modules=['detection', 'recognition'],
                providers=['CPUExecutionProvider']
            )
            app.prepare(ctx_id=0, det_size=(320, 320))
        return app, None
    except Exception as e:
        return None, f"Erreur chargement InsightFace: {str(e)}"


def cosine_similarity(v1, v2):
    """Similarité cosinus entre deux vecteurs numpy."""
    import numpy as np
    d = float(np.dot(v1, v2))
    n = float(np.linalg.norm(v1) * np.linalg.norm(v2))
    return d / n if n > 0 else 0.0


def cosine_to_percent(cos_sim):
    """
    Convertit la similarité cosinus ArcFace en pourcentage affiché.
    Plage de référence ArcFace : [0.0, 0.85+]
    Seuil validation : >= 0.40 → affiché >= 95%
    """
    if cos_sim >= MATCH_THRESHOLD:
        pct = 95.0 + (cos_sim - MATCH_THRESHOLD) * (4.5 / (1.0 - MATCH_THRESHOLD))
    else:
        pct = (cos_sim / MATCH_THRESHOLD) * 94.0
    return max(0.0, min(99.5, pct))


def get_embedding(app, img_path):
    """
    Extrait le vecteur embedding ArcFace 512D depuis une image.
    Retourne le vecteur normalisé ou None si aucun visage n'est détecté.
    """
    import cv2
    import numpy as np

    try:
        with open(img_path, 'rb') as f:
            img_bytes = f.read()
        img = cv2.imdecode(np.frombuffer(img_bytes, np.uint8), cv2.IMREAD_COLOR)
    except Exception:
        return None

    if img is None:
        return None

    # Normalisation de l'éclairage via CLAHE
    try:
        lab = cv2.cvtColor(img, cv2.COLOR_BGR2LAB)
        l, a, b = cv2.split(lab)
        clahe = cv2.createCLAHE(clipLimit=2.5, tileGridSize=(8, 8))
        cl = clahe.apply(l)
        img = cv2.cvtColor(cv2.merge((cl, a, b)), cv2.COLOR_LAB2BGR)
    except Exception:
        pass

    try:
        faces = app.get(img)
    except Exception:
        return None

    if not faces:
        return None

    # Sélectionner le visage avec le plus grand score de détection
    best_face = max(faces, key=lambda f: f.det_score)
    embedding = best_face.embedding  # vecteur 512D normalisé par InsightFace

    # Normalisation L2 pour garantir la cohérence cosinus
    norm = np.linalg.norm(embedding)
    return (embedding / norm).astype(np.float32) if norm > 0 else embedding


def decode_b64_to_temp(b64_input):
    """Décode une image base64 vers un fichier temporaire."""
    import time, random
    os.makedirs(TEMP_DIR, exist_ok=True)
    try:
        raw_b64 = b64_input.split(',')[-1].strip()
        missing_padding = len(raw_b64) % 4
        if missing_padding:
            raw_b64 += '=' * (4 - missing_padding)
        img_bytes = base64.b64decode(raw_b64)
        path = os.path.join(TEMP_DIR, f'_temp_{int(time.time())}_{random.randint(1000,9999)}.jpg')
        with open(path, 'wb') as f:
            f.write(img_bytes)
        return path
    except Exception:
        return None


def run_recognition():
    if len(sys.argv) < 3:
        print(json.dumps({
            "status": "error",
            "message": "Usage: python face_recognize.py <action> <arg1> [arg2]"
        }))
        sys.exit(1)

    action = sys.argv[1]

    # Charger InsightFace
    app, err = get_app()
    if app is None:
        print(json.dumps({"status": "fallback", "message": err}))
        sys.exit(0)

    # =========================================================================
    # Action 1 : extract — Extrait l'embedding ArcFace 512D d'une image
    # =========================================================================
    if action == 'extract':
        captured_input = sys.argv[2]
        is_temp        = False
        captured_path  = captured_input

        if captured_input.startswith('data:image') or len(captured_input) > 255:
            captured_path = decode_b64_to_temp(captured_input)
            is_temp       = True

        if captured_path is None or not os.path.exists(captured_path):
            print(json.dumps({
                "status":  "error",
                "message": "Fichier image capturee introuvable ou illisible."
            }))
            sys.exit(0)

        v = get_embedding(app, captured_path)

        if is_temp and os.path.exists(captured_path):
            try:
                os.remove(captured_path)
            except Exception:
                pass

        if v is not None:
            print(json.dumps({
                "status":    "success",
                "embedding": v.tolist(),
                "dims":      len(v),
                "engine":    "insightface_arcface_512d"
            }))
        else:
            print(json.dumps({
                "status":  "error",
                "message": "Aucun visage detecte ou impossible d'extraire la signature faciale."
            }))
        return

    # =========================================================================
    # Action 2 : compare — Compare un visage contre des fichiers photos sur disque
    # =========================================================================
    elif action == 'compare':
        import numpy as np

        captured_input = sys.argv[2]
        faces_dir      = sys.argv[3]
        is_temp        = False
        captured_path  = captured_input

        if captured_input.startswith('data:image') or len(captured_input) > 255:
            captured_path = decode_b64_to_temp(captured_input)
            is_temp       = True

        if captured_path is None or not os.path.exists(captured_path):
            print(json.dumps({
                "status":  "error",
                "message": "Fichier image capturee introuvable ou illisible."
            }))
            sys.exit(0)

        v_captured = get_embedding(app, captured_path)

        if is_temp and os.path.exists(captured_path):
            try:
                os.remove(captured_path)
            except Exception:
                pass

        if v_captured is None:
            print(json.dumps({
                "status":  "error",
                "message": "Aucun visage detecte sur la capture camera."
            }))
            sys.exit(0)

        candidates = []
        for fname in os.listdir(faces_dir):
            if fname.startswith('_temp') or not fname.endswith('.jpg'):
                continue
            emp_id    = fname.replace('.jpg', '')
            cand_path = os.path.join(faces_dir, fname)
            v_cand    = get_embedding(app, cand_path)
            if v_cand is not None:
                cos_sim = cosine_similarity(v_captured, v_cand)
                candidates.append({
                    "id":             int(emp_id),
                    "cos_sim":        cos_sim,
                    "similarity_pct": cosine_to_percent(cos_sim)
                })

        candidates.sort(key=lambda c: c["cos_sim"], reverse=True)
        top1 = candidates[0] if candidates else None
        top2 = candidates[1] if len(candidates) > 1 else None

        if top1 and top1["cos_sim"] >= MATCH_THRESHOLD:
            if top2 and top2["cos_sim"] >= MATCH_THRESHOLD:
                delta = top1["cos_sim"] - top2["cos_sim"]
                if delta < AMBIGUITY_DELTA:
                    print(json.dumps({
                        "status":     "ambiguous",
                        "message":    "Ambiguite biometrique : deux profils tres similaires.",
                        "candidates": [
                            {"id": top1["id"], "similarity": round(top1["similarity_pct"], 1)},
                            {"id": top2["id"], "similarity": round(top2["similarity_pct"], 1)}
                        ],
                        "delta":  round(delta, 4),
                        "engine": "insightface_arcface"
                    }))
                    return

            print(json.dumps({
                "status":      "success",
                "matched_id":  top1["id"],
                "similarity":  round(top1["similarity_pct"], 1),
                "cos_sim":     round(top1["cos_sim"], 4),
                "engine":      "insightface_arcface"
            }))
        else:
            print(json.dumps({
                "status":          "error",
                "message":         "Visage non reconnu.",
                "best_similarity": round(top1["similarity_pct"], 1) if top1 else 0.0,
                "best_cos_sim":    round(top1["cos_sim"], 4) if top1 else 0.0
            }))
        return

    # =========================================================================
    # Action 3 : compare_vectors — Compare contre des embeddings DB passés en JSON
    # Utilisé par le PHP pour la comparaison 1:N depuis la base de données.
    # =========================================================================
    elif action == 'compare_vectors':
        captured_input   = sys.argv[2]
        candidates_input = sys.argv[3]
        is_temp          = False
        captured_path    = captured_input

        if captured_input.startswith('data:image') or len(captured_input) > 255:
            captured_path = decode_b64_to_temp(captured_input)
            is_temp       = True

        if captured_path is None or not os.path.exists(captured_path):
            print(json.dumps({
                "status":  "error",
                "message": "Fichier image capturee introuvable ou illisible."
            }))
            sys.exit(0)

        v_captured = get_embedding(app, captured_path)

        if is_temp and os.path.exists(captured_path):
            try:
                os.remove(captured_path)
            except Exception:
                pass

        if v_captured is None:
            print(json.dumps({
                "status":  "error",
                "message": "Aucun visage detecte sur la capture camera."
            }))
            sys.exit(0)

        try:
            if os.path.isfile(candidates_input):
                with open(candidates_input, 'r', encoding='utf-8') as f:
                    candidates_data = json.load(f)
            else:
                candidates_data = json.loads(candidates_input)
        except Exception as ex:
            print(json.dumps({"status": "error", "message": f"JSON candidats invalide: {str(ex)}"}))
            sys.exit(1)

        results = []
        for cand in candidates_data:
            cand_vec = np.array(cand["embedding"], dtype=np.float32)
            # Re-normaliser au cas où
            norm = np.linalg.norm(cand_vec)
            if norm > 0:
                cand_vec = cand_vec / norm
            cos_sim = cosine_similarity(v_captured, cand_vec)
            results.append({
                "id":             cand["id"],
                "type":           cand.get("type", "EMPLOYE"),
                "cos_sim":        cos_sim,
                "similarity_pct": cosine_to_percent(cos_sim)
            })

        results.sort(key=lambda c: c["cos_sim"], reverse=True)
        top1 = results[0] if results else None
        top2 = results[1] if len(results) > 1 else None

        if top1 and top1["cos_sim"] >= MATCH_THRESHOLD:
            if top2 and top2["cos_sim"] >= MATCH_THRESHOLD:
                delta = top1["cos_sim"] - top2["cos_sim"]
                if delta < AMBIGUITY_DELTA:
                    print(json.dumps({
                        "status":     "ambiguous",
                        "message":    "Ambiguite biometrique : deux profils tres similaires (jumeaux potentiels).",
                        "candidates": [
                            {
                                "id":         top1["id"],
                                "type":       top1["type"],
                                "similarity": round(top1["similarity_pct"], 1)
                            },
                            {
                                "id":         top2["id"],
                                "type":       top2["type"],
                                "similarity": round(top2["similarity_pct"], 1)
                            }
                        ],
                        "delta":  round(delta, 4),
                        "engine": "insightface_arcface_vectors"
                    }))
                    return

            print(json.dumps({
                "status":       "success",
                "matched_id":   top1["id"],
                "matched_type": top1["type"],
                "similarity":   round(top1["similarity_pct"], 1),
                "cos_sim":      round(top1["cos_sim"], 4),
                "engine":       "insightface_arcface_vectors"
            }))
        else:
            print(json.dumps({
                "status":          "error",
                "message":         "Visage non reconnu.",
                "best_similarity": round(top1["similarity_pct"], 1) if top1 else 0.0,
                "best_cos_sim":    round(top1["cos_sim"], 4) if top1 else 0.0
            }))
        return

    else:
        print(json.dumps({"status": "error", "message": f"Action inconnue : {action}"}))
        sys.exit(1)


if __name__ == '__main__':
    run_recognition()
