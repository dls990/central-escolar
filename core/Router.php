<?php
// Registra e encaminha as rotas da aplicacao.




namespace Core;

use App\Models\Usuario;

class Router {
    private static array $routes = [];
    private static array $routeRoles = [];

    // Registra a rota e os perfis autorizados; esconder links na tela nao protege a URL.
    public static function get(string $path, string $handler, ?array $roles = null): void {
        self::$routes['GET'][$path] = $handler;
        self::$routeRoles['GET'][$path] = $roles;
    }

    // Aplica as mesmas regras de autorizacao tambem as operacoes enviadas por formulario.
    public static function post(string $path, string $handler, ?array $roles = null): void {
        self::$routes['POST'][$path] = $handler;
        self::$routeRoles['POST'][$path] = $roles;
    }

    // Localiza a rota e valida autenticacao/perfil antes de executar qualquer controller.
    public static function dispatch(): void {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = self::resolveUri();

        $uri = '/' . trim($uri, '/');

        $publicRoutes = ['/login'];
        if (!in_array($uri, $publicRoutes, true) && !Usuario::estaLogado()) {
            header('Location: ' . url('login'));
            exit;
        }

        if (!isset(self::$routes[$method][$uri])) {
            http_response_code(404);
            require_once __DIR__ . '/../app/Views/errors/404.php';
            return;
        }

        $allowedRoles = self::$routeRoles[$method][$uri] ?? null;
        if ($allowedRoles !== null && !Usuario::temPerfil($allowedRoles)) {
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Acesso negado para este perfil.';
            return;
        }

        self::executeHandler(self::$routes[$method][$uri]);
    }

    // Usa somente o caminho real requisitado; parametros como ?url=/admin nao escolhem rotas.
    private static function resolveUri(): string {
        $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $requestUri = is_string($requestUri) ? $requestUri : '/';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = dirname($scriptName);

        if ($requestUri === $scriptName) {
            return '/';
        }

        if ($basePath !== '/' && $basePath !== '\\' && strpos($requestUri, $basePath . '/') === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }

        if ($requestUri === '/index.php') {
            return '/';
        }

        return $requestUri === '' ? '/' : $requestUri;
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
