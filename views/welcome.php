<?php
session_start();

// Si no hay usuario, mandamos al login
if (!isset($_SESSION['user'])) {
    header('Location: ../index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Dashboard</title>
    <!-- Ruta relativa para salir de 'views' e ir a 'assets' -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <div class="app-container">
        <!-- Cabecera del Chat -->
        <header class="chat-header">
            <div class="user-info">
                <strong>@<?php echo htmlspecialchars($_SESSION['user']); ?></strong>
                <span style="font-size: 0.8em; color: #00ff88;">● Conectado</span>
            </div>
            
            <!-- Botón de salir -->
            <a href="../api/logout.php" class="btn btn-small">Cerrar Sesión</a>
        </header>

        <!-- Área principal (Aquí irá el chat en el futuro) -->
        <main class="chat-content">
            <h1>👋 ¡Hola, <?php echo htmlspecialchars($_SESSION['user']); ?>!</h1>
            <p>Selecciona un chat para empezar a hablar.</p>
            <p style="opacity: 0.5;">(Próximamente...)</p>
        </main>
    </div>

</body>
</html>