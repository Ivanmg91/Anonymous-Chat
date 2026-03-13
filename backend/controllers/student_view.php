<?php
// backend/controllers/student_view.php
// Recuperamos datos del modal si existen (para nuevos usuarios anónimos)
$showModal = false;
if (isset($_SESSION['temp_credentials'])) {
    $showModal = true;
    $newUser = $_SESSION['temp_credentials']['user'];
    $newPass = $_SESSION['temp_credentials']['pass'];
    unset($_SESSION['temp_credentials']);
}

// Si aún no se ha consultado leer.php, la clave puede no existir en sesión.
$chatRoomEstado = $_SESSION['chat_room_estado'] ?? 'abierto';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Chat Ayuda</title>
    <link rel="stylesheet" href="../../frontend/css/style.css">
</head>
<body>

    <!-- MODAL (Solo nuevos) -->
    <?php if ($showModal): ?>
    <div class="modal-overlay" id="credModal">
        <div class="modal-content">
            <h2 style="color: var(--success-color);">¡Cuenta Creada!</h2>
            <div class="cred-box">
                <p>Usuario: <b><?php echo htmlspecialchars($newUser); ?></b></p>
                <p>Contraseña: <b><?php echo htmlspecialchars($newPass); ?></b></p>
            </div>
            <button class="btn-ok" onclick="document.getElementById('credModal').style.display='none'">Entendido</button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Si clickas en el Nueva del dropdown de salir lanzara un modal de confirmación -->
    <div class="modal-overlay" id="confirmModal" style="display:none;">
        <div class="modal-content">
            <h2>¿Quieres empezar un nuevo chat?</h2>
            <p>Esto cerrará y borrará tu chat y usuario actual</p>
            <div style="display:flex; gap:10px; justify-content:center; margin-top:20px;">
                <button class="btn" onclick="document.getElementById('confirmModal').style.display='none'">Cancelar</button>
                <a href="../api/delete_chat.php" class="btn" style="background:#0f3460; color:white;">Confirmar</a>
            </div>
        </div>
    </div>

    <div class="app-container">
        <header class="chat-header">
            <div class="user-info">
                <strong>Hola, <?php echo htmlspecialchars($_SESSION['user']); ?></strong>
            </div>
            <details class="header-menu">
                <summary class="btn btn-small header-menu-trigger">Salir</summary>
                <div class="header-menu-dropdown">
                    <a href="../api/logout.php" class="header-menu-item">Cerrar Chat</a>
                    <!-- <a href="#" class="header-menu-item" onclick="document.getElementById('confirmModal').style.display='block'">Acabar Chat</a> -->
                </div>
            </details>
        </header>

        <div id="chat-box" class="chat-messages">
            <!-- Mensajes aquí -->
        </div>

        <?php if ($chatRoomEstado === 'abierto'): ?>
            <footer class="chat-input-area">
            <form id="chatForm" style="display:flex; width:100%; gap:10px; align-items:center; flex-wrap:wrap;">
                <input type="text" id="messageInput" placeholder="Escribe tu problema aquí..." autocomplete="off" style="flex:1; min-width:220px;">
                    <input type="file" id="imageInput" accept="image/*" style="display:none;">
                    <label for="imageInput" class="btn btn-small" style="width:auto; white-space:nowrap; background:#0f3460;">Subir imagen</label>
                    <!-- <input type="file" id="imageInput" accept="image/*" style="display:none;"> -->
                    <!-- <span id="selectedImageName" style="font-size:0.9rem; color:var(--text-muted); min-width:160px;">Ninguna imagen seleccionada</span> -->
                </input>
                <button type="submit" style="width: 50px;">➤</button>
            </form>
        </footer>
        <?php else: ?>
            <div style="padding:20px; text-align:center; color:var(--text-muted);">
                <p>La sala ha sido cerrada por el profesor.</p>
                <p>Si crees que es un error, contacta con tu profesor para más información.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Usamos el JS estándar -->
    <script src="../../frontend/js/chat.js"></script>
</body>
</html>
