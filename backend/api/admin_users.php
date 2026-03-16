<?php
session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function isAllowedRole($role) {
    return in_array($role, ['profesor', 'alumno'], true);
}

if ($method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT id, user, role, created_at FROM usuarios WHERE role IN ('profesor', 'alumno') ORDER BY role, user ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [
            'profesor' => [],
            'alumno' => []
        ];

        foreach ($rows as $row) {
            $result[$row['role']][] = $row;
        }

        echo json_encode(['success' => true, 'users' => $result]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al cargar usuarios']);
    }
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!is_array($data)) {
    $data = [];
}

if ($method === 'POST') {
    $user = trim($data['user'] ?? '');
    $password = (string) ($data['password'] ?? '');
    $role = (string) ($data['role'] ?? '');

    if ($user === '' || $password === '' || !isAllowedRole($role)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos invalidos']);
        exit;
    }

    if (strlen($user) > 50) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Usuario demasiado largo']);
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare('INSERT INTO usuarios (user, password, role) VALUES (?, ?, ?)');
        $stmt->execute([$user, $passwordHash, $role]);

        $newId = (int) $pdo->lastInsertId();

        if ($role === 'alumno') {
            $roomKey = bin2hex(random_bytes(16));
            $roomStmt = $pdo->prepare('INSERT INTO chat_rooms (room_key, student_user_id) VALUES (?, ?)');
            $roomStmt->execute([$roomKey, $newId]);
        }

        echo json_encode(['success' => true, 'message' => 'Usuario creado']);
    } catch (PDOException $e) {
        if ((int) $e->getCode() === 23000) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'El usuario ya existe']);
            exit;
        }

        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'No se pudo crear el usuario']);
    }
    exit;
}

if ($method === 'DELETE') {
    $userId = (int) ($data['id'] ?? 0);

    if ($userId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID invalido']);
        exit;
    }

    if ($userId === (int) $_SESSION['user_id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No puedes borrarte a ti mismo']);
        exit;
    }

    try {
        $roleStmt = $pdo->prepare("SELECT role FROM usuarios WHERE id = ? LIMIT 1");
        $roleStmt->execute([$userId]);
        $role = $roleStmt->fetchColumn();

        if (!$role || !isAllowedRole($role)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            exit;
        }

        $deleteStmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
        $deleteStmt->execute([$userId]);

        echo json_encode(['success' => true, 'message' => 'Usuario eliminado']);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el usuario']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
