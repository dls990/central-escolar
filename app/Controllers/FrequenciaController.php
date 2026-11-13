<?php
// Controla a chamada e o registro de frequencia.





namespace App\Controllers;

use Core\Controller;
use App\Models\Aluno;
use App\Models\Frequencia;
use App\Models\PedidoCozinha;
use App\Models\HistoricoFrequencia;
use App\Models\Usuario;

class FrequenciaController extends Controller {

    // Reune alunos, filtros, estatisticas e dados do grafico para a chamada.
    public function index(): void {
        $this->requireAuth();

        $data = $_GET['data'] ?? date('Y-m-d');
        $turma = $_GET['turma'] ?? 'todas';

        $turmas = Aluno::getTurmas();
        $alunos = Frequencia::getChamada($data, $turma);
        $stats = Frequencia::getEstatisticas($data);
        $pedidoCozinha = PedidoCozinha::getPorData($data);
        $usuario = Usuario::usuarioAtual();


        $pizzaStats = HistoricoFrequencia::getDadosGraficoPizza($data);

        $this->view('frequencia/index', [
            'tituloPagina' => 'Chamada & Frequência Escolar | VivalCris',
            'data' => $data,
            'turmaSelecionada' => $turma,
            'turmas' => $turmas,
            'alunos' => $alunos,
            'stats' => $stats,
            'pedidoCozinha' => $pedidoCozinha,
            'pizzaStats' => $pizzaStats,
            'usuario' => $usuario
        ]);
    }

    // Registra a situacao de um aluno e atualiza o historico diario.
    public function salvar(): void {
        $usuario = $this->requireAuth();

        $alunoId = (int)($_POST['aluno_id'] ?? 0);
        $data = $_POST['data'] ?? date('Y-m-d');
        $status = $_POST['status'] ?? 'presente';
        $obs = $_POST['observacao'] ?? null;

        if ($alunoId <= 0 || !in_array($status, ['presente', 'ausente', 'justificado'])) {
            $this->json(['success' => false, 'message' => 'Dados de frequência inválidos!'], 400);
            return;
        }

        $horario = ($status === 'presente') ? date('H:i') : null;
        $inspetoraNome = $usuario['nome'] ?? 'Inspetora';

        $ok = Frequencia::registrar($alunoId, $data, $status, $horario, $obs, $inspetoraNome);


        HistoricoFrequencia::salvarDiario($data, $inspetoraNome);

        $stats = Frequencia::getEstatisticas($data);
        $pizzaData = HistoricoFrequencia::getDadosGraficoPizza($data);

        $this->json([
            'success' => $ok,
            'status' => $status,
            'horario' => $horario,
            'stats' => $stats,
            'pizza' => $pizzaData,
            'message' => 'Frequência registrada e gráfico de pizza atualizado automaticamente!'
        ]);
    }

    // Marca em lote os alunos da turma selecionada e recalcula as estatisticas.
    public function marcarTodos(): void {
        $usuario = $this->requireAuth();

        $data = $_POST['data'] ?? date('Y-m-d');
        $status = $_POST['status'] ?? 'presente';
        $turma = $_POST['turma'] ?? 'todas';
        $inspetoraNome = $usuario['nome'] ?? 'Inspetora';

        $totalAlterados = Frequencia::marcarTodos($data, $status, $turma, $inspetoraNome);


        HistoricoFrequencia::salvarDiario($data, $inspetoraNome);

        $stats = Frequencia::getEstatisticas($data);
        $pizzaData = HistoricoFrequencia::getDadosGraficoPizza($data);

        $this->json([
            'success' => true,
            'totalAlterados' => $totalAlterados,
            'stats' => $stats,
            'pizza' => $pizzaData,
            'message' => "Todos os alunos foram marcados como {$status}s por {$inspetoraNome}!"
        ]);
    }

    // Consolida a chamada do dia e envia o pedido de refeicoes para a cozinha.
    public function enviarParaCozinha(): void {
        $usuario = $this->requireAuth();

        $data = $_POST['data'] ?? date('Y-m-d');
        $obs = trim($_POST['observacao'] ?? '');
        $inspetoraNome = $usuario['nome'] ?? 'Inspetora';

        $resultado = PedidoCozinha::enviarParaCozinha($data, $inspetoraNome, $obs);

        $this->json([
            'success' => true,
            'pedido' => $resultado,
            'message' => "Censo de presença enviado para a Cozinha com sucesso por {$inspetoraNome}! Total de {$resultado['total_presentes']} refeições solicitadas."
        ]);
    }

    // Retorna em JSON os valores do grafico para a data solicitada.
    public function pizzaDados(): void {
        $data = $_GET['data'] ?? date('Y-m-d');
        $dados = HistoricoFrequencia::getDadosGraficoPizza($data);
        $this->json($dados);
    }

    // Carrega os dias arquivados e o detalhe da data escolhida.
    public function historico(): void {
        $this->requireAuth();

        $dataSelecionada = $_GET['data'] ?? date('Y-m-d');
        $historicos = HistoricoFrequencia::getTodos(30);
        $pizzaStats = HistoricoFrequencia::getDadosGraficoPizza($dataSelecionada);
        $pedido = PedidoCozinha::getPorData($dataSelecionada);
        $usuario = Usuario::usuarioAtual();

        $this->view('frequencia/historico', [
            'tituloPagina' => 'Histórico de Frequência & Gráficos Salvos | VivalCris',
            'dataSelecionada' => $dataSelecionada,
            'historicos' => $historicos,
            'pizzaStats' => $pizzaStats,
            'pedido' => $pedido,
            'usuario' => $usuario
        ]);
    }
}
