<?php
// Controla a autenticacao e as sessoes.





namespace App\Controllers;

use Core\Controller;
use App\Models\Usuario;

class AuthController extends Controller {

    public function login(): void {

        if (Usuario::estaLogado()) {
            $user = Usuario::usuarioAtual();
            $this->redirecionarPorPerfil($user['perfil']);
            return;
        }

        $usuariosDisponiveis = Usuario::all();

        $this->view('auth/login', [
            'tituloPagina' => 'Login | Central de Frequência e Alimentação Escolar',
            'usuarios' => $usuariosDisponiveis
        ]);
    }

    public function autenticar(): void {
        $usuario = trim($_POST['usuario'] ?? '');
        $senha = trim($_POST['senha'] ?? '');

        if (empty($usuario) || empty($senha)) {
            $this->flash('error', 'Por favor, informe seu usuário e senha.');
            $this->redirect('login');
            return;
        }

        $user = Usuario::autenticar($usuario, $senha);

        if (!$user) {
            $this->flash('error', 'Usuário ou senha incorretos! Tente novamente.');
            $this->redirect('login');
            return;
        }

        Usuario::login($user);
        $this->flash('success', "Bem-vindo(a), {$user['nome']}! Acesso concedido como {$user['cargo']}.");
        $this->redirecionarPorPerfil($user['perfil']);
    }

    public function logout(): void {
        Usuario::logout();
        $this->flash('success', 'Você saiu do sistema com segurança. Até breve!');
        $this->redirect('login');
    }

    private function redirecionarPorPerfil(string $perfil): void {
        switch ($perfil) {
            case 'inspetora':
                $this->redirect('frequencia');
                break;
            case 'nutricionista':
                $this->redirect('cozinha');
                break;
            case 'admin':
                $this->redirect('admin');
                break;
            default:
                $this->redirect('');
                break;
        }
    }
}
