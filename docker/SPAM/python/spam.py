import tensorflow as tf
from flask import Flask, request, jsonify

app = Flask(__name__)

# Cargar el modelo completo (incluye la vectorización)
model = tf.keras.models.load_model('modelo_spam_completo.keras')

def predecir_spam(texto):
    prediccion = model.predict(tf.constant([texto]))[0][0]
    print(texto)
    print("Prediccion: ",prediccion)
    return prediccion > 0.5

def procesar_parrafo(parrafo):
    # Dividir por puntos y limpiar frases vacías
    frases = [frase.strip() for frase in parrafo.split('.') if frase.strip()]
    
    # Verificar si alguna frase es SPAM
    for frase in frases:
        if predecir_spam(frase):
            return "SPAM"
    
    return "HAM"

@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json()
    if not data or 'texto' not in data:
        return jsonify({'error': 'Campo texto requerido'}), 400
    
    resultado = procesar_parrafo(data['texto'])
    return jsonify({'resultado': resultado})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)