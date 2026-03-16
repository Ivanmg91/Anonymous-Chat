<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin</title>
    <link rel="stylesheet" href="../../frontend/css/style.css">
</head>
<body>
    <div class="app-container admin-container">
        <header class="chat-header">
            <strong>Panel de Administracion</strong>
            <a href="../api/logout.php" class="btn btn-small" style="background: #444; width:auto;">Cerrar sesion</a>
        </header>

        <main class="admin-main">
            <section class="admin-card">
                <h3>Crear usuario</h3>
                <form id="adminCreateForm" class="admin-form">
                    <input type="text" id="newUser" placeholder="Usuario" required autocomplete="off">
                    <input type="password" id="newPassword" placeholder="Contrasena" required autocomplete="new-password">
                    <select id="newRole" required>
                        <option value="profesor">Profesor</option>
                        <option value="alumno">Alumno</option>
                    </select>
                    <button type="submit" class="btn">Dar de alta</button>
                </form>
                <p id="adminMessage" class="admin-message"></p>
            </section>

            <section class="admin-card">
                <div class="admin-list-header">
                    <h3>Profesores</h3>
                    <button id="refreshProfesores" class="btn btn-small" type="button">Actualizar</button>
                </div>
                <ul id="profesoresList" class="user-list admin-user-list">
                    <li class="admin-empty">Cargando...</li>
                </ul>
            </section>

            <section class="admin-card">
                <div class="admin-list-header">
                    <h3>Alumnos</h3>
                    <button id="refreshAlumnos" class="btn btn-small" type="button">Actualizar</button>
                </div>
                <ul id="alumnosList" class="user-list admin-user-list">
                    <li class="admin-empty">Cargando...</li>
                </ul>
            </section>
        </main>
    </div>

    <script src="../../frontend/js/admin_panel.js"></script>
</body>
</html>
