<?php
// config/db.php
// Conexión simple y directa (Leyendo credenciales para que GitHub no se queje)

require_once __DIR__ . '/credentials.php';

try {
    // Conexión "a pelo" sin array de opciones
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8";
    
    $pdo = new PDO($dsn, $db_user, $db_pass);
    
    // Solo activamos reporte de errores
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error Conexión: ' . $e->getMessage()]);
    exit;
}
?>