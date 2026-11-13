<?php
// Controla os cardapios, votacoes e refeicoes.





namespace App\Controllers;

use Core\Controller;
use App\Models\Aluno;
use App\Models\Cardapio;
use App\Models\Refeicao;
use App\Models\Votacao;
use App\Models\Usuario;

class AlimentacaoController extends Controller {
    // Prepara os cardapios da semana e os dados do formulario de edicao.
    public function cardapio(): void {
        $cardapios = Cardapio::all();
        $cardapioHoje = Cardapio::getCardapioDeHoje();
        $idEdicao = (int)($_GET['editar'] ?? 0);

        $this->view('alimentacao/cardapio', [
            'cardapios' => $cardapios,
            'cardapioHoje' => $cardapioHoje,
            'cardapioEdicao' => $idEdicao > 0 ? Cardapio::find($idEdicao) : null,
            'podeGerenciar' => Usuario::temPerfil('nutricionista'),
            'diasSemana' => ['Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira']
        ]);
    }

    // Valida e grava um cardapio novo ou as alteracoes de um existente.
    public function salvarCardapio(): void {
        $this->requireRole('nutricionista');

        $diasSemana = ['Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira'];
        $id = (int)($_POST['id'] ?? 0);
        $diaSemana = trim($_POST['dia_semana'] ?? '');
        $calorias = filter_var($_POST['calorias'] ?? null, FILTER_VALIDATE_INT);
        $dados = [
            'dia_semana' => $diaSemana,
            'prato_principal' => trim($_POST['prato_principal'] ?? ''),
            'acompanhamento' => trim($_POST['acompanhamento'] ?? ''),
            'sobremesa' => trim($_POST['sobremesa'] ?? ''),
            'bebida' => trim($_POST['bebida'] ?? ''),
            'calorias' => $calorias,
            'tags' => trim($_POST['tags'] ?? ''),
            'emoji' => trim($_POST['emoji'] ?? '') ?: '🍽️'
        ];

        if (!in_array($diaSemana, $diasSemana, true)
            || $dados['prato_principal'] === ''
            || $dados['acompanhamento'] === ''
            || $dados['sobremesa'] === ''
            || $dados['bebida'] === ''
            || $dados['tags'] === ''
            || $calorias === false
            || $calorias < 0
            || $calorias > 2000) {
            $this->flash('error', 'Confira os campos obrigatórios e informe calorias entre 0 e 2000.');
            $this->redirect('alimentacao/cardapio' . ($id > 0 ? '?editar=' . $id : ''));
            return;
        }

        if (!Cardapio::salvar($dados, $id > 0 ? $id : null)) {
            $this->flash('error', 'Já existe um cardápio cadastrado para esse dia ou o registro não foi encontrado.');
            $this->redirect('alimentacao/cardapio' . ($id > 0 ? '?editar=' . $id : ''));
            return;
        }

        $this->flash('success', $id > 0 ? 'Cardápio atualizado.' : 'Cardápio adicionado.');
        $this->redirect('alimentacao/cardapio');
    }

    // Duplica um cardapio de origem para outro dia da semana.
    public function copiarCardapio(): void {
        $this->requireRole('nutricionista');

        $origemId = (int)($_POST['origem_id'] ?? 0);
        $diaDestino = trim($_POST['dia_destino'] ?? '');
        $diasSemana = ['Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira'];

        if ($origemId <= 0 || !in_array($diaDestino, $diasSemana, true)) {
            $this->flash('error', 'Selecione um cardápio de origem e um dia de destino válidos.');
            $this->redirect('alimentacao/cardapio');
            return;
        }

        if (!Cardapio::copiar($origemId, $diaDestino)) {
            $this->flash('error', 'Não foi possível copiar. Selecione dias diferentes e tente novamente.');
            $this->redirect('alimentacao/cardapio');
            return;
        }

        $this->flash('success', "Cardápio copiado para {$diaDestino}. Você pode editar o conteúdo copiado.");
        $this->redirect('alimentacao/cardapio');
    }

    // Reune alunos, consumo e estatisticas para montar a fila do refeitorio.
    public function fila(): void {
        $data = $_GET['data'] ?? date('Y-m-d');
        $turma = $_GET['turma'] ?? 'todas';

        $turmas = Aluno::getTurmas();
        $alunos = (!empty($turma) && $turma !== 'todas') ? Aluno::getByTurma($turma) : Aluno::all();

        $cardapioHoje = Cardapio::getCardapioDeHoje();
        $refeicoesHoje = Refeicao::getDoDia($data);


        $consumoMap = [];
        foreach ($refeicoesHoje as $r) {
            $consumoMap[$r['aluno_id']] = $r;
        }

        $stats = Refeicao::getEstatisticas($data);

        $this->view('alimentacao/fila', [
            'data' => $data,
            'turmaSelecionada' => $turma,
            'turmas' => $turmas,
            'alunos' => $alunos,
            'consumoMap' => $consumoMap,
            'cardapioHoje' => $cardapioHoje,
            'stats' => $stats
        ]);
    }

    // Registra o consumo do aluno ou cancela um consumo ja anotado.
    public function salvarConsumo(): void {
        $alunoId = (int)($_POST['aluno_id'] ?? 0);
        $cardapioId = (int)($_POST['cardapio_id'] ?? 1);
        $data = $_POST['data'] ?? date('Y-m-d');
        $acao = $_POST['acao'] ?? 'consumir';
        $avaliacao = (int)($_POST['avaliacao'] ?? 5);
        $comentario = trim($_POST['comentario'] ?? '');

        if ($alunoId <= 0) {
            $this->json(['success' => false, 'message' => 'Aluno inválido!'], 400);
            return;
        }

        if ($acao === 'cancelar') {
            $ok = Refeicao::remover($alunoId, $data);
            $msg = 'Registro de merenda cancelado.';
            $consumiu = false;
        } else {
            $ok = Refeicao::registrar($alunoId, $cardapioId, $data, $avaliacao, $comentario);
            $msg = 'Merenda registrada com sucesso! +10 Pontos Nutri para o aluno! 🎉';
            $consumiu = true;
        }

        $stats = Refeicao::getEstatisticas($data);

        $this->json([
            'success' => $ok,
            'consumiu' => $consumiu,
            'stats' => $stats,
            'message' => $msg
        ]);
    }

    // Carrega as opcoes da votacao e identifica a opcao mais votada.
    public function votacao(): void {
        $pratos = Votacao::all();
        $totalVotos = Votacao::getTotalVotos();
        $lider = Votacao::getLider();

        $this->view('alimentacao/votacao', [
            'pratos' => $pratos,
            'totalVotos' => $totalVotos,
            'lider' => $lider
        ]);
    }

    // Registra o voto recebido e devolve a contagem atualizada em JSON.
    public function votar(): void {
        $pratoId = (int)($_POST['prato_id'] ?? 0);

        if ($pratoId <= 0) {
            $this->json(['success' => false, 'message' => 'Opção inválida!'], 400);
            return;
        }

        $ok = Votacao::votar($pratoId);
        $pratos = Votacao::all();
        $totalVotos = Votacao::getTotalVotos();

        $this->json([
            'success' => $ok,
            'pratos' => $pratos,
            'totalVotos' => $totalVotos,
            'message' => 'Voto computado com sucesso! 🌟'
        ]);
    }

    // Exibe a pagina interativa com a roleta de lanches.
    public function roleta(): void {
        $this->view('alimentacao/roleta');
    }
}
