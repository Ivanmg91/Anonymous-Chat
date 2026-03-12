FROM python:3.12-slim

ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    && rm -rf /var/lib/apt/lists/*

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copiamos el modelo y tu script con el nombre correcto
COPY language_filter.py .
COPY spam_detector.py . 

# Ejecutamos spam_detector.py
CMD ["python", "spam_detector.py"]
