<?php
// Controla o cadastro e a gestao de alunos.





namespace App\Controllers;

use Core\Controller;
use App\Models\Aluno;

class AlunosController extends Controller {
    public function index(): void {
        $turma = $_GET['turma'] ?? 'todas';
        $busca = trim($_GET['busca'] ?? '');

        $turmas = Aluno::getTurmas();
        $alunos = (!empty($turma) && $turma !== 'todas') ? Aluno::getByTurma($turma) : Aluno::all();

        if (!empty($busca)) {
            $buscaLower = mb_strtolower($busca, 'UTF-8');
            $alunos = array_filter($alunos, function($a) use ($buscaLower) {
                return (str_contains(mb_strtolower($a['nome'], 'UTF-8'), $buscaLower) ||
                        str_contains(mb_strtolower($a['matricula'], 'UTF-8'), $buscaLower) ||
                        str_contains(mb_strtolower($a['restricoes'], 'UTF-8'), $buscaLower));
            });
        }

        $this->view('alunos/index', [
            'alunos' => $alunos,
            'turmas' => $turmas,
            'turmaSelecionada' => $turma,
            'busca' => $busca,
            'total' => count($alunos)
        ]);
    }

    public function novo(): void {
        $turmas = Aluno::getTurmas();
        $this->view('alunos/form', [
            'aluno' => null,
            'turmas' => $turmas,
            'titulo' => 'Cadastrar Novo Aluno'
        ]);
    }

    public function salvar(): void {
        $nome = trim($_POST['nome'] ?? '');
        $matricula = trim($_POST['matricula'] ?? '');
        $turma = trim($_POST['turma'] ?? '');
        $restricoes = trim($_POST['restricoes'] ?? 'Nenhuma');
        $telefone = trim($_POST['telefone_responsavel'] ?? '');
        $avatar = $_POST['avatar'] ?? '👦';

        if (empty($nome) || empty($matricula) || empty($turma)) {
            $_SESSION['flash_error'] = 'Preencha todos os campos obrigatórios!';
            $this->redirect('alunos/novo');
            return;
        }

        Aluno::create([
            'nome' => $nome,
            'matricula' => $matricula,
            'turma' => $turma,
            'avatar' => $avatar,
            'restricoes' => $restricoes,
            'telefone_responsavel' => $telefone
        ]);

        $_SESSION['flash_success'] = "Aluno {$nome} cadastrado com sucesso! 🎉";
        $this->redirect('alunos');
    }

    public function editar(): void {
        $id = (int)($_GET['id'] ?? 0);
        $aluno = Aluno::find($id);

        if (!$aluno) {
            $_SESSION['flash_error'] = 'Aluno não encontrado!';
            $this->redirect('alunos');
            return;
        }

        $turmas = Aluno::getTurmas();
        $this->view('alunos/form', [
            'aluno' => $aluno,
            'turmas' => $turmas,
            'titulo' => 'Editar Dados do Aluno'
        ]);
    }

    public function atualizar(): void {
        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $matricula = trim($_POST['matricula'] ?? '');
        $turma = trim($_POST['turma'] ?? '');
        $restricoes = trim($_POST['restricoes'] ?? 'Nenhuma');
        $telefone = trim($_POST['telefone_responsavel'] ?? '');

        if ($id <= 0 || empty($nome) || empty($matricula) || empty($turma)) {
            $_SESSION['flash_error'] = 'Dados inválidos para atualização!';
            $this->redirect('alunos');
            return;
        }

        Aluno::update($id, [
            'nome' => $nome,
            'matricula' => $matricula,
            'turma' => $turma,
            'restricoes' => $restricoes,
            'telefone_responsavel' => $telefone
        ]);

        $_SESSION['flash_success'] = "Dados de {$nome} atualizados com sucesso!";
        $this->redirect('alunos');
    }

    public function excluir(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            Aluno::delete($id);
            $_SESSION['flash_success'] = 'Aluno removido do sistema.';
        }
        $this->redirect('alunos');
    }
}
