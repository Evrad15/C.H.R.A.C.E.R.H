import os
import urllib.request

def download_models():
    base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    models_dir = os.path.join(base_dir, 'models')
    os.makedirs(models_dir, exist_ok=True)

    yunet_url = 'https://huggingface.co/opencv/face_detection_yunet/resolve/main/face_detection_yunet_2023mar.onnx'
    sface_url = 'https://huggingface.co/opencv/face_recognition_sface/resolve/main/face_recognition_sface_2021dec.onnx'

    yunet_dest = os.path.join(models_dir, 'face_detection_yunet_2023mar.onnx')
    sface_dest = os.path.join(models_dir, 'face_recognition_sface_2021dec.onnx')

    print("Downloading binary YuNet face detection model from HuggingFace...")
    try:
        urllib.request.urlretrieve(yunet_url, yunet_dest)
        print("YuNet model downloaded successfully.")
    except Exception as e:
        print(f"Error downloading YuNet: {e}")

    print("Downloading binary SFace face recognition model from HuggingFace...")
    try:
        urllib.request.urlretrieve(sface_url, sface_dest)
        print("SFace model downloaded successfully.")
    except Exception as e:
        print(f"Error downloading SFace: {e}")

if __name__ == '__main__':
    download_models()
