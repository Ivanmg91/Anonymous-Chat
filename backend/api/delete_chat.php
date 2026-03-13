<?php
    // backend/api/new_chat.php
    session_start();
    require_once '../config/db.php';

    if (!isset($_SESSION['user_id'], $_SESSION['user'])) {
        echo json_encode([]);
        exit;
    }

    try {

        $user_id = (int) $_SESSION['user_id'];

        // Eliminar usuario actual, en cadena borramos todo lo relacionado (gracias a ON DELETE CASCADE)
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$user_id]);

        // Redirigir a logout
        header('Location: /backend/api/logout.php');
        exit;
    } catch (\Throwable $th) {
        // Si falla, volvemos al login con error
        header('Location: ../../frontend/index.html?error=' . urlencode($th->getMessage()));
        exit;
    }
?>