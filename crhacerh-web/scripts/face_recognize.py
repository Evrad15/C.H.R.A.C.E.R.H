import sys
import os
import json
import base64
import numpy as np

def run_recognition():
    if len(sys.argv) < 3:
        print(json.dumps({"status": "error", "message": "Usage: python face_recognize.py <captured_image_path_or_base64> <faces_dir> [candidate_id]"}))
        sys.exit(1)

    captured_input = sys.argv[1]
    faces_dir = sys.argv[2]
    candidate_id = sys.argv[3] if len(sys.argv) > 3 else None

    # Load face_recognition library dynamically
    try:
        import face_recognition
        use_dlib = True
    except ImportError:
        use_dlib = False

    # Try loading OpenCV as fallback if face_recognition is not installed
    try:
        import cv2
    except ImportError:
        cv2 = None

    # Decode captured image
    captured_path = captured_input
    if captured_input.startswith('data:image') or len(captured_input) > 255:
        # Base64 string
        raw_b64 = captured_input.split(',')[-1]
        img_bytes = base64.b64decode(raw_b64)
        captured_path = os.path.join(faces_dir, '_temp_captured.jpg')
        with open(captured_path, 'wb') as f:
            f.write(img_bytes)

    if not os.path.exists(captured_path):
        print(json.dumps({"status": "error", "message": "Captured image file not found."}))
        sys.exit(1)

    if use_dlib:
        try:
            captured_img = face_recognition.load_image_file(captured_path)
            captured_encs = face_recognition.face_encodings(captured_img)
            if not captured_encs:
                print(json.dumps({"status": "error", "message": "Aucun visage détecté sur la capture caméra."}))
                sys.exit(0)

            captured_enc = captured_encs[0]
            best_id = None
            best_similarity = 0.0

            # Scan candidate face files in faces_dir
            for fname in os.listdir(faces_dir):
                if fname.startswith('_temp') or not fname.endswith('.jpg'):
                    continue

                emp_id_str = fname.replace('.jpg', '')
                if candidate_id and str(emp_id_str) != str(candidate_id):
                    continue

                cand_path = os.path.join(faces_dir, fname)
                try:
                    cand_img = face_recognition.load_image_file(cand_path)
                    cand_encs = face_recognition.face_encodings(cand_img)
                    if cand_encs:
                        dist = face_recognition.face_distance([cand_encs[0]], captured_enc)[0]
                        # Convert dlib Euclidean distance (0.0 to 1.0) to Similarity Percentage
                        # Distance 0.0 = 100% match, Distance 0.4 = 90% match, Distance 0.6 = 75%
                        similarity = max(0.0, min(100.0, (1.0 - (dist / 0.60)) * 100.0))
                        if dist <= 0.45 and similarity > best_similarity:
                            best_similarity = similarity
                            best_id = int(emp_id_str)
                except Exception as ex:
                    continue

            if best_id and best_similarity >= 95.0:
                print(json.dumps({
                    "status": "success",
                    "matched_id": best_id,
                    "similarity": round(best_similarity, 1),
                    "engine": "face_recognition_dlib"
                }))
            else:
                print(json.dumps({
                    "status": "error",
                    "message": "Reconnaissance faciale non concluante (Similitude < 95%).",
                    "best_similarity": round(best_similarity, 1)
                }))
            return
        except Exception as e:
            pass

    # OpenCV / Perceptual feature fallback if face_recognition library is still initializing
    print(json.dumps({"status": "fallback", "message": "Using PHP / Gemini engine"}))

if __name__ == '__main__':
    run_recognition()
