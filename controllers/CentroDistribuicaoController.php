<?php
/**
 * Controlador para Centros de Distribuição
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/Response.php';

class CentroDistribuicaoController {
    private $db;
    private $view = 'vw_centros_distribuicao_resumo';

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Retorna todos os centros de distribuição com ocupação
    public function getAll() {
        try {
            $query = "SELECT id, codigo, nome, endereco, capacidade, ocupacao, status FROM " . $this->view . " ORDER BY nome ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo Response::success($data);
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }
}
?>
