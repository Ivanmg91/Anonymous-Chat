<?php
session_start();

// 1. Limpiar variables
$_SESSION = [];

// 2. Eliminar cookie del cliente (Vital para seguridad)
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

// 3. Destruir sesión en servidor
session_destroy();

// 4. Redirigir
header('Location: ../index.html');
exit;
?>