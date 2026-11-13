<?php
/**
 * Controller Relatorios
 */
namespace App\Controllers;

use Core\Controller;
use App\Models\Aluno;
use App\Models\Frequencia;
use App\Models\Refeicao;

class RelatoriosController extends Controller {
    public function index(): void {
        $hoje = date('Y-m-d');
        $alunos = Aluno::all();
        $turmas = Aluno::getTurmas();

        $statsFreq = Frequencia::getEstatisticas($hoje);
        $statsRef = Refeicao::getEstatisticas($hoje);

        // Agrupamento por turma
        $resumoPorTurma = [];
        foreach ($turmas as $turma) {
            $alunosTurma = Aluno::getByTurma($turma);
            $totalTurma = count($alunosTurma);
            $chamada = Frequencia::getChamada($hoje, $turma);
            
            $presentes = 0;
            foreach ($chamada as $c) {
                if (($c['status'] ?? '') === 'presente') {
                    $presentes++;
                }
            }

            $porcentagem = ($totalTurma > 0) ? round(($presentes / $totalTurma) * 100, 1) : 0;
            $resumoPorTurma[] = [
                'turma' => $turma,
                'total' => $totalTurma,
                'presentes' => $presentes,
                'porcentagem' => $porcentagem
            ];
        }

        // Estatística de restrições
        $restricoesContagem = [];
        foreach ($alunos as $a) {
            $r = trim($a['restricoes'] ?? 'Nenhuma');
            $restricoesContagem[$r] = ($restricoesContagem[$r] ?? 0) + 1;
        }

        $this->view('relatorios/index', [
            'hoje' => $hoje,
            'statsFreq' => $statsFreq,
            'statsRef' => $statsRef,
            'resumoPorTurma' => $resumoPorTurma,
            'restricoesContagem' => $restricoesContagem,
            'totalAlunos' => count($alunos)
        ]);
    }
}
