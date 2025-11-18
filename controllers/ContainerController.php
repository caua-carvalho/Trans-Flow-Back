<?php
/**
 * Controlador de Containers
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/Response.php';

class ContainerController {
    private $db;
    private $table = 'containers';

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getAll() {
        try {
            $query = "SELECT * FROM " . $this->table . " ORDER BY data_criacao DESC";
            $stmt = $this->db->prepare($query);
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
                echo Response::notFound("Container não encontrado");
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

            $query = "INSERT INTO " . $this->table . " 
                      (codigo, status, origem, destino, data_atualizacao) 
                      VALUES (:codigo, :status, :origem, :destino, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':codigo', $input['codigo']);
            $stmt->bindValue(':status', $input['status'] ?? 'aguardando_coleta');
            $stmt->bindValue(':origem', $input['origem'] ?? null);
            $stmt->bindValue(':destino', $input['destino'] ?? null);
            
            if ($stmt->execute()) {
                $id = $this->db->lastInsertId();
                $this->logHistory('container', $id, 'Container criado', null);
                echo Response::created(['id' => $id], "Container criado com sucesso");
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'unique') !== false) {
                echo Response::error("Código de container já existe");
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
            
            if (isset($input['codigo'])) {
                $fields[] = "codigo = :codigo";
                $params[':codigo'] = $input['codigo'];
            }
            if (isset($input['status'])) {
                $fields[] = "status = :status";
                $params[':status'] = $input['status'];
            }
            if (isset($input['origem'])) {
                $fields[] = "origem = :origem";
                $params[':origem'] = $input['origem'];
            }
            if (isset($input['destino'])) {
                $fields[] = "destino = :destino";
                $params[':destino'] = $input['destino'];
            }
            
            if (empty($fields)) {
                echo Response::error("Nenhum campo para atualizar");
                return;
            }
            
            $fields[] = "data_atualizacao = NOW()";
            $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $this->logHistory('container', $id, 'Container atualizado', json_encode($input));
            echo Response::success(null, "Container atualizado com sucesso");
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

            $query = "UPDATE " . $this->table . " SET status = :status, data_atualizacao = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $input['status']);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $this->logHistory('container', $id, 'Status atualizado', 'Novo status: ' . $input['status']);
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
                $this->logHistory('container', $id, 'Container deletado', null);
                echo Response::success(null, "Container deletado com sucesso");
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
