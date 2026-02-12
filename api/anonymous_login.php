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
        $stmt = $pdo->prepare("INSERT INTO usuarios (user, password, role) VALUES (?, ?, 'alumno')");

        // Ejecutamos
        if ($stmt->execute([$username, $hashPassword])) {
            // Iniciamos la sesión aqui mismo
            $_SESSION['user'] = $username;
            $_SESSION['role'] = 'alumno';
            
            // Session para que welcome.php sepa que es un usuario nuevo y lo muestre
            $_SESSION['temp_credentials'] = [
                'user' => $username,
                'pass' => $rawPassword
            ];

            // Redirigimos al chat
            header('Location: ../views/welcome.php');
            exit;
        } else {
            throw new Exception("Error al guardar en base de datos");
        }
    } catch (Exception $e) {
        // Si falla, volvemos al login con error
        header('Location: ../index.html?error=' . urlencode($e->getMessage()));
        exit;
    }
?>