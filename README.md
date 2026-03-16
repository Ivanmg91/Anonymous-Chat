# Chat Denuncias (Anonymous Chat)

Aplicación web de chat anónimo entre alumnado y profesorado. El proyecto está implementado con PHP, MySQL y una interfaz web con JavaScript nativo. Incluye además un módulo Python para detección de spam, pero ese servicio no forma parte del flujo activo de la aplicación en su estado actual.

## Estado actual del proyecto

- El acceso principal funciona desde la web en PHP.
- El alumnado puede entrar de forma anónima y abrir un chat propio.
- El profesorado puede ver conversaciones activas y responder por sala.
- El rol `admin` tiene panel funcional para alta, listado y borrado de usuarios `profesor` y `alumno`.
- El panel `admin` también permite listar chats profesor-alumno, ver su historial y cambiar su estado a cualquier valor permitido en base de datos.
- Profesor puede cambiar el estado de una sala (`revision`, `finalizado`) desde el flujo de gestión. Admin puede cambiar a todos los estados.
- El detector de spam en Python está preparado de forma independiente, pero no está conectado al frontend ni al backend PHP.
- En `docker/docker-compose.yml` el contenedor Python está comentado, por lo que el stack actual levanta solo MySQL, PHP/Apache y phpMyAdmin.

## Estructura del proyecto

```text
/Anonymous-Chat-
├── index.php                       # Redirección a frontend/index.html
├── README.md
├── backend/
│   ├── api/
│   │   ├── admin_chats.php         # Gestión de chats para admin (listado, historial y cambio de estado)
│   │   ├── admin_users.php         # CRUD básico de usuarios para panel admin
│   │   ├── anonymous_login.php     # Alta anónima y login automático
│   │   ├── change_chat_state.php   # Cambio de estado de salas
│   │   ├── delete_chat.php         # Eliminación de usuario/sesión actual
│   │   ├── enviar.php              # Envío de mensajes
│   │   ├── get_users.php           # Lista de chats para profesor/admin
│   │   ├── leer.php                # Lectura de mensajes por sala
│   │   ├── login.php               # Login normal
│   │   └── logout.php              # Cierre de sesión
│   ├── config/
│   │   ├── credentials.example.php # Plantilla de credenciales locales
│   │   ├── credentials.local.php   # Credenciales locales, ignorado por git
│   │   └── db.php                  # Conexión PDO por variables de entorno o archivo local
│   └── controllers/
│       ├── admin_dashboard.php     # Panel de administración
│       ├── student_view.php        # Vista de chat para alumnado
│       ├── teacher_dashboard.php   # Panel de conversaciones del profesorado
│       └── welcome.php             # Router por rol tras autenticación
├── docker/
│   ├── .env.example                # Variables de entorno de ejemplo para Docker
│   ├── docker-compose.yml
│   └── Dockerfile                  # PHP 8.2 + Apache + extensiones MySQL
├── frontend/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   ├── admin_panel.js          # Gestión de usuarios y chats desde panel admin
│   │   ├── app.js                  # Login desde la pantalla inicial
│   │   ├── chat.js                 # Chat del alumnado
│   │   └── chat_profesor.js        # Panel y chat del profesorado
│   └── index.html                  # Pantalla inicial
├── python-ia/
│   ├── Dockerfile
│   ├── language_filter.py          # Placeholder, sin integración actual
│   ├── requirements.txt
│   └── spam_detector.py            # API Flask para predicción de spam
└── sql/
    └── database.sql                # Inicialización de base de datos y datos de prueba
```

## Requisitos

### Para ejecutar la aplicación web

- Docker y Docker Compose, recomendado
- PHP 8.2+ con `pdo_mysql`
- MySQL 8.0+

### Para ejecutar el servicio Python de spam por separado

- Python 3.12+
- Dependencias de `python-ia/requirements.txt`
- Un archivo de modelo llamado `modelo_spam_completo.keras` dentro de `python-ia/`

## Instalación recomendada: Docker

La forma recomendada de ejecutar el proyecto es con Docker, y en este repositorio el arranque está planteado para hacerse mediante el script `docker/up.sh`. Ese script está diseñado para que el usuario no tenga que preparar nada más antes de levantar el entorno.

Su función es simplificar el arranque al máximo:

- crea `docker/.env` automáticamente a partir de `docker/.env.example` si todavía no existe
- entra en la carpeta correcta de Docker
- ejecuta `docker compose up -d --build`
- espera a que MySQL esté listo y aplica `sql/database.sql`

La idea es que, para levantar el entorno Docker, el usuario solo tenga que lanzar un comando.

El flujo Docker actualmente levanta estos servicios:

- MySQL en `localhost:3306`
- PHP + Apache en `http://localhost:8080`
- phpMyAdmin en `http://localhost:8081`

El servicio Python no se levanta porque está comentado en `docker/docker-compose.yml`.

### Pasos

La única forma documentada para levantar el entorno con Docker es usar el script de arranque:

```bash
sh ./docker/up.sh
```

Con ese comando no hace falta copiar archivos manualmente ni preparar `docker/.env` a mano para el arranque inicial.
Tampoco hace falta borrar el volumen para que se creen las tablas que falten, porque el script reaplica el esquema al arrancar.

Si quieres reiniciar el entorno completo y dejar la app en estado limpio para pruebas, puedes usar:

```bash
sh ./docker/reset.sh
```

Ese script hace lo siguiente:

- baja los contenedores del proyecto
- elimina los volúmenes Docker para borrar la base de datos persistida
- vuelve a levantar el stack con build
- reaplica `sql/database.sql`
- borra los archivos subidos en `frontend/uploads`

Esto también invalida las sesiones guardadas en el contenedor PHP al recrearlo. Si el navegador conserva una cookie vieja, la aplicación simplemente te tratará como no autenticado.

### Accesos

- Aplicación: `http://localhost:8080`
- Frontend directo: `http://localhost:8080/frontend/index.html`
- phpMyAdmin: `http://localhost:8081`

Para entrar en phpMyAdmin, usa las credenciales MySQL definidas en `docker/.env`. Si todavía no existe ese archivo, `docker/up.sh` lo crea automáticamente a partir de `docker/.env.example`, así que por defecto los valores iniciales son:

- Servidor: `db`
- Puerto: `3306`
- Base de datos: `login`
- Usuario de aplicación: `chat_app`
- Contraseña de aplicación: `una_clave_segura`
- Usuario root: `root`
- Contraseña root: `root`

Puedes acceder con `chat_app` o con `root`. Si has cambiado `docker/.env`, phpMyAdmin usará esos nuevos valores en lugar de los de ejemplo.

La raíz del proyecto sirve `index.php`, que redirige automáticamente a `frontend/index.html`.

### Variables usadas por Docker

El script genera `docker/.env` automáticamente si falta. A partir de ese archivo, el contenedor PHP recibe estas variables:

- `MYSQL_DATABASE`
- `MYSQL_ROOT_PASSWORD`
- `MYSQL_USER`
- `MYSQL_PASSWORD`

Internamente PHP traduce esos valores a:

- `DB_HOST=db`
- `DB_NAME=${MYSQL_DATABASE}`
- `DB_PORT=3306`
- `DB_USER=${MYSQL_USER}`
- `DB_PASS=${MYSQL_PASSWORD}`

## Instalación alternativa: entorno local sin Docker

Esta opción requiere más configuración manual. Si vas a ejecutar PHP directamente en local, necesitas tener MySQL corriendo y cargar la base de datos manualmente con `sql/database.sql`.

### 1. Configura credenciales locales

```bash
cp backend/config/credentials.example.php backend/config/credentials.local.php
```

Edita `backend/config/credentials.local.php` con tus valores reales.

La aplicación busca credenciales en este orden:

1. Variables de entorno `DB_HOST`, `DB_NAME`, `DB_PORT`, `DB_USER`, `DB_PASS`
2. Archivo local `backend/config/credentials.local.php`

### 2. Importa la base de datos

Ejecuta el contenido de `sql/database.sql` sobre tu servidor MySQL.

### 3. Lanza PHP

```bash
php -S localhost:8000
```

Después accede a:

- `http://localhost:8000`
- o `http://localhost:8000/frontend/index.html`

## Base de datos

El script `sql/database.sql` crea la base `login` con estas tablas:

- `usuarios`: `id`, `user`, `password`, `role`, timestamps
- `chat_rooms`: `id`, `room_key`, `student_user_id`, timestamps
- `mensajes`: `id`, `chat_room_id`, `sender_user_id`, `message`, `created_at`
- `archivos`: tabla preparada para adjuntos, relacionada con sala, usuario y mensaje

También inserta usuarios de prueba:

- `test` / `password` con rol `admin` (admin de pruebas)
- `profe` / `password` con rol `profesor`

El login anónimo genera automáticamente un usuario con formato `anon_xxxxx`, contraseña aleatoria y rol `alumno`.

## Flujo de la aplicación

### Login normal

- El formulario inicial llama a `backend/api/login.php`.
- Si el login es correcto, redirige a `backend/controllers/welcome.php`.
- `welcome.php` carga la vista correspondiente según el rol.

### Login anónimo

- El botón "Entrar como Anónimo" llama a `backend/api/anonymous_login.php`.
- Se crea un usuario nuevo en la base de datos con rol `alumno`.
- Se inicia sesión automáticamente.
- La vista del alumno muestra un modal temporal con las credenciales generadas.

### Flujo por roles

- `alumno`: entra en `backend/controllers/student_view.php` y solo puede leer y escribir en su propia sala.
- `profesor`: entra en `backend/controllers/teacher_dashboard.php`, puede listar salas activas, responder en la seleccionada y cambiar estado de sala.
- `admin`: entra en `backend/controllers/admin_dashboard.php` y puede:
    - crear usuarios (`profesor`/`alumno`)
    - listar y eliminar usuarios
    - listar chats profesor-alumno
    - ver historial de mensajes por chat
    - cambiar el estado de cualquier chat a cualquier estado permitido por la columna `chat_rooms.estado`

## Endpoints disponibles

- `POST /backend/api/login.php`: autentica usuario y guarda sesión.
- `GET /backend/api/anonymous_login.php`: crea usuario anónimo, inicia sesión y redirige.
- `GET /backend/api/leer.php`: lee mensajes. Para alumno usa su sesión; para profesor/admin requiere `?chat_room=...`.
- `POST /backend/api/enviar.php`: envía mensajes (texto y opcionalmente imagen). Para profesor/admin requiere `chat_room`.
- `GET /backend/api/get_users.php`: devuelve las salas activas ordenadas por actividad; accesible para profesor y admin.
- `GET /backend/api/admin_users.php`: devuelve usuarios agrupados por rol (`profesor`, `alumno`). Solo admin.
- `POST /backend/api/admin_users.php`: crea usuario con rol `profesor` o `alumno`. Solo admin.
- `DELETE /backend/api/admin_users.php`: elimina un usuario por ID (excepto el propio admin autenticado). Solo admin.
- `GET /backend/api/admin_chats.php`: devuelve listado de chats y lista de estados permitidos. Solo admin.
- `GET /backend/api/admin_chats.php?chat_room=...`: devuelve historial de mensajes de una sala. Solo admin.
- `PATCH /backend/api/admin_chats.php`: actualiza estado de una sala con JSON `{ "chat_room": "...", "state": "..." }`. Solo admin.
- `GET /backend/api/change_chat_state.php?state=...&chat_room=...`: cambia estado de una sala. Acceso profesor/admin.
- `GET /backend/api/delete_chat.php`: elimina el usuario en sesión y su rastro asociado (por cascada), luego hace logout.
- `GET /backend/api/logout.php`: destruye la sesión y redirige al login.

## Frontend actual

- `frontend/js/app.js` gestiona el login con `fetch`.
- `frontend/js/chat.js` actualiza el chat del alumno mediante polling cada 2 segundos.
- `frontend/js/chat_profesor.js` refresca la lista de chats cada 5 segundos y los mensajes cada 2 segundos.
- `frontend/js/admin_panel.js` gestiona:
    - altas/bajas de usuarios
    - carga de chats y estados disponibles
    - visualización del historial del chat seleccionado
    - cambio de estado de chat desde el panel admin

No hay WebSockets ni tiempo real push; la actualización es por sondeo periódico.

## Módulo Python de spam

En `python-ia/spam_detector.py` hay una API Flask con endpoint:

- `POST /predict` con un JSON del tipo `{ "texto": "..." }`

El servicio carga el modelo `modelo_spam_completo.keras` y responde `SPAM` o `HAM`.

Puntos importantes sobre este módulo:

- No está conectado al flujo de mensajería PHP actual.
- No se levanta por defecto con Docker porque el servicio está comentado.
- `python-ia/language_filter.py` sigue siendo un placeholder.

## Seguridad implementada actualmente

- Contraseñas almacenadas con `password_hash()`.
- Verificación con `password_verify()`.
- Uso de sesiones PHP.
- Restricción básica por rol en endpoints como `backend/api/get_users.php`.
- Cookie de sesión con `HttpOnly`, `SameSite=Strict` y `session.use_strict_mode` en el flujo de login estándar.

## Limitaciones actuales

- No hay integración activa del detector de spam en el chat.
- No hay filtro de lenguaje operativo.
- La actualización de mensajes depende de polling, no de comunicación en tiempo real.
- La creación de usuarios anónimos genera credenciales aleatorias, pero no hay flujo adicional de recuperación.

## Archivos clave

- `index.php`
- `frontend/index.html`
- `backend/controllers/welcome.php`
- `backend/controllers/admin_dashboard.php`
- `backend/api/login.php`
- `backend/api/anonymous_login.php`
- `backend/api/enviar.php`
- `backend/api/leer.php`
- `backend/api/get_users.php`
- `backend/api/admin_users.php`
- `backend/api/admin_chats.php`
- `backend/api/change_chat_state.php`
- `backend/config/db.php`
- `docker/docker-compose.yml`
- `sql/database.sql`
