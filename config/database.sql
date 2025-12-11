CREATE DATABASE login;
USE login;

CREATE TABLE usuarios (
    user VARCHAR(50) PRIMARY KEY,
    password VARCHAR(255) NOT NULL
);

-- Contraseña: 123456
INSERT INTO usuarios (user, password) 
VALUES ('test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');