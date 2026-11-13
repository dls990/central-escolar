<?php
/**
 * Model HistoricoFrequencia - Histórico diário e persistência do gráfico de pizza
 */
namespace App\Models;

use Core\Model;
use PDO;

class HistoricoFrequencia extends Model {

    public static function salvarDiario(string $data, ?string $fechadoPor = null): bool {
        $db = self::getConnection();

        // Total de alunos
        $totalAlunos = (int)$db->query("SELECT COUNT(*) FROM alunos")->fetchColumn();

        // Contagens na data
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

        // Se ninguém foi marcado com falta ou justificado, os que faltam registrar podem ser considerados pendentes
        // Mas para efeito do censo escolar diário:
        $registrados = $presentes + $ausentes + $justificados;
        if ($totalAlunos > 0 && $registrados === 0) {
            // Se nenhum registro existe ainda nessa data, não sobrescreve com zeros
            return false;
        }

        $taxaPresenca = ($totalAlunos > 0) ? round(($presentes / $totalAlunos) * 100, 1) : 0;

        // Verifica se já existe registro dessa data
        $stmtCheck = $db->prepare("SELECT id, fechado_por FROM historico_frequencia_diaria WHERE data = ?");
        $stmtCheck->execute([$data]);
        $existente = $stmtCheck->fetch();

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

    public static function getPorData(string $data): ?array {
        $stmt = self::getConnection()->prepare("SELECT * FROM historico_frequencia_diaria WHERE data = ?");
        $stmt->execute([$data]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function getTodos(int $limite = 30): array {
        $stmt = self::getConnection()->prepare("SELECT * FROM historico_frequencia_diaria ORDER BY data DESC LIMIT ?");
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Retorna estrutura pronta para o gráfico de pizza do Chart.js
     */
    public static function getDadosGraficoPizza(string $data): array {
        // Tenta obter do histórico salvo
        $historico = self::getPorData($data);

        if (!$historico) {
            // Se ainda não foi fechado no histórico diário, calcula em tempo real
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
                            '#22c55e', // Verde Presente
                            '#ef4444', // Vermelho Ausente
                            '#f59e0b', // Amarelo Justificado
                            '#94a3b8'  // Cinza Pendente
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
