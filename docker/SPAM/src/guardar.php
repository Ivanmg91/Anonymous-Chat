<?php
require_once 'db.php';

function consultarDetectorSpam($texto) {
    $url = 'http://spam_detector:5000/predict';
    $data = json_encode(['texto' => $texto]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $data
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    if ($response === FALSE) {
        return 'HAM'; // Por defecto si falla la consulta
    }
    
    $result = json_decode($response, true);
    return $result['resultado'] ?? 'HAM';
}

// Leemos el contenido JSON enviado desde JS
$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data['contenido'])) {
    try {
        $tipoFiltro = consultarDetectorSpam($data['contenido']);
        
        $stmt = $pdo->prepare("INSERT INTO mensajes (contenido, tipo_filtro) VALUES (?, ?)");
        $stmt->execute([$data['contenido'], $tipoFiltro]);
        
        echo json_encode([
            'status' => 'success', 
            'mensaje' => $data['contenido'],
            'tipo' => $tipoFiltro
        ]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}