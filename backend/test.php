<?php
require 'Conexion.php'; // Asegúrate de que Conexion.php esté en la misma carpeta

$pdo = Conexion::conectar();

if ($pdo) {
    echo "¡Conexión exitosa!";
} else {
    echo "No se pudo conectar.";
}
