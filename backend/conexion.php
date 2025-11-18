<?php
class Conexion {
    public static function conectar() {
        $host = 'localhost';
        $db = 'municipalidad_db'; 
        $usuario = 'root';        
        $contrasena = '';         
        $charset = 'utf8mb4';

        try {
            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            // Crear la instancia de PDO
            $pdo = new PDO($dsn, $usuario, $contrasena, $opciones);
            
            return $pdo;

        } catch (PDOException $e) {
            // Manejo de errores de conexión
            echo 'Error de conexión: ' . $e->getMessage();
            exit;
        }
    }
}
?>