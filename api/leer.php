<?php
// api/leer.php
session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode([]);
    exit;
}

$role = $_SESSION['role'] ?? 'alumno';
$chat_room_to_read = '';

if ($role === 'alumno') {
    // El alumno solo lee SU sala
    $chat_room_to_read = $_SESSION['user'];
} else {
    // El profesor lee la sala que pida por ?chat_room=...
    $chat_room_to_read = $_GET['chat_room'] ?? '';
}

if ($chat_room_to_read === '') {
    echo json_encode([]); // Si no hay sala definida, devolvemos vacío
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT user, message, date FROM mensajes WHERE chat_room = ? ORDER BY date ASC");
    $stmt->execute([$chat_room_to_read]);
    $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Añadimos un campo extra para que JS sepa si el mensaje es "mío"
    foreach ($mensajes as &$msg) {
        $msg['is_me'] = ($msg['user'] === $_SESSION['user']);
        // Formateamos la fecha bonita (Ej: 14:30)
        $msg['time'] = date('H:i', strtotime($msg['date']));
    }

    echo json_encode($mensajes);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>