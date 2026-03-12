# Chat Denuncias (Anonymous Chat)

Un sistema de chat anónimo entre dos personas. Ideal para situaciones como un chat anónimo para jóvenes que sufren acoso.

## Estructura del Proyecto

```text
/chat-denuncias
├── frontend/              # Interfaz de usuario web
│   ├── css/              # Estilos CSS
│   ├── js/               # Scripts JavaScript (app.js, chat.js, chat_profesor.js)
│   └── index.html        # Página de inicio/login
│
├── backend/              # Lógica del servidor
│   ├── api/              # Endpoints API
│   │   ├── login.php
│   │   ├── anonymous_login.php
│   │   ├── logout.php
│   │   ├── leer.php
│   │   ├── enviar.php
│   │   └── get_users.php
│   ├── config/           # Configuración de base de datos
│   │   ├── db.php
│   │   ├── credentials.example.php
│   │   └── credentials.local.php  # Local, ignorado por git
│   └── controllers/      # Controladores de vistas
│       ├── welcome.php
│       ├── student_view.php
│       └── teacher_dashboard.php
│
├── python-ia/           # Módulos de inteligencia artificial
│   ├── spam_detector.py    # Detector de spam con modelo Keras
│   ├── language_filter.py  # Filtro de lenguaje (placeholder)
│   ├── requirements.txt    # Dependencias Python
│   └── Dockerfile         # Imagen Docker para Python
│
├── sql/                 # Scripts de base de datos
│   └── database.sql     # Inicialización de BD
│
├── docker/              # Configuración Docker
│   ├── Dockerfile       # Imagen PHP + Apache
│   └── docker-compose.yml  # Orquestación de contenedores
│
└── README.md           # Este archivo
```

## Requisitos

- PHP 8.2+
- Docker y Docker Compose (recomendado)
- MySQL 8.0+
- Python 3.12+ (para AI modules)

## Instalación y Ejecución

### Opción 1: Con Docker (Recomendado)

```bash
cd docker
cp .env.example .env
docker compose up -d --build
```

Esto iniciará:

- **MySQL Database**: `localhost:3306`
- **PHP Apache**: `http://localhost:8080/frontend/index.html`
- **Spam Detector (Python)**: `http://localhost:5001`
- **phpMyAdmin**: `http://localhost:8081`

Las credenciales quedan en `docker/.env`, que no se versiona. Puedes dejar los valores del ejemplo para desarrollo local o cambiarlos antes de levantar el stack.

### Acceso a phpMyAdmin

Si levantas el entorno con Docker, puedes entrar en phpMyAdmin desde:

- URL: `http://localhost:8081`
- Servidor: `db`
- Usuario recomendado: el que hayas definido en `docker/.env`
- Contraseña: la que hayas definido en `docker/.env`

Tambien puedes acceder con el usuario definido para la aplicacion en `docker/.env`.

Para la administracion de MySQL conviene usar `root` solo en phpMyAdmin o terminal. La aplicacion PHP deberia usar un usuario dedicado con permisos limitados sobre la base de datos `login`.

### Opción 2: Configuración Local

Requiere PHP 8.2 con PDO_MySQL:

```bash
# Estando en la raíz del proyecto
php -S localhost:8000
```

Accede a: `http://localhost:8000/frontend/index.html`

## Base de Datos

La base de datos se inicializa automáticamente con:

- Tabla `usuarios` (user, password, role: admin|profesor|alumno)
- Tabla `mensajes` (id, user, chat_room, message, date)

Usuarios de prueba:

- Usuario: `test` | Contraseña: `password` | Rol: admin
- Usuario: `profe` | Contraseña: `password` | Rol: profesor

## Funcionalidades

- ✅ Chat anónimo entre estudiantes y profesores
- ✅ Autenticación con roles (alumno, profesor, admin)
- ✅ Sesiones seguras
- ✅ Detector de spam integrado (Python + TensorFlow)
- ✅ Interface responsiva
- ✅ Módulo para filtrado de lenguaje

## API Endpoints

El frontend se comunica con los siguientes endpoints:

- `POST /backend/api/login.php` - Autenticar usuario
- `POST /backend/api/anonymous_login.php` - Crear cuenta anónima
- `GET /backend/api/leer.php` - Leer mensajes
- `POST /backend/api/enviar.php` - Enviar mensaje
- `GET /backend/api/get_users.php` - Obtener lista de usuarios (solo profesores)
- `GET /backend/api/logout.php` - Cerrar sesión

## Configuración (Importante para las credenciales)

La aplicación busca primero variables de entorno (`DB_HOST`, `DB_NAME`, `DB_PORT`, `DB_USER`, `DB_PASS`).
Si no existen, usa `backend/config/credentials.local.php`, que está ignorado por git.

En Docker no hace falta tocar PHP: `docker/docker-compose.yml` ya inyecta esas variables en el contenedor `php` a partir de `docker/.env`.

Puedes crear `backend/config/credentials.local.php` copiando `backend/config/credentials.example.php`:

```bash
cp backend/config/credentials.example.php backend/config/credentials.local.php
```

Despues, edita `backend/config/credentials.local.php` y rellena tus datos:

```php
$db_host = 'tu_host';
$db_name = 'tu_db';
$db_port = 'tu_puerto';
$db_user = 'tu_usuario';
$db_pass = 'tu_contraseña';
```

Si vas a usar Docker, los valores deben coincidir con lo que tengas en `docker/.env`. Normalmente quedaria asi:

```php
$db_host = 'db';
$db_name = 'login';
$db_port = '3306';
$db_user = 'chat_app';
$db_pass = 'el_mismo_valor_de_MYSQL_PASSWORD';
```

Si ejecutas PHP fuera de Docker y te conectas al MySQL publicado en tu maquina, normalmente tendras que usar:

```php
$db_host = '127.0.0.1';
$db_name = 'login';
$db_port = '3306';
$db_user = 'root';
$db_pass = 'password';
```

## Seguridad

- ✅ Contraseñas hasheadas con `password_hash()`
- ✅ Sesiones seguras (HttpOnly, SameSite=Strict)
- ✅ Validación de roles en APIs
- ✅ Protección CSRF mediante sesiones

## Notas de Desarrollo

- Para usar el detector de spam, necesitas copiar el modelo `modelo_spam_completo.keras` a `python-ia/`
- Los scripts JavaScript usan Fetch API con polling cada 2 segundos
- El streaming de mensajes es en tiempo real mediante lecturas periódicas
