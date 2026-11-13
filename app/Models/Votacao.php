<?php
// Gerencia a votacao das refeicoes.





namespace App\Models;

use Core\Model;

class Votacao extends Model {
    // Lista as opcoes da votacao com suas quantidades de votos.
    public static function all(): array {
        $stmt = self::getConnection()->query("SELECT * FROM votacao_pratos ORDER BY votos DESC");
        return $stmt->fetchAll();
    }

    // Soma um voto para a opcao escolhida.
    public static function votar(int $id): bool {
        $stmt = self::getConnection()->prepare("UPDATE votacao_pratos SET votos = votos + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Retorna a quantidade total de votos registrados.
    public static function getTotalVotos(): int {
        $stmt = self::getConnection()->query("SELECT SUM(votos) FROM votacao_pratos");
        return (int)$stmt->fetchColumn();
    }

    // Identifica a opcao com maior numero de votos.
    public static function getLider(): ?array {
        $stmt = self::getConnection()->query("SELECT * FROM votacao_pratos ORDER BY votos DESC LIMIT 1");
        $lider = $stmt->fetch();
        return $lider ?: null;
    }
}
