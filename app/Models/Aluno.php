<?php
// Consulta e atualiza os dados dos alunos.





namespace App\Models;

use Core\Model;
use PDO;

class Aluno extends Model {
    public static function all(): array {
        $turmas = self::getTurmas();
        $stmt = self::getConnection()->query("SELECT * FROM alunos");
        $todos = $stmt->fetchAll();

        usort($todos, function($a, $b) use ($turmas) {
            $posA = array_search($a['turma'], $turmas);
            $posB = array_search($b['turma'], $turmas);
            if ($posA !== $posB) {
                return $posA <=> $posB;
            }
            return strcmp($a['nome'], $b['nome']);
        });

        return $todos;
    }

    public static function find(int $id): ?array {
        $stmt = self::getConnection()->prepare("SELECT * FROM alunos WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function getByTurma(string $turma): array {
        $stmt = self::getConnection()->prepare("SELECT * FROM alunos WHERE turma = ? ORDER BY nome ASC");
        $stmt->execute([$turma]);
        return $stmt->fetchAll();
    }

    public static function getTurmas(): array {
        $ordem = [
            '1º Ano EF', '2º Ano EF', '3º Ano EF', '4º Ano EF', '5º Ano EF',
            '6º Ano EF', '7º Ano EF', '8º Ano EF', '9º Ano EF',
            '1º Ano EM', '2º Ano EM', '3º Ano EM'
        ];

        $stmt = self::getConnection()->query("SELECT DISTINCT turma FROM alunos");
        $turmas = $stmt->fetchAll(PDO::FETCH_COLUMN);

        usort($turmas, function($a, $b) use ($ordem) {
            $posA = array_search($a, $ordem);
            $posB = array_search($b, $ordem);
            if ($posA === false && $posB === false) return strcmp($a, $b);
            if ($posA === false) return 1;
            if ($posB === false) return -1;
            return $posA <=> $posB;
        });

        return !empty($turmas) ? $turmas : $ordem;
    }

    public static function create(array $data): int {
        $avatars = ['👦', '👧', '🧒', '👩', '🧑', '🎒', '⭐', '🎈'];
        $avatar = $data['avatar'] ?? $avatars[array_rand($avatars)];

        $stmt = self::getConnection()->prepare("
            INSERT INTO alunos (nome, matricula, turma, avatar, restricoes, telefone_responsavel, pontos_nutri)
            VALUES (?, ?, ?, ?, ?, ?, 50)
        ");
        $stmt->execute([
            $data['nome'],
            $data['matricula'],
            $data['turma'],
            $avatar,
            $data['restricoes'] ?? 'Nenhuma',
            $data['telefone_responsavel'] ?? ''
        ]);
        return (int)self::getConnection()->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $stmt = self::getConnection()->prepare("
            UPDATE alunos
            SET nome = ?, matricula = ?, turma = ?, restricoes = ?, telefone_responsavel = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['nome'],
            $data['matricula'],
            $data['turma'],
            $data['restricoes'] ?? 'Nenhuma',
            $data['telefone_responsavel'] ?? '',
            $id
        ]);
    }

    public static function delete(int $id): bool {
        $stmt = self::getConnection()->prepare("DELETE FROM alunos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function adicionarPontos(int $id, int $pontos): bool {
        $stmt = self::getConnection()->prepare("UPDATE alunos SET pontos_nutri = pontos_nutri + ? WHERE id = ?");
        return $stmt->execute([$pontos, $id]);
    }

    public static function countTotal(): int {
        $stmt = self::getConnection()->query("SELECT COUNT(*) FROM alunos");
        return (int)$stmt->fetchColumn();
    }
}
