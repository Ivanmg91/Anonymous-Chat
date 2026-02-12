CREATE TABLE IF NOT EXISTS mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contenido TEXT NOT NULL,
    tipo_filtro ENUM('HAM', 'SPAM') DEFAULT 'HAM',
    esta_borrado TINYINT(1) DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar un mensaje de prueba
INSERT INTO mensajes (contenido, tipo_filtro) VALUES ('¡Hola, este es mi primer mensaje!', 'HAM');
INSERT INTO mensajes (contenido, tipo_filtro) VALUES ('Gana dinero rápido haciendo clic aquí', 'SPAM');