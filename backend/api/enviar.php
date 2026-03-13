<?php
session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$rawInput = null;
$jsonData = [];

// Si el contenido es JSON, lo decodificamos
if (stripos($contentType, 'application/json') !== false) {
    $rawInput = file_get_contents('php://input');
    $decodedInput = json_decode($rawInput, true);

    if (!is_array($decodedInput)) {
        echo json_encode(['success' => false, 'message' => 'JSON no válido']);
        exit;
    }

    $jsonData = $decodedInput;
}

// Verificamos que el usuario esté autenticado
if (!isset($_SESSION['user_id'], $_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Solo profesores y alumnos pueden enviar mensajes
$mensaje = trim((string) ($_POST['message'] ?? $jsonData['message'] ?? ''));
$imageFile = $_FILES['image'] ?? null;
$hasImage = $imageFile && ($imageFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

if ($mensaje === '' && !$hasImage) {
    echo json_encode(['success' => false, 'message' => 'Debes enviar un mensaje o una imagen']);
    exit;
}

$chatRoomId = null;

// Determinamos la sala de chat según el rol
if (($_SESSION['role'] ?? 'alumno') === 'alumno') {
    $roomStmt = $pdo->prepare('SELECT id FROM chat_rooms WHERE student_user_id = ? LIMIT 1');
    $roomStmt->execute([$_SESSION['user_id']]);
    $chatRoomId = $roomStmt->fetchColumn();

    if (!$chatRoomId) {
        echo json_encode(['success' => false, 'message' => 'Sala no disponible para el alumno']);
        exit;
    }
} else {
    $chatRoomKey = trim((string) ($_POST['chat_room'] ?? $jsonData['chat_room'] ?? ''));
    if ($chatRoomKey === '') {
        echo json_encode(['success' => false, 'message' => 'Sala no especificada']);
        exit;
    }

    $roomStmt = $pdo->prepare('SELECT id FROM chat_rooms WHERE room_key = ? LIMIT 1');
    $roomStmt->execute([$chatRoomKey]);
    $chatRoomId = $roomStmt->fetchColumn();

    if (!$chatRoomId) {
        echo json_encode(['success' => false, 'message' => 'Sala no encontrada']);
        exit;
    }
}

$stateStmt = $pdo->prepare('SELECT estado FROM chat_rooms WHERE id = ? LIMIT 1');
$stateStmt->execute([(int) $chatRoomId]);
$chatState = (string) $stateStmt->fetchColumn();
if ($chatState !== 'abierto') {
    echo json_encode(['success' => false, 'message' => 'Chat no disponible para nuevos mensajes']);
    exit;
}

$storedName = null;
$publicPath = null;
$absolutePath = null;
$mimeType = null;
$fileSize = null;
$originalName = null;

try {
    if ($hasImage) {
        // Validaciones básicas del archivo
        // Verificamos que no haya errores en la subida
        if ($imageFile['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Error al subir la imagen');
        }

        // Verificamos que el archivo haya sido subido por HTTP POST
        if (!is_uploaded_file($imageFile['tmp_name'])) {
            throw new RuntimeException('Archivo subido no válido');
        }

        // Limitamos el tamaño a 5 MB
        if ($imageFile['size'] > 5 * 1024 * 1024) {
            throw new RuntimeException('La imagen supera el límite de 5 MB');
        }

        // Validamos el tipo MIME usando finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($imageFile['tmp_name']);

        // Permitimos solo ciertos tipos de imagen
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        // Verificamos que el tipo MIME sea permitido
        if (!isset($allowedTypes[$mimeType])) {
            throw new RuntimeException('Tipo de imagen no permitido');
        }

        // Preparamos los nombres y rutas para almacenar la imagen
        $uploadDir = dirname(__DIR__, 2) . '/frontend/uploads';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
            throw new RuntimeException('No se pudo crear la carpeta de subida');
        }

        // Generamos un nombre único para evitar colisiones
        $extension = $allowedTypes[$mimeType];
        $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
        $absolutePath = $uploadDir . '/' . $storedName;
        $publicPath = '/frontend/uploads/' . $storedName;
        $fileSize = (int) $imageFile['size'];
        $originalName = basename($imageFile['name']);
    }

    // Iniciamos la transacción para asegurar que ambos inserts (mensaje y archivo) se realicen correctamente
    $pdo->beginTransaction();

    // Insertamos el mensaje en la base de datos
    $stmt = $pdo->prepare('INSERT INTO mensajes (chat_room_id, sender_user_id, message) VALUES (?, ?, ?)');
    $stmt->execute([
        (int) $chatRoomId,
        (int) $_SESSION['user_id'],
        $mensaje
    ]);

    $mensajeId = (int) $pdo->lastInsertId();

    // Si hay una imagen
    if ($hasImage) {
        // Movemos el archivo a su ubicación final
        if (!move_uploaded_file($imageFile['tmp_name'], $absolutePath)) {
            throw new RuntimeException('No se pudo guardar la imagen en disco');
        }

        // Insertamos la información del archivo en la base de datos
        $imgStmt = $pdo->prepare(
            'INSERT INTO archivos (
                chat_room_id,
                uploaded_by_user_id,
                mensaje_id,
                original_name,
                stored_name,
                storage_path,
                mime_type,
                file_size
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $imgStmt->execute([
            (int) $chatRoomId,
            (int) $_SESSION['user_id'],
            $mensajeId,
            $originalName,
            $storedName,
            $publicPath,
            $mimeType,
            $fileSize
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($absolutePath && file_exists($absolutePath)) {
        unlink($absolutePath);
    }

    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}