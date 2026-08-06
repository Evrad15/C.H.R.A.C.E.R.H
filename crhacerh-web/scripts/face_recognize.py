import sys
import os
import json
import base64

def run_recognition():
    if len(sys.argv) < 3:
        print(json.dumps({"status": "error", "message": "Usage: python face_recognize.py <action> <arg1> [arg2]"}))
        sys.exit(1)

    action = sys.argv[1]
    
    try:
        import cv2
        import numpy as np
    except ImportError:
        print(json.dumps({"status": "fallback", "message": "OpenCV or Numpy not available"}))
        sys.exit(0)

    # Accents-free paths for models
    models_dir = 'C:/Users/SOLUTION TECH/.gemini/antigravity-ide/models'
    yunet_path = os.path.join(models_dir, 'face_detection_yunet_2023mar.onnx')
    sface_path = os.path.join(models_dir, 'face_recognition_sface_2021dec.onnx')

    temp_dir = 'C:/Users/SOLUTION TECH/.gemini/antigravity-ide/temp_faces'
    os.makedirs(temp_dir, exist_ok=True)

    try:
        detector = cv2.FaceDetectorYN.create(yunet_path, "", (320, 320), 0.6, 0.3, 5000)
        recognizer = cv2.FaceRecognizerSF.create(sface_path, "")
    except Exception as e:
        print(json.dumps({"status": "fallback", "message": f"Failed to load ONNX models: {str(e)}"}))
        sys.exit(0)

    def get_embedding(img_path):
        try:
            with open(img_path, 'rb') as f:
                img_bytes = f.read()
            img = cv2.imdecode(np.frombuffer(img_bytes, np.uint8), cv2.IMREAD_COLOR)
        except Exception:
            return None

        if img is None:
            return None

        # Preprocess illumination using CLAHE to handle low-light or shadow conditions
        try:
            lab = cv2.cvtColor(img, cv2.COLOR_BGR2LAB)
            l, a, b = cv2.split(lab)
            clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
            cl = clahe.apply(l)
            limg = cv2.merge((cl, a, b))
            img = cv2.cvtColor(limg, cv2.COLOR_LAB2BGR)
        except Exception:
            pass

        h, w, _ = img.shape
        detector.setInputSize((w, h))
        _, faces = detector.detect(img)
        if faces is None or len(faces) == 0:
            return None
        aligned = recognizer.alignCrop(img, faces[0])
        feature = recognizer.feature(aligned)
        return feature[0]

    # Action 1: Extract 128D SFace features
    if action == 'extract':
        captured_input = sys.argv[2]
        captured_path = captured_input
        is_temp = False
        if captured_input.startswith('data:image') or len(captured_input) > 255:
            import time, random
            raw_b64 = captured_input.split(',')[-1]
            img_bytes = base64.b64decode(raw_b64)
            captured_path = os.path.join(temp_dir, f'_temp_ext_{int(time.time())}_{random.randint(1000,9999)}.jpg')
            with open(captured_path, 'wb') as f:
                f.write(img_bytes)
            is_temp = True

        v = get_embedding(captured_path)
        
        # Cleanup
        if is_temp and os.path.exists(captured_path):
            try:
                os.remove(captured_path)
            except Exception:
                pass

        if v is not None:
            print(json.dumps({
                "status": "success",
                "embedding": v.tolist()
            }))
        else:
            print(json.dumps({
                "status": "error",
                "message": "Aucun visage détecté ou impossible d'extraire la signature."
            }))
        return

    # Action 2: Compare captured face with DB templates or files on disk
    elif action == 'compare':
        captured_input = sys.argv[2]
        faces_dir = sys.argv[3]

        captured_path = captured_input
        is_temp = False
        if captured_input.startswith('data:image') or len(captured_input) > 255:
            import time, random
            raw_b64 = captured_input.split(',')[-1]
            img_bytes = base64.b64decode(raw_b64)
            captured_path = os.path.join(temp_dir, f'_temp_cmp_{int(time.time())}_{random.randint(1000,9999)}.jpg')
            with open(captured_path, 'wb') as f:
                f.write(img_bytes)
            is_temp = True

        v_captured = get_embedding(captured_path)
        
        # Cleanup
        if is_temp and os.path.exists(captured_path):
            try:
                os.remove(captured_path)
            except Exception:
                pass

        if v_captured is None:
            print(json.dumps({
                "status": "error",
                "message": "Aucun visage détecté sur la capture caméra."
            }))
            sys.exit(0)

        best_id = None
        best_similarity = 0.0

        for fname in os.listdir(faces_dir):
            if fname.startswith('_temp') or not fname.endswith('.jpg'):
                continue

            emp_id_str = fname.replace('.jpg', '')
            cand_path = os.path.join(faces_dir, fname)
            
            v_cand = get_embedding(cand_path)
            if v_cand is not None:
                # Cosine similarity using SFace model features
                cos_sim = np.dot(v_captured, v_cand) / (np.linalg.norm(v_captured) * np.linalg.norm(v_cand))
                
                # SFace threshold is typically >= 0.36
                # Map SFace cosine range [0.15, 0.80] to match display threshold [60%, 100%]
                if cos_sim >= 0.36:
                    similarity_pct = 95.0 + (cos_sim - 0.36) * 10.0
                else:
                    similarity_pct = (cos_sim / 0.36) * 94.0
                
                similarity_pct = max(0.0, min(99.4, similarity_pct))

                if cos_sim >= 0.36 and similarity_pct > best_similarity:
                    best_similarity = similarity_pct
                    best_id = int(emp_id_str)

        if best_id and best_similarity >= 95.0:
            print(json.dumps({
                "status": "success",
                "matched_id": best_id,
                "similarity": round(best_similarity, 1),
                "engine": "opencv_sface_deeplearning"
            }))
        else:
            print(json.dumps({
                "status": "error",
                "message": "Visage non reconnu.",
                "best_similarity": round(best_similarity, 1)
            }))
        return

if __name__ == '__main__':
    run_recognition()
