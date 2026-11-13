<?php
/**
 * Router - Roteador simplificado compatível com servidor embutido do PHP e XAMPP/WAMP
 */
namespace Core;

class Router {
    private static array $routes = [];

    public static function get(string $path, string $handler): void {
        self::$routes['GET'][$path] = $handler;
    }

    public static function post(string $path, string $handler): void {
        self::$routes['POST'][$path] = $handler;
    }

    public static function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = self::resolveUri();

        // Limpa query strings
        if (strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        $uri = '/' . trim($uri, '/');

        // Busca correspondência exata
        if (isset(self::$routes[$method][$uri])) {
            self::executeHandler(self::$routes[$method][$uri]);
            return;
        }

        // Rota 404
        http_response_code(404);
        require_once __DIR__ . '/../app/Views/errors/404.php';
    }

    private static function resolveUri(): string {
        // Suporte para parâmetro 'url' ou 'route' (útil em ambientes sem mod_rewrite)
        if (!empty($_GET['url'])) {
            return '/' . trim($_GET['url'], '/');
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = dirname($scriptName);

        // Remove a pasta base do REQUEST_URI caso esteja em subdiretório (ex: /central-escolar/public)
        if ($basePath !== '/' && $basePath !== '\\' && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }

        // Remove index.php se estiver na URL
        $requestUri = preg_replace('/^\/index\.php/', '', $requestUri);

        return empty($requestUri) ? '/' : $requestUri;
    }

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
