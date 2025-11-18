<?php
// Permitir peticiones desde cualquier origen (CORS) - Útil para desarrollo
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Manejar petición OPTIONS (pre-flight request para CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluimos el controlador
require_once __DIR__ . '/../controlador/denuncia_controlador.php';

// Creamos una instancia del controlador
$controlador = new DenunciaControlador();

// Dejamos que el controlador maneje la petición
$controlador->manejarPeticion();

?>