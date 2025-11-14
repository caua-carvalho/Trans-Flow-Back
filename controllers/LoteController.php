<?php
/**
 * Controlador de Lotes
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/Response.php';

class LoteController {
    private $db;
    private $table = 'lotes';

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getAll() {
        try {
            $query = "SELECT * FROM " . $this->table;
            
            if (isset($_GET['container_id'])) {
                $query .= " WHERE container_id = :container_id";
            }
            
            $query .= " ORDER BY data_criacao DESC";
            
            $stmt = $this->db->prepare($query);
            
            if (isset($_GET['container_id'])) {
                $stmt->bindParam(':container_id', $_GET['container_id'], PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo Response::success($data);
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }

    public function getById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$data) {
                echo Response::notFound("Lote não encontrado");
                return;
            }
            
            echo Response::success($data);
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }

    public function getByCodigo($codigo) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE codigo = :codigo";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':codigo', $codigo);
            $stmt->execute();
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$data) {
                echo Response::notFound("Lote não encontrado");
                return;
            }
            
            echo Response::success($data);
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }

    public function create() {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            
            if (!isset($input['codigo']) || empty($input['codigo'])) {
                echo Response::error("Campo 'codigo' é obrigatório");
                return;
            }

            if (!isset($input['data_envio'])) {
                echo Response::error("Campo 'data_envio' é obrigatório");
                return;
            }

            $query = "INSERT INTO " . $this->table . " 
                      (container_id, codigo, status, data_envio, data_recebimento, observacoes) 
                      VALUES (:container_id, :codigo, :status, :data_envio, :data_recebimento, :observacoes)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':container_id', $input['container_id'] ?? null, PDO::PARAM_INT);
            $stmt->bindParam(':codigo', $input['codigo']);
            $stmt->bindParam(':status', $input['status'] ?? 'aguardando_coleta');
            $stmt->bindParam(':data_envio', $input['data_envio']);
            $stmt->bindParam(':data_recebimento', $input['data_recebimento'] ?? null);
            $stmt->bindParam(':observacoes', $input['observacoes'] ?? null);
            
            if ($stmt->execute()) {
                $id = $this->db->lastInsertId();
                $this->logHistory('lote', $id, 'Lote criado', null);
                echo Response::created(['id' => $id], "Lote criado com sucesso");
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'unique') !== false) {
                echo Response::error("Código de lote já existe");
            } else {
                echo Response::serverError($e->getMessage());
            }
        }
    }

    public function update($id) {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            
            $fields = [];
            $params = [':id' => $id];
            
            if (isset($input['container_id'])) {
                $fields[] = "container_id = :container_id";
                $params[':container_id'] = $input['container_id'];
            }
            if (isset($input['codigo'])) {
                $fields[] = "codigo = :codigo";
                $params[':codigo'] = $input['codigo'];
            }
            if (isset($input['status'])) {
                $fields[] = "status = :status";
                $params[':status'] = $input['status'];
            }
            if (isset($input['observacoes'])) {
                $fields[] = "observacoes = :observacoes";
                $params[':observacoes'] = $input['observacoes'];
            }
            
            if (empty($fields)) {
                echo Response::error("Nenhum campo para atualizar");
                return;
            }
            
            $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $this->logHistory('lote', $id, 'Lote atualizado', json_encode($input));
            echo Response::success(null, "Lote atualizado com sucesso");
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }

    public function updateStatus($id) {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            
            if (!isset($input['status'])) {
                echo Response::error("Campo 'status' é obrigatório");
                return;
            }

            $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $input['status']);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $this->logHistory('lote', $id, 'Status atualizado', 'Novo status: ' . $input['status']);
                echo Response::success(null, "Status atualizado com sucesso");
            }
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $this->logHistory('lote', $id, 'Lote deletado', null);
                echo Response::success(null, "Lote deletado com sucesso");
            }
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }

    private function logHistory($tipo, $refId, $acao, $detalhes) {
        try {
            $query = "INSERT INTO historico (tipo, referencia_id, acao, detalhes) 
                      VALUES (:tipo, :referencia_id, :acao, :detalhes)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':tipo' => $tipo,
                ':referencia_id' => $refId,
                ':acao' => $acao,
                ':detalhes' => $detalhes
            ]);
        } catch (Exception $e) {
            // Log silencioso
        }
    }
}
?>
