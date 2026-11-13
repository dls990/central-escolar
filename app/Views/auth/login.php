<?php
// Apresenta o formulario de login e mensagens de validacao da autenticacao.


$tituloPagina = 'Entrar | VivalCris';
require_once __DIR__ . '/../layouts/header.php';
?>

<section class="auth-page">
    <div class="auth-intro scroll-reveal-left">
        <p class="auth-kicker">VivalCris • Central de Frequência e Alimentação Escolar</p>
        <h1>Acesso da equipe</h1>
        <p class="auth-description">Entre para registrar a frequência, acompanhar o preparo das refeições ou administrar o sistema.</p>

        <div class="auth-roles" aria-label="Perfis do sistema">
            <div class="auth-role">
                <span class="auth-role-mark">01</span>
                <div><strong>Inspetoria</strong><span>Vivi, Val e Cris</span></div>
                <small>Frequência e envio do censo</small>
            </div>
            <div class="auth-role">
                <span class="auth-role-mark">02</span>
                <div><strong>Nutrição</strong><span>Dra. Camila</span></div>
                <small>Recebimento e preparo das refeições</small>
            </div>
            <div class="auth-role">
                <span class="auth-role-mark">03</span>
                <div><strong>Administração</strong><span>Diretoria</span></div>
                <small>Monitoramento e gestão de usuários</small>
            </div>
        </div>
    </div>

    <div class="auth-panel scroll-reveal-right">
        <div class="auth-panel-heading">
            <span class="auth-status-dot" aria-hidden="true"></span>
            <div>
                <h2>Entrar no sistema</h2>
                <p>Use seu usuário e sua senha.</p>
            </div>
        </div>

        <form method="POST" action="<?= url('login') ?>" class="auth-form">
            <div class="auth-field">
                <label for="campoUsuario">Usuário</label>
                <input type="text" id="campoUsuario" name="usuario" required autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="Digite seu usuário">
            </div>

            <div class="auth-field">
                <label for="campoSenha">Senha</label>
                <input type="password" id="campoSenha" name="senha" required autocomplete="current-password" placeholder="Digite sua senha">
            </div>

            <button type="submit" class="auth-submit">Entrar</button>
        </form>

        <p class="auth-note">O acesso depende da validação das suas credenciais e do perfil associado à sua conta.</p>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
