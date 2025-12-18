CREATE DATABASE login;
USE login;

CREATE TABLE usuarios (
    user VARCHAR(50) PRIMARY KEY,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL, -- Aquí guardarás $_SESSION['user']
    mensaje TEXT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    -- Esto conecta ambas tablas (opcional, pero recomendado para integridad)
    FOREIGN KEY (usuario) REFERENCES usuarios(user) 
);

-- Contraseña: 123456
INSERT INTO usuarios (user, password) 
VALUES ('test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');