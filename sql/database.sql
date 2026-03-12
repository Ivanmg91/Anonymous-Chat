DROP DATABASE IF EXISTS login;
CREATE DATABASE login;

USE login;

-- 1. Tabla de Usuarios
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
    user VARCHAR(50) PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'profesor', 'alumno') NOT NULL DEFAULT 'alumno'
DROP TABLE IF EXISTS archivos;
DROP TABLE IF EXISTS mensajes;
DROP TABLE IF EXISTS chat_rooms;
DROP TABLE IF EXISTS usuarios;

-- 1. Tabla de Usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'profesor', 'alumno') NOT NULL DEFAULT 'alumno',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuarios_user (user)
);

-- 2. Tabla de Salas de chat
CREATE TABLE chat_rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_key CHAR(32) NOT NULL,
    student_user_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_chat_rooms_room_key (room_key),
    UNIQUE KEY uq_chat_rooms_student_user_id (student_user_id),
    CONSTRAINT fk_chat_rooms_student_user
        FOREIGN KEY (student_user_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 3. Tabla de Mensajes
CREATE TABLE mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_room_id INT NOT NULL,
    sender_user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mensajes_chat_room_created (chat_room_id, created_at, id),
    INDEX idx_mensajes_sender (sender_user_id),
    CONSTRAINT fk_mensajes_chat_room
        FOREIGN KEY (chat_room_id) REFERENCES chat_rooms(id) ON DELETE CASCADE,
    CONSTRAINT fk_mensajes_sender_user
        FOREIGN KEY (sender_user_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 4. Tabla de Archivos adjuntos
CREATE TABLE archivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_room_id INT NOT NULL,
    uploaded_by_user_id INT NOT NULL,
    mensaje_id INT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    storage_path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_archivos_chat_room (chat_room_id, created_at),
    INDEX idx_archivos_uploaded_by (uploaded_by_user_id),
    INDEX idx_archivos_mensaje (mensaje_id),
    CONSTRAINT fk_archivos_chat_room
        FOREIGN KEY (chat_room_id) REFERENCES chat_rooms(id) ON DELETE CASCADE,
    CONSTRAINT fk_archivos_uploaded_by
        FOREIGN KEY (uploaded_by_user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_archivos_mensaje
        FOREIGN KEY (mensaje_id) REFERENCES mensajes(id) ON DELETE SET NULL
);

-- 5. Insertar usuario de prueba (Ahora es ADMIN)
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
-- 6. Insertar un profesor de prueba
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user) REFERENCES usuarios(user) ON DELETE CASCADE,
    FOREIGN KEY (message_id) REFERENCES mensajes(id) ON DELETE SET NULL
);

-- 3. Insertar usuario de prueba (Ahora es ADMIN)
-- Contraseña: password
INSERT INTO usuarios (user, password, role) 
VALUES ('test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- (Opcional) Insertar un profesor de prueba
-- Contraseña: password
INSERT INTO usuarios (user, password, role) 
VALUES ('profe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor');
