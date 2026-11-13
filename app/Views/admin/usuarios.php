<?php
$tituloPagina = 'Usuários | Administração';
$rotulosPerfil = [
    'inspetora' => 'Inspetoria',
    'nutricionista' => 'Nutrição',
    'admin' => 'Administração'
];
require_once __DIR__ . '/../layouts/header.php';
?>

<section class="users-page">
    <header class="users-heading scroll-reveal">
        <div>
            <h1>Usuários e acessos</h1>
            <p>Cadastre e acompanhe as contas da equipe escolar.</p>
        </div>
        <a href="<?= url('admin') ?>" class="users-back-link">Voltar ao painel</a>
    </header>

    <div class="users-layout">
        <section class="users-panel scroll-reveal-left" aria-labelledby="new-user-title">
            <div class="users-panel-title">
                <h2 id="new-user-title">Adicionar usuário</h2>
                <p>Todos os campos com * são obrigatórios.</p>
            </div>

            <form method="POST" action="<?= url('admin/usuarios/salvar') ?>" class="users-form">
                <div>
                    <label for="nome">Nome completo *</label>
                    <input type="text" id="nome" name="nome" required autocomplete="name" placeholder="Ex.: Juliana Silva">
                </div>

                <div>
                    <label for="usuario">Nome de usuário *</label>
                    <input type="text" id="usuario" name="usuario" required autocomplete="off" autocapitalize="none" spellcheck="false" placeholder="Ex.: juliana">
                </div>

                <div>
                    <label for="senha">Senha provisória *</label>
                    <input type="password" id="senha" name="senha" required autocomplete="new-password" minlength="8" placeholder="Mínimo de 8 caracteres">
                </div>

                <div>
                    <label for="perfil">Perfil de acesso *</label>
                    <select id="perfil" name="perfil" required>
                        <option value="inspetora">Inspetoria</option>
                        <option value="nutricionista">Nutrição / Cozinha</option>
                        <option value="admin">Administração</option>
                    </select>
                </div>

                <div>
                    <label for="cargo">Cargo ou função</label>
                    <input type="text" id="cargo" name="cargo" placeholder="Ex.: Inspetora escolar">
                </div>

                <input type="hidden" name="avatar" value="👤">
                <button type="submit" class="users-form-submit">Cadastrar usuário</button>
            </form>
        </section>

        <section class="users-panel scroll-reveal-right" aria-labelledby="users-list-title">
            <div class="users-panel-title">
                <h2 id="users-list-title">Contas cadastradas <span class="user-row-login">(<?= count($usuarios) ?>)</span></h2>
                <p>O acesso exige usuário e senha válidos.</p>
            </div>

            <div class="users-list">
                <?php foreach ($usuarios as $u): ?>
                    <article class="user-row">
                        <div class="user-row-main">
                            <div>
                                <span class="user-row-name"><?= htmlspecialchars($u['nome']) ?></span>
                                <span class="user-row-login">@<?= htmlspecialchars($u['usuario']) ?></span>
                            </div>
                            <p class="user-row-detail">
                                <?= htmlspecialchars($u['cargo']) ?>
                                <span aria-hidden="true">·</span>
                                Último acesso: <?= $u['ultimo_login'] ? date('d/m/Y H:i', strtotime($u['ultimo_login'])) : 'Nunca' ?>
                            </p>
                        </div>

                        <div class="user-badges">
                            <span class="user-badge"><?= htmlspecialchars($rotulosPerfil[$u['perfil']] ?? $u['perfil']) ?></span>
                            <span class="user-badge <?= empty($u['ativo']) ? 'inactive' : '' ?>"><?= empty($u['ativo']) ? 'Inativo' : 'Ativo' ?></span>

                            <?php if ($u['usuario'] !== 'admin'): ?>
                                <form method="POST" action="<?= url('admin/usuarios/excluir') ?>" onsubmit="return confirm('Deseja excluir esta conta?')">
                                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                    <button type="submit" class="user-delete" title="Excluir usuário" aria-label="Excluir <?= htmlspecialchars($u['nome']) ?>">×</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
