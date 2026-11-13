<?php
// Registra e consulta a frequencia dos alunos.





namespace App\Models;

use Core\Model;
use PDO;

class Frequencia extends Model {

    // Monta a chamada do dia, com filtro opcional e situacao de cada aluno.
    public static function getChamada(string $data, ?string $turma = null): array {
        $sql = "
            SELECT a.id as aluno_id, a.nome, a.matricula, a.turma, a.avatar, a.restricoes,
                   f.id as frequencia_id, f.status, f.horario, f.observacao, f.inspetora_nome
            FROM alunos a
            LEFT JOIN frequencias f ON a.id = f.aluno_id AND f.data = ?
        ";

        $params = [$data];

        if (!empty($turma) && $turma !== 'todas') {
            $sql .= " WHERE a.turma = ?";
            $params[] = $turma;
        }

        $sql .= " ORDER BY a.turma, a.nome ASC";

        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        $alunos = $stmt->fetchAll();

        $turmasOrdem = Aluno::getTurmas();
        usort($alunos, function($a, $b) use ($turmasOrdem) {
            $posA = array_search($a['turma'], $turmasOrdem);
            $posB = array_search($b['turma'], $turmasOrdem);
            if ($posA !== $posB) {
                return $posA <=> $posB;
            }
            return strcmp($a['nome'], $b['nome']);
        });

        return $alunos;
    }

    // Cria ou atualiza a presenca de um aluno para a data indicada.
    public static function registrar(int $alunoId, string $data, string $status, ?string $horario = null, ?string $obs = null, ?string $inspetoraNome = null): bool {
        if ($status === 'presente' && empty($horario)) {
            $horario = date('H:i');
        } elseif ($status !== 'presente') {
            $horario = null;
        }

        $db = self::getConnection();


        $stmtCheck = $db->prepare("SELECT id FROM frequencias WHERE aluno_id = ? AND data = ?");
        $stmtCheck->execute([$alunoId, $data]);
        $existente = $stmtCheck->fetchColumn();

        // Mantem um unico registro por aluno e data: atualiza ou insere.
        if ($existente) {
            $stmt = $db->prepare("
                UPDATE frequencias
                SET status = ?, horario = ?, observacao = ?, inspetora_nome = ?
                WHERE aluno_id = ? AND data = ?
            ");
            return $stmt->execute([$status, $horario, $obs, $inspetoraNome, $alunoId, $data]);
        } else {
            $stmt = $db->prepare("
                INSERT INTO frequencias (aluno_id, data, status, horario, observacao, inspetora_nome)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$alunoId, $data, $status, $horario, $obs, $inspetoraNome]);
        }
    }

    // Aplica o mesmo status a todos os alunos da turma ou da escola.
    public static function marcarTodos(string $data, string $status, ?string $turma = null, ?string $inspetoraNome = null): int {
        $alunos = (!empty($turma) && $turma !== 'todas')
            ? Aluno::getByTurma($turma)
            : Aluno::all();

        $count = 0;
        foreach ($alunos as $aluno) {
            if (self::registrar($aluno['id'], $data, $status, null, null, $inspetoraNome)) {
                $count++;
            }
        }
        return $count;
    }

    // Calcula presentes, ausentes, pendentes e taxa de presenca do dia.
    public static function getEstatisticas(string $data): array {
        $totalAlunos = Aluno::countTotal();

        $stmt = self::getConnection()->prepare("
            SELECT
                SUM(CASE WHEN status = 'presente' THEN 1 ELSE 0 END) as presentes,
                SUM(CASE WHEN status = 'ausente' THEN 1 ELSE 0 END) as ausentes,
                SUM(CASE WHEN status = 'justificado' THEN 1 ELSE 0 END) as justificados
            FROM frequencias
            WHERE data = ?
        ");
        $stmt->execute([$data]);
        $stats = $stmt->fetch() ?: ['presentes' => 0, 'ausentes' => 0, 'justificados' => 0];

        $presentes = (int)($stats['presentes'] ?? 0);
        $ausentes = (int)($stats['ausentes'] ?? 0);
        $justificados = (int)($stats['justificados'] ?? 0);
        $registrados = $presentes + $ausentes + $justificados;
        // A diferenca mostra quantos estudantes ainda aguardam marcacao.
        $naoRegistrados = max(0, $totalAlunos - $registrados);

        $taxaPresenca = ($totalAlunos > 0) ? round(($presentes / $totalAlunos) * 100, 1) : 0;

        return [
            'total' => $totalAlunos,
            'presentes' => $presentes,
            'ausentes' => $ausentes,
            'justificados' => $justificados,
            'nao_registrados' => $naoRegistrados,
            'taxa_presenca' => $taxaPresenca
        ];
    }
}
