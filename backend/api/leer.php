<?php
// backend/api/leer.php
session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'], $_SESSION['user'])) {
    echo json_encode([]);
    exit;
}

$role = $_SESSION['role'] ?? 'alumno';
$chatRoomKey = '';
$chatRoomId = null;

if ($role === 'alumno') {
    $roomStmt = $pdo->prepare("SELECT id, room_key FROM chat_rooms WHERE student_user_id = ? LIMIT 1");
    $roomStmt->execute([$_SESSION['user_id']]);
    $chatRoom = $roomStmt->fetch(PDO::FETCH_ASSOC);

    if (!$chatRoom) {
        echo json_encode([]);
        exit;
    }

    $chatRoomId = (int) $chatRoom['id'];
    $chatRoomKey = $chatRoom['room_key'];
} else {
    $chatRoomKey = trim($_GET['chat_room'] ?? '');
}

if ($chatRoomId === null && $chatRoomKey === '') {
    echo json_encode([]);
    exit;
}

try {
    if ($chatRoomId === null) {
        $roomStmt = $pdo->prepare("SELECT id FROM chat_rooms WHERE room_key = ? LIMIT 1");
        $roomStmt->execute([$chatRoomKey]);
        $chatRoomId = $roomStmt->fetchColumn();

        if (!$chatRoomId) {
            echo json_encode([]);
            exit;
        }
    }

    $stmt = $pdo->prepare(
        "SELECT u.user AS user, m.message, m.created_at, a.storage_path AS image_url, a.mime_type
         FROM mensajes m
         INNER JOIN usuarios u ON u.id = m.sender_user_id
         LEFT JOIN archivos a ON a.mensaje_id = m.id
         WHERE m.chat_room_id = ?
         ORDER BY m.created_at ASC, m.id ASC"
    );
    $stmt->execute([(int) $chatRoomId]);
    $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($mensajes as &$msg) {
        $msg['is_me'] = ($msg['user'] === $_SESSION['user']);
        $msg['time'] = date('H:i', strtotime($msg['created_at']));
    }
    unset($msg);

    echo json_encode($mensajes);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
