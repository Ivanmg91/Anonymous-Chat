<?php
session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

function getAllowedStates(PDO $pdo): array
{
    $stmt = $pdo->query("SHOW COLUMNS FROM chat_rooms LIKE 'estado'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    $type = (string) ($column['Type'] ?? '');

    if (!preg_match("/^enum\((.*)\)$/", $type, $matches)) {
        return ['abierto', 'finalizado', 'revision'];
    }

    $rawValues = explode(',', $matches[1]);
    $states = array_map(static function ($value) {
        return trim($value, "'\"");
    }, $rawValues);

    return array_values(array_filter($states, static function ($value) {
        return $value !== '';
    }));
}

function listChats(PDO $pdo): array
{
    $sql = "SELECT
                cr.room_key,
                cr.estado,
                stu.user AS student_name,
                GROUP_CONCAT(DISTINCT prof.user ORDER BY prof.user SEPARATOR ', ') AS teacher_names,
                MAX(m.created_at) AS last_message_at,
                COUNT(m.id) AS message_count
            FROM chat_rooms cr
            INNER JOIN usuarios stu ON stu.id = cr.student_user_id
            LEFT JOIN mensajes m ON m.chat_room_id = cr.id
            LEFT JOIN usuarios prof ON prof.id = m.sender_user_id AND prof.role = 'profesor'
            GROUP BY cr.id, cr.room_key, cr.estado, stu.user
            ORDER BY COALESCE(MAX(m.created_at), cr.updated_at) DESC";

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listMessages(PDO $pdo, string $chatRoomKey): array
{
    $roomStmt = $pdo->prepare('SELECT id FROM chat_rooms WHERE room_key = ? LIMIT 1');
    $roomStmt->execute([$chatRoomKey]);
    $chatRoomId = $roomStmt->fetchColumn();

    if (!$chatRoomId) {
        return [];
    }

    $msgStmt = $pdo->prepare(
        "SELECT u.user, u.role, m.message, m.created_at, a.storage_path AS image_url
         FROM mensajes m
         INNER JOIN usuarios u ON u.id = m.sender_user_id
         LEFT JOIN archivos a ON a.mensaje_id = m.id
         WHERE m.chat_room_id = ?
         ORDER BY m.created_at ASC, m.id ASC"
    );
    $msgStmt->execute([(int) $chatRoomId]);
    $messages = $msgStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($messages as &$message) {
        $message['time'] = date('H:i', strtotime($message['created_at']));
    }
    unset($message);

    return $messages;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method === 'GET') {
        $chatRoomKey = trim((string) ($_GET['chat_room'] ?? ''));

        if ($chatRoomKey !== '') {
            echo json_encode([
                'success' => true,
                'messages' => listMessages($pdo, $chatRoomKey),
            ]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'states' => getAllowedStates($pdo),
            'chats' => listChats($pdo),
        ]);
        exit;
    }

    if ($method === 'PATCH' || $method === 'POST') {
        $rawInput = file_get_contents('php://input');
        $payload = json_decode($rawInput ?: '{}', true);

        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'JSON no valido']);
            exit;
        }

        $chatRoomKey = trim((string) ($payload['chat_room'] ?? ''));
        $state = trim((string) ($payload['state'] ?? ''));
        $allowedStates = getAllowedStates($pdo);

        if ($chatRoomKey === '' || $state === '' || !in_array($state, $allowedStates, true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos invalidos']);
            exit;
        }

        $roomStmt = $pdo->prepare('SELECT id FROM chat_rooms WHERE room_key = ? LIMIT 1');
        $roomStmt->execute([$chatRoomKey]);
        $chatRoomId = $roomStmt->fetchColumn();

        if (!$chatRoomId) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Sala no encontrada']);
            exit;
        }

        $updateStmt = $pdo->prepare('UPDATE chat_rooms SET estado = ? WHERE id = ?');
        $updateStmt->execute([$state, (int) $chatRoomId]);

        echo json_encode(['success' => true, 'message' => 'Estado actualizado']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
