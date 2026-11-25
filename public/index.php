<?php
// Inicializa a aplicacao e registra as rotas.







// A sessao disponibiliza o usuario e as mensagens entre paginas.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Mantem datas e horarios do sistema no fuso local da escola.
date_default_timezone_set('America/Sao_Paulo');
ini_set('default_charset', 'UTF-8');


// Localiza automaticamente as classes dos controllers, modelos e componentes Core.
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


// Carrega URLs, verificacoes de perfil e outras funcoes usadas pelas views.
require_once __DIR__ . '/../core/helpers.php';

use Core\Router;






// Rotas de entrada, autenticacao e encerramento da sessao.
Router::get('/login', 'AuthController@login');
Router::post('/login', 'AuthController@autenticar');
Router::get('/logout', 'AuthController@logout');


// Pagina publica de apresentacao do sistema.
Router::get('/', 'HomeController@index');


// Chamada diaria, indicadores, envio para cozinha e consulta do historico.
Router::get('/frequencia', 'FrequenciaController@index', ['inspetora']);
Router::post('/frequencia/salvar', 'FrequenciaController@salvar', ['inspetora']);
Router::post('/frequencia/marcarTodos', 'FrequenciaController@marcarTodos', ['inspetora']);
Router::post('/frequencia/enviarCozinha', 'FrequenciaController@enviarParaCozinha', ['inspetora']);
Router::get('/frequencia/pizza-dados', 'FrequenciaController@pizzaDados', ['inspetora']);
Router::get('/frequencia/historico', 'FrequenciaController@historico', ['inspetora']);


// Recebimento dos pedidos e atualizacao do preparo das refeicoes.
Router::get('/cozinha', 'CozinhaController@index', ['nutricionista']);
Router::post('/cozinha/atualizarStatus', 'CozinhaController@atualizarStatus', ['nutricionista']);


// Painel administrativo e operacoes de gerenciamento de contas.
Router::get('/admin', 'AdminController@index', ['admin']);
Router::get('/admin/usuarios', 'AdminController@usuarios', ['admin']);
Router::post('/admin/usuarios/salvar', 'AdminController@salvarUsuario', ['admin']);
Router::post('/admin/usuarios/alterar-senha', 'AdminController@alterarSenhaUsuario', ['admin']);
Router::post('/admin/usuarios/excluir', 'AdminController@excluirUsuario', ['admin']);


// Cardapio, fila do refeitorio, consumo e interacoes de alimentacao.
Router::get('/alimentacao/cardapio', 'AlimentacaoController@cardapio');
Router::post('/alimentacao/cardapio/salvar', 'AlimentacaoController@salvarCardapio', ['nutricionista']);
Router::post('/alimentacao/cardapio/copiar', 'AlimentacaoController@copiarCardapio', ['nutricionista']);
Router::get('/alimentacao/fila', 'AlimentacaoController@fila', ['nutricionista']);
Router::post('/alimentacao/salvarConsumo', 'AlimentacaoController@salvarConsumo', ['nutricionista']);
Router::get('/alimentacao/votacao', 'AlimentacaoController@votacao');
Router::post('/alimentacao/votar', 'AlimentacaoController@votar');
Router::get('/alimentacao/roleta', 'AlimentacaoController@roleta');


// Cadastro e manutencao dos estudantes e suas turmas.
Router::get('/alunos', 'AlunosController@index', ['admin']);
Router::get('/alunos/novo', 'AlunosController@novo', ['admin']);
Router::post('/alunos/salvar', 'AlunosController@salvar', ['admin']);
Router::get('/alunos/editar', 'AlunosController@editar', ['admin']);
Router::post('/alunos/atualizar', 'AlunosController@atualizar', ['admin']);
Router::post('/alunos/excluir', 'AlunosController@excluir', ['admin']);


// Consulta dos indicadores consolidados em relatorios.
Router::get('/relatorios', 'RelatoriosController@index');


// Entrega a requisicao ao controller associado a rota.
Router::dispatch();
