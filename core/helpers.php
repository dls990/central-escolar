<?php
// Fornece funcoes auxiliares compartilhadas.





use App\Models\Usuario;

if (!function_exists('base_url')) {
    function base_url(): string {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = dirname($scriptName);
        if ($base === '/' || $base === '\\') {
            return '';
        }
        return rtrim(str_replace('\\', '/', $base), '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        $path = ltrim($path, '/');
        $base = base_url();
        return $base ? "{$base}/{$path}" : "/{$path}";
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        $path = ltrim($path, '/');
        return url($path);
    }
}

if (!function_exists('isActiveRoute')) {
    function isActiveRoute(string $route): string {
        $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
        $base = base_url();
        if ($base && strpos($currentUri, $base) === 0) {
            $currentUri = substr($currentUri, strlen($base));
        }
        $currentUri = '/' . trim(explode('?', $currentUri)[0], '/');
        $check = '/' . trim($route, '/');

        return ($currentUri === $check) ? 'nav-link-active' : '';
    }
}

if (!function_exists('usuario_logado')) {
    function usuario_logado(): ?array {
        return Usuario::usuarioAtual();
    }
}

if (!function_exists('esta_logado')) {
    function esta_logado(): bool {
        return Usuario::estaLogado();
    }
}

if (!function_exists('tem_perfil')) {
    function tem_perfil(string|array $perfis): bool {
        return Usuario::temPerfil($perfis);
    }
}
