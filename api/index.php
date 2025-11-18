<?php
/**
 * API TransFlow - Arquivo principal
 * Ponto de entrada para todas as requisições
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Inclusão de classes necessárias
require_once __DIR__ . '/../utils/Router.php';

require_once __DIR__ . '/../controllers/ContainerController.php';
require_once __DIR__ . '/../controllers/LoteController.php';
require_once __DIR__ . '/../controllers/ProdutoController.php';
require_once __DIR__ . '/../controllers/HistoricoController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../controllers/CentroDistribuicaoController.php';

// ===== ROUTES: CENTROS DE DISTRIBUICAO =====
$router->route('GET', '/centros-distribuicao', function() {
    $controller = new CentroDistribuicaoController();
    $controller->getAll();
});

// Inicializar roteador
$router = new Router();

// ===== ROUTES: CONTAINERS =====
$router->route('GET', '/containers', function() {
    $controller = new ContainerController();
    $controller->getAll();
});

$router->route('GET', '/containers/{id}', function() {
    $controller = new ContainerController();
    $controller->getById($_GET['id']);
});

$router->route('POST', '/containers', function() {
    $controller = new ContainerController();
    $controller->create();
});

$router->route('PUT', '/containers/{id}', function() {
    $controller = new ContainerController();
    $controller->update($_GET['id']);
});

$router->route('PATCH', '/containers/{id}/status', function() {
    $controller = new ContainerController();
    $controller->updateStatus($_GET['id']);
});

$router->route('DELETE', '/containers/{id}', function() {
    $controller = new ContainerController();
    $controller->delete($_GET['id']);
});

// ===== ROUTES: LOTES =====
$router->route('GET', '/lotes', function() {
    $controller = new LoteController();
    $controller->getAll();
});

$router->route('GET', '/lotes/{id}', function() {
    $controller = new LoteController();
    $controller->getById($_GET['id']);
});

$router->route('GET', '/lotes/codigo/{codigo}', function() {
    $controller = new LoteController();
    $controller->getByCodigo($_GET['codigo']);
});

$router->route('POST', '/lotes', function() {
    $controller = new LoteController();
    $controller->create();
});

$router->route('PUT', '/lotes/{id}', function() {
    $controller = new LoteController();
    $controller->update($_GET['id']);
});

$router->route('PATCH', '/lotes/{id}/status', function() {
    $controller = new LoteController();
    $controller->updateStatus($_GET['id']);
});

$router->route('DELETE', '/lotes/{id}', function() {
    $controller = new LoteController();
    $controller->delete($_GET['id']);
});

// ===== ROUTES: PRODUTOS =====
$router->route('GET', '/produtos', function() {
    $controller = new ProdutoController();
    $controller->getAll();
});

$router->route('GET', '/produtos/{id}', function() {
    $controller = new ProdutoController();
    $controller->getById($_GET['id']);
});

$router->route('POST', '/produtos', function() {
    $controller = new ProdutoController();
    $controller->create();
});

$router->route('PUT', '/produtos/{id}', function() {
    $controller = new ProdutoController();
    $controller->update($_GET['id']);
});

$router->route('PATCH', '/produtos/{id}/status', function() {
    $controller = new ProdutoController();
    $controller->updateStatus($_GET['id']);
});

$router->route('DELETE', '/produtos/{id}', function() {
    $controller = new ProdutoController();
    $controller->delete($_GET['id']);
});

// ===== ROUTES: HISTORICO =====
$router->route('GET', '/historico', function() {
    $controller = new HistoricoController();
    $controller->getAll();
});

$router->route('POST', '/historico', function() {
    $controller = new HistoricoController();
    $controller->create();
});

$router->route('GET', '/historico/export', function() {
    $controller = new HistoricoController();
    $controller->export();
});

// ===== ROUTES: DASHBOARD =====
$router->route('GET', '/dashboard/stats', function() {
    $controller = new DashboardController();
    $controller->getStats();
});

// ===== Dispatch =====
$router->dispatch();
?>
