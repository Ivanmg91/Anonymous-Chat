<?php
// backend/config/db.php
// Conexión simple y directa usando variables de entorno o un archivo local ignorado.

$localCredentials = __DIR__ . '/credentials.local.php';

if (file_exists($localCredentials)) {
    require_once $localCredentials;
}

function getConfigValue($envName, $fallback = null) {
    $value = getenv($envName);

    if ($value !== false && $value !== '') {
        return $value;
    }

    return $fallback;
}

$db_host = getConfigValue('DB_HOST', $db_host ?? null);
$db_name = getConfigValue('DB_NAME', $db_name ?? null);
$db_port = getConfigValue('DB_PORT', $db_port ?? null);
$db_user = getConfigValue('DB_USER', $db_user ?? null);
$db_pass = getConfigValue('DB_PASS', $db_pass ?? null);

try {
    if (!$db_host || !$db_name || !$db_port || !$db_user || !$db_pass) {
        throw new RuntimeException('Credenciales de base de datos no configuradas. Usa variables de entorno o backend/config/credentials.local.php');
    }

    // Conexión "a pelo" sin array de opciones
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8";
    
    $pdo = new PDO($dsn, $db_user, $db_pass);
    
    // Solo activamos reporte de errores
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (Throwable $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error Conexión: ' . $e->getMessage()]);
    exit;
}
?>
