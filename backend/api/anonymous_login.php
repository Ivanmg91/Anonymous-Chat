<?php
    // backend/api/anonymous_login.php

    session_start();
    require_once '../config/db.php';

    try {
        // GENERAR DATOS
        // Usuario random
        $username = 'anon_' . substr(uniqid(), -5);

        // Password random
        $rawPassword = bin2hex(random_bytes(4));

        // Transformar la contraseña
        $hashPassword = password_hash($rawPassword, PASSWORD_DEFAULT);

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO usuarios (user, password, role) VALUES (?, ?, 'alumno')");
        if (!$stmt->execute([$username, $hashPassword])) {
            throw new Exception("Error al guardar en base de datos");
        }

        // Obtener el ID del usuario recién creado
        $userId = (int) $pdo->lastInsertId();
        $roomKey = bin2hex(random_bytes(16));

        // Crear la sala de chat para el usuario anónimo
        $roomStmt = $pdo->prepare("INSERT INTO chat_rooms (room_key, student_user_id) VALUES (?, ?)");
        $roomStmt->execute([$roomKey, $userId]);

        // Si todo va bien, confirmamos la transacción
        $pdo->commit();

        // Iniciar sesión con los datos generados
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['user'] = $username;
        $_SESSION['role'] = 'alumno';
        $_SESSION['room_key'] = $roomKey;

        $_SESSION['temp_credentials'] = [
            'user' => $username,
            'pass' => $rawPassword
        ];

        header('Location: ../controllers/welcome.php');
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // Si falla, volvemos al login con error
        header('Location: ../../frontend/index.html?error=' . urlencode($e->getMessage()));
        exit;
    }
?>
