<?php
    // api/login.php

    // 1. Configuraciones de sesión
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');

    session_start();
    header('Content-Type: application/json');

    // 2. Incluir la conexión (fíjate en los dos puntos ".." para salir de "api" e ir a "config")
    require_once '../config/db.php';

    // 3. Leer JSON
    // 1. LEER EL PAQUETE (La caja de texto que envió JS)
    $input = file_get_contents('php://input');

    // 2. ABRIR LA CAJA (Convertir texto JSON a variables PHP)
    $data = json_decode($input, true);

    // 3. USAR LOS DATOS
    $user_input = $data['user'] ?? '';
    $password_input = $data['password'] ?? '';

    // 4. Lógica de verificación
    // Usamos $pdo que viene del archivo incluido db.php
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE user = ?");
    $stmt->execute([$user_input]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password_input, $usuario['password'])) {
        $_SESSION['user'] = $user_input;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos']);
    }
?>