<?php
// backend/api/enviar.php

session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'], $_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$mensaje = trim($data['message'] ?? '');
if ($mensaje === '') {
    echo json_encode(['success' => false, 'message' => 'Mensaje vacío']);
    exit;
}

// LÓGICA DE SALAS
$chatRoomId = null;

if ($_SESSION['role'] === 'alumno') {
    $roomStmt = $pdo->prepare("SELECT id, room_key FROM chat_rooms WHERE student_user_id = ? LIMIT 1");
    $roomStmt->execute([$_SESSION['user_id']]);
    $chatRoom = $roomStmt->fetch(PDO::FETCH_ASSOC);

    if (!$chatRoom) {
        echo json_encode(['success' => false, 'message' => 'Sala no disponible para el alumno']);
        exit;
    }

    $chatRoomId = (int) $chatRoom['id'];
} else {
    $chatRoomKey = trim($data['chat_room'] ?? '');
    if ($chatRoomKey === '') {
        echo json_encode(['success' => false, 'message' => 'Error: Sala no especificada']);
        exit;
    }

    $roomStmt = $pdo->prepare("SELECT id FROM chat_rooms WHERE room_key = ? LIMIT 1");
    $roomStmt->execute([$chatRoomKey]);
    $chatRoomId = $roomStmt->fetchColumn();

    if (!$chatRoomId) {
        echo json_encode(['success' => false, 'message' => 'Sala no encontrada']);
        exit;
    }
}

try {
    $stmt = $pdo->prepare("INSERT INTO mensajes (chat_room_id, sender_user_id, message) VALUES (?, ?, ?)");
    $stmt->execute([(int) $chatRoomId, (int) $_SESSION['user_id'], $mensaje]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error BD: ' . $e->getMessage()]);
}
?>
