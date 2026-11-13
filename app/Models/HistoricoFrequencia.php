<?php
// Gera e consulta o historico de frequencia.





namespace App\Models;

use Core\Model;
use PDO;

class HistoricoFrequencia extends Model {

    // Recalcula o resumo do dia e cria ou atualiza seu registro historico.
    public static function salvarDiario(string $data, ?string $fechadoPor = null): bool {
        $db = self::getConnection();


        $totalAlunos = (int)$db->query("SELECT COUNT(*) FROM alunos")->fetchColumn();


        $stmt = $db->prepare("
            SELECT
                SUM(CASE WHEN status = 'presente' THEN 1 ELSE 0 END) as presentes,
                SUM(CASE WHEN status = 'ausente' THEN 1 ELSE 0 END) as ausentes,
                SUM(CASE WHEN status = 'justificado' THEN 1 ELSE 0 END) as justificados
            FROM frequencias
            WHERE data = ?
        ");
        $stmt->execute([$data]);
        $res = $stmt->fetch();

        $presentes = (int)($res['presentes'] ?? 0);
        $ausentes = (int)($res['ausentes'] ?? 0);
        $justificados = (int)($res['justificados'] ?? 0);



        // Evita gravar um historico vazio antes de qualquer chamada do dia.
        $registrados = $presentes + $ausentes + $justificados;
        if ($totalAlunos > 0 && $registrados === 0) {

            return false;
        }

        // A taxa usa todos os matriculados como base, inclusive os pendentes.
        $taxaPresenca = ($totalAlunos > 0) ? round(($presentes / $totalAlunos) * 100, 1) : 0;


        $stmtCheck = $db->prepare("SELECT id, fechado_por FROM historico_frequencia_diaria WHERE data = ?");
        $stmtCheck->execute([$data]);
        $existente = $stmtCheck->fetch();

        // Atualiza o resumo existente ou cria o primeiro registro para a data.
        if ($existente) {
            $responsavel = $fechadoPor ?: ($existente['fechado_por'] ?: 'Inspetora');
            $stmtUp = $db->prepare("
                UPDATE historico_frequencia_diaria
                SET presentes = ?, ausentes = ?, justificados = ?, taxa_presenca = ?,
                    total_alunos = ?, fechado_por = ?, updated_at = CURRENT_TIMESTAMP
                WHERE data = ?
            ");
            return $stmtUp->execute([
                $presentes,
                $ausentes,
                $justificados,
                $taxaPresenca,
                $totalAlunos,
                $responsavel,
                $data
            ]);
        } else {
            $responsavel = $fechadoPor ?: 'Inspetora';
            $stmtIn = $db->prepare("
                INSERT INTO historico_frequencia_diaria
                (data, presentes, ausentes, justificados, taxa_presenca, total_alunos, fechado_por)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            return $stmtIn->execute([
                $data,
                $presentes,
                $ausentes,
                $justificados,
                $taxaPresenca,
                $totalAlunos,
                $responsavel
            ]);
        }
    }

    // Recupera o resumo arquivado para uma data especifica.
    public static function getPorData(string $data): ?array {
        $stmt = self::getConnection()->prepare("SELECT * FROM historico_frequencia_diaria WHERE data = ?");
        $stmt->execute([$data]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    // Retorna os dias arquivados mais recentes dentro do limite solicitado.
    public static function getTodos(int $limite = 30): array {
        $stmt = self::getConnection()->prepare("SELECT * FROM historico_frequencia_diaria ORDER BY data DESC LIMIT ?");
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Usa o resumo salvo ou calcula os dados atuais para alimentar o grafico.
    public static function getDadosGraficoPizza(string $data): array {

        $historico = self::getPorData($data);

        // Sem resumo arquivado, calcula os dados diretamente dos registros atuais.
        if (!$historico) {

            $db = self::getConnection();
            $totalAlunos = (int)$db->query("SELECT COUNT(*) FROM alunos")->fetchColumn();

            $stmt = $db->prepare("
                SELECT
                    SUM(CASE WHEN status = 'presente' THEN 1 ELSE 0 END) as presentes,
                    SUM(CASE WHEN status = 'ausente' THEN 1 ELSE 0 END) as ausentes,
                    SUM(CASE WHEN status = 'justificado' THEN 1 ELSE 0 END) as justificados
                FROM frequencias
                WHERE data = ?
            ");
            $stmt->execute([$data]);
            $res = $stmt->fetch();

            $presentes = (int)($res['presentes'] ?? 0);
            $ausentes = (int)($res['ausentes'] ?? 0);
            $justificados = (int)($res['justificados'] ?? 0);
            $taxaPresenca = ($totalAlunos > 0) ? round(($presentes / $totalAlunos) * 100, 1) : 0;
            $fechadoPor = 'Em andamento';
        } else {
            $totalAlunos = (int)$historico['total_alunos'];
            $presentes = (int)$historico['presentes'];
            $ausentes = (int)$historico['ausentes'];
            $justificados = (int)$historico['justificados'];
            $taxaPresenca = (float)$historico['taxa_presenca'];
            $fechadoPor = $historico['fechado_por'] ?? 'Inspetora';
        }

        // Alunos sem status entram como pendentes para completar o grafico.
        $pendentes = max(0, $totalAlunos - ($presentes + $ausentes + $justificados));

        return [
            'data' => $data,
            'data_formatada' => date('d/m/Y', strtotime($data)),
            'total_alunos' => $totalAlunos,
            'presentes' => $presentes,
            'ausentes' => $ausentes,
            'justificados' => $justificados,
            'pendentes' => $pendentes,
            'taxa_presenca' => $taxaPresenca,
            'fechado_por' => $fechadoPor,
            'chart_data' => [
                'labels' => ['Presentes (Refeições)', 'Ausentes (Faltas)', 'Justificados', 'Não Registrados'],
                'datasets' => [
                    [
                        'data' => [$presentes, $ausentes, $justificados, $pendentes],
                        'backgroundColor' => [
                            '#22c55e',
                            '#ef4444',
                            '#f59e0b',
                            '#94a3b8'
                        ],
                        'hoverOffset' => 8,
                        'borderWidth' => 2,
                        'borderColor' => '#ffffff'
                    ]
                ]
            ]
        ];
    }
}
