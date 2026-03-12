<?php
// backend/api/logout.php
session_start();

// Limpiar variables
$_SESSION = [];

// Eliminar cookie del cliente (Vital para seguridad)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"], 
        $params["secure"], 
        $params["httponly"]
    );
}

// Destruir sesión en servidor
session_destroy();

// Redirigir
header('Location: ../../frontend/index.html');
exit;
?>
