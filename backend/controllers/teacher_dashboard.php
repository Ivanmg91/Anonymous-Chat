<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Profesor</title>
    <link rel="stylesheet" href="../../frontend/css/style.css">
</head>
<body>
    <div class="app-container">
        <div class="dashboard">
            <!-- IZQUIERDA: LISTA DE ALUMNOS -->
            <aside class="sidebar">
                <h3>Chats Activos</h3>
                <ul class="user-list" id="usersList">
                    <li style="padding:20px; text-align:center;">Cargando...</li>
                </ul>
                <div style="padding:10px;">
                    <a href="../api/logout.php" class="btn btn-small" style="background: #444;">Cerrar Sesión</a>
                </div>
            </aside>

            <!-- DERECHA: CHAT -->
            <main class="main-chat">
                <header class="chat-header">
                    <strong id="chatTitle" style="color: white;">Selecciona un alumno</strong>
                    <div class="chat-header-actions">
                        <a id="finalizeChatBtn" class="header-action-btn header-action-danger">Finalizar Chat</a>
                        <a id="reviewChatBtn" class="header-action-btn header-action-warning">Mandar a Revision</a>
                    </div>
                </header>
                
                <div id="chat-box" class="chat-messages">
                    <div class="message system"><p>Selecciona un alumno de la izquierda para ver su chat.</p></div>
                </div>

                <footer class="chat-input-area" id="inputArea" style="display:none;">
                    <form id="chatForm" style="display:flex; width:100%; gap:10px;">
                        <input type="text" id="messageInput" placeholder="Respuesta del profesor..." autocomplete="off" required>
                        <button type="submit" style="width: 50px;">➤</button>
                    </form>
                </footer>
            </main>
        </div>
    </div>

    <!-- JS ESPECÍFICO DEL PROFESOR -->
    <script src="../../frontend/js/chat_profesor.js"></script>
</body>
</html>
