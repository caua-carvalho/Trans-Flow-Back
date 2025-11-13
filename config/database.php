<?php
/**
 * Configuração de Conexão com PostgreSQL
 */

class Database {
    // Use environment variables when available (for Docker / CI). Fall back to sensible defaults.
    private $host;
    private $db;
    private $user;
    private $password;
    private $port;
    private $conn;

    public function __construct()
    {
        $this->host = getenv('DB_HOST') !== false ? getenv('DB_HOST') : 'dpg-d3sc29re5dus73e0ngmg-a.oregon-postgres.render.com';
        $this->port = getenv('DB_PORT') !== false ? getenv('DB_PORT') : '5432';
        $this->db = getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'trans_flow';
        $this->user = getenv('DB_USER') !== false ? getenv('DB_USER') : 'trans_flow_user';
        $this->password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'Gkg0LiqDETtkzRpKjRPbyPPCaZOqi6by';
    }

    public function connect() {
        $this->conn = null;

        try {
            $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db;
            $this->conn = new PDO($dsn, $this->user, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Erro de conexão: ' . $e->getMessage()
            ]);
            exit;
        }

        return $this->conn;
    }
}
?>
