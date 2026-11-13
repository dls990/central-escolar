<?php
// Gerencia os pedidos enviados a cozinha.





namespace App\Models;

use Core\Model;
use PDO;

class PedidoCozinha extends Model {

    public static function getPorData(string $data): ?array {
        $stmt = self::getConnection()->prepare("SELECT * FROM pedidos_cozinha WHERE data = ?");
        $stmt->execute([$data]);
        $res = $stmt->fetch();
        if (!$res) {
            return null;
        }
        if (!empty($res['restricoes_detalhes'])) {
            $res['restricoes_lista'] = json_decode($res['restricoes_detalhes'], true) ?: [];
        } else {
            $res['restricoes_lista'] = [];
        }
        return $res;
    }

    public static function getUltimosPedidos(int $limite = 15): array {
        $stmt = self::getConnection()->prepare("SELECT * FROM pedidos_cozinha ORDER BY data DESC LIMIT ?");
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }




    public static function enviarParaCozinha(string $data, string $inspetoraNome, ?string $obs = null): array {
        $db = self::getConnection();


        $sql = "
            SELECT a.id, a.nome, a.matricula, a.turma, a.restricoes,
                   COALESCE(f.status, 'presente') as status
            FROM alunos a
            LEFT JOIN frequencias f ON a.id = f.aluno_id AND f.data = ?
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$data]);
        $alunos = $stmt->fetchAll();

        $totalMatriculados = count($alunos);
        $totalPresentes = 0;
        $totalAusentes = 0;
        $totalJustificados = 0;

        $refeicoesFund1 = 0;
        $refeicoesFund2 = 0;
        $refeicoesMedio = 0;

        $turmasFund1 = ['1º Ano EF', '2º Ano EF', '3º Ano EF', '4º Ano EF', '5º Ano EF'];
        $turmasFund2 = ['6º Ano EF', '7º Ano EF', '8º Ano EF', '9º Ano EF'];
        $turmasMedio = ['1º Ano EM', '2º Ano EM', '3º Ano EM'];

        $restricoesContagem = [];
        $restricoesDetalhes = [];

        foreach ($alunos as $a) {
            $st = $a['status'];
            if ($st === 'presente') {
                $totalPresentes++;


                if (in_array($a['turma'], $turmasFund1)) {
                    $refeicoesFund1++;
                } elseif (in_array($a['turma'], $turmasFund2)) {
                    $refeicoesFund2++;
                } elseif (in_array($a['turma'], $turmasMedio)) {
                    $refeicoesMedio++;
                }


                if (!empty($a['restricoes']) && strtolower($a['restricoes']) !== 'nenhuma') {
                    $tipo = trim($a['restricoes']);
                    $restricoesContagem[$tipo] = ($restricoesContagem[$tipo] ?? 0) + 1;
                    $restricoesDetalhes[] = [
                        'aluno' => $a['nome'],
                        'turma' => $a['turma'],
                        'restricao' => $tipo
                    ];
                }
            } elseif ($st === 'ausente') {
                $totalAusentes++;
            } elseif ($st === 'justificado') {
                $totalJustificados++;
            }
        }


        $resumoTextArray = [];
        foreach ($restricoesContagem as $tipo => $qtd) {
            $resumoTextArray[] = "{$qtd}x {$tipo}";
        }
        $resumoTexto = empty($resumoTextArray) ? 'Nenhuma restrição alimentar entre os presentes' : implode(', ', $resumoTextArray);

        $horarioAtual = date('H:i');


        $existente = self::getPorData($data);

        if ($existente) {
            $stmtUp = $db->prepare("
                UPDATE pedidos_cozinha
                SET enviado_por = ?, horario_envio = ?, total_matriculados = ?,
                    total_presentes = ?, total_ausentes = ?, total_justificados = ?,
                    refeicoes_fund1 = ?, refeicoes_fund2 = ?, refeicoes_medio = ?,
                    restricoes_resumo = ?, restricoes_detalhes = ?,
                    status = 'enviado', observacao_inspetora = ?, updated_at = CURRENT_TIMESTAMP
                WHERE data = ?
            ");
            $stmtUp->execute([
                $inspetoraNome,
                $horarioAtual,
                $totalMatriculados,
                $totalPresentes,
                $totalAusentes,
                $totalJustificados,
                $refeicoesFund1,
                $refeicoesFund2,
                $refeicoesMedio,
                $resumoTexto,
                json_encode($restricoesDetalhes, JSON_UNESCAPED_UNICODE),
                $obs,
                $data
            ]);
        } else {
            $stmtIn = $db->prepare("
                INSERT INTO pedidos_cozinha
                (data, enviado_por, horario_envio, total_matriculados, total_presentes, total_ausentes, total_justificados,
                 refeicoes_fund1, refeicoes_fund2, refeicoes_medio, restricoes_resumo, restricoes_detalhes, status, observacao_inspetora)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'enviado', ?)
            ");
            $stmtIn->execute([
                $data,
                $inspetoraNome,
                $horarioAtual,
                $totalMatriculados,
                $totalPresentes,
                $totalAusentes,
                $totalJustificados,
                $refeicoesFund1,
                $refeicoesFund2,
                $refeicoesMedio,
                $resumoTexto,
                json_encode($restricoesDetalhes, JSON_UNESCAPED_UNICODE),
                $obs
            ]);
        }


        HistoricoFrequencia::salvarDiario($data, $inspetoraNome);

        return [
            'data' => $data,
            'enviado_por' => $inspetoraNome,
            'horario_envio' => $horarioAtual,
            'total_matriculados' => $totalMatriculados,
            'total_presentes' => $totalPresentes,
            'total_ausentes' => $totalAusentes,
            'total_justificados' => $totalJustificados,
            'refeicoes_fund1' => $refeicoesFund1,
            'refeicoes_fund2' => $refeicoesFund2,
            'refeicoes_medio' => $refeicoesMedio,
            'restricoes_resumo' => $resumoTexto,
            'restricoes_lista' => $restricoesDetalhes,
            'status' => 'enviado'
        ];
    }

    public static function atualizarStatus(string $data, string $status, ?string $obsCozinha = null): bool {
        $db = self::getConnection();
        if ($obsCozinha !== null) {
            $stmt = $db->prepare("UPDATE pedidos_cozinha SET status = ?, observacao_cozinha = ?, updated_at = CURRENT_TIMESTAMP WHERE data = ?");
            return $stmt->execute([$status, $obsCozinha, $data]);
        } else {
            $stmt = $db->prepare("UPDATE pedidos_cozinha SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE data = ?");
            return $stmt->execute([$status, $data]);
        }
    }
}
