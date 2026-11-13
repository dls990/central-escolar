<?php
// Configura o acesso e a estrutura do banco de dados.






namespace App\Models;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    // Abre a conexao selecionada na configuracao e prepara o banco para uso.
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            // O arquivo de configuracao define qual banco e credenciais usar.
            $config = require __DIR__ . '/../../config/database.php';
            $driver = $config['driver'] ?? 'mysql';

            try {
                if ($driver === 'mysql') {
                    // MySQL e o modo recomendado para a instalacao no XAMPP.
                    $mc = $config['mysql'];
                    $dsn = "mysql:host={$mc['host']};port={$mc['port']};dbname={$mc['database']};charset={$mc['charset']}";
                    self::$instance = new PDO($dsn, $mc['username'], $mc['password']);
                    self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                    self::$instance->exec("SET NAMES utf8mb4");
                } else {

                    // SQLite guarda o banco em arquivo, sem exigir servidor separado.
                    $dbDir = __DIR__ . '/../../database';
                    if (!is_dir($dbDir)) {
                        mkdir($dbDir, 0777, true);
                    }
                    $dbPath = $config['sqlite']['database'] ?? ($dbDir . '/escola.sqlite');
                    self::$instance = new PDO("sqlite:{$dbPath}");
                    self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                }


                // Garante que a estrutura exista antes de qualquer consulta.
                self::ensureSchema(self::$instance, $driver);


                // A carga inicial so roda quando ainda nao ha contas cadastradas.
                $userCount = (int)self::$instance->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
                if ($userCount === 0) {
                    self::seedData(self::$instance, $driver);
                }
            } catch (PDOException $e) {
                die("Erro ao conectar ao banco de dados ({$driver}): " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    // Descarta a conexao em memoria e abre uma nova instancia.
    public static function resetDatabase(): void {
        self::$instance = null;
        self::getInstance();
    }

    // Direciona a criacao da estrutura para o driver ativo.
    private static function ensureSchema(PDO $db, string $driver): void {
        if ($driver === 'mysql') {
            self::ensureSchemaMysql($db);
        } else {
            self::ensureSchemaSqlite($db);
        }
    }

    // Confere se as tabelas MySQL existem e cria a estrutura se necessario.
    private static function ensureSchemaMysql(PDO $db): void {

        // A lista permite detectar uma instalacao incompleta, nao apenas vazia.
        $tabelas = ['usuarios', 'alunos', 'frequencias', 'pedidos_cozinha',
                     'historico_frequencia_diaria', 'cardapios', 'refeicoes',
                     'votacao_pratos', 'dicas_nutricao'];

        foreach ($tabelas as $tabela) {
            $stmt = $db->query("SHOW TABLES LIKE '{$tabela}'");
            if ($stmt->rowCount() === 0) {

                self::createTablesMysql($db);
                return;
            }
        }
    }

    // Define as tabelas relacionais usadas quando o driver e MySQL.
    private static function createTablesMysql(PDO $db): void {
        $db->exec("
            CREATE TABLE IF NOT EXISTS `usuarios` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nome` VARCHAR(150) NOT NULL,
                `usuario` VARCHAR(60) NOT NULL UNIQUE,
                `senha` VARCHAR(255) NOT NULL,
                `perfil` ENUM('inspetora', 'nutricionista', 'admin') NOT NULL,
                `cargo` VARCHAR(150) NOT NULL,
                `avatar` VARCHAR(10) NOT NULL DEFAULT '👤',
                `ativo` TINYINT(1) DEFAULT 1,
                `ultimo_login` DATETIME NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `alunos` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nome` VARCHAR(150) NOT NULL,
                `matricula` VARCHAR(50) NOT NULL UNIQUE,
                `turma` VARCHAR(50) NOT NULL,
                `avatar` VARCHAR(10) NOT NULL DEFAULT '👦',
                `restricoes` VARCHAR(255) DEFAULT 'Nenhuma',
                `telefone_responsavel` VARCHAR(30) DEFAULT NULL,
                `pontos_nutri` INT DEFAULT 50,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `frequencias` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `aluno_id` INT NOT NULL,
                `data` DATE NOT NULL,
                `status` ENUM('presente', 'ausente', 'justificado') NOT NULL DEFAULT 'presente',
                `horario` VARCHAR(10) DEFAULT NULL,
                `observacao` TEXT DEFAULT NULL,
                `inspetora_nome` VARCHAR(100) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT `fk_freq_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `alunos`(`id`) ON DELETE CASCADE,
                UNIQUE KEY `uq_aluno_data` (`aluno_id`, `data`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `pedidos_cozinha` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `data` DATE NOT NULL UNIQUE,
                `enviado_por` VARCHAR(100) NOT NULL,
                `horario_envio` VARCHAR(10) NOT NULL,
                `total_matriculados` INT NOT NULL,
                `total_presentes` INT NOT NULL,
                `total_ausentes` INT NOT NULL,
                `total_justificados` INT NOT NULL,
                `refeicoes_fund1` INT NOT NULL,
                `refeicoes_fund2` INT NOT NULL,
                `refeicoes_medio` INT NOT NULL,
                `restricoes_resumo` TEXT DEFAULT NULL,
                `restricoes_detalhes` LONGTEXT DEFAULT NULL,
                `status` ENUM('pendente', 'enviado', 'em_preparo', 'pronto') DEFAULT 'enviado',
                `observacao_inspetora` TEXT DEFAULT NULL,
                `observacao_cozinha` TEXT DEFAULT NULL,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `historico_frequencia_diaria` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `data` DATE NOT NULL UNIQUE,
                `presentes` INT NOT NULL,
                `ausentes` INT NOT NULL,
                `justificados` INT NOT NULL,
                `taxa_presenca` DECIMAL(5,2) NOT NULL,
                `total_alunos` INT NOT NULL,
                `fechado_por` VARCHAR(100) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `cardapios` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `dia_semana` VARCHAR(30) NOT NULL,
                `prato_principal` VARCHAR(200) NOT NULL,
                `acompanhamento` VARCHAR(255) NOT NULL,
                `sobremesa` VARCHAR(150) NOT NULL,
                `bebida` VARCHAR(100) NOT NULL,
                `calorias` INT DEFAULT 450,
                `tags` VARCHAR(255) NOT NULL,
                `emoji` VARCHAR(10) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `refeicoes` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `aluno_id` INT NOT NULL,
                `cardapio_id` INT DEFAULT NULL,
                `data` DATE NOT NULL,
                `consumiu` TINYINT(1) DEFAULT 1,
                `avaliacao` INT DEFAULT 5,
                `comentario` TEXT DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT `fk_ref_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `alunos`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `votacao_pratos` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nome` VARCHAR(150) NOT NULL,
                `descricao` TEXT NOT NULL,
                `votos` INT DEFAULT 0,
                `emoji` VARCHAR(10) NOT NULL,
                `categoria` VARCHAR(50) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `dicas_nutricao` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `titulo` VARCHAR(150) NOT NULL,
                `dica` TEXT NOT NULL,
                `icone` VARCHAR(10) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    // Cria as tabelas SQLite quando o banco ainda nao tem estrutura.
    private static function ensureSchemaSqlite(PDO $db): void {
        $db->exec("
            CREATE TABLE IF NOT EXISTS usuarios (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                usuario TEXT UNIQUE NOT NULL,
                senha TEXT NOT NULL,
                perfil TEXT NOT NULL,
                cargo TEXT NOT NULL,
                avatar TEXT NOT NULL DEFAULT '👤',
                ativo INTEGER DEFAULT 1,
                ultimo_login DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS alunos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                matricula TEXT UNIQUE NOT NULL,
                turma TEXT NOT NULL,
                avatar TEXT NOT NULL DEFAULT '👦',
                restricoes TEXT DEFAULT 'Nenhuma',
                telefone_responsavel TEXT,
                pontos_nutri INTEGER DEFAULT 50,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS frequencias (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                aluno_id INTEGER NOT NULL,
                data DATE NOT NULL,
                status TEXT CHECK(status IN ('presente', 'ausente', 'justificado')) NOT NULL DEFAULT 'presente',
                horario TEXT,
                observacao TEXT,
                inspetora_nome TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
                UNIQUE(aluno_id, data)
            );

            CREATE TABLE IF NOT EXISTS pedidos_cozinha (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                data DATE NOT NULL UNIQUE,
                enviado_por TEXT NOT NULL,
                horario_envio TEXT NOT NULL,
                total_matriculados INTEGER NOT NULL,
                total_presentes INTEGER NOT NULL,
                total_ausentes INTEGER NOT NULL,
                total_justificados INTEGER NOT NULL,
                refeicoes_fund1 INTEGER NOT NULL,
                refeicoes_fund2 INTEGER NOT NULL,
                refeicoes_medio INTEGER NOT NULL,
                restricoes_resumo TEXT,
                restricoes_detalhes TEXT,
                status TEXT DEFAULT 'enviado',
                observacao_inspetora TEXT,
                observacao_cozinha TEXT,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS historico_frequencia_diaria (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                data DATE NOT NULL UNIQUE,
                presentes INTEGER NOT NULL,
                ausentes INTEGER NOT NULL,
                justificados INTEGER NOT NULL,
                taxa_presenca REAL NOT NULL,
                total_alunos INTEGER NOT NULL,
                fechado_por TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS cardapios (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                dia_semana TEXT NOT NULL,
                prato_principal TEXT NOT NULL,
                acompanhamento TEXT NOT NULL,
                sobremesa TEXT NOT NULL,
                bebida TEXT NOT NULL,
                calorias INTEGER DEFAULT 450,
                tags TEXT NOT NULL,
                emoji TEXT NOT NULL
            );

            CREATE TABLE IF NOT EXISTS refeicoes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                aluno_id INTEGER NOT NULL,
                cardapio_id INTEGER,
                data DATE NOT NULL,
                consumiu INTEGER DEFAULT 1,
                avaliacao INTEGER DEFAULT 5,
                comentario TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS votacao_pratos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                descricao TEXT NOT NULL,
                votos INTEGER DEFAULT 0,
                emoji TEXT NOT NULL,
                categoria TEXT NOT NULL
            );

            CREATE TABLE IF NOT EXISTS dicas_nutricao (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                titulo TEXT NOT NULL,
                dica TEXT NOT NULL,
                icone TEXT NOT NULL
            );
        ");
    }

    // Executa as cargas iniciais apenas quando o banco esta sem usuarios.
    public static function seedData(PDO $db, ?string $driver = null): void {
        // Se nao receber o driver, reaproveita a escolha da configuracao.
        if ($driver === null) {
            $config = require __DIR__ . '/../../config/database.php';
            $driver = $config['driver'] ?? 'mysql';
        }
        self::seedUsuarios($db);
        self::seedAlunos($db);
        self::seedCardapios($db);
        self::seedVotacao($db);
        self::seedDicas($db);
        self::seedHistoricoEFrequencia($db, $driver);
    }

    // Insere as contas demonstrativas com senhas armazenadas em hash.
    public static function seedUsuarios(PDO $db): void {
        $db->exec("DELETE FROM usuarios");

        $usuarios = [
            [
                'Vivi',
                'vivi',
                password_hash('123456', PASSWORD_DEFAULT),
                'inspetora',
                'Inspetora Escolar (Fund 1 & 2)',
                '👩‍🏫',
                1
            ],
            [
                'Val',
                'val',
                password_hash('123456', PASSWORD_DEFAULT),
                'inspetora',
                'Inspetora Escolar (Ensino Médio)',
                '👩‍💼',
                1
            ],
            [
                'Cris',
                'cris',
                password_hash('123456', PASSWORD_DEFAULT),
                'inspetora',
                'Inspetora Escolar (Geral)',
                '👩‍🏫',
                1
            ],
            [
                'Dra. Camila',
                'nutri',
                password_hash('123456', PASSWORD_DEFAULT),
                'nutricionista',
                'Nutricionista Chefe & Cozinha Escolar',
                '🥗',
                1
            ],
            [
                'Diretoria & ADM',
                'admin',
                password_hash('admin123', PASSWORD_DEFAULT),
                'admin',
                'Administrador Geral do Sistema',
                '👑',
                1
            ],
        ];

        $stmt = $db->prepare("INSERT INTO usuarios (nome, usuario, senha, perfil, cargo, avatar, ativo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($usuarios as $u) {
            $stmt->execute($u);
        }
    }

    // Popula as turmas com os registros demonstrativos de alunos.
    public static function seedAlunos(PDO $db): void {
        $db->exec("DELETE FROM alunos");

        $alunos = [

            ['Arthur Ferreira', '20260101', '1º Ano EF', '👦', 'Nenhuma', '(11) 98111-0101', 90],
            ['Helena Castro', '20260102', '1º Ano EF', '👧', 'Sem Lactose', '(11) 98111-0102', 120],
            ['Bernardo Dias', '20260103', '1º Ano EF', '🧒', 'Nenhuma', '(11) 98111-0103', 85],

            ['Alice Santos', '20260201', '2º Ano EF', '👧', 'Sem Glúten', '(11) 98111-0201', 110],
            ['Theo Carvalho', '20260202', '2º Ano EF', '👦', 'Nenhuma', '(11) 98111-0202', 95],
            ['Manuela Souza', '20260203', '2º Ano EF', '👩', 'Nenhuma', '(11) 98111-0203', 100],

            ['Davi Lucca', '20260301', '3º Ano EF', '👦', 'Nenhuma', '(11) 98111-0301', 105],
            ['Valentina Lima', '20260302', '3º Ano EF', '👧', 'Vegetariana', '(11) 98111-0302', 140],
            ['Gabriel Alves', '20260303', '3º Ano EF', '🧒', 'Nenhuma', '(11) 98111-0303', 80],

            ['Lorena Martins', '20260401', '4º Ano EF', '👧', 'Alérgica a Amendoim', '(11) 98111-0401', 130],
            ['Heitor Barbosa', '20260402', '4º Ano EF', '👦', 'Nenhuma', '(11) 98111-0402', 115],
            ['Sophia Ribeiro', '20260403', '4º Ano EF', '👩', 'Sem Lactose', '(11) 98111-0403', 125],

            ['Samuel Cardoso', '20260501', '5º Ano EF', '👦', 'Nenhuma', '(11) 98111-0501', 110],
            ['Laura Moreira', '20260502', '5º Ano EF', '👧', 'Nenhuma', '(11) 98111-0502', 105],
            ['Felipe Araujo', '20260503', '5º Ano EF', '🧒', 'Vegetariano', '(11) 98111-0503', 95],

            ['Lucas Oliveira', '20260601', '6º Ano EF', '👦', 'Nenhuma', '(11) 98111-0601', 135],
            ['Sofia Mendes', '20260602', '6º Ano EF', '👧', 'Sem Lactose', '(11) 98111-0602', 150],
            ['Mateus Silva', '20260603', '6º Ano EF', '🧒', 'Vegetariano', '(11) 98111-0603', 95],

            ['Beatriz Costa', '20260701', '7º Ano EF', '👩', 'Sem Glúten', '(11) 98111-0701', 180],
            ['Enzo Gabriel', '20260702', '7º Ano EF', '🧑', 'Nenhuma', '(11) 98111-0702', 110],
            ['Camila Rocha', '20260703', '7º Ano EF', '👧', 'Alérgica a Frutos do Mar', '(11) 98111-0703', 140],

            ['Pedro Henrique', '20260801', '8º Ano EF', '🧒', 'Nenhuma', '(11) 98111-0801', 85],
            ['Mariana Fernandes', '20260802', '8º Ano EF', '👧', 'Vegetariana', '(11) 98111-0802', 130],
            ['Guilherme Ribeiro', '20260803', '8º Ano EF', '🧑', 'Nenhuma', '(11) 98111-0803', 90],

            ['Isabela Souza', '20260901', '9º Ano EF', '👩', 'Sem Glúten', '(11) 98111-0901', 175],
            ['Gustavo Nogueira', '20260902', '9º Ano EF', '👦', 'Nenhuma', '(11) 98111-0902', 120],
            ['Leticia Campos', '20260903', '9º Ano EF', '👧', 'Sem Lactose', '(11) 98111-0903', 145],

            ['Thiago Mendes', '20261001', '1º Ano EM', '🧑', 'Nenhuma', '(11) 98111-1001', 160],
            ['Julia Azevedo', '20261002', '1º Ano EM', '👩', 'Vegetariana', '(11) 98111-1002', 170],
            ['Nicolas Ramos', '20261003', '1º Ano EM', '👦', 'Nenhuma', '(11) 98111-1003', 115],

            ['Rafaela Pires', '20261101', '2º Ano EM', '👩', 'Sem Lactose', '(11) 98111-1101', 190],
            ['Leonardo Freitas', '20261102', '2º Ano EM', '🧑', 'Nenhuma', '(11) 98111-1102', 130],
            ['Bruna Guimaraes', '20261103', '2º Ano EM', '👧', 'Nenhuma', '(11) 98111-1103', 140],

            ['Caio Vicente', '20261201', '3º Ano EM', '👦', 'Nenhuma', '(11) 98111-1201', 200],
            ['Yasmin Toledo', '20261202', '3º Ano EM', '👩', 'Sem Glúten', '(11) 98111-1202', 185],
            ['Rodrigo Peixoto', '20261203', '3º Ano EM', '🧑', 'Vegetariano', '(11) 98111-1203', 165],
        ];

        $stmt = $db->prepare("INSERT INTO alunos (nome, matricula, turma, avatar, restricoes, telefone_responsavel, pontos_nutri) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($alunos as $a) {
            $stmt->execute($a);
        }
    }

    // Prepara exemplos de chamada, pedidos para a cozinha e historico.
    public static function seedHistoricoEFrequencia(PDO $db, ?string $driver = null): void {
        $db->exec("DELETE FROM frequencias");
        $db->exec("DELETE FROM pedidos_cozinha");
        $db->exec("DELETE FROM historico_frequencia_diaria");

        $totalAlunos = (int)$db->query("SELECT COUNT(*) FROM alunos")->fetchColumn();
        $hoje = date('Y-m-d');
        $ontem = date('Y-m-d', strtotime('-1 day'));
        $anteontem = date('Y-m-d', strtotime('-2 days'));


        $stmtFreq = $db->prepare("INSERT INTO frequencias (aluno_id, data, status, horario, observacao, inspetora_nome) VALUES (?, ?, ?, ?, ?, ?)");


        for ($id = 1; $id <= $totalAlunos; $id++) {
            if ($id === 6 || $id === 18 || $id === 28) {
                $status = 'ausente';
                $horario = null;
                $obs = 'Falta comunicada pela família';
            } elseif ($id === 4 || $id === 22) {
                $status = 'justificado';
                $horario = null;
                $obs = 'Consulta médica / odontológica';
            } else {
                $status = 'presente';
                $horario = sprintf('07:%02d', 10 + ($id % 25));
                $obs = null;
            }
            $stmtFreq->execute([$id, $hoje, $status, $horario, $obs, 'Inspetora Vivi']);
        }


        HistoricoFrequencia::salvarDiario($hoje, 'Inspetora Vivi');


        PedidoCozinha::enviarParaCozinha($hoje, 'Inspetora Vivi', 'Alunos do 7º e 8º EF têm aula prática antes do almoço. Refeições conferidas!');


        $db->exec("
            INSERT INTO historico_frequencia_diaria
            (data, presentes, ausentes, justificados, taxa_presenca, total_alunos, fechado_por)
            VALUES ('{$ontem}', 33, 2, 1, 91.7, {$totalAlunos}, 'Inspetora Val')
        ");
        $db->exec("
            INSERT INTO pedidos_cozinha
            (data, enviado_por, horario_envio, total_matriculados, total_presentes, total_ausentes, total_justificados,
             refeicoes_fund1, refeicoes_fund2, refeicoes_medio, restricoes_resumo, status, observacao_inspetora, observacao_cozinha)
            VALUES ('{$ontem}', 'Inspetora Val', '08:20', {$totalAlunos}, 33, 2, 1, 14, 11, 8, '3x Sem Lactose, 2x Sem Glúten, 3x Vegetariano', 'pronto', 'Tudo tranquilo nas salas de aula.', 'Cardápio servido com 100% de aceitação!')
        ");


        $db->exec("
            INSERT INTO historico_frequencia_diaria
            (data, presentes, ausentes, justificados, taxa_presenca, total_alunos, fechado_por)
            VALUES ('{$anteontem}', 34, 1, 1, 94.4, {$totalAlunos}, 'Inspetora Cris')
        ");
        $db->exec("
            INSERT INTO pedidos_cozinha
            (data, enviado_por, horario_envio, total_matriculados, total_presentes, total_ausentes, total_justificados,
             refeicoes_fund1, refeicoes_fund2, refeicoes_medio, restricoes_resumo, status, observacao_inspetora, observacao_cozinha)
            VALUES ('{$anteontem}', 'Inspetora Cris', '08:10', {$totalAlunos}, 34, 1, 1, 14, 12, 8, '4x Sem Lactose, 2x Sem Glúten, 3x Vegetariano', 'pronto', 'Dia de prova do EM, chamada antecipada.', 'Almoço finalizado às 12:30.')
        ");
    }

    // Insere as opcoes iniciais de refeicao para cada dia da semana.
    public static function seedCardapios(PDO $db): void {
        $db->exec("DELETE FROM cardapios");
        $cardapios = [
            ['Segunda-feira', 'Frango Grelhado ao Molho de Laranja', 'Arroz Integral, Feijão Carioca e Salada Colorida', 'Melancia Fresquinha Fatiada', 'Suco Natural de Maracujá', 480, 'Rico em Vitamina C,Rico em Proteína', '🍗'],
            ['Terça-feira', 'Iscas de Carne com Legumes Salteados', 'Arroz Branco, Feijão Preto e Vinagrete', 'Banana Prata com Canela', 'Suco de Abacaxi com Hortelã', 520, 'Energia Total,Rico em Ferro', '🥩'],
            ['Quarta-feira', 'Filé de Peixe Empanado com Aveia', 'Purê de Mandioquinha e Brócolis ao Vapor', 'Maçã Doce Higienizada', 'Suco de Laranja Natural', 460, 'Ômega 3,Amigo do Cérebro', '🐟'],
            ['Quinta-feira', 'Estrogonofe Nutritivo de Frango & Abóbora', 'Arroz de Couve-Flor & Batata Rústica Assada', 'Salada de Frutas Tropicais', 'Suco de Melão com Limão', 490, 'Cremoso & Saudável,Super Vitaminas', '🍲'],
            ['Sexta-feira', 'Macarronada Colorida com Molho de Tomate Fresco e Almôndegas', 'Mix de Folhas Verdes e Cenoura Ralada', 'Picolé Natural de Fruta', 'Limonada Suíça Refrescante', 510, 'Prato da Alegria,Fibras & Sabor', '🍝']
        ];

        $stmt = $db->prepare("INSERT INTO cardapios (dia_semana, prato_principal, acompanhamento, sobremesa, bebida, calorias, tags, emoji) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($cardapios as $c) {
            $stmt->execute($c);
        }
    }

    // Cadastra as opcoes iniciais da votacao dos pratos.
    public static function seedVotacao(PDO $db): void {
        $db->exec("DELETE FROM votacao_pratos");
        $pratosVotacao = [
            ['Hambúrguer Gourmet Saudável', 'Pão integral, blend de frango e aveia, alface crocante, tomate e molho de iogurte.', 48, '🍔', 'Especial'],
            ['Tacos Mexicanos Nutritivos', 'Tortilhas de milho assadas, recheio colorido de vegetais, frango desfiado e guacamole.', 39, '🌮', 'Internacional'],
            ['Lasanha de Berinjela e Queijo Magro', 'Camadas suculentas de berinjela grelhada, molho caseiro de tomate e queijo derretido.', 31, '🧀', 'Conforto'],
            ['Crepioca Recheada com Ricota e Espinafre', 'Massa leve e crocante com gergelim dourado e recheio cremoso aromático.', 25, '🥞', 'Fitness']
        ];

        $stmt = $db->prepare("INSERT INTO votacao_pratos (nome, descricao, votos, emoji, categoria) VALUES (?, ?, ?, ?, ?)");
        foreach ($pratosVotacao as $pv) {
            $stmt->execute($pv);
        }
    }

    // Cadastra as dicas exibidas pelo mascote de nutricao.
    public static function seedDicas(PDO $db): void {
        $db->exec("DELETE FROM dicas_nutricao");
        $dicas = [
            ['Beba Água com Frequência', 'Manter-se hidratado melhora a concentração nas aulas e a energia para brincar no recreio!', '💧'],
            ['Prato Colorido = Prato Saudável', 'Quanto mais cores tiver o seu prato na hora da merenda, mais vitaminas você está ingerindo!', '🥗'],
            ['Não Pule o Desjejum', 'Tomar um café da manhã balanceado acorda seu cérebro e evita cansaço nas primeiras aulas.', '🍎'],
            ['Experimente Novos Sabores', 'Às vezes você descobre que adora um vegetal simplesmente comendo com o tempero certo!', '🥕'],
            ['Fruta é a Melhor Sobremesa', 'Frutas fornecem energia rápida e fibras naturais que ajudam no seu crescimento saudável!', '🍊']
        ];

        $stmt = $db->prepare("INSERT INTO dicas_nutricao (titulo, dica, icone) VALUES (?, ?, ?)");
        foreach ($dicas as $d) {
            $stmt->execute($d);
        }
    }
}
