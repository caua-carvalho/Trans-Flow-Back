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

        // normaliza tudo pra minúsculo
        $requestUri = strtolower(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $scriptName = strtolower(str_replace('\\', '/', $_SERVER['SCRIPT_NAME']));

        $basePath = dirname($scriptName);

        $this->path = preg_replace('#^' . $basePath . '#', '', $requestUri);

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

        // DEBUG EXTRA COMPLETO
        $debug = [
            "success" => false,
            "error" => "Endpoint não encontrado",
            "php" => [
                "REQUEST_URI" => $_SERVER['REQUEST_URI'] ?? null,
                "SCRIPT_NAME" => $_SERVER['SCRIPT_NAME'] ?? null,
                "SCRIPT_FILENAME" => $_SERVER['SCRIPT_FILENAME'] ?? null,
                "DOCUMENT_ROOT" => $_SERVER['DOCUMENT_ROOT'] ?? null,
                "method" => $this->method,
            ],
            "router" => [
                "calculatedPath" => $this->path,
                "registeredRoutes" => array_map(function($r) {
                    return [
                        "method" => $r['method'],
                        "pattern" => $r['pattern']
                    ];
                }, $this->routes)
            ]
        ];

        header("Content-Type: application/json");
        echo json_encode($debug, JSON_PRETTY_PRINT);
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
