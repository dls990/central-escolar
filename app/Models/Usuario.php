<?php
// Gerencia os usuarios e seus perfis.





namespace App\Models;

use Core\Model;
use PDO;

class Usuario extends Model {
    // Lista as contas disponiveis para o painel administrativo.
    public static function all(): array {
        $stmt = self::getConnection()->query("SELECT id, nome, usuario, perfil, cargo, avatar, ativo, ultimo_login, created_at FROM usuarios ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    // Busca uma conta pelo identificador interno.
    public static function find(int $id): ?array {
        $stmt = self::getConnection()->prepare("SELECT id, nome, usuario, perfil, cargo, avatar, ativo, ultimo_login, created_at FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    // Localiza a conta pelo nome de usuario usado no login.
    public static function findByUsuario(string $usuario): ?array {
        $stmt = self::getConnection()->prepare("SELECT * FROM usuarios WHERE LOWER(usuario) = LOWER(?) AND ativo = 1");
        $stmt->execute([trim($usuario)]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    // Valida a senha e devolve os dados seguros da conta.
    public static function autenticar(string $usuario, string $senha): ?array {
        $user = self::findByUsuario($usuario);
        if (!$user) {
            return null;
        }

        if (password_verify($senha, $user['senha'])) {

            $stmt = self::getConnection()->prepare("UPDATE usuarios SET ultimo_login = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$user['id']]);


            unset($user['senha']);
            return $user;
        }

        return null;
    }

    // Armazena os dados da conta autenticada na sessao.
    public static function login(array $user): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
        $_SESSION['usuario_logado'] = $user;
    }

    // Limpa os dados de autenticacao da sessao atual.
    public static function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['usuario_logado']);
    }

    // Devolve os dados da sessao sem expor informacoes privadas.
    public static function usuarioAtual(): ?array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['usuario_logado'] ?? null;
    }

    // Indica se a sessao contem uma conta autenticada.
    public static function estaLogado(): bool {
        return self::usuarioAtual() !== null;
    }

    // Permite testar um perfil unico ou uma lista de perfis.
    public static function temPerfil(string|array $perfis): bool {
        $usuario = self::usuarioAtual();
        if (!$usuario) {
            return false;
        }


        if ($usuario['perfil'] === 'admin') {
            return true;
        }

        if (is_array($perfis)) {
            return in_array($usuario['perfil'], $perfis);
        }

        return $usuario['perfil'] === $perfis;
    }

    // Cria uma conta e armazena a senha protegida por hash.
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

    // Atualiza os dados cadastrais e de acesso da conta informada.
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

    // Impede remover a conta principal e exclui as demais contas.
    public static function excluir(int $id): bool {

        $u = self::find($id);
        if ($u && $u['usuario'] === 'admin') {
            return false;
        }
        $stmt = self::getConnection()->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
