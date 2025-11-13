<?php
/**
 * Controlador de Produtos
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/Response.php';

class ProdutoController {
    private $db;
    private $table = 'produtos';

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getAll() {
        try {
            $query = "SELECT * FROM " . $this->table;
            
            if (isset($_GET['lote_id'])) {
                $query .= " WHERE lote_id = :lote_id";
            }
            
            $query .= " ORDER BY data_criacao DESC";
            
            $stmt = $this->db->prepare($query);
            
            if (isset($_GET['lote_id'])) {
                $stmt->bindParam(':lote_id', $_GET['lote_id'], PDO::PARAM_INT);
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
                echo Response::notFound("Produto não encontrado");
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
            
            if (!isset($input['nome']) || empty($input['nome'])) {
                echo Response::error("Campo 'nome' é obrigatório");
                return;
            }

            $query = "INSERT INTO " . $this->table . " 
                      (lote_id, nome, status, localizacao, area, prateleira, data_expedicao) 
                      VALUES (:lote_id, :nome, :status, :localizacao, :area, :prateleira, :data_expedicao)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':lote_id', $input['lote_id'] ?? null, PDO::PARAM_INT);
            $stmt->bindParam(':nome', $input['nome']);
            $stmt->bindParam(':status', $input['status'] ?? 'armazenado');
            $stmt->bindParam(':localizacao', $input['localizacao'] ?? null);
            $stmt->bindParam(':area', $input['area'] ?? null);
            $stmt->bindParam(':prateleira', $input['prateleira'] ?? null);
            $stmt->bindParam(':data_expedicao', $input['data_expedicao'] ?? null);
            
            if ($stmt->execute()) {
                $id = $this->db->lastInsertId();
                $this->logHistory('produto', $id, 'Produto criado', null);
                echo Response::created(['id' => $id], "Produto criado com sucesso");
            }
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }

    public function update($id) {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            
            $fields = [];
            $params = [':id' => $id];
            
            if (isset($input['nome'])) {
                $fields[] = "nome = :nome";
                $params[':nome'] = $input['nome'];
            }
            if (isset($input['status'])) {
                $fields[] = "status = :status";
                $params[':status'] = $input['status'];
            }
            if (isset($input['localizacao'])) {
                $fields[] = "localizacao = :localizacao";
                $params[':localizacao'] = $input['localizacao'];
            }
            if (isset($input['area'])) {
                $fields[] = "area = :area";
                $params[':area'] = $input['area'];
            }
            if (isset($input['prateleira'])) {
                $fields[] = "prateleira = :prateleira";
                $params[':prateleira'] = $input['prateleira'];
            }
            if (isset($input['data_expedicao'])) {
                $fields[] = "data_expedicao = :data_expedicao";
                $params[':data_expedicao'] = $input['data_expedicao'];
            }
            
            if (empty($fields)) {
                echo Response::error("Nenhum campo para atualizar");
                return;
            }
            
            $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $this->logHistory('produto', $id, 'Produto atualizado', json_encode($input));
            echo Response::success(null, "Produto atualizado com sucesso");
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

            $query = "UPDATE " . $this->table . " SET status = :status";
            
            if (isset($input['localizacao'])) {
                $query .= ", localizacao = :localizacao";
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $input['status']);
            
            if (isset($input['localizacao'])) {
                $stmt->bindParam(':localizacao', $input['localizacao']);
            }
            
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $this->logHistory('produto', $id, 'Status atualizado', 'Novo status: ' . $input['status']);
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
                $this->logHistory('produto', $id, 'Produto deletado', null);
                echo Response::success(null, "Produto deletado com sucesso");
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
