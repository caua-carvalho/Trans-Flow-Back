<?php
/**
 * Controlador de Histórico
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/Response.php';

class HistoricoController {
    private $db;
    private $table = 'historico';

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getAll() {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE 1=1";
            $params = [];
            
            if (isset($_GET['tipo'])) {
                $query .= " AND tipo = :tipo";
                $params[':tipo'] = $_GET['tipo'];
            }
            
            if (isset($_GET['referencia_id'])) {
                $query .= " AND referencia_id = :referencia_id";
                $params[':referencia_id'] = $_GET['referencia_id'];
            }
            
            if (isset($_GET['data_inicio'])) {
                $query .= " AND data_evento >= :data_inicio";
                $params[':data_inicio'] = $_GET['data_inicio'] . ' 00:00:00';
            }
            
            if (isset($_GET['data_fim'])) {
                $query .= " AND data_evento <= :data_fim";
                $params[':data_fim'] = $_GET['data_fim'] . ' 23:59:59';
            }
            
            $query .= " ORDER BY data_evento DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo Response::success($data);
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }

    public function create() {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            
            if (!isset($input['tipo']) || empty($input['tipo'])) {
                echo Response::error("Campo 'tipo' é obrigatório");
                return;
            }

            if (!isset($input['referencia_id']) || empty($input['referencia_id'])) {
                echo Response::error("Campo 'referencia_id' é obrigatório");
                return;
            }

            if (!isset($input['acao']) || empty($input['acao'])) {
                echo Response::error("Campo 'acao' é obrigatório");
                return;
            }

            $query = "INSERT INTO " . $this->table . " 
                      (tipo, referencia_id, acao, usuario, detalhes) 
                      VALUES (:tipo, :referencia_id, :acao, :usuario, :detalhes)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':tipo', $input['tipo']);
            $stmt->bindParam(':referencia_id', $input['referencia_id']);
            $stmt->bindParam(':acao', $input['acao']);
            $stmt->bindParam(':usuario', $input['usuario'] ?? null);
            $stmt->bindParam(':detalhes', $input['detalhes'] ?? null);
            
            if ($stmt->execute()) {
                $id = $this->db->lastInsertId();
                echo Response::created(['id' => $id], "Evento registrado com sucesso");
            }
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }

    public function export() {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE 1=1";
            $params = [];
            
            if (isset($_GET['tipo'])) {
                $query .= " AND tipo = :tipo";
                $params[':tipo'] = $_GET['tipo'];
            }
            
            if (isset($_GET['data_inicio'])) {
                $query .= " AND data_evento >= :data_inicio";
                $params[':data_inicio'] = $_GET['data_inicio'] . ' 00:00:00';
            }
            
            if (isset($_GET['data_fim'])) {
                $query .= " AND data_evento <= :data_fim";
                $params[':data_fim'] = $_GET['data_fim'] . ' 23:59:59';
            }
            
            $query .= " ORDER BY data_evento DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Configurar headers para download CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="historico_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // Headers do CSV
            if (!empty($data)) {
                fputcsv($output, array_keys($data[0]), ';');
                
                foreach ($data as $row) {
                    fputcsv($output, $row, ';');
                }
            }
            
            fclose($output);
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }
}
?>
