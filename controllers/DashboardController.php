<?php
/**
 * Controlador de Dashboard
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/Response.php';

class DashboardController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getStats() {
        try {
            // Containers aguardando coleta
            $query1 = "SELECT COUNT(*) as total FROM containers WHERE status = 'aguardando_coleta'";
            $stmt1 = $this->db->prepare($query1);
            $stmt1->execute();
            $containers_aguardando = $stmt1->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Containers em trânsito
            $query2 = "SELECT COUNT(*) as total FROM containers WHERE status = 'em_transito'";
            $stmt2 = $this->db->prepare($query2);
            $stmt2->execute();
            $containers_transito = $stmt2->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Containers no CD
            $query3 = "SELECT COUNT(*) as total FROM containers WHERE status = 'chegou_cd'";
            $stmt3 = $this->db->prepare($query3);
            $stmt3->execute();
            $containers_cd = $stmt3->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Containers finalizados
            $query4 = "SELECT COUNT(*) as total FROM containers WHERE status = 'finalizado'";
            $stmt4 = $this->db->prepare($query4);
            $stmt4->execute();
            $containers_finalizados = $stmt4->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Lotes em transporte
            $query5 = "SELECT COUNT(*) as total FROM lotes WHERE status = 'em_transito'";
            $stmt5 = $this->db->prepare($query5);
            $stmt5->execute();
            $lotes_transito = $stmt5->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Lotes no CD
            $query6 = "SELECT COUNT(*) as total FROM lotes WHERE status = 'chegou_cd'";
            $stmt6 = $this->db->prepare($query6);
            $stmt6->execute();
            $lotes_cd = $stmt6->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Produtos expedidos
            $query7 = "SELECT COUNT(*) as total FROM produtos WHERE status = 'expedido'";
            $stmt7 = $this->db->prepare($query7);
            $stmt7->execute();
            $produtos_expedidos = $stmt7->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Produtos entregues
            $query8 = "SELECT COUNT(*) as total FROM produtos WHERE status = 'entregue'";
            $stmt8 = $this->db->prepare($query8);
            $stmt8->execute();
            $produtos_entregues = $stmt8->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Total de eventos hoje
            $query9 = "SELECT COUNT(*) as total FROM historico WHERE DATE(data_evento) = CURRENT_DATE";
            $stmt9 = $this->db->prepare($query9);
            $stmt9->execute();
            $eventos_hoje = $stmt9->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stats = [
                'containers_aguardando' => intval($containers_aguardando),
                'containers_transito' => intval($containers_transito),
                'containers_cd' => intval($containers_cd),
                'containers_finalizados' => intval($containers_finalizados),
                'lotes_transito' => intval($lotes_transito),
                'lotes_cd' => intval($lotes_cd),
                'produtos_expedidos' => intval($produtos_expedidos),
                'produtos_entregues' => intval($produtos_entregues),
                'eventos_hoje' => intval($eventos_hoje)
            ];
            
            echo Response::success($stats);
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }
}
?>
