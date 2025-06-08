from flask import Flask, request, jsonify
import os
import cv2
import numpy as np
import face_recognition
import pickle
from datetime import datetime

app = Flask(__name__)
DATASET_DIR = os.path.join(os.getcwd(), "public", "storage/dataset_faces")
MODEL_PATH = "model/face_encodings.pkl"
os.makedirs(DATASET_DIR, exist_ok=True)
os.makedirs("model", exist_ok=True)


# Helper: encode all images from dataset
def train_model():
    known_encodings = []
    known_names = []

    for name in os.listdir(DATASET_DIR):
        person_dir = os.path.join(DATASET_DIR, name)
        if not os.path.isdir(person_dir):
            continue

        for img_name in os.listdir(person_dir):
            img_path = os.path.join(person_dir, img_name)
            img = face_recognition.load_image_file(img_path)
            face_locations = face_recognition.face_locations(img)
            if face_locations:
                encoding = face_recognition.face_encodings(img, face_locations)[0]
                known_encodings.append(encoding)
                known_names.append(name)

    # Save model
    with open(MODEL_PATH, "wb") as f:
        pickle.dump({"encodings": known_encodings, "names": known_names}, f)

    return len(known_names)


@app.route("/train", methods=["POST"])
def train():
    total_trained = train_model()  # pakai fungsi yang sudah ada
    return jsonify({"message": "Training ulang selesai", "total_faces": total_trained})


@app.route("/predict", methods=["POST"])
def absen():
    if not os.path.exists(MODEL_PATH):
        return jsonify({"error": "Model not trained yet"}), 400

    if "image" not in request.files:
        return jsonify({"error": "Image file is required"}), 400

    file = request.files["image"]
    img_array = np.frombuffer(file.read(), np.uint8)
    img = cv2.imdecode(img_array, cv2.IMREAD_COLOR)

    with open(MODEL_PATH, "rb") as f:
        data = pickle.load(f)

    face_locations = face_recognition.face_locations(img)
    face_encodings = face_recognition.face_encodings(img, face_locations)

    for face_encoding in face_encodings:
        matches = face_recognition.compare_faces(data["encodings"], face_encoding)
        if True in matches:
            match_index = matches.index(True)
            name = data["names"][match_index]
            return jsonify(
                {
                    "message": f"Wajah dikenali sebagai {name}",
                    "status": "Absen Berhasil",
                    "waktu": datetime.now().isoformat(),
                    "user_id": match_index + 1,  # kamu bisa ganti sesuai kebutuhan
                }
            )

    return jsonify({"message": "Wajah tidak dikenali", "status": "Gagal"}), 401


if __name__ == "__main__":
    app.run(debug=True)
