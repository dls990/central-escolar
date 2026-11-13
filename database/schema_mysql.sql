-- ATENCAO: este script remove as tabelas atuais antes de recria-las.
-- Use-o apenas quando os dados existentes puderem ser apagados.
CREATE DATABASE IF NOT EXISTS `central_escolar` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `central_escolar`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `refeicoes`;
DROP TABLE IF EXISTS `frequencias`;
DROP TABLE IF EXISTS `pedidos_cozinha`;
DROP TABLE IF EXISTS `historico_frequencia_diaria`;
DROP TABLE IF EXISTS `cardapios`;
DROP TABLE IF EXISTS `votacao_pratos`;
DROP TABLE IF EXISTS `dicas_nutricao`;
DROP TABLE IF EXISTS `alunos`;
DROP TABLE IF EXISTS `usuarios`;
SET FOREIGN_KEY_CHECKS = 1;

-- Contas do sistema, dados de acesso e perfis de permissao.
CREATE TABLE `usuarios` (
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

-- Estudantes cadastrados, suas turmas e restricoes alimentares.
CREATE TABLE `alunos` (
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

-- Situacao diaria individual de cada estudante.
CREATE TABLE `frequencias` (
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

-- Quantidades de refeicoes e observacoes trocadas com a cozinha.
CREATE TABLE `pedidos_cozinha` (
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

-- Totais arquivados para graficos e consultas de dias anteriores.
CREATE TABLE `historico_frequencia_diaria` (
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

-- Pratos planejados para cada dia letivo da semana.
CREATE TABLE `cardapios` (
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

-- Consumo e avaliacao de merenda associados a cada estudante.
CREATE TABLE `refeicoes` (
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

-- Pratos da votacao e quantidade de votos recebidos.
CREATE TABLE `votacao_pratos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(150) NOT NULL,
    `descricao` TEXT NOT NULL,
    `votos` INT DEFAULT 0,
    `emoji` VARCHAR(10) NOT NULL,
    `categoria` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Conteudo educativo mostrado pelo mascote de nutricao.
CREATE TABLE `dicas_nutricao` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titulo` VARCHAR(150) NOT NULL,
    `dica` TEXT NOT NULL,
    `icone` VARCHAR(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `usuarios` (`id`, `nome`, `usuario`, `senha`, `perfil`, `cargo`, `avatar`, `ativo`) VALUES
(1, 'Vivi', 'vivi', '$2y$10$TKXpka3wUO.YPChrMgVCreOJ9epmQ.FC6FNqN9prIk1EJxHDWnfEy', 'inspetora', 'Inspetora Escolar (Fund 1 & 2)', '👩‍🏫', 1),
(2, 'Val', 'val', '$2y$10$TKXpka3wUO.YPChrMgVCreOJ9epmQ.FC6FNqN9prIk1EJxHDWnfEy', 'inspetora', 'Inspetora Escolar (Ensino Médio)', '👩‍💼', 1),
(3, 'Cris', 'cris', '$2y$10$TKXpka3wUO.YPChrMgVCreOJ9epmQ.FC6FNqN9prIk1EJxHDWnfEy', 'inspetora', 'Inspetora Escolar (Geral)', '👩‍🏫', 1),
(4, 'Dra. Camila', 'nutri', '$2y$10$TKXpka3wUO.YPChrMgVCreOJ9epmQ.FC6FNqN9prIk1EJxHDWnfEy', 'nutricionista', 'Nutricionista Chefe & Cozinha Escolar', '🥗', 1),
(5, 'Diretoria & ADM', 'admin', '$2y$10$odgap6N9AA8EB615iHO8c.byo9Yq5UzmsbfDxNpcJScJZSb6ha3Bq', 'admin', 'Administrador Geral do Sistema', '👑', 1);

INSERT INTO `alunos` (`id`, `nome`, `matricula`, `turma`, `avatar`, `restricoes`, `telefone_responsavel`, `pontos_nutri`) VALUES
(1, 'Arthur Ferreira', '20260101', '1º Ano EF', '👦', 'Nenhuma', '(11) 98111-0101', 90),
(2, 'Helena Castro', '20260102', '1º Ano EF', '👧', 'Sem Lactose', '(11) 98111-0102', 120),
(3, 'Bernardo Dias', '20260103', '1º Ano EF', '🧒', 'Nenhuma', '(11) 98111-0103', 85),
(4, 'Alice Santos', '20260201', '2º Ano EF', '👧', 'Sem Glúten', '(11) 98111-0201', 110),
(5, 'Theo Carvalho', '20260202', '2º Ano EF', '👦', 'Nenhuma', '(11) 98111-0202', 95),
(6, 'Manuela Souza', '20260203', '2º Ano EF', '👩', 'Nenhuma', '(11) 98111-0203', 100),
(7, 'Davi Lucca', '20260301', '3º Ano EF', '👦', 'Nenhuma', '(11) 98111-0301', 105),
(8, 'Valentina Lima', '20260302', '3º Ano EF', '👧', 'Vegetariana', '(11) 98111-0302', 140),
(9, 'Gabriel Alves', '20260303', '3º Ano EF', '🧒', 'Nenhuma', '(11) 98111-0303', 80),
(10, 'Lorena Martins', '20260401', '4º Ano EF', '👧', 'Alérgica a Amendoim', '(11) 98111-0401', 130),
(11, 'Heitor Barbosa', '20260402', '4º Ano EF', '👦', 'Nenhuma', '(11) 98111-0402', 115),
(12, 'Sophia Ribeiro', '20260403', '4º Ano EF', '👩', 'Sem Lactose', '(11) 98111-0403', 125),
(13, 'Samuel Cardoso', '20260501', '5º Ano EF', '👦', 'Nenhuma', '(11) 98111-0501', 110),
(14, 'Laura Moreira', '20260502', '5º Ano EF', '👧', 'Nenhuma', '(11) 98111-0502', 105),
(15, 'Felipe Araujo', '20260503', '5º Ano EF', '🧒', 'Vegetariano', '(11) 98111-0503', 95),
(16, 'Lucas Oliveira', '20260601', '6º Ano EF', '👦', 'Nenhuma', '(11) 98111-0601', 135),
(17, 'Sofia Mendes', '20260602', '6º Ano EF', '👧', 'Sem Lactose', '(11) 98111-0602', 150),
(18, 'Mateus Silva', '20260603', '6º Ano EF', '🧒', 'Vegetariano', '(11) 98111-0603', 95),
(19, 'Beatriz Costa', '20260701', '7º Ano EF', '👩', 'Sem Glúten', '(11) 98111-0701', 180),
(20, 'Enzo Gabriel', '20260702', '7º Ano EF', '🧑', 'Nenhuma', '(11) 98111-0702', 110),
(21, 'Camila Rocha', '20260703', '7º Ano EF', '👧', 'Alérgica a Frutos do Mar', '(11) 98111-0703', 140),
(22, 'Pedro Henrique', '20260801', '8º Ano EF', '🧒', 'Nenhuma', '(11) 98111-0801', 85),
(23, 'Mariana Fernandes', '20260802', '8º Ano EF', '👧', 'Vegetariana', '(11) 98111-0802', 130),
(24, 'Guilherme Ribeiro', '20260803', '8º Ano EF', '🧑', 'Nenhuma', '(11) 98111-0803', 90),
(25, 'Isabela Souza', '20260901', '9º Ano EF', '👩', 'Sem Glúten', '(11) 98111-0901', 175),
(26, 'Gustavo Nogueira', '20260902', '9º Ano EF', '👦', 'Nenhuma', '(11) 98111-0902', 120),
(27, 'Leticia Campos', '20260903', '9º Ano EF', '👧', 'Sem Lactose', '(11) 98111-0903', 145),
(28, 'Thiago Mendes', '20261001', '1º Ano EM', '🧑', 'Nenhuma', '(11) 98111-1001', 160),
(29, 'Julia Azevedo', '20261002', '1º Ano EM', '👩', 'Vegetariana', '(11) 98111-1002', 170),
(30, 'Nicolas Ramos', '20261003', '1º Ano EM', '👦', 'Nenhuma', '(11) 98111-1003', 115),
(31, 'Rafaela Pires', '20261101', '2º Ano EM', '👩', 'Sem Lactose', '(11) 98111-1101', 190),
(32, 'Leonardo Freitas', '20261102', '2º Ano EM', '🧑', 'Nenhuma', '(11) 98111-1102', 130),
(33, 'Bruna Guimaraes', '20261103', '2º Ano EM', '👧', 'Nenhuma', '(11) 98111-1103', 140),
(34, 'Caio Vicente', '20261201', '3º Ano EM', '👦', 'Nenhuma', '(11) 98111-1201', 200),
(35, 'Yasmin Toledo', '20261202', '3º Ano EM', '👩', 'Sem Glúten', '(11) 98111-1202', 185),
(36, 'Rodrigo Peixoto', '20261203', '3º Ano EM', '🧑', 'Vegetariano', '(11) 98111-1203', 165);

INSERT INTO `cardapios` (`id`, `dia_semana`, `prato_principal`, `acompanhamento`, `sobremesa`, `bebida`, `calorias`, `tags`, `emoji`) VALUES
(1, 'Segunda-feira', 'Frango Grelhado ao Molho de Laranja', 'Arroz Integral, Feijão Carioca e Salada Colorida', 'Melancia Fresquinha Fatiada', 'Suco Natural de Maracujá', 480, 'Rico em Vitamina C,Rico em Proteína', '🍗'),
(2, 'Terça-feira', 'Iscas de Carne com Legumes Salteados', 'Arroz Branco, Feijão Preto e Vinagrete', 'Banana Prata com Canela', 'Suco de Abacaxi com Hortelã', 520, 'Energia Total,Rico em Ferro', '🥩'),
(3, 'Quarta-feira', 'Filé de Peixe Empanado com Aveia', 'Purê de Mandioquinha e Brócolis ao Vapor', 'Maçã Doce Higienizada', 'Suco de Laranja Natural', 460, 'Ômega 3,Amigo do Cérebro', '🐟'),
(4, 'Quinta-feira', 'Estrogonofe Nutritivo de Frango & Abóbora', 'Arroz de Couve-Flor & Batata Rústica Assada', 'Salada de Frutas Tropicais', 'Suco de Melão com Limão', 490, 'Cremoso & Saudável,Super Vitaminas', '🍲'),
(5, 'Sexta-feira', 'Macarronada Colorida com Molho de Tomate Fresco e Almôndegas', 'Mix de Folhas Verdes e Cenoura Ralada', 'Picolé Natural de Fruta', 'Limonada Suíça Refrescante', 510, 'Prato da Alegria,Fibras & Sabor', '🍝');

INSERT INTO `votacao_pratos` (`id`, `nome`, `descricao`, `votos`, `emoji`, `categoria`) VALUES
(1, 'Hambúrguer Gourmet Saudável', 'Pão integral, blend de frango e aveia, alface crocante, tomate e molho de iogurte.', 48, '🍔', 'Especial'),
(2, 'Tacos Mexicanos Nutritivos', 'Tortilhas de milho assadas, recheio colorido de vegetais, frango desfiado e guacamole.', 39, '🌮', 'Internacional'),
(3, 'Lasanha de Berinjela e Queijo Magro', 'Camadas suculentas de berinjela grelhada, molho caseiro de tomate e queijo derretido.', 31, '🧀', 'Conforto'),
(4, 'Crepioca Recheada com Ricota e Espinafre', 'Massa leve e crocante com gergelim dourado e recheio cremoso aromático.', 25, '🥞', 'Fitness');

INSERT INTO `dicas_nutricao` (`id`, `titulo`, `dica`, `icone`) VALUES
(1, 'Beba Água com Frequência', 'Manter-se hidratado melhora a concentração nas aulas e a energia para brincar no recreio!', '💧'),
(2, 'Prato Colorido = Prato Saudável', 'Quanto mais cores tiver o seu prato na hora da merenda, mais vitaminas você está ingerindo!', '🥗'),
(3, 'Não Pule o Desjejum', 'Tomar um café da manhã balanceado acorda seu cérebro e evita cansaço nas primeiras aulas.', '🍎'),
(4, 'Experimente Novos Sabores', 'Às vezes você descobre que adora um vegetal simplesmente comendo com o tempero certo!', '🥕'),
(5, 'Fruta é a Melhor Sobremesa', 'Frutas fornecem energia rápida e fibras naturais que ajudam no seu crescimento saudável!', '🍊');
