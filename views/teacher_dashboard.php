<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Profesor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Estilos específicos para el layout de dos columnas del profesor */
        .dashboard { display: flex; height: 100%; }
        
        .sidebar { 
            width: 250px; 
            border-right: 1px solid rgba(255,255,255,0.1); 
            display: flex; 
            flex-direction: column; 
            background: rgba(0,0,0,0.2); 
        }
        
        .sidebar h3 { 
            padding: 15px; 
            margin:0; 
            color: var(--success-color); 
            font-size: 1rem; 
            border-bottom: 1px solid rgba(255,255,255,0.1); 
        }
        
        .user-list { 
            list-style: none; 
            padding: 0; 
            margin: 0; 
            overflow-y: auto; 
            flex: 1; 
            scrollbar-width: thin;
        }
        
        .user-item { 
            padding: 15px; 
            cursor: pointer; 
            border-bottom: 1px solid rgba(255,255,255,0.05); 
            color: #aaa; 
            transition: 0.2s; 
        }
        
        .user-item:hover { 
            background: rgba(255,255,255,0.05); 
            color: white; 
        }
        
        .user-item.active { 
            background: var(--accent-color); 
            color: white; 
            border-left: 4px solid var(--success-color); 
        }
        
        .main-chat { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
        }
    </style>
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
    <script src="../assets/js/chat_profesor.js"></script>
</body>
</html>