# Chat Denuncias (Anonymous Chat)

## Introducción y justificación del proyecto

Chat Denuncias es una aplicación web orientada a facilitar la comunicación anónima entre alumnado y profesorado. El objetivo del proyecto es ofrecer un canal sencillo, accesible y relativamente seguro para que un estudiante pueda iniciar una conversación sin exponer su identidad pública dentro de la interfaz, mientras el profesorado dispone de herramientas para revisar, responder y gestionar cada caso.

El sistema está implementado con PHP, MySQL y JavaScript nativo, e incorpora además un módulo Python preparado para detección de spam. Aunque ese módulo de inteligencia artificial todavía no participa en el flujo activo del chat, forma parte de una arquitectura ampliable.

La justificación principal del desarrollo es cubrir una necesidad habitual en entornos educativos: disponer de un medio de contacto discreto para consultas sensibles, incidencias o denuncias, sin exigir al alumnado un proceso de registro complejo ni una exposición directa frente a otros usuarios. A nivel técnico, el proyecto también sirve como ejercicio completo de desarrollo web con autenticación, control por roles, persistencia de datos, administración, despliegue con contenedores y preparación para futuras integraciones de IA.

### Estado actual del proyecto

- El acceso principal funciona desde la web en PHP.
- El alumnado puede entrar de forma anónima y abrir un chat propio.
- El profesorado puede ver conversaciones activas y responder por sala.
- El rol admin tiene panel funcional para alta, listado y borrado de usuarios profesor y alumno.
- El panel admin también permite listar chats profesor-alumno, ver su historial y cambiar su estado a cualquier valor permitido en base de datos.
- El profesorado puede cambiar el estado de una sala a revision o finalizado desde su flujo de gestión.
- El detector de spam en Python está preparado de forma independiente, pero no está conectado todavía al frontend ni al backend PHP.
- En docker/docker-compose.yml el contenedor Python está comentado, por lo que el stack actual levanta solo MySQL, PHP/Apache y phpMyAdmin.

## Análisis de requisitos y casos de uso

### Requisitos funcionales

- Permitir autenticación estándar mediante usuario y contraseña.
- Permitir acceso anónimo para alumnado con creación automática de credenciales.
- Crear o reutilizar una sala de chat asociada al alumno.
- Permitir al alumnado leer y enviar mensajes únicamente en su propia sala.
- Permitir al profesorado listar conversaciones activas y responder en una sala concreta.
- Permitir al profesorado cambiar el estado de una conversación dentro de los estados habilitados para su flujo.
- Permitir al administrador crear, listar y eliminar usuarios de tipo profesor y alumno.
- Permitir al administrador consultar el historial completo de los chats.
- Permitir al administrador cambiar el estado de cualquier chat a cualquier valor permitido por la base de datos.
- Mantener persistencia de mensajes, salas y usuarios en MySQL.
- Ofrecer un entorno reproducible de despliegue mediante Docker.

### Requisitos no funcionales

- Separación básica entre frontend, backend y base de datos.
- Uso de sesiones PHP para mantener autenticación.
- Restricción de acceso por rol en endpoints y paneles.
- Almacenamiento seguro de contraseñas mediante hash.
- Facilidad de arranque del entorno mediante scripts sencillos.
- Posibilidad de ampliación futura con servicios externos, como detección de spam.

### Actores del sistema

- Alumno: usuario autenticado de forma anónima o creado por admin, limitado a su propia conversación.
- Profesor: usuario con acceso al panel de conversaciones y capacidad de respuesta y cambio de estado.
- Admin: usuario con privilegios de administración de usuarios y supervisión total de chats.

### Casos de uso principales

#### 1. Login normal

- El formulario inicial llama a backend/api/login.php.
- Si el login es correcto, se redirige a backend/controllers/welcome.php.
- welcome.php determina la vista que corresponde según el rol del usuario.

#### 2. Login anónimo del alumnado

- El botón Entrar como Anónimo llama a backend/api/anonymous_login.php.
- El sistema crea un usuario nuevo con formato anon_xxxxx, contraseña aleatoria y rol alumno.
- Se inicia sesión automáticamente.
- La vista del alumno muestra temporalmente las credenciales generadas.

#### 3. Participación del alumno en el chat

- El alumno entra en backend/controllers/student_view.php.
- Puede leer y escribir solo en la sala que le pertenece.
- La lectura de mensajes se realiza por polling periódico desde frontend/js/chat.js.

#### 4. Gestión del profesorado

- El profesor entra en backend/controllers/teacher_dashboard.php.
- Puede listar las salas activas, seleccionar una conversación y responder.
- Puede cambiar el estado de la sala, por ejemplo a revision o finalizado.

#### 5. Gestión administrativa

- El administrador entra en backend/controllers/admin_dashboard.php.
- Puede crear usuarios con rol profesor o alumno.
- Puede listar y eliminar usuarios, salvo su propia cuenta autenticada.
- Puede listar chats, consultar su historial y cambiar estados de conversación.

### Endpoints disponibles

- POST /backend/api/login.php: autentica usuario y guarda sesión.
- GET /backend/api/anonymous_login.php: crea usuario anónimo, inicia sesión y redirige.
- GET /backend/api/leer.php: lee mensajes. Para alumno usa su sesión; para profesor o admin requiere chat_room.
- POST /backend/api/enviar.php: envía mensajes de texto y opcionalmente imagen. Para profesor o admin requiere chat_room.
- GET /backend/api/get_users.php: devuelve las salas activas ordenadas por actividad; accesible para profesor y admin.
- GET /backend/api/admin_users.php: devuelve usuarios agrupados por rol profesor y alumno. Solo admin.
- POST /backend/api/admin_users.php: crea usuario con rol profesor o alumno. Solo admin.
- DELETE /backend/api/admin_users.php: elimina un usuario por ID, excepto el propio admin autenticado. Solo admin.
- GET /backend/api/admin_chats.php: devuelve listado de chats y estados permitidos. Solo admin.
- GET /backend/api/admin_chats.php?chat_room=...: devuelve historial de mensajes de una sala. Solo admin.
- PATCH /backend/api/admin_chats.php: actualiza estado de una sala con JSON { "chat_room": "...", "state": "..." }. Solo admin.
- GET /backend/api/change_chat_state.php?state=...&chat_room=...: cambia estado de una sala. Acceso profesor y admin.
- GET /backend/api/delete_chat.php: elimina el usuario en sesión y su rastro asociado, y después cierra la sesión.
- GET /backend/api/logout.php: destruye la sesión y redirige al login.

## Diseño de la base de datos

La persistencia del sistema se define en sql/database.sql. El esquema crea la base de datos login y organiza la información en torno a usuarios, salas, mensajes y archivos adjuntos.

### Tablas principales

- usuarios: almacena id, user, password, role y marcas temporales.
- chat_rooms: almacena id, room_key, student_user_id, estado y timestamps.
- mensajes: almacena id, chat_room_id, sender_user_id, message y created_at.
- archivos: tabla preparada para adjuntos, relacionada con sala, usuario y mensaje.

### Decisiones de diseño

- Se emplean IDs internos para las relaciones principales entre tablas.
- La tabla chat_rooms utiliza room_key como identificador externo para la interfaz, mientras internamente mantiene la relación con student_user_id.
- La tabla mensajes relaciona cada mensaje con una sala concreta y con el usuario emisor.
- La tabla archivos deja preparada la estructura para soportar adjuntos, aunque el flujo actual está centrado principalmente en mensajes de texto e imágenes opcionales.

### Datos iniciales

El script de base de datos inserta usuarios de prueba:

- test / password con rol admin.
- profe / password con rol profesor.

Además, el login anónimo genera automáticamente usuarios con formato anon_xxxxx y rol alumno.

### Relación lógica entre entidades

- Un usuario alumno tiene asociada una sala principal de chat.
- Una sala puede contener múltiples mensajes.
- Cada mensaje pertenece a una sala y a un usuario emisor.
- Un archivo puede quedar asociado a una sala, a un usuario y opcionalmente a un mensaje.

## Arquitectura del sistema

La arquitectura sigue una separación sencilla por capas, suficiente para un proyecto académico y funcional en entorno web tradicional.

### Visión general

- Frontend: HTML, CSS y JavaScript nativo en frontend/.
- Backend: PHP organizado en controladores, APIs y configuración dentro de backend/.
- Persistencia: MySQL con esquema definido en sql/database.sql.
- Servicio IA independiente: API Flask en python-ia/.
- Despliegue local: Docker Compose con contenedores para PHP/Apache, MySQL y phpMyAdmin.

### Estructura del proyecto

```text
/Anonymous-Chat-
├── index.php                       # Redirección a frontend/index.html
├── README.md
├── backend/
│   ├── api/
│   │   ├── admin_chats.php         # Gestión de chats para admin
│   │   ├── admin_users.php         # CRUD de usuarios para admin
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
│       ├── teacher_dashboard.php   # Panel del profesorado
│       └── welcome.php             # Router por rol
├── docker/
│   ├── .env.example                # Variables de entorno de ejemplo
│   ├── docker-compose.yml
│   ├── Dockerfile                  # Imagen PHP 8.2 + Apache + extensiones MySQL
│   ├── reset.sh                    # Reinicio completo del entorno
│   └── up.sh                       # Arranque automatizado del stack
├── frontend/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   ├── admin_panel.js          # Panel admin
│   │   ├── app.js                  # Login inicial
│   │   ├── chat.js                 # Chat del alumnado
│   │   └── chat_profesor.js        # Chat y panel del profesorado
│   ├── index.html                  # Pantalla inicial
│   └── uploads/                    # Archivos subidos
├── python-ia/
│   ├── Dockerfile
│   ├── language_filter.py          # Placeholder
│   ├── requirements.txt
│   ├── spam_detector.py            # API Flask para detección de spam
│   └── modelo_spam_completo.keras  # Modelo requerido por el servicio
└── sql/
    └── database.sql                # Inicialización de base de datos
```

### Flujo técnico de la aplicación

1. El usuario accede a index.php, que redirige a frontend/index.html.
2. El frontend envía la petición de autenticación o de login anónimo al backend.
3. El backend valida credenciales o crea el usuario anónimo, establece la sesión y redirige a welcome.php.
4. welcome.php carga la vista de alumno, profesor o admin según el rol.
5. La interfaz realiza peticiones periódicas a los endpoints de lectura y envío de mensajes.
6. El backend persiste usuarios, salas y mensajes en MySQL.

### Frontend actual

- frontend/js/app.js gestiona el login con fetch.
- frontend/js/chat.js actualiza el chat del alumno mediante polling cada 2 segundos.
- frontend/js/chat_profesor.js refresca la lista de chats cada 5 segundos y los mensajes cada 2 segundos.
- frontend/js/admin_panel.js gestiona altas y bajas de usuarios, carga de chats, visualización de historial y cambio de estado.

Actualmente no hay WebSockets ni tiempo real push; la actualización depende de sondeo periódico.

### Seguridad implementada actualmente

- Contraseñas almacenadas con password_hash().
- Verificación con password_verify().
- Uso de sesiones PHP.
- Restricción básica por rol en varios endpoints.
- Cookie de sesión con HttpOnly, SameSite=Strict y session.use_strict_mode en el login estándar.

## Integración de IA

El proyecto incorpora un módulo Python preparado para clasificación de spam, aunque todavía no está integrado en el flujo principal de mensajería.

### Componentes existentes

- python-ia/spam_detector.py expone una API Flask.
- El endpoint disponible es POST /predict.
- Recibe un JSON del tipo { "texto": "..." }.
- Carga el archivo modelo_spam_completo.keras.
- Devuelve una clasificación SPAM o HAM.

### Estado de integración

- No está conectado al envío de mensajes en backend/api/enviar.php.
- No se levanta por defecto en Docker porque su servicio está comentado en docker/docker-compose.yml.
- python-ia/language_filter.py sigue siendo un placeholder sin integración activa.

### Potencial de uso

Esta integración permitiría, en una versión posterior:

- analizar mensajes antes de persistirlos,
- bloquear o marcar contenido sospechoso,
- generar alertas para profesorado o administración,
- combinar detección de spam con filtrado de lenguaje ofensivo.

## Uso de Git y control de versiones

El proyecto está versionado con Git y alojado en GitHub en el repositorio Ivanmg91/Anonymous-Chat. La rama principal actual es main, que además coincide con la rama por defecto.

El control de versiones es importante en este proyecto porque:

- permite registrar la evolución del sistema,
- facilita separar cambios de frontend, backend, base de datos o Docker,
- ayuda a mantener trazabilidad sobre correcciones y nuevas funcionalidades,
- simplifica el trabajo colaborativo y la revisión de cambios.

### Buenas prácticas recomendadas para este repositorio

- Realizar commits pequeños y temáticos.
- Separar cambios de documentación, lógica de negocio y despliegue cuando sea posible.
- Describir los commits de forma clara y orientada al cambio realizado.
- Mantener fuera del repositorio archivos sensibles como credenciales locales.

En el proyecto ya existe esta separación entre archivos versionables y configuración local, por ejemplo mediante backend/config/credentials.local.php, pensado para permanecer fuera del control de versiones.

## Despliegue con Docker (Importante)

La forma recomendada de ejecutar la aplicación es mediante Docker. El flujo está diseñado para que el arranque inicial requiera un único comando.

### Requisitos

#### Para la aplicación web

- Docker y Docker Compose.
- Alternativamente, PHP 8.2+ con pdo_mysql y MySQL 8.0+ si no se usa Docker.

#### Para el servicio Python por separado

- Python 3.12+.
- Dependencias de python-ia/requirements.txt.
- Archivo modelo_spam_completo.keras dentro de python-ia/.

### Arranque recomendado

El script docker/up.sh automatiza el inicio del entorno:

- crea docker/.env automáticamente a partir de docker/.env.example si no existe,
- entra en la carpeta correcta de Docker,
- ejecuta docker compose up -d --build,
- espera a que MySQL esté listo,
- reaplica sql/database.sql para garantizar el esquema.

Comando de arranque:

```bash
sh ./docker/up.sh
```

Este proceso evita tener que copiar archivos manualmente o preparar docker/.env a mano en el primer arranque.

### Servicios levantados actualmente

- MySQL en localhost:3306.
- PHP + Apache en http://localhost:8080.
- phpMyAdmin en http://localhost:8081.

El servicio Python no se levanta por defecto porque está comentado en docker/docker-compose.yml.

### Reinicio completo del entorno

Para dejar la aplicación en estado limpio de pruebas:

```bash
sh ./docker/reset.sh
```

Este script:

- baja los contenedores,
- elimina los volúmenes Docker para limpiar la base de datos persistida,
- vuelve a construir y levantar el stack,
- reaplica sql/database.sql,
- borra los archivos subidos en frontend/uploads.

Al recrear el contenedor PHP también se invalidan las sesiones guardadas. Si el navegador conserva una cookie antigua, la aplicación tratará al usuario como no autenticado.

### Accesos y credenciales Docker

- Aplicación: http://localhost:8080
- Frontend directo: http://localhost:8080/frontend/index.html
- phpMyAdmin: http://localhost:8081

Si docker/.env todavía no existe, docker/up.sh lo genera desde docker/.env.example y los valores iniciales quedan así:

- Servidor: db
- Puerto: 3306
- Base de datos: login
- Usuario de aplicación: chat_app
- Contraseña de aplicación: una_clave_segura
- Usuario root: root
- Contraseña root: root

PHP recibe estas variables desde Docker:

- MYSQL_DATABASE
- MYSQL_ROOT_PASSWORD
- MYSQL_USER
- MYSQL_PASSWORD

Y las traduce internamente a:

- DB_HOST=db
- DB_NAME=${MYSQL_DATABASE}
- DB_PORT=3306
- DB_USER=${MYSQL_USER}
- DB_PASS=${MYSQL_PASSWORD}

### Ejecución sin Docker

También es posible ejecutar el proyecto en local con configuración manual.

1. Copiar backend/config/credentials.example.php a backend/config/credentials.local.php.
2. Editar backend/config/credentials.local.php con las credenciales reales.
3. Importar sql/database.sql en el servidor MySQL local.
4. Lanzar el servidor PHP con:

```bash
php -S localhost:8000
```

Accesos en ese modo:

- http://localhost:8000
- http://localhost:8000/frontend/index.html

La aplicación busca credenciales en este orden:

1. Variables de entorno DB_HOST, DB_NAME, DB_PORT, DB_USER, DB_PASS.
2. Archivo local backend/config/credentials.local.php.

## Manual de usuario y administrador

### Acceso inicial

La aplicación sirve index.php en la raíz del proyecto y redirige automáticamente a frontend/index.html. Desde esa pantalla se puede:

- iniciar sesión con un usuario existente,
- entrar como usuario anónimo.

### Manual de usuario alumno

#### Acceso

- Puede entrar con credenciales propias o mediante login anónimo.
- Si entra como anónimo, el sistema crea automáticamente una cuenta y le muestra sus credenciales de forma temporal.

#### Uso del chat

- El alumno solo puede ver su propia conversación.
- Puede enviar mensajes de texto y, según el flujo disponible, adjuntar contenido soportado por el backend.
- La actualización del chat se realiza automáticamente cada pocos segundos.

#### Cierre de sesión o eliminación

- backend/api/logout.php destruye la sesión y devuelve al login.
- backend/api/delete_chat.php elimina el usuario en sesión y su rastro asociado, y después cierra la sesión.

### Manual de usuario profesor

#### Acceso

- Inicia sesión con credenciales de rol profesor.
- El sistema le redirige al panel de conversaciones del profesorado.

#### Gestión de conversaciones

- Puede ver la lista de salas activas ordenadas por actividad.
- Puede seleccionar una sala concreta para leer su historial.
- Puede responder dentro de la sala seleccionada.
- Puede cambiar el estado de la conversación usando el flujo habilitado para profesorado.

### Manual de usuario administrador

#### Acceso

- Inicia sesión con una cuenta de rol admin.
- El sistema redirige al panel de administración.

#### Gestión de usuarios

- Puede crear usuarios de tipo profesor y alumno.
- Puede listar usuarios agrupados por rol.
- Puede eliminar usuarios, excepto su propia cuenta autenticada.

#### Gestión de chats

- Puede listar chats profesor-alumno.
- Puede consultar el historial completo de una sala.
- Puede cambiar el estado de cualquier conversación a cualquier valor permitido por la base de datos.

### Credenciales de prueba

- Admin: test / password
- Profesor: profe / password

## Conclusiones y posibles mejoras

El proyecto resuelve correctamente un flujo de mensajería anónima con separación por roles, persistencia en MySQL, administración básica y despliegue reproducible con Docker. También deja preparada una línea de evolución interesante mediante la incorporación de inteligencia artificial para moderación o filtrado.

### Limitaciones actuales

- No hay integración activa del detector de spam en el chat.
- No hay filtro de lenguaje operativo.
- La actualización de mensajes depende de polling y no de comunicación en tiempo real.
- La creación de usuarios anónimos genera credenciales aleatorias, pero no existe un flujo adicional de recuperación.

### Posibles mejoras

- Integrar el servicio de IA en backend/api/enviar.php para analizar mensajes antes de guardarlos.
- Activar el servicio Python en Docker y documentar su despliegue conjunto.
- Sustituir el polling por WebSockets o Server-Sent Events.
- Añadir trazabilidad de estados y auditoría de acciones administrativas.
- Mejorar la gestión de adjuntos y validaciones de seguridad.
- Incorporar tests automáticos para backend, frontend y base de datos.
- Añadir recuperación o regeneración controlada de credenciales anónimas cuando el caso de uso lo requiera.

