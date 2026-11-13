<?php
/**
 * Front Controller Principal - VivalCris
 * Central de Frequência e Alimentação Escolar
 */

// Inicia sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações de fuso horário e caracteres
date_default_timezone_set('America/Sao_Paulo');
ini_set('default_charset', 'UTF-8');

// Autoload simples baseado no padrão PSR-4
spl_autoload_register(function ($class) {
    $prefixApp = 'App\\';
    $prefixCore = 'Core\\';

    $baseDirApp = __DIR__ . '/../app/';
    $baseDirCore = __DIR__ . '/../core/';

    if (strncmp($prefixApp, $class, strlen($prefixApp)) === 0) {
        $relativeClass = substr($class, strlen($prefixApp));
        $file = $baseDirApp . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    if (strncmp($prefixCore, $class, strlen($prefixCore)) === 0) {
        $relativeClass = substr($class, strlen($prefixCore));
        $file = $baseDirCore . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Carrega funções auxiliares globais
require_once __DIR__ . '/../core/helpers.php';

use Core\Router;

// ========================================================
// Definição de Rotas do Sistema
// ========================================================

// 1. Autenticação e perfis de acesso
Router::get('/login', 'AuthController@login');
Router::post('/login', 'AuthController@autenticar');
Router::get('/logout', 'AuthController@logout');

// 2. Dashboard Principal
Router::get('/', 'HomeController@index');

// 3. Módulo de Frequência, Gráfico de Pizza & Envio para a Cozinha (Inspetoras)
Router::get('/frequencia', 'FrequenciaController@index');
Router::post('/frequencia/salvar', 'FrequenciaController@salvar');
Router::post('/frequencia/marcarTodos', 'FrequenciaController@marcarTodos');
Router::post('/frequencia/enviarCozinha', 'FrequenciaController@enviarParaCozinha');
Router::get('/frequencia/pizza-dados', 'FrequenciaController@pizzaDados');
Router::get('/frequencia/historico', 'FrequenciaController@historico');

// 4. Módulo da Cozinha & Nutricionista (Recebimento de Dados em Tempo Real)
Router::get('/cozinha', 'CozinhaController@index');
Router::post('/cozinha/atualizarStatus', 'CozinhaController@atualizarStatus');

// 5. Módulo do Administrador Geral (Monitora Ambos os Lados e Usuários)
Router::get('/admin', 'AdminController@index');
Router::get('/admin/usuarios', 'AdminController@usuarios');
Router::post('/admin/usuarios/salvar', 'AdminController@salvarUsuario');
Router::post('/admin/usuarios/excluir', 'AdminController@excluirUsuario');

// 6. Módulo de Alimentação & Merenda (Cardápio, Fila, Votação, Roleta)
Router::get('/alimentacao/cardapio', 'AlimentacaoController@cardapio');
Router::post('/alimentacao/cardapio/salvar', 'AlimentacaoController@salvarCardapio');
Router::post('/alimentacao/cardapio/copiar', 'AlimentacaoController@copiarCardapio');
Router::get('/alimentacao/fila', 'AlimentacaoController@fila');
Router::post('/alimentacao/salvarConsumo', 'AlimentacaoController@salvarConsumo');
Router::get('/alimentacao/votacao', 'AlimentacaoController@votacao');
Router::post('/alimentacao/votar', 'AlimentacaoController@votar');
Router::get('/alimentacao/roleta', 'AlimentacaoController@roleta');

// 7. Módulo de Gestão de Alunos (12 Salas Únicas)
Router::get('/alunos', 'AlunosController@index');
Router::get('/alunos/novo', 'AlunosController@novo');
Router::post('/alunos/salvar', 'AlunosController@salvar');
Router::get('/alunos/editar', 'AlunosController@editar');
Router::post('/alunos/atualizar', 'AlunosController@atualizar');
Router::post('/alunos/excluir', 'AlunosController@excluir');

// 8. Módulo de Relatórios Escolares
Router::get('/relatorios', 'RelatoriosController@index');

// Despacha a requisição
Router::dispatch();
