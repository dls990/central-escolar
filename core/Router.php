<?php
// Registra e encaminha as rotas da aplicacao.




namespace Core;

class Router {
    private static array $routes = [];

    // Associa uma rota GET a uma acao de controller.
    public static function get(string $path, string $handler): void {
        self::$routes['GET'][$path] = $handler;
    }

    // Associa uma rota POST a uma acao de controller.
    public static function post(string $path, string $handler): void {
        self::$routes['POST'][$path] = $handler;
    }

    // Localiza a rota correspondente ao metodo e ao caminho solicitados.
    public static function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = self::resolveUri();


        if (strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        $uri = '/' . trim($uri, '/');


        if (isset(self::$routes[$method][$uri])) {
            self::executeHandler(self::$routes[$method][$uri]);
            return;
        }


        http_response_code(404);
        require_once __DIR__ . '/../app/Views/errors/404.php';
    }

    // Normaliza o endereco e remove a pasta base usada no servidor.
    private static function resolveUri(): string {

        if (!empty($_GET['url'])) {
            return '/' . trim($_GET['url'], '/');
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = dirname($scriptName);


        if ($basePath !== '/' && $basePath !== '\\' && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }


        $requestUri = preg_replace('/^\/index\.php/', '', $requestUri);

        return empty($requestUri) ? '/' : $requestUri;
    }

    // Cria o controller e chama a acao registrada para a rota.
    private static function executeHandler(string $handler): void {
        [$controllerName, $action] = explode('@', $handler);
        $fullControllerClass = "App\\Controllers\\{$controllerName}";

        if (!class_exists($fullControllerClass)) {
            die("Controller não encontrado: {$fullControllerClass}");
        }

        $controller = new $fullControllerClass();

        if (!method_exists($controller, $action)) {
            die("Método {$action} não encontrado no controller {$fullControllerClass}");
        }

        $controller->$action();
    }
}
