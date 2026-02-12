<?php
// api/get_users.php
session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

// 1. SEGURIDAD: Solo Admin o Profesor pasan
// Usamos ?? '' para evitar errores si la variable no existe
$rol = $_SESSION['role'] ?? 'alumno';

if ($rol === 'alumno') {
    echo json_encode(['error' => 'No autorizado. Rol actual: ' . $rol]);
    exit;
}

try {
    // 2. CONSULTA SQL ROBUSTA
    // Agrupa por sala y ordena para que salgan primero los que han hablado hace poco
    $sql = "SELECT chat_room 
            FROM mensajes 
            GROUP BY chat_room 
            ORDER BY MAX(date) DESC";
            
    $stmt = $pdo->query($sql);
    $salas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($salas);

} catch (PDOException $e) {
    // Enviamos el error exacto para que lo veas en la consola si falla
    echo json_encode(['error' => 'Error BD: ' . $e->getMessage()]);
}
?>