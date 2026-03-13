<?php
session_start();
include_once '../config/db.php';

$state = trim((string) ($_GET['state'] ?? ''));
$chatRoomKey = trim((string) ($_GET['chat_room'] ?? ''));
$allowedStates = ['abierto', 'finalizado', 'revision'];

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] === 'alumno') {
    header('Location: ../../frontend/index.html?error=' . urlencode('No autorizado'));
    exit;
}

if ($state === '' || $chatRoomKey === '' || !in_array($state, $allowedStates, true)) {
    header('Location: ../controllers/teacher_dashboard.php?error=' . urlencode('Parámetros inválidos'));
    exit;
}

try {
    $roomStmt = $pdo->prepare('SELECT id FROM chat_rooms WHERE room_key = ? LIMIT 1');
    $roomStmt->execute([$chatRoomKey]);
    $chatRoomId = $roomStmt->fetchColumn();

    if (!$chatRoomId) {
        header('Location: ../controllers/teacher_dashboard.php?error=' . urlencode('Sala no encontrada'));
        exit;
    }

    $stmt = $pdo->prepare('UPDATE chat_rooms SET estado = ? WHERE id = ?');
    $stmt->execute([$state, (int) $chatRoomId]);

    header('Location: ../controllers/teacher_dashboard.php?chat_room=' . urlencode($chatRoomKey));
    exit;
} catch (Throwable $th) {
    header('Location: ../controllers/teacher_dashboard.php?error=' . urlencode($th->getMessage()));
    exit;
}
?>