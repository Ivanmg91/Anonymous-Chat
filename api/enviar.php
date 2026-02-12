<?php
// api/enviar.php
session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
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
$chat_room = '';

if ($_SESSION['role'] === 'alumno') {
    // Si soy alumno, OBLIGATORIAMENTE escribo en mi propia sala
    $chat_room = $_SESSION['user'];
} else {
    // Si soy profesor, tengo que recibir a qué sala (alumno) quiero contestar
    // JS me lo tiene que mandar en el JSON
    $chat_room = $data['chat_room'] ?? '';
    if (empty($chat_room)) {
        echo json_encode(['success' => false, 'message' => 'Error: Sala no especificada']);
        exit;
    }
}

try {
    $stmt = $pdo->prepare("INSERT INTO mensajes (user, chat_room, message) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user'], $chat_room, $mensaje]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error BD: ' . $e->getMessage()]);
}
?>