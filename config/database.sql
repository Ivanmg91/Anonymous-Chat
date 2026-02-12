DROP DATABASE IF EXISTS login;
CREATE DATABASE login;

USE login;

-- 1. Tabla de Usuarios
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
    user VARCHAR(50) PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'profesor', 'alumno') NOT NULL DEFAULT 'alumno'
);

-- 2. Tabla de Mensajes
DROP TABLE IF EXISTS mensajes;
CREATE TABLE mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user VARCHAR(50) NOT NULL,
    chat_room VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    -- ON DELETE CASCADE: Si borras un usuario, se borran sus mensajes automáticamente
    FOREIGN KEY (user) REFERENCES usuarios(user) ON DELETE CASCADE
);

-- 3. Insertar usuario de prueba (Ahora es ADMIN)
-- Contraseña: password
INSERT INTO usuarios (user, password, role) 
VALUES ('test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- (Opcional) Insertar un profesor de prueba
-- Contraseña: password
INSERT INTO usuarios (user, password, role) 
VALUES ('profe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor');