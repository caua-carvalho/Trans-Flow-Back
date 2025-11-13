<?php
/**
 * Script de verificação da API
 * Acesse: http://localhost/Trans-Flow-Back/api/test.php
 */

header('Content-Type: application/json; charset=utf-8');

$result = [
    'status' => 'OK',
    'timestamp' => date('Y-m-d H:i:s'),
    'checks' => []
];

// Check 1: PHP Version
$result['checks']['php_version'] = [
    'name' => 'PHP Version',
    'required' => '7.4+',
    'current' => phpversion(),
    'pass' => version_compare(phpversion(), '7.4.0') >= 0
];

// Check 2: PDO PostgreSQL Extension
$result['checks']['pdo_pgsql'] = [
    'name' => 'PDO PostgreSQL Extension',
    'pass' => extension_loaded('pdo_pgsql')
];

// Check 3: Database Connection
try {
    require_once __DIR__ . '/../config/database.php';
    $database = new Database();
    $conn = $database->connect();
    
    // Test query
    $stmt = $conn->query("SELECT version()");
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $result['checks']['database_connection'] = [
        'name' => 'Database Connection',
        'pass' => true,
        'version' => $version['version'] ?? 'Unknown'
    ];
} catch (Exception $e) {
    $result['checks']['database_connection'] = [
        'name' => 'Database Connection',
        'pass' => false,
        'error' => $e->getMessage()
    ];
    $result['status'] = 'ERROR';
}

// Check 4: Tables Existence
try {
    require_once __DIR__ . '/../config/database.php';
    $database = new Database();
    $conn = $database->connect();
    
    $tables = ['containers', 'lotes', 'produtos', 'historico', 'usuarios'];
    $tables_status = [];
    
    foreach ($tables as $table) {
        $stmt = $conn->query("SELECT EXISTS (
            SELECT 1 FROM information_schema.tables 
            WHERE table_name = '$table'
        )");
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);
        $tables_status[$table] = (bool)$exists['exists'];
    }
    
    $result['checks']['tables'] = [
        'name' => 'Database Tables',
        'pass' => !in_array(false, $tables_status),
        'tables' => $tables_status
    ];
} catch (Exception $e) {
    $result['checks']['tables'] = [
        'name' => 'Database Tables',
        'pass' => false,
        'error' => $e->getMessage()
    ];
}

// Check 5: File Permissions
$files = [
    'api/index.php',
    'config/database.php',
    'controllers/ContainerController.php',
    'utils/Response.php',
    'utils/Router.php'
];

$files_status = [];
foreach ($files as $file) {
    $path = __DIR__ . '/../' . $file;
    $files_status[$file] = file_exists($path) && is_readable($path);
}

$result['checks']['files'] = [
    'name' => 'Required Files',
    'pass' => !in_array(false, $files_status),
    'files' => $files_status
];

// Overall status
$all_pass = true;
foreach ($result['checks'] as $check) {
    if ($check['pass'] === false) {
        $all_pass = false;
        break;
    }
}

$result['status'] = $all_pass ? 'OK' : 'ERROR';

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
