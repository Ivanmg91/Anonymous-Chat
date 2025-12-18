<?php
    // api/anonymous_login.php

    
    session_start();
    require_once '../config/db.php';

    try {
        // 1. GENERAR DATOS
        // Usuario random
        $username = 'anon_' . substr(uniqid(), -5);

        // Password random
        $rawPassword = bin2hex(random_bytes(4));

        // Transformar la contraseña
        $hashPassword = password_hash($rawPassword, PASSWORD_DEFAULT);

        // INSERTAR Y LOGUEAR
        // Preparamos la consulta
        $stmt = $pdo->prepare("INSERT INTO usuarios (user, password) VALUES (?, ?)");

        // Ejecutamos
        if ($stmt->execute([$username, $hashPassword])) {
            // Iniciamos la sesión aqui mismo
            $_SESSION['user'] = $username;

            echo json_encode([
                'success' => true,
                'user' => $username
            ]);
        } else {
            throw new Exception("No se ha podido generar el usuario en la base de datos");
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
?>