<?php
/**
 * Model Refeicao - Controle de Almoço / Merenda Escolar
 */
namespace App\Models;

use Core\Model;

class Refeicao extends Model {
    public static function getDoDia(string $data): array {
        $stmt = self::getConnection()->prepare("
            SELECT r.*, a.nome as aluno_nome, a.matricula, a.turma, a.avatar, a.restricoes
            FROM refeicoes r
            JOIN alunos a ON r.aluno_id = a.id
            WHERE r.data = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$data]);
        return $stmt->fetchAll();
    }

    public static function jaConsumiu(int $alunoId, string $data): bool {
        $stmt = self::getConnection()->prepare("SELECT id FROM refeicoes WHERE aluno_id = ? AND data = ?");
        $stmt->execute([$alunoId, $data]);
        return (bool)$stmt->fetchColumn();
    }

    public static function registrar(int $alunoId, int $cardapioId, string $data, int $avaliacao = 5, ?string $comentario = null): bool {
        // Se já consumiu, atualiza avaliação
        $stmtCheck = self::getConnection()->prepare("SELECT id FROM refeicoes WHERE aluno_id = ? AND data = ?");
        $stmtCheck->execute([$alunoId, $data]);
        $id = $stmtCheck->fetchColumn();

        if ($id) {
            $stmt = self::getConnection()->prepare("UPDATE refeicoes SET avaliacao = ?, comentario = ? WHERE id = ?");
            return $stmt->execute([$avaliacao, $comentario, $id]);
        }

        $stmt = self::getConnection()->prepare("
            INSERT INTO refeicoes (aluno_id, cardapio_id, data, consumiu, avaliacao, comentario)
            VALUES (?, ?, ?, 1, ?, ?)
        ");
        $success = $stmt->execute([$alunoId, $cardapioId, $data, $avaliacao, $comentario]);
        if ($success) {
            // Bonifica o aluno com +10 pontos no jogo saudável
            Aluno::adicionarPontos($alunoId, 10);
        }
        return $success;
    }

    public static function remover(int $alunoId, string $data): bool {
        $stmt = self::getConnection()->prepare("DELETE FROM refeicoes WHERE aluno_id = ? AND data = ?");
        return $stmt->execute([$alunoId, $data]);
    }

    public static function getEstatisticas(string $data): array {
        $totalAlunos = Aluno::countTotal();

        $stmt = self::getConnection()->prepare("
            SELECT 
                COUNT(*) as total_refeicoes,
                AVG(avaliacao) as media_avaliacao
            FROM refeicoes
            WHERE data = ?
        ");
        $stmt->execute([$data]);
        $row = $stmt->fetch();

        $totalRefeicoes = (int)($row['total_refeicoes'] ?? 0);
        $mediaAvaliacao = round((float)($row['media_avaliacao'] ?? 5.0), 1);
        $porcentagemAtendida = ($totalAlunos > 0) ? round(($totalRefeicoes / $totalAlunos) * 100, 1) : 0;

        return [
            'total_refeicoes' => $totalRefeicoes,
            'media_avaliacao' => $mediaAvaliacao,
            'porcentagem_atendida' => $porcentagemAtendida
        ];
    }
}
