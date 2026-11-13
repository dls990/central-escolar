<?php
/**
 * Model Usuario - Gestão de Usuários e Autenticação (Inspetoras, Nutricionista, ADM)
 */
namespace App\Models;

use Core\Model;
use PDO;

class Usuario extends Model {

    public static function all(): array {
        $stmt = self::getConnection()->query("SELECT id, nome, usuario, perfil, cargo, avatar, ativo, ultimo_login, created_at FROM usuarios ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array {
        $stmt = self::getConnection()->prepare("SELECT id, nome, usuario, perfil, cargo, avatar, ativo, ultimo_login, created_at FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function findByUsuario(string $usuario): ?array {
        $stmt = self::getConnection()->prepare("SELECT * FROM usuarios WHERE LOWER(usuario) = LOWER(?) AND ativo = 1");
        $stmt->execute([trim($usuario)]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function autenticar(string $usuario, string $senha): ?array {
        $user = self::findByUsuario($usuario);
        if (!$user) {
            return null;
        }

        if (password_verify($senha, $user['senha'])) {
            // Atualiza último login
            $stmt = self::getConnection()->prepare("UPDATE usuarios SET ultimo_login = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$user['id']]);

            // Remove a senha do array por segurança
            unset($user['senha']);
            return $user;
        }

        return null;
    }

    public static function login(array $user): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
        $_SESSION['usuario_logado'] = $user;
    }

    public static function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['usuario_logado']);
    }

    public static function usuarioAtual(): ?array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['usuario_logado'] ?? null;
    }

    public static function estaLogado(): bool {
        return self::usuarioAtual() !== null;
    }

    public static function temPerfil(string|array $perfis): bool {
        $usuario = self::usuarioAtual();
        if (!$usuario) {
            return false;
        }

        // Admin tem acesso a tudo
        if ($usuario['perfil'] === 'admin') {
            return true;
        }

        if (is_array($perfis)) {
            return in_array($usuario['perfil'], $perfis);
        }

        return $usuario['perfil'] === $perfis;
    }

    public static function criar(array $dados): int {
        $hash = password_hash($dados['senha'], PASSWORD_DEFAULT);
        $stmt = self::getConnection()->prepare("
            INSERT INTO usuarios (nome, usuario, senha, perfil, cargo, avatar, ativo)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $dados['nome'],
            strtolower(trim($dados['usuario'])),
            $hash,
            $dados['perfil'],
            $dados['cargo'] ?? 'Usuário',
            $dados['avatar'] ?? '👤',
            $dados['ativo'] ?? 1
        ]);
        return (int)self::getConnection()->lastInsertId();
    }

    public static function atualizar(int $id, array $dados): bool {
        if (!empty($dados['senha'])) {
            $hash = password_hash($dados['senha'], PASSWORD_DEFAULT);
            $stmt = self::getConnection()->prepare("
                UPDATE usuarios 
                SET nome = ?, usuario = ?, senha = ?, perfil = ?, cargo = ?, avatar = ?, ativo = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $dados['nome'],
                strtolower(trim($dados['usuario'])),
                $hash,
                $dados['perfil'],
                $dados['cargo'],
                $dados['avatar'],
                $dados['ativo'] ?? 1,
                $id
            ]);
        } else {
            $stmt = self::getConnection()->prepare("
                UPDATE usuarios 
                SET nome = ?, usuario = ?, perfil = ?, cargo = ?, avatar = ?, ativo = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $dados['nome'],
                strtolower(trim($dados['usuario'])),
                $dados['perfil'],
                $dados['cargo'],
                $dados['avatar'],
                $dados['ativo'] ?? 1,
                $id
            ]);
        }
    }

    public static function excluir(int $id): bool {
        // Impede excluir o admin principal (id 1 ou usuário 'admin')
        $u = self::find($id);
        if ($u && $u['usuario'] === 'admin') {
            return false;
        }
        $stmt = self::getConnection()->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
