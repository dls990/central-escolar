-- Define as tabelas e os dados iniciais do sistema.
PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    usuario TEXT NOT NULL UNIQUE,
    senha TEXT NOT NULL,
    perfil TEXT NOT NULL CHECK (perfil IN ('inspetora', 'nutricionista', 'admin')),
    cargo TEXT NOT NULL,
    avatar TEXT NOT NULL DEFAULT '👤',
    ativo INTEGER NOT NULL DEFAULT 1,
    ultimo_login DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS alunos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    matricula TEXT NOT NULL UNIQUE,
    turma TEXT NOT NULL CHECK (turma IN (
        '1º Ano EF', '2º Ano EF', '3º Ano EF', '4º Ano EF', '5º Ano EF',
        '6º Ano EF', '7º Ano EF', '8º Ano EF', '9º Ano EF',
        '1º Ano EM', '2º Ano EM', '3º Ano EM'
    )),
    avatar TEXT NOT NULL DEFAULT '👦',
    restricoes TEXT DEFAULT 'Nenhuma',
    telefone_responsavel TEXT,
    pontos_nutri INTEGER NOT NULL DEFAULT 50,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS frequencias (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    aluno_id INTEGER NOT NULL,
    data DATE NOT NULL,
    status TEXT NOT NULL DEFAULT 'presente' CHECK (status IN ('presente', 'ausente', 'justificado')),
    horario TEXT,
    observacao TEXT,
    inspetora_nome TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    UNIQUE (aluno_id, data)
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
    status TEXT NOT NULL DEFAULT 'enviado' CHECK (status IN ('pendente', 'enviado', 'em_preparo', 'pronto')),
    observacao_inspetora TEXT,
    observacao_cozinha TEXT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
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
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cardapios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    dia_semana TEXT NOT NULL,
    prato_principal TEXT NOT NULL,
    acompanhamento TEXT NOT NULL,
    sobremesa TEXT NOT NULL,
    bebida TEXT NOT NULL,
    calorias INTEGER NOT NULL DEFAULT 450,
    tags TEXT NOT NULL,
    emoji TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS refeicoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    aluno_id INTEGER NOT NULL,
    cardapio_id INTEGER,
    data DATE NOT NULL,
    consumiu INTEGER NOT NULL DEFAULT 1 CHECK (consumiu IN (0, 1)),
    avaliacao INTEGER NOT NULL DEFAULT 5 CHECK (avaliacao BETWEEN 1 AND 5),
    comentario TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (cardapio_id) REFERENCES cardapios(id) ON DELETE SET NULL,
    UNIQUE (aluno_id, data)
);

CREATE TABLE IF NOT EXISTS votacao_pratos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    descricao TEXT NOT NULL,
    votos INTEGER NOT NULL DEFAULT 0,
    emoji TEXT NOT NULL,
    categoria TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS dicas_nutricao (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo TEXT NOT NULL,
    dica TEXT NOT NULL,
    icone TEXT NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_frequencias_data ON frequencias(data);
CREATE INDEX IF NOT EXISTS idx_frequencias_inspetora ON frequencias(inspetora_nome);
CREATE INDEX IF NOT EXISTS idx_pedidos_status ON pedidos_cozinha(status);
CREATE INDEX IF NOT EXISTS idx_refeicoes_data ON refeicoes(data);
