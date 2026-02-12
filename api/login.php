<?php
// api/login.php

// 1. Configuraciones de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();
header('Content-Type: application/json');

require_once '../config/db.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$user_input = $data['user'] ?? '';
$password_input = $data['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE user = ?");
$stmt->execute([$user_input]);
$usuario = $stmt->fetch();

if ($usuario && password_verify($password_input, $usuario['password'])) {
    $_SESSION['user'] = $user_input;
    
    // --- CAMBIO CLAVE: Guardamos el rol en la sesión ---
    // Si por algún motivo el campo role está vacío en BD, asumimos 'alumno'
    $_SESSION['role'] = $usuario['role'] ?? 'alumno'; 
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos']);
}
?>