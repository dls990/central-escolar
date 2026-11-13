<?php
// Controla os pedidos e o painel da cozinha.





namespace App\Controllers;

use Core\Controller;
use App\Models\Usuario;
use App\Models\PedidoCozinha;
use App\Models\Cardapio;
use App\Models\Frequencia;
use App\Models\Aluno;

class CozinhaController extends Controller {

    public function index(): void {
        $this->requireAuth();

        $data = $_GET['data'] ?? date('Y-m-d');
        $pedido = PedidoCozinha::getPorData($data);
        $cardapioHoje = Cardapio::getCardapioDeHoje();
        $ultimosPedidos = PedidoCozinha::getUltimosPedidos(10);
        $usuario = Usuario::usuarioAtual();



        $previaTempoReal = null;
        if (!$pedido) {
            $stats = Frequencia::getEstatisticas($data);
            $alunos = Frequencia::getChamada($data);

            $fund1 = 0;
            $fund2 = 0;
            $medio = 0;
            $restricoes = [];

            $turmasFund1 = ['1º Ano EF', '2º Ano EF', '3º Ano EF', '4º Ano EF', '5º Ano EF'];
            $turmasFund2 = ['6º Ano EF', '7º Ano EF', '8º Ano EF', '9º Ano EF'];
            $turmasMedio = ['1º Ano EM', '2º Ano EM', '3º Ano EM'];

            foreach ($alunos as $a) {
                if (($a['status'] ?? 'presente') === 'presente') {
                    if (in_array($a['turma'], $turmasFund1)) $fund1++;
                    elseif (in_array($a['turma'], $turmasFund2)) $fund2++;
                    elseif (in_array($a['turma'], $turmasMedio)) $medio++;

                    if (!empty($a['restricoes']) && strtolower($a['restricoes']) !== 'nenhuma') {
                        $restricoes[] = [
                            'aluno' => $a['nome'],
                            'turma' => $a['turma'],
                            'restricao' => $a['restricoes']
                        ];
                    }
                }
            }

            $previaTempoReal = [
                'total_presentes' => $stats['presentes'],
                'total_ausentes' => $stats['ausentes'],
                'total_matriculados' => $stats['total'],
                'refeicoes_fund1' => $fund1,
                'refeicoes_fund2' => $fund2,
                'refeicoes_medio' => $medio,
                'restricoes_lista' => $restricoes
            ];
        }

        $this->view('cozinha/index', [
            'tituloPagina' => 'Painel da Cozinha & Nutricionista | VivalCris',
            'data' => $data,
            'pedido' => $pedido,
            'previaTempoReal' => $previaTempoReal,
            'cardapioHoje' => $cardapioHoje,
            'ultimosPedidos' => $ultimosPedidos,
            'usuario' => $usuario
        ]);
    }

    public function atualizarStatus(): void {
        $this->requireRole(['nutricionista', 'admin']);

        $data = $_POST['data'] ?? date('Y-m-d');
        $status = $_POST['status'] ?? 'em_preparo';
        $obs = trim($_POST['observacao_cozinha'] ?? '');

        if (!in_array($status, ['pendente', 'enviado', 'em_preparo', 'pronto'])) {
            $this->json(['success' => false, 'message' => 'Status inválido'], 400);
            return;
        }

        $ok = PedidoCozinha::atualizarStatus($data, $status, $obs);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $this->json([
                'success' => $ok,
                'status' => $status,
                'message' => 'Status da cozinha atualizado com sucesso! 🍲'
            ]);
            return;
        }

        $this->flash('success', 'Status do preparo atualizado com sucesso!');
        $this->redirect("cozinha?data={$data}");
    }
}
