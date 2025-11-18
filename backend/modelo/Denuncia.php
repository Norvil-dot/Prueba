<?php
require_once __DIR__ . '/../conexion.php'; // <-- ¡Esta es la línea clave!


class Denuncia {
    private $pdo;

    // Campos de la tabla
    public $id;
    public $titulo;
    public $descripcion;
    public $ubicacion;
    public $estado;
    public $ciudadano;
    public $telefono_ciudadano;
    

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    
    public function crear() {
        try {
            $sql = "INSERT INTO denuncias (titulo, descripcion, ubicacion, estado, ciudadano, telefono_ciudadano) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $this->titulo,
                $this->descripcion,
                $this->ubicacion,
                $this->estado,
                $this->ciudadano,
                $this->telefono_ciudadano
            ]);
            return true;
        } catch (PDOException $e) {
            echo 'Error al crear: ' . $e->getMessage();
            return false;
        }
    }

    public function leer($terminoBusqueda = '', $pagina = 1, $porPagina = 5) {
        try {
            $offset = ($pagina - 1) * $porPagina;
            $terminoBusqueda = "%{$terminoBusqueda}%"; // Para usar con LIKE

            // Consulta para los datos paginados y con búsqueda
            $sql = "SELECT * FROM denuncias 
                    WHERE titulo LIKE ? OR ciudadano LIKE ? OR ubicacion LIKE ?
                    ORDER BY id DESC
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$terminoBusqueda, $terminoBusqueda, $terminoBusqueda, $porPagina, $offset]);
            $resultados = $stmt->fetchAll();

            // Consulta para el conteo total (para la paginación)
            $sqlTotal = "SELECT COUNT(*) as total FROM denuncias 
                         WHERE titulo LIKE ? OR ciudadano LIKE ? OR ubicacion LIKE ?";
            
            $stmtTotal = $this->pdo->prepare($sqlTotal);
            $stmtTotal->execute([$terminoBusqueda, $terminoBusqueda, $terminoBusqueda]);
            $total = $stmtTotal->fetch()['total'];
            
            return [
                'denuncias' => $resultados,
                'total' => $total,
                'pagina' => $pagina,
                'paginas_totales' => ceil($total / $porPagina)
            ];

        } catch (PDOException $e) {
            echo 'Error al leer: ' . $e->getMessage();
            return [];
        }
    }

    public function actualizar() {
        try {
            $sql = "UPDATE denuncias SET 
                        titulo = ?, 
                        descripcion = ?, 
                        ubicacion = ?, 
                        estado = ?, 
                        ciudadano = ?, 
                        telefono_ciudadano = ?
                    WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $this->titulo,
                $this->descripcion,
                $this->ubicacion,
                $this->estado,
                $this->ciudadano,
                $this->telefono_ciudadano,
                $this->id
            ]);
            return true;
        } catch (PDOException $e) {
            echo 'Error al actualizar: ' . $e->getMessage();
            return false;
        }
    }

    public function eliminar() {
        try {
            $sql = "DELETE FROM denuncias WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$this->id]);
            return true;
        } catch (PDOException $e) {
            echo 'Error al eliminar: ' . $e->getMessage();
            return false;
        }
    }

    public function obtenerPorId($id) {
        try {
            $sql = "SELECT * FROM denuncias WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            echo 'Error al obtener: ' . $e->getMessage();
            return false;
        }
    }
}
?>