<?php
// Recuperamos datos del modal si existen (para nuevos usuarios anónimos)
$showModal = false;
if (isset($_SESSION['temp_credentials'])) {
    $showModal = true;
    $newUser = $_SESSION['temp_credentials']['user'];
    $newPass = $_SESSION['temp_credentials']['pass'];
    unset($_SESSION['temp_credentials']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Chat Ayuda</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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

    <div class="app-container">
        <header class="chat-header">
            <div class="user-info">
                <strong>Hola, <?php echo htmlspecialchars($_SESSION['user']); ?></strong>
            </div>
            <a href="../api/logout.php" class="btn btn-small" style="width: auto; background: #555;">Salir</a>
        </header>

        <div id="chat-box" class="chat-messages">
            <!-- Mensajes aquí -->
        </div>

        <footer class="chat-input-area">
            <form id="chatForm" style="display:flex; width:100%; gap:10px;">
                <input type="text" id="messageInput" placeholder="Escribe tu duda aquí..." autocomplete="off" required>
                <button type="submit" style="width: 50px;">➤</button>
            </form>
        </footer>
    </div>

    <!-- Usamos el JS estándar -->
    <script src="../assets/js/chat.js"></script>
</body>
</html>