<?php
// backend/api/get_users.php
session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

// SEGURIDAD: Solo Admin o Profesor pasan
// Usamos ?? '' para evitar errores si la variable no existe
$rol = $_SESSION['role'] ?? 'alumno';

if ($rol === 'alumno') {
    echo json_encode(['error' => 'No autorizado. Rol actual: ' . $rol]);
    exit;
}

try {
    $sql = "SELECT cr.room_key, cr.estado, u.user AS student_name
        FROM chat_rooms cr
        INNER JOIN usuarios u ON u.id = cr.student_user_id
        INNER JOIN mensajes m ON m.chat_room_id = cr.id
        GROUP BY cr.id, cr.room_key, cr.estado, u.user
        ORDER BY MAX(m.created_at) DESC";
            
    $stmt = $pdo->query($sql);
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($salas);

} catch (PDOException $e) {
    // Enviamos el error exacto para que lo veas en la consola si falla
    echo json_encode(['error' => 'Error BD: ' . $e->getMessage()]);
}
?>
