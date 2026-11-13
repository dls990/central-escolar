<?php
// Gerencia a votacao das refeicoes.





namespace App\Models;

use Core\Model;

class Votacao extends Model {
    public static function all(): array {
        $stmt = self::getConnection()->query("SELECT * FROM votacao_pratos ORDER BY votos DESC");
        return $stmt->fetchAll();
    }

    public static function votar(int $id): bool {
        $stmt = self::getConnection()->prepare("UPDATE votacao_pratos SET votos = votos + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getTotalVotos(): int {
        $stmt = self::getConnection()->query("SELECT SUM(votos) FROM votacao_pratos");
        return (int)$stmt->fetchColumn();
    }

    public static function getLider(): ?array {
        $stmt = self::getConnection()->query("SELECT * FROM votacao_pratos ORDER BY votos DESC LIMIT 1");
        $lider = $stmt->fetch();
        return $lider ?: null;
    }
}
