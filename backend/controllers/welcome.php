<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../../frontend/index.html');
    exit;
}

// SI ERES ALUMNO -> Vista de chat simple
// El alumno siempre va a su propia sala (su usuario)
if ($_SESSION['role'] == 'alumno') {
    include 'student_view.php';
    exit;
}

// SI ERES PROFESOR -> Dashboard de control
// El profesor necesita ver la lista de todos
if ($_SESSION['role'] == 'profesor') {
    include 'teacher_dashboard.php';
    exit;
}

// SI ERES ADMIN (Opcional por ahora)
if ($_SESSION['role'] == 'admin') {
    include 'admin_dashboard.php';
    exit;
}

header('Location: ../../frontend/index.html');
exit;
