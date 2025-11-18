CREATE DATABASE municipalidad_db;


USE municipalidad_db;

CREATE TABLE denuncias (
    id INT IDENTITY(1,1) PRIMARY KEY,          
    titulo VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255),
    ubicacion VARCHAR(150),
    estado VARCHAR(20) NOT NULL,
    ciudadano VARCHAR(100) NOT NULL,
    telefono_ciudadano VARCHAR(15),
    fecha_registro DATETIME DEFAULT GETDATE()
);
