<?php
// Controla a administracao e os usuarios do sistema.





namespace App\Controllers;

use Core\Controller;
use App\Models\Usuario;
use App\Models\Aluno;
use App\Models\Frequencia;
use App\Models\PedidoCozinha;
use App\Models\HistoricoFrequencia;
use App\Models\Cardapio;

class AdminController extends Controller {

    public function index(): void {
        $usuario = $this->requireRole('admin');

        $hoje = date('Y-m-d');
        $totalAlunos = Aluno::countTotal();
        $turmas = Aluno::getTurmas();


        $statsFrequencia = Frequencia::getEstatisticas($hoje);
        $pizzaStats = HistoricoFrequencia::getDadosGraficoPizza($hoje);
        $historicos = HistoricoFrequencia::getTodos(7);


        $pedidoHoje = PedidoCozinha::getPorData($hoje);
        $cardapioHoje = Cardapio::getCardapioDeHoje();
        $pedidosRecentes = PedidoCozinha::getUltimosPedidos(7);


        $usuarios = Usuario::all();

        $this->view('admin/index', [
            'tituloPagina' => 'Painel Administrativo Geral | VivalCris',
            'hoje' => $hoje,
            'usuario' => $usuario,
            'totalAlunos' => $totalAlunos,
            'turmas' => $turmas,
            'statsFrequencia' => $statsFrequencia,
            'pizzaStats' => $pizzaStats,
            'historicos' => $historicos,
            'pedidoHoje' => $pedidoHoje,
            'cardapioHoje' => $cardapioHoje,
            'pedidosRecentes' => $pedidosRecentes,
            'usuarios' => $usuarios
        ]);
    }

    public function usuarios(): void {
        $usuario = $this->requireRole('admin');
        $usuarios = Usuario::all();

        $this->view('admin/usuarios', [
            'tituloPagina' => 'Gerenciamento de Usuários | Painel ADM',
            'usuario' => $usuario,
            'usuarios' => $usuarios
        ]);
    }

    public function salvarUsuario(): void {
        $this->requireRole('admin');

        $nome = trim($_POST['nome'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $senha = trim($_POST['senha'] ?? '');
        $perfil = trim($_POST['perfil'] ?? 'inspetora');
        $cargo = trim($_POST['cargo'] ?? '');
        $avatar = trim($_POST['avatar'] ?? '👤');

        if (empty($nome) || empty($usuario) || empty($senha)) {
            $this->flash('error', 'Nome, login e senha são obrigatórios!');
            $this->redirect('admin/usuarios');
            return;
        }

        if (strlen($senha) < 8) {
            $this->flash('error', 'A senha deve ter pelo menos 8 caracteres.');
            $this->redirect('admin/usuarios');
            return;
        }

        if (!in_array($perfil, ['inspetora', 'nutricionista', 'admin'], true)) {
            $this->flash('error', 'Selecione um perfil de acesso válido.');
            $this->redirect('admin/usuarios');
            return;
        }

        try {
            Usuario::criar([
                'nome' => $nome,
                'usuario' => $usuario,
                'senha' => $senha,
                'perfil' => $perfil,
                'cargo' => $cargo ?: ($perfil === 'inspetora' ? 'Inspetora Escolar' : ($perfil === 'nutricionista' ? 'Nutricionista' : 'Administrador')),
                'avatar' => $avatar,
                'ativo' => 1
            ]);
            $this->flash('success', "Novo usuário '{$nome}' cadastrado com sucesso!");
        } catch (\Exception $e) {
            $this->flash('error', "Erro ao cadastrar usuário: {$e->getMessage()}");
        }

        $this->redirect('admin/usuarios');
    }

    public function excluirUsuario(): void {
        $this->requireRole('admin');

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            if (Usuario::excluir($id)) {
                $this->flash('success', 'Usuário removido com sucesso!');
            } else {
                $this->flash('error', 'Não é permitido remover o administrador principal.');
            }
        }

        $this->redirect('admin/usuarios');
    }
}
