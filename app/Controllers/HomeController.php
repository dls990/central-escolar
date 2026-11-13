<?php
// Controla a pagina inicial.





namespace App\Controllers;

use Core\Controller;
use App\Models\Aluno;
use App\Models\Frequencia;
use App\Models\Cardapio;
use App\Models\Refeicao;
use App\Models\DicaNutricao;
use App\Models\Votacao;
use App\Models\HistoricoFrequencia;
use App\Models\PedidoCozinha;

class HomeController extends Controller {
    // Prepara os dados publicos e os avisos exibidos na pagina inicial.
    public function index(): void {
        $hoje = date('Y-m-d');

        $statsFrequencia = Frequencia::getEstatisticas($hoje);
        $pizzaStats = HistoricoFrequencia::getDadosGraficoPizza($hoje);
        $pedidoHoje = PedidoCozinha::getPorData($hoje);
        $statsRefeicao = Refeicao::getEstatisticas($hoje);
        $cardapioHoje = Cardapio::getCardapioDeHoje();
        $dicaNutri = DicaNutricao::getAleatoria();
        $liderVotacao = Votacao::getLider();
        $totalAlunos = Aluno::countTotal();


        $todosAlunos = Aluno::all();
        $alunosComAlergia = array_filter($todosAlunos, function($a) {
            return !empty($a['restricoes']) && strtolower($a['restricoes']) !== 'nenhuma';
        });

        $this->view('home/index', [
            'hoje' => $hoje,
            'statsFrequencia' => $statsFrequencia,
            'pizzaStats' => $pizzaStats,
            'pedidoHoje' => $pedidoHoje,
            'statsRefeicao' => $statsRefeicao,
            'cardapioHoje' => $cardapioHoje,
            'dicaNutri' => $dicaNutri,
            'liderVotacao' => $liderVotacao,
            'totalAlunos' => $totalAlunos,
            'alunosComAlergia' => array_values($alunosComAlergia)
        ]);
    }
}
