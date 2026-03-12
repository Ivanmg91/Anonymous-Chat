<?php
// backend/api/login.php

// 1. Configuraciones de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();
header('Content-Type: application/json');

require_once '../config/db.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$user_input = $data['user'] ?? '';
$password_input = $data['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE user = ?");
$stmt->execute([$user_input]);
$usuario = $stmt->fetch();

if ($usuario && password_verify($password_input, $usuario['password'])) {
    session_regenerate_id(true);

    $_SESSION['user_id'] = (int) $usuario['id'];
    $_SESSION['user'] = $usuario['user'];
    $_SESSION['role'] = $usuario['role'] ?? 'alumno';

    if ($_SESSION['role'] === 'alumno') {
        $roomStmt = $pdo->prepare("SELECT room_key FROM chat_rooms WHERE student_user_id = ? LIMIT 1");
        $roomStmt->execute([$_SESSION['user_id']]);
        $roomKey = $roomStmt->fetchColumn();

        if (!$roomKey) {
            $roomKey = bin2hex(random_bytes(16));
            $createRoomStmt = $pdo->prepare("INSERT INTO chat_rooms (room_key, student_user_id) VALUES (?, ?)");
            $createRoomStmt->execute([$roomKey, $_SESSION['user_id']]);
        }

        $_SESSION['room_key'] = $roomKey;
    } else {
        unset($_SESSION['room_key']);
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos']);
}
?>
