CREATE DATABASE IF NOT EXISTS ventas;
USE ventas;

CREATE TABLE IF NOT EXISTS vendedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO vendedores (nombre, apellido, email) VALUES
('Juan', 'Perez', 'juan.perez@example.com'),
('Maria', 'Gomez', 'maria.gomez@example.com'),
('Carlos', 'Ramirez', 'carlos.ramirez@example.com');
