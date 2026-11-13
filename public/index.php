<?php
// Inicializa a aplicacao e registra as rotas.







if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


date_default_timezone_set('America/Sao_Paulo');
ini_set('default_charset', 'UTF-8');


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


require_once __DIR__ . '/../core/helpers.php';

use Core\Router;






Router::get('/login', 'AuthController@login');
Router::post('/login', 'AuthController@autenticar');
Router::get('/logout', 'AuthController@logout');


Router::get('/', 'HomeController@index');


Router::get('/frequencia', 'FrequenciaController@index');
Router::post('/frequencia/salvar', 'FrequenciaController@salvar');
Router::post('/frequencia/marcarTodos', 'FrequenciaController@marcarTodos');
Router::post('/frequencia/enviarCozinha', 'FrequenciaController@enviarParaCozinha');
Router::get('/frequencia/pizza-dados', 'FrequenciaController@pizzaDados');
Router::get('/frequencia/historico', 'FrequenciaController@historico');


Router::get('/cozinha', 'CozinhaController@index');
Router::post('/cozinha/atualizarStatus', 'CozinhaController@atualizarStatus');


Router::get('/admin', 'AdminController@index');
Router::get('/admin/usuarios', 'AdminController@usuarios');
Router::post('/admin/usuarios/salvar', 'AdminController@salvarUsuario');
Router::post('/admin/usuarios/excluir', 'AdminController@excluirUsuario');


Router::get('/alimentacao/cardapio', 'AlimentacaoController@cardapio');
Router::post('/alimentacao/cardapio/salvar', 'AlimentacaoController@salvarCardapio');
Router::post('/alimentacao/cardapio/copiar', 'AlimentacaoController@copiarCardapio');
Router::get('/alimentacao/fila', 'AlimentacaoController@fila');
Router::post('/alimentacao/salvarConsumo', 'AlimentacaoController@salvarConsumo');
Router::get('/alimentacao/votacao', 'AlimentacaoController@votacao');
Router::post('/alimentacao/votar', 'AlimentacaoController@votar');
Router::get('/alimentacao/roleta', 'AlimentacaoController@roleta');


Router::get('/alunos', 'AlunosController@index');
Router::get('/alunos/novo', 'AlunosController@novo');
Router::post('/alunos/salvar', 'AlunosController@salvar');
Router::get('/alunos/editar', 'AlunosController@editar');
Router::post('/alunos/atualizar', 'AlunosController@atualizar');
Router::post('/alunos/excluir', 'AlunosController@excluir');


Router::get('/relatorios', 'RelatoriosController@index');


Router::dispatch();
