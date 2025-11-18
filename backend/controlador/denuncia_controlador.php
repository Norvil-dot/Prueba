<?php
// Incluimos el modelo que está en la carpeta 'modelo'
require_once __DIR__ . '/../modelo/Denuncia.php';

class DenunciaControlador {
    private $modeloDenuncia;

    public function __construct() {
        $this->modeloDenuncia = new Denuncia();
    }

    // Método principal que maneja todas las peticiones (API)
    public function manejarPeticion() {
        // Obtenemos el método de la petición (GET, POST, PUT, DELETE)
        $metodo = $_SERVER['REQUEST_METHOD'];

        // Obtenemos la 'accion' de la URL (ej: /api/denuncias_api.php?accion=listar)
        $accion = isset($_GET['accion']) ? $_GET['accion'] : null;

        switch ($metodo) {
            case 'GET':
                if ($accion == 'listar') {
                    $this->listar();
                } elseif ($accion == 'obtener' && isset($_GET['id'])) {
                    $this->obtener($_GET['id']);
                }
                break;
            
            case 'POST':
                if ($accion == 'crear') {
                    $this->crear();
                } elseif ($accion == 'actualizar') {
                    $this->actualizar();
                }
                break;

            case 'DELETE':
                if ($accion == 'eliminar' && isset($_GET['id'])) {
                    $this->eliminar($_GET['id']);
                }
                break;
            
            default:
                $this->jsonResponse(['error' => 'Método no permitido'], 405);
                break;
        }
    }

    // ---- MÉTODOS DEL CONTROLADOR ----

    private function listar() {
        // Parámetros de búsqueda y paginación
        $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = 5; // Cuántos registros por página

        $datos = $this->modeloDenuncia->leer($busqueda, $pagina, $porPagina);
        $this->jsonResponse($datos);
    }

    private function obtener($id) {
        $denuncia = $this->modeloDenuncia->obtenerPorId($id);
        if ($denuncia) {
            $this->jsonResponse($denuncia);
        } else {
            $this->jsonResponse(['error' => 'Denuncia no encontrada'], 404);
        }
    }

    private function crear() {
        // Obtenemos los datos del formulario (enviados por POST)
        $datos = $_POST;

        // Asignamos los datos al modelo
        $this->modeloDenuncia->titulo = $datos['titulo'];
        $this->modeloDenuncia->descripcion = $datos['descripcion'];
        $this->modeloDenuncia->ubicacion = $datos['ubicacion'];
        $this->modeloDenuncia->estado = $datos['estado'];
        $this->modeloDenuncia->ciudadano = $datos['ciudadano'];
        $this->modeloDenuncia->telefono_ciudadano = $datos['telefono_ciudadano'];

        if ($this->modeloDenuncia->crear()) {
            $this->jsonResponse(['mensaje' => 'Denuncia creada con éxito'], 201);
        } else {
            $this->jsonResponse(['error' => 'Error al crear la denuncia'], 500);
        }
    }

    private function actualizar() {
        $datos = $_POST;

        // Verificamos que el ID esté presente
        if (!isset($datos['id'])) {
            $this->jsonResponse(['error' => 'ID no proporcionado'], 400);
            return;
        }

        // Asignamos los datos al modelo
        $this->modeloDenuncia->id = $datos['id'];
        $this->modeloDenuncia->titulo = $datos['titulo'];
        $this->modeloDenuncia->descripcion = $datos['descripcion'];
        $this->modeloDenuncia->ubicacion = $datos['ubicacion'];
        $this->modeloDenuncia->estado = $datos['estado'];
        $this->modeloDenuncia->ciudadano = $datos['ciudadano'];
        $this->modeloDenuncia->telefono_ciudadano = $datos['telefono_ciudadano'];

        if ($this->modeloDenuncia->actualizar()) {
            $this->jsonResponse(['mensaje' => 'Denuncia actualizada con éxito']);
        } else {
            $this->jsonResponse(['error' => 'Error al actualizar la denuncia'], 500);
        }
    }

    private function eliminar($id) {
        $this->modeloDenuncia->id = $id;
        
        if ($this->modeloDenuncia->eliminar()) {
            $this->jsonResponse(['mensaje' => 'Denuncia eliminada con éxito']);
        } else {
            $this->jsonResponse(['error' => 'Error al eliminar la denuncia'], 500);
        }
    }

    // --- Función utilitaria para responder en JSON ---
    private function jsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }
}
?>