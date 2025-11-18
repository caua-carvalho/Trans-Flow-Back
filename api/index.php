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

// Inicializar roteador
$router = new Router();

// ===== ROUTES: CENTROS DE DISTRIBUICAO =====Open. Azure. Service code. Hey, Cortana. Hey, Cortana. Tune into Mujhpe Naam message from exclusive actress Packing Sunny speaking as Associate Kumaraswamy's operation Shopping in town. Cortana. Hey, Cortana. Hey, Cortana. Hey, Cortana play. Play let me call you. Hey, Cortana. Hey, Cortana. C Drive. Pulling sucker. Close. Help. Hey, Cortana. Closes them and do them by saying you SFAM, racing, SSN. Window Machine does a. Hey, Cortana. Hey, Cortana. Play Let It Go. Pasta. What's up good for the director of searching for Newcastle? I like to go to the wedding in a machine. I bring something romance. Hey, Cortana. Mossadi Ko, do you have a search for me? A quizzes on my list. Youtube. Hey, Cortana. Please tell me. Hey, Cortana. Hey, Cortana. Hey, Cortana. Hey, Cortana. Hey, Cortana. Hey, Cortana. Hey, Cortana. Hey, Cortana. Hey, Cortana. Advancing that you last time. Sushi near Karma at 6:00 PM. Hey, Cortana. Hey, Cortana. Play send. Time was it done. Play Amanda. Hey, Cortana. Hey, Cortana. Hey, Cortana. Youtube. Hey, Cortana. Hey, Cortana. Hey, Cortana. Youtube. Can you sing open Shady Cow? We are simply kind of spots with usual location of cities individuals but only juggled for much of my fire abink. Like. Can you start? Hey, Cortana. Hey, Cortana. What's the weather? Like in HP. 
$router->route('GET', '/centros-distribuicao', function() {
    $controller = new CentroDistribuicaoController();
    $controller->getAll();
});

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
