<?php
/**
 * Controller Base
 */
namespace Core;

use App\Models\Usuario;

class Controller {
    /**
     * Renderiza uma view passando dados
     */
    protected function view(string $viewPath, array $data = []): void {
        extract($data);

        $viewFile = __DIR__ . "/../app/Views/{$viewPath}.php";

        if (!file_exists($viewFile)) {
            die("View não encontrada: {$viewFile}");
        }

        require_once $viewFile;
    }

    /**
     * Retorna resposta JSON (útil para interações dinâmicas via AJAX / Fetch)
     */
    protected function json(mixed $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Redireciona para uma rota relativa
     */
    protected function redirect(string $path): void {
        $url = url($path);
        header("Location: {$url}");
        exit;
    }

    /**
     * Define mensagem flash na sessão
     */
    protected function flash(string $tipo, string $mensagem): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION["flash_{$tipo}"] = $mensagem;
    }

    /**
     * Retorna o usuário logado atualmente
     */
    protected function currentUser(): ?array {
        return Usuario::usuarioAtual();
    }

    /**
     * Exige que o usuário esteja autenticado, senão redireciona para o login
     */
    protected function requireAuth(): array {
        $user = Usuario::usuarioAtual();
        if (!$user) {
            $this->flash('error', 'Por favor, faça login para acessar esta funcionalidade da Central Escolar.');
            $this->redirect('login');
        }
        return $user;
    }

    /**
     * Exige que o usuário tenha um determinado perfil
     */
    protected function requireRole(string|array $perfis): array {
        $user = $this->requireAuth();
        if (!Usuario::temPerfil($perfis)) {
            $this->flash('error', 'Acesso restrito: seu perfil de usuário não tem permissão para acessar esta área.');
            $this->redirect('');
        }
        return $user;
    }
}
