import sys
import os
import json
import base64
import time
import random
import logging
import io
import contextlib
from http.server import HTTPServer, BaseHTTPRequestHandler
import numpy as np

# Configuration
MATCH_THRESHOLD = 0.40
AMBIGUITY_DELTA = 0.04
BASE_DIR        = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MODELS_DIR      = os.path.join(BASE_DIR, 'models', 'insightface')
TEMP_DIR        = os.path.join(BASE_DIR, 'temp_faces')
PORT            = 5001
HOST            = '127.0.0.1'

os.makedirs(TEMP_DIR, exist_ok=True)
logging.getLogger('insightface').setLevel(logging.ERROR)

print("[AI Daemon] Chargement des modeles ArcFace InsightFace en memoire...")
import insightface
from insightface.app import FaceAnalysis

with contextlib.redirect_stdout(io.StringIO()), contextlib.redirect_stderr(io.StringIO()):
    app = FaceAnalysis(
        name='buffalo_l',
        root=MODELS_DIR,
        allowed_modules=['detection', 'recognition'],
        providers=['CPUExecutionProvider']
    )
    app.prepare(ctx_id=0, det_size=(320, 320))

print(f"[AI Daemon] Modeles prets ! Serveur biometrique ultra-rapide sur http://{HOST}:{PORT}")


def cosine_similarity(v1, v2):
    d = float(np.dot(v1, v2))
    n = float(np.linalg.norm(v1) * np.linalg.norm(v2))
    return d / n if n > 0 else 0.0


def cosine_to_percent(cos_sim):
    if cos_sim >= MATCH_THRESHOLD:
        pct = 95.0 + (cos_sim - MATCH_THRESHOLD) * (4.5 / (1.0 - MATCH_THRESHOLD))
    else:
        pct = (cos_sim / MATCH_THRESHOLD) * 94.0
    return max(0.0, min(99.5, pct))


def decode_b64_to_temp(b64_input):
    try:
        raw_b64 = b64_input.split(',')[-1].strip()
        missing_padding = len(raw_b64) % 4
        if missing_padding:
            raw_b64 += '=' * (4 - missing_padding)
        img_bytes = base64.b64decode(raw_b64)
        path = os.path.join(TEMP_DIR, f'_daemon_{int(time.time()*1000)}_{random.randint(1000,9999)}.jpg')
        with open(path, 'wb') as f:
            f.write(img_bytes)
        return path
    except Exception:
        return None


def get_embedding(img_path):
    import cv2
    if not img_path or not os.path.exists(img_path):
        return None
    try:
        img = cv2.imread(img_path)
        if img is None:
            return None
        faces = app.get(img)
        if not faces:
            return None
        best_face = max(faces, key=lambda f: f.det_score)
        embedding = best_face.embedding
        norm = np.linalg.norm(embedding)
        return (embedding / norm).astype(np.float32) if norm > 0 else embedding
    except Exception:
        return None


class BiometricHandler(BaseHTTPRequestHandler):
    def _set_headers(self, status=200):
        self.send_response(status)
        self.send_header('Content-Type', 'application/json')
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        self.send_header('Access-Control-Allow-Headers', 'Content-Type')
        self.end_headers()

    def do_OPTIONS(self):
        self._set_headers(200)

    def do_GET(self):
        if self.path == '/ping':
            self._set_headers(200)
            self.wfile.write(json.dumps({"status": "ok", "engine": "insightface_arcface_daemon_512d"}).encode('utf-8'))
        else:
            self._set_headers(404)
            self.wfile.write(json.dumps({"status": "error", "message": "Route non trouvee."}).encode('utf-8'))

    def do_POST(self):
        content_length = int(self.headers.get('Content-Length', 0))
        body = self.rfile.read(content_length).decode('utf-8')

        try:
            data = json.loads(body) if body else {}
        except Exception:
            self._set_headers(400)
            self.wfile.write(json.dumps({"status": "error", "message": "JSON invalide."}).encode('utf-8'))
            return

        # 1. Action : Extract
        if self.path == '/extract':
            img_input = data.get('image') or data.get('image_path')
            if not img_input:
                self._set_headers(400)
                self.wfile.write(json.dumps({"status": "error", "message": "Parametre image manquant."}).encode('utf-8'))
                return

            is_temp = False
            img_path = img_input
            if img_input.startswith('data:image') or len(img_input) > 255:
                img_path = decode_b64_to_temp(img_input)
                is_temp = True

            v = get_embedding(img_path)
            if is_temp and img_path and os.path.exists(img_path):
                try: os.remove(img_path)
                except Exception: pass

            if v is not None:
                self._set_headers(200)
                self.wfile.write(json.dumps({
                    "status": "success",
                    "embedding": v.tolist(),
                    "dims": len(v),
                    "engine": "insightface_arcface_512d"
                }).encode('utf-8'))
            else:
                self._set_headers(200)
                self.wfile.write(json.dumps({
                    "status": "error",
                    "message": "Aucun visage detecte sur la capture."
                }).encode('utf-8'))

        # 2. Action : Compare Vectors (1:N ou 1:1)
        elif self.path == '/compare_vectors':
            img_input = data.get('image') or data.get('image_path')
            candidates = data.get('candidates')
            candidates_path = data.get('candidates_path')

            if candidates_path and os.path.isfile(candidates_path):
                with open(candidates_path, 'r', encoding='utf-8') as f:
                    candidates = json.load(f)

            if not img_input or candidates is None:
                self._set_headers(400)
                self.wfile.write(json.dumps({"status": "error", "message": "Parametres image ou candidates manquants."}).encode('utf-8'))
                return

            is_temp = False
            img_path = img_input
            if img_input.startswith('data:image') or len(img_input) > 255:
                img_path = decode_b64_to_temp(img_input)
                is_temp = True

            v_captured = get_embedding(img_path)
            if is_temp and img_path and os.path.exists(img_path):
                try: os.remove(img_path)
                except Exception: pass

            if v_captured is None:
                self._set_headers(200)
                self.wfile.write(json.dumps({
                    "status": "error",
                    "message": "Aucun visage detecte sur la capture camera."
                }).encode('utf-8'))
                return

            results = []
            for cand in candidates:
                cand_vec = np.array(cand["embedding"], dtype=np.float32)
                norm = np.linalg.norm(cand_vec)
                if norm > 0:
                    cand_vec = cand_vec / norm
                cos_sim = cosine_similarity(v_captured, cand_vec)
                results.append({
                    "id": cand["id"],
                    "type": cand.get("type", "EMPLOYE"),
                    "cos_sim": cos_sim,
                    "similarity_pct": cosine_to_percent(cos_sim)
                })

            results.sort(key=lambda c: c["cos_sim"], reverse=True)
            top1 = results[0] if results else None
            top2 = results[1] if len(results) > 1 else None

            if top1 and top1["cos_sim"] >= MATCH_THRESHOLD:
                if top2 and top2["cos_sim"] >= MATCH_THRESHOLD:
                    delta = top1["cos_sim"] - top2["cos_sim"]
                    if delta < AMBIGUITY_DELTA:
                        self._set_headers(200)
                        self.wfile.write(json.dumps({
                            "status": "ambiguous",
                            "message": "Ambiguite biometrique : deux profils tres similaires.",
                            "candidates": [
                                {"id": top1["id"], "similarity": round(top1["similarity_pct"], 1)},
                                {"id": top2["id"], "similarity": round(top2["similarity_pct"], 1)}
                            ],
                            "delta": round(delta, 4),
                            "engine": "insightface_arcface"
                        }).encode('utf-8'))
                        return

                self._set_headers(200)
                self.wfile.write(json.dumps({
                    "status": "success",
                    "matched_id": top1["id"],
                    "similarity": round(top1["similarity_pct"], 1),
                    "cos_sim": round(top1["cos_sim"], 4),
                    "engine": "insightface_arcface"
                }).encode('utf-8'))
            else:
                self._set_headers(200)
                self.wfile.write(json.dumps({
                    "status": "error",
                    "message": "Visage non reconnu.",
                    "best_similarity": round(top1["similarity_pct"], 1) if top1 else 0.0,
                    "best_cos_sim": round(top1["cos_sim"], 4) if top1 else 0.0
                }).encode('utf-8'))

        else:
            self._set_headers(404)
            self.wfile.write(json.dumps({"status": "error", "message": "Action inconnue."}).encode('utf-8'))

    def log_message(self, format, *args):
        # Silence standard HTTP logs for maximum speed
        pass


def run_daemon():
    server = HTTPServer((HOST, PORT), BiometricHandler)
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        pass
    finally:
        server.server_close()


if __name__ == '__main__':
    run_daemon()
