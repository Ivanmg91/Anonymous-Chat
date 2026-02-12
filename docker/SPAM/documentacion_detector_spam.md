# Detector de SPAM con TensorFlow y Flask - Explicación Pedagógica

## ¿Qué es Flask y por qué lo usamos?

**Flask** es como un "camarero digital" que:
- Escucha cuando alguien le hace una petición por internet
- Procesa esa petición 
- Devuelve una respuesta

Imagínate un restaurante donde el camarero (Flask) recibe pedidos (peticiones HTTP) y trae la comida (respuestas).

---

## 1. Importación de Librerías - ¿Qué necesitamos?

```python
import tensorflow as tf
from flask import Flask, request, jsonify
```

### Analogía con la vida real:
- **TensorFlow**: Es como tener un "cerebro artificial" ya entrenado para reconocer spam
- **Flask**: Es nuestro "camarero" que atiende peticiones web
- **request**: Es como el "oído" del camarero para escuchar lo que pide el cliente
- **jsonify**: Es como el "traductor" que convierte nuestras respuestas a un formato que entienden los navegadores

---

## 2. Creando Nuestro "Camarero Digital"

```python
app = Flask(__name__)
```

### Explicación detallada:

#### ¿Qué es `Flask(__name__)`?
- **Flask**: Es la "clase" o "molde" para crear un servidor web
- **`__name__`**: Es una variable especial de Python que contiene el nombre del archivo actual

#### ¿Por qué `__name__`?
```python
# Si tu archivo se llama "spam.py", entonces:
print(__name__)  # Imprime: "__main__" (si ejecutas directamente el archivo)
                 # o "spam" (si lo importas desde otro archivo)
```

#### Analogía completa:
```python
# Es como decir: "Crea un restaurante y ponle el nombre del archivo actual"
restaurante = Flask("spam")  # Sería equivalente
app = Flask(__name__)        # Forma automática y recomendada
```

#### ¿Qué hace realmente esta línea?
1. Crea un **servidor web** que puede recibir peticiones HTTP
2. Le dice a Flask dónde encontrar archivos (templates, static files, etc.)
3. Prepara todo lo necesario para que funcione como una aplicación web

---

## 3. Los Decoradores - ¿Qué es `@app.route`?

```python
@app.route('/predict', methods=['POST'])
def predict():
    # código aquí
```

### ¿Qué es un decorador (@)?

Un **decorador** es como poner una "etiqueta" o "cartel" en una función para darle superpoderes.

#### Analogía del restaurante:
```python
# Sin decorador - función normal
def cocinar_pizza():
    return "Pizza lista"

# Con decorador - función que atiende pedidos web
@app.route('/pizza', methods=['POST'])
def cocinar_pizza():
    return "Pizza lista"
```

### Desglosando `@app.route('/predict', methods=['POST'])`:

#### 1. `@app.route` - "Asignar una dirección"
- Le dice al camarero (Flask): "Cuando alguien vaya a esta dirección, ejecuta esta función"
- Es como poner un cartel que dice: "Para pedidos de detección de spam, ir al mostrador '/predict'"

#### 2. `'/predict'` - "La dirección web"
- Es la URL donde los clientes pueden hacer peticiones
- Si tu servidor está en `localhost:5000`, la dirección completa sería: `http://localhost:5000/predict`

#### 3. `methods=['POST']` - "Tipo de petición permitida"

##### ¿Qué son los métodos HTTP?
Los métodos HTTP son como "tipos de pedidos" en un restaurante:

```python
# GET = "Quiero VER el menú" (solo leer información)
@app.route('/menu', methods=['GET'])
def mostrar_menu():
    return "Aquí está el menú"

# POST = "Quiero ENVIAR mi pedido" (enviar información)
@app.route('/pedido', methods=['POST'])
def recibir_pedido():
    return "Pedido recibido"

# PUT = "Quiero CAMBIAR mi pedido"
# DELETE = "Quiero CANCELAR mi pedido"
```

##### ¿Por qué usamos POST para el detector de spam?
- Porque estamos **enviando** texto para que lo analice
- GET sería para **ver** información, no para enviarla
- POST es perfecto para **enviar datos** y recibir una respuesta

---

## 4. Ejemplo Completo Paso a Paso

### Código simplificado:
```python
from flask import Flask, request, jsonify

# 1. Crear el "camarero digital"
app = Flask(__name__)

# 2. Definir qué hace cuando alguien va a "/hola"
@app.route('/hola', methods=['GET'])
def saludar():
    return "¡Hola mundo!"

# 3. Definir qué hace cuando alguien envía datos a "/predict"
@app.route('/predict', methods=['POST'])
def detectar_spam():
    # Recibir los datos que envió el cliente
    datos = request.get_json()
    texto = datos['texto']
    
    # Procesar (aquí iría la IA)
    if "dinero fácil" in texto:
        resultado = "SPAM"
    else:
        resultado = "HAM"
    
    # Devolver la respuesta
    return jsonify({'resultado': resultado})

# 4. Arrancar el servidor
if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
```

### ¿Cómo funciona en la práctica?

#### Paso 1: Cliente hace petición
```bash
# El cliente (PHP) envía esto:
POST http://localhost:5000/predict
Content-Type: application/json

{
    "texto": "¡Gana dinero fácil haciendo clic aquí!"
}
```

#### Paso 2: Flask recibe y procesa
1. Flask ve que alguien fue a `/predict` con método POST
2. Busca la función que tiene el decorador `@app.route('/predict', methods=['POST'])`
3. Ejecuta la función `detectar_spam()`
4. La función analiza el texto y decide si es spam

#### Paso 3: Flask devuelve respuesta
```json
{
    "resultado": "SPAM"
}
```

---

## 5. ¿Por qué `if __name__ == '__main__'`?

```python
if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
```

### Explicación simple:
- **Si ejecutas directamente este archivo** → arranca el servidor
- **Si importas este archivo desde otro** → NO arranca el servidor automáticamente

### Analogía:
```python
# Es como decir:
# "Si soy el jefe (archivo principal), abro el restaurante"
# "Si soy solo un empleado (archivo importado), espero órdenes"

if soy_el_jefe:
    abrir_restaurante()
```

---

## 6. Resumen Visual del Flujo

```
1. Cliente (PHP) → "Quiero analizar este texto"
                ↓
2. Internet → POST http://servidor:5000/predict
                ↓
3. Flask → "Alguien fue a /predict con POST, ejecuto la función"
                ↓
4. Función → Analiza el texto con IA
                ↓
5. Función → Devuelve {"resultado": "SPAM"}
                ↓
6. Cliente → Recibe la respuesta y actúa en consecuencia
```

Esta es la base de cómo funciona cualquier API REST: **recibir peticiones, procesarlas y devolver respuestas**.

---

## 3. Carga del Modelo de IA

```python
model = tf.keras.models.load_model('modelo_spam_completo.keras')
```

### Detalles importantes:
- Carga un modelo **pre-entrenado** de TensorFlow
- El archivo `.keras` contiene:
  - La arquitectura de la red neuronal
  - Los pesos entrenados
  - El preprocesador de texto (vectorización)
- Es un modelo **completo** que no necesita preprocesamiento adicional

---

## 4. Función de Predicción Individual

```python
def predecir_spam(texto):
    prediccion = model.predict(tf.constant([texto]))[0][0]
    print(texto)
    print("Prediccion: ",prediccion)
    return prediccion > 0.5
```

### Análisis paso a paso:
1. **`tf.constant([texto])`**: Convierte el texto en un tensor de TensorFlow
2. **`model.predict()`**: Ejecuta la predicción del modelo
3. **`[0][0]`**: Extrae el valor numérico de la predicción (probabilidad entre 0 y 1)
4. **`> 0.5`**: Umbral de decisión:
   - Si > 0.5 → **SPAM** (True)
   - Si ≤ 0.5 → **HAM** (False)
5. **`print()`**: Debug para ver el texto y la probabilidad calculada

---

## 5. Procesamiento de Párrafos Completos

```python
def procesar_parrafo(parrafo):
    # Dividir por puntos y limpiar frases vacías
    frases = [frase.strip() for frase in parrafo.split('.') if frase.strip()]
    
    # Verificar si alguna frase es SPAM
    for frase in frases:
        if predecir_spam(frase):
            return "SPAM"
    
    return "HAM"
```

### Lógica de procesamiento:
1. **División**: Separa el párrafo en frases usando el punto (`.`) como delimitador
2. **Limpieza**: Elimina espacios en blanco y frases vacías con `strip()`
3. **Análisis individual**: Evalúa cada frase por separado
4. **Criterio estricto**: Si **cualquier frase** es SPAM, todo el párrafo es SPAM
5. **Resultado**: Solo devuelve "HAM" si **todas** las frases son legítimas

---

## 6. Endpoint de la API REST

```python
@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json()
    if not data or 'texto' not in data:
        return jsonify({'error': 'Campo texto requerido'}), 400
    
    resultado = procesar_parrafo(data['texto'])
    return jsonify({'resultado': resultado})
```

### Funcionamiento del endpoint:
1. **Ruta**: `POST /predict`
2. **Validación**: Verifica que el JSON contenga el campo `texto`
3. **Error 400**: Si faltan datos requeridos
4. **Procesamiento**: Llama a `procesar_parrafo()` con el texto recibido
5. **Respuesta**: JSON con el resultado (`SPAM` o `HAM`)

### Ejemplo de uso:
```bash
curl -X POST http://localhost:5000/predict \
  -H "Content-Type: application/json" \
  -d '{"texto": "¡Gana dinero fácil! Haz clic aquí."}'
```

**Respuesta esperada:**
```json
{"resultado": "SPAM"}
```

---

## 7. Servidor de Desarrollo

```python
if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
```

### Configuración del servidor:
- **`host='0.0.0.0'`**: Acepta conexiones desde cualquier IP (necesario para Docker)
- **`port=5000`**: Puerto interno del contenedor
- **Solo desarrollo**: No usar en producción (usar Gunicorn o similar)

---

## Flujo Completo de Ejecución

1. **Inicio**: Flask carga el modelo de IA al arrancar
2. **Petición**: Cliente envía POST con texto a `/predict`
3. **Validación**: Se verifica que el JSON tenga el campo `texto`
4. **División**: El texto se separa en frases por puntos
5. **Análisis**: Cada frase se evalúa individualmente con el modelo
6. **Decisión**: Si alguna frase es SPAM, todo el texto es SPAM
7. **Respuesta**: Se devuelve el resultado en JSON

---

## Integración con el Chat PHP

El script PHP consulta este detector así:

```php
$url = 'http://spam_detector:5000/predict';
$data = json_encode(['texto' => $mensaje_usuario]);
// ... envía POST y recibe respuesta
```

Esto permite que cada mensaje del chat se analice automáticamente antes de guardarse en la base de datos.
