# 🦉 VivalCris - Central de Frequência e Alimentação Escolar
### 🎓 Projeto para Feira Tecnológica Escolar (Apresentação aos Pais & Avaliadores)

Sistema web completo desenvolvido em **PHP Puro com Arquitetura MVC (Model-View-Controller)**, integrando a chamada diária das **Inspetoras** com o painel de preparo da **Cozinha (Nutricionista)** e supervisão geral do **Administrador**.

---

## 🎯 Tema & Proposta do Projeto
* **Tema Oficial:** *Central de Frequência e Alimentação Escolar*
* **Problema Real Resolvido:** O desperdício de merenda e atrasos na cozinha ocorrem quando a quantidade de alunos presentes não é comunicada a tempo.
* **Solução:** As inspetoras realizam a chamada nas salas de aula e, com um clique, o sistema compila o número exato de presentes, alunos ausentes e restrições alimentares (sem lactose, sem glúten, vegetarianismo) e transmite em tempo real para o painel da nutricionista na cozinha, com salvamento diário do gráfico de pizza e histórico auditável.

---

## 👥 Atores do Sistema & Perfis de Usuários (Login MVC)

| Perfil | Usuários | Função Principal no Sistema | Credenciais (Login / Senha) |
| :--- | :--- | :--- | :--- |
| **👩‍🏫 Inspetoras** | **Vivi, Val e Cris** | Realizam a chamada nas salas de aula, visualizam o gráfico de pizza diário (com auto-save) e enviam o censo de alimentação para a cozinha. | `vivi` / `123456`<br>`val` / `123456`<br>`cris` / `123456` |
| **🥗 Nutricionista** | **Dra. Camila** | Recebe os dados de frequência diretamente pelo site, visualiza a quantidade de refeições a cozinhar, pratos poupados, dietas especiais e atualiza o status de preparo. | `nutri` / `123456` |
| **👑 Administrador** | **Diretoria & ADM** | Monitora os dois lados simultaneamente (Inspetoria + Cozinha), gerencia as contas de usuários e emite relatórios globais. | `admin` / `admin123` |

> 💡 **Acesso seguro:** cada perfil entra pela tela de login com seu próprio usuário e senha. Para demonstrar outro perfil, encerre a sessão e autentique-se novamente.

---

## 🏫 Estrutura das Turmas (Regra das Salas Únicas)
Conforme as diretrizes da escola, **todas as salas são únicas** (sem divisão em turmas A, B ou C):
1. **Ensino Fundamental 1:** `1º Ano EF`, `2º Ano EF`, `3º Ano EF`, `4º Ano EF`, `5º Ano EF`
2. **Ensino Fundamental 2:** `6º Ano EF`, `7º Ano EF`, `8º Ano EF`, `9º Ano EF`
3. **Ensino Médio:** `1º Ano EM`, `2º Ano EM`, `3º Ano EM`
* **Total:** **12 Salas Únicas**

---

## ✨ Principais Funcionalidades

### 1. 📋 Chamada Interativa & Comunicação Direta com a Cozinha
* Marcação de presença com um clique: `Presente`, `Ausente` e `Justificado`.
* **Botão "🚀 Enviar para a Cozinha":** Notifica a nutricionista com a quantidade de pratos a preparar, porções poupadas e detalhamento por segmento.
* Efeitos sonoros dinâmicos (Web Audio API) e chuva de confetes quando a turma atinge 100% de presença.

### 2. 🥧 Gráfico de Pizza Diário Automático & Histórico
* **Chart.js Interativo:** Gráfico em formato de pizza/donut que exibe `Presentes`, `Ausentes` e `Justificados`.
* **Salvamento Automático:** A cada clique ou envio, o snapshot do dia é salvo automaticamente no SQLite com a assinatura da inspetora responsável.
* **Página de Histórico (`/frequencia/historico`):** Permite navegar por dias anteriores, inspecionar o gráfico de qualquer data e conferir a resposta da cozinha.

### 3. 🍲 Painel da Nutricionista (Cozinha)
* **Recepção em Tempo Real:** Recebe o alerta assim que a inspetora envia a contagem da chamada.
* **Previsão de Porções:** Total exato de pratos a preparar para o dia.
* **Combate ao Desperdício:** Contador de pratos poupados com base nas ausências.
* **Alerta de Dietas Especiais:** Lista nominal dos estudantes presentes com intolerância a lactose, doença celíaca, alergia a amendoim ou vegetarianismo para preparo em bancada separada.
* **Controle de Status:** Altera o status do fogão: `[Recebido]` ➔ `[Em Preparo / Cozinhando]` ➔ `[Pronto para Servir]`.

### 4. 👑 Painel do Administrador Geral
* Monitoramento dos dois lados: verifica se todas as salas já tiveram chamada feita e se o refeitório já iniciou o preparo.
* Módulo CRUD de Usuários (`/admin/usuarios`): cadastro e gerenciamento das inspetoras, nutricionista e administradores.

### 5. 🎨 Design Moderno com Animações de Entrada e Rolagem
* **Animações de Entrada:** Fade-in-up, Scale-up e Glassmorphism com micro-interações.
* **Animações de Rolagem:** Elementos surgem suavemente conforme o visitante desce a página (`IntersectionObserver`).
* **Mascote Interativo:** "Chef Corujito" interativo com dicas de nutrição e sons.

---

## 🏛️ Arquitetura MVC (Model-View-Controller)

```
central-escolar/
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php          # Login e logout com validação de credenciais
│   │   ├── FrequenciaController.php    # Chamada, gráfico de pizza, auto-save e envio para a cozinha
│   │   ├── CozinhaController.php       # Painel da Nutricionista e controle de preparo
│   │   ├── AdminController.php         # Monitoramento dos dois lados e usuários
│   │   ├── HomeController.php          # Dashboard principal da feira tecnológica
│   │   ├── AlimentacaoController.php   # Cardápio semanal, fila do refeitório e votação
│   │   ├── AlunosController.php        # Gestão dos estudantes das 12 salas únicas
│   │   └── RelatoriosController.php    # Relatórios gerenciais e modo impressão
│   ├── Models/
│   │   ├── Usuario.php                 # Autenticação, perfis e gerenciamento de contas
│   │   ├── Frequencia.php              # Registro individual de faltas e presenças
│   │   ├── HistoricoFrequencia.php     # Persistência diária do gráfico de pizza
│   │   ├── PedidoCozinha.php           # Comunicação e cálculo de refeições p/ a cozinha
│   │   ├── Aluno.php                   # Dados escolares das 12 salas e restrições
│   │   ├── Cardapio.php                # Cardápios semanais e nutrientes
│   │   ├── Refeicao.php                # Controle de pratos servidos e avaliações
│   │   └── Database.php                # SQLite PDO com auto-migração e seed completo
│   └── Views/
│       ├── auth/login.php              # Tela de login estilizada com atalhos de demonstração
│       ├── frequencia/index.php        # Chamada com gráfico de pizza interativo
│       ├── frequencia/historico.php    # Histórico arquivado de gráficos de pizza
│       ├── cozinha/index.php           # Painel da nutricionista e controle do fogão
│       ├── admin/index.php             # Painel unificado do administrador
│       ├── admin/usuarios.php          # Gestão de usuários do sistema
│       ├── home/index.php              # Página inicial da feira
│       ├── layouts/                    # Cabeçalho e Rodapé com perfil do usuário
│       └── ...
├── core/                               # Mini-Framework MVC PHP
│   ├── Router.php                      # Roteador de URLs
│   ├── Controller.php                  # Base com view, json, redirect e auth
│   ├── Model.php                       # Base de conexão PDO
│   └── helpers.php                     # Funções auxiliares (url, asset, tem_perfil)
├── database/
│   ├── escola.sqlite                   # Banco SQLite portátil pré-populado
│   └── schema.sql                      # DDL do banco de dados
├── public/                             # Raiz pública do servidor web
│   ├── index.php                       # Front controller
│   ├── css/style.css                   # Animações de entrada e rolagem
│   └── js/app.js                       # AJAX, Chart.js e IntersectionObserver
└── iniciar.bat                         # Executável para iniciar com 1 duplo-clique
```

---

## 🚀 Como Executar o Projeto na Apresentação

### Opção 1: Pelo executável (Mais Fácil)
Dê um duplo clique no arquivo:
```
iniciar.bat
```
O navegador abrirá automaticamente em `http://localhost:8000`!

### Opção 2: Pelo Terminal / Linha de Comando
No diretório do projeto, execute:
```bash
php -S localhost:8000 -t public
```
Acesse no navegador: **`http://localhost:8000`**

---

## 🏆 Roteiro Recomendado para Apresentar aos Pais na Feira
1. **Entrada:** Abra o site e mostre a tela inicial com o mascote e a proposta pedagógica.
2. **Login da Inspetoria:** Entre com as credenciais da Vivi na tela de login.
3. **Chamada das Salas:** Acesse a tela de Chamada, filtre por uma sala (ex: `1º Ano EF` ou `9º Ano EF`), altere a presença de alguns alunos e mostre o **Gráfico de Pizza** atualizando na hora!
4. **Envio para a Cozinha:** Clique no botão **"🚀 Enviar Censo para a Cozinha"**.
5. **Acesso da Nutricionista:** Encerre a sessão, entre como **Dra. Camila (Nutricionista)** e abra o **Painel da Cozinha**. Mostre que o número exato de pratos e a lista de restrições alimentares já estão na tela!
6. **Acesso do ADM:** Encerre a sessão, entre como **Administrador** e mostre a visão unificada acompanhando tanto a chamada quanto a cozinha, além do controle de usuários.
