<?php
/**
 * Classe simples para roteamento de requisições
 */

class Router {
    private $routes = [];
    private $method;
    private $path;

    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        // Remove a base path se necessário
        $this->path = str_replace('/api', '', $this->path);
        if ($this->path === '') {
            $this->path = '/';
        }
    }

    public function route($method, $pattern, $callback) {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'callback' => $callback
        ];
    }

    public function dispatch() {
        foreach ($this->routes as $route) {
            if ($this->method === $route['method'] && $this->matchPattern($route['pattern'])) {
                return call_user_func($route['callback']);
            }
        }
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Endpoint não encontrado']);
    }

    private function matchPattern($pattern) {
        $regex = preg_replace('/\{([a-z_]+)\}/', '(?P<\1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        
        if (preg_match($regex, $this->path, $matches)) {
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $_GET[$key] = $value;
                }
            }
            return true;
        }
        return false;
    }
}
?>
