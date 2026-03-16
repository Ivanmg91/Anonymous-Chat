import os
import re
import logging

import tensorflow as tf
from flask import Flask, request, jsonify

app = Flask(__name__)
logging.basicConfig(level=logging.INFO)

MODEL_PATH = os.getenv("SPAM_MODEL_PATH", "modelo_spam_completo.keras")
SPAM_THRESHOLD = float(os.getenv("SPAM_THRESHOLD", "0.5"))
MAX_TEXT_LEN = int(os.getenv("SPAM_MAX_TEXT_LEN", "2000"))

model = None
model_error = None


def cargar_modelo():
    global model, model_error
    try:
        model = tf.keras.models.load_model(MODEL_PATH)
        model_error = None
        app.logger.info("Modelo cargado correctamente desde: %s", MODEL_PATH)
    except Exception as e:
        model = None
        model_error = str(e)
        app.logger.exception("No se pudo cargar el modelo: %s", e)


def normalizar_texto(texto):
    texto = texto.strip()
    texto = re.sub(r"\s+", " ", texto)
    if len(texto) > MAX_TEXT_LEN:
        texto = texto[:MAX_TEXT_LEN]
    return texto


def dividir_frases(parrafo):
    # Divide por fin de frase y elimina vacios
    frases = [f.strip() for f in re.split(r"[.!?\n;]+", parrafo) if f.strip()]
    return frases if frases else [parrafo]


def predecir_probabilidad(texto):
    # El modelo exportado acepta string directo (incluye vectorizacion)
    pred = model.predict(tf.constant([texto]), verbose=0)
    return float(pred[0][0])


def clasificar_parrafo(parrafo):
    frases = dividir_frases(parrafo)
    score_max = 0.0

    for frase in frases:
        score = predecir_probabilidad(frase)
        if score > score_max:
            score_max = score
        if score >= SPAM_THRESHOLD:
            return "SPAM", score_max

    return "HAM", score_max


@app.get("/health")
def health():
    if model is None:
        return jsonify({
            "status": "error",
            "ready": False,
            "model_loaded": False,
            "error": model_error
        }), 503

    return jsonify({
        "status": "ok",
        "ready": True,
        "model_loaded": True,
        "threshold": SPAM_THRESHOLD,
        "model_path": MODEL_PATH
    }), 200


@app.post("/predict")
def predict():
    if model is None:
        return jsonify({
            "error": "Modelo no disponible",
            "details": model_error
        }), 503

    data = request.get_json(silent=True)
    if not data or "texto" not in data:
        return jsonify({"error": "Campo 'texto' requerido"}), 400

    texto = str(data.get("texto", ""))
    texto = normalizar_texto(texto)

    if not texto:
        return jsonify({"error": "El campo 'texto' no puede estar vacio"}), 400

    resultado, score_max = clasificar_parrafo(texto)

    return jsonify({
        "resultado": resultado,
        "score_max": round(score_max, 6),
        "threshold": SPAM_THRESHOLD
    }), 200


if __name__ == "__main__":
    cargar_modelo()
    port = int(os.getenv("PORT", "5000"))
    app.run(host="0.0.0.0", port=port)