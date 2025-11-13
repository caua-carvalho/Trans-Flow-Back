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
        $this->host = getenv('DB_HOST') !== false ? getenv('DB_HOST') : 'localhost';
        $this->port = getenv('DB_PORT') !== false ? getenv('DB_PORT') : '5432';
        $this->db = getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'transflow';
        $this->user = getenv('DB_USER') !== false ? getenv('DB_USER') : 'root';
        $this->password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
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
