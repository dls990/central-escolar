<?php
// Apresenta os pratos da semana e, para a nutricionista, formularios de edicao e copia.


$tituloPagina = 'Cardápio Semanal Escolar | VivalCris';
require_once __DIR__ . '/../layouts/header.php';
$cardapiosPorDia = array_column($cardapios, null, 'dia_semana');
$origemPadrao = $cardapioEdicao['id'] ?? ($cardapios[0]['id'] ?? 0);
$diaOrigemPadrao = $cardapioEdicao['dia_semana'] ?? $diaSelecionado;
$diaDestinoPadrao = '';
foreach ($diasSemana as $dia):
    if ($dia !== $diaOrigemPadrao) {
        $diaDestinoPadrao = $dia;
        break;
    }
endforeach;
?>


<div class="menu-page-heading">
    <div>
        <h1>Cardápio semanal</h1>
        <p>Planejamento de refeições e informações nutricionais da escola.</p>
    </div>

    <a href="<?= url('alimentacao/fila') ?>" class="menu-nav-link">Fila do refeitório</a>
</div>

<?php if ($podeGerenciar): ?>
    <?php
    $valoresEdicao = $cardapioEdicao ?? [
        'id' => '',
        'dia_semana' => '',
        'prato_principal' => '',
        'acompanhamento' => '',
        'sobremesa' => '',
        'bebida' => '',
        'calorias' => 450,
        'tags' => '',
        'emoji' => '🍽️'
    ];
    ?>
    <section class="menu-manager scroll-reveal" aria-labelledby="menu-manager-title">
        <div class="menu-manager-heading">
            <div>
                <h2 id="menu-manager-title">Gerenciar cardápios</h2>
                <p>Escolha um dia na lista para adicionar ou editar o cardápio correspondente.</p>
            </div>
        </div>

        <div class="menu-manager-grid">
            <nav class="menu-calendar" aria-label="Dias da semana do cardápio">
                <h3>Semana</h3>
                <?php foreach ($diasSemana as $dia): ?>
                    <?php $menuDoDia = $cardapiosPorDia[$dia] ?? null; ?>
                    <a href="<?= url('alimentacao/cardapio?dia=' . rawurlencode($dia)) ?>"
                       class="menu-calendar-day <?= $dia === $diaSelecionado ? 'is-selected' : '' ?>">
                        <span><?= htmlspecialchars($dia) ?></span>
                        <small><?= $menuDoDia ? 'Editar cardápio' : 'Adicionar cardápio' ?></small>
                    </a>
                <?php endforeach; ?>
            </nav>

            <form method="POST" action="<?= url('alimentacao/cardapio/salvar') ?>" class="menu-editor">
                <h3><?= $cardapioEdicao ? 'Editar cardápio' : 'Adicionar cardápio' ?> · <?= htmlspecialchars($diaSelecionado) ?></h3>
                <input type="hidden" name="id" value="<?= (int)$valoresEdicao['id'] ?>">

                <div class="menu-form-grid">
                    <div class="menu-field">
                        <label for="menu-dia">Dia da semana</label>
                        <select id="menu-dia" name="dia_semana" required>
                            <?php foreach ($diasSemana as $dia): ?>
                                <option value="<?= htmlspecialchars($dia) ?>" <?= $diaSelecionado === $dia ? 'selected' : '' ?>><?= htmlspecialchars($dia) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="menu-field">
                        <label for="menu-calorias">Calorias (kcal)</label>
                        <input id="menu-calorias" type="number" name="calorias" min="0" max="2000" value="<?= (int)$valoresEdicao['calorias'] ?>" required>
                    </div>
                    <div class="menu-field menu-field-wide">
                        <label for="menu-prato">Prato principal</label>
                        <input id="menu-prato" type="text" name="prato_principal" maxlength="200" value="<?= htmlspecialchars($valoresEdicao['prato_principal']) ?>" required>
                    </div>
                    <div class="menu-field menu-field-wide">
                        <label for="menu-acompanhamento">Acompanhamentos</label>
                        <input id="menu-acompanhamento" type="text" name="acompanhamento" maxlength="255" value="<?= htmlspecialchars($valoresEdicao['acompanhamento']) ?>" required>
                    </div>
                    <div class="menu-field">
                        <label for="menu-sobremesa">Sobremesa</label>
                        <input id="menu-sobremesa" type="text" name="sobremesa" maxlength="150" value="<?= htmlspecialchars($valoresEdicao['sobremesa']) ?>" required>
                    </div>
                    <div class="menu-field">
                        <label for="menu-bebida">Bebida</label>
                        <input id="menu-bebida" type="text" name="bebida" maxlength="100" value="<?= htmlspecialchars($valoresEdicao['bebida']) ?>" required>
                    </div>
                    <div class="menu-field">
                        <label for="menu-tags">Tags nutricionais</label>
                        <input id="menu-tags" type="text" name="tags" maxlength="255" value="<?= htmlspecialchars($valoresEdicao['tags']) ?>" placeholder="Ex.: Rico em ferro, Proteína" required>
                    </div>
                    <div class="menu-field">
                        <label for="menu-emoji">Símbolo (opcional)</label>
                        <input id="menu-emoji" type="text" name="emoji" maxlength="10" value="<?= htmlspecialchars($valoresEdicao['emoji']) ?>">
                    </div>
                </div>

                <button type="submit" class="menu-submit"><?= $cardapioEdicao ? 'Salvar alterações' : 'Adicionar ao cardápio' ?></button>
            </form>

            <form method="POST" action="<?= url('alimentacao/cardapio/copiar') ?>" class="menu-copy menu-copy-wide" onsubmit="return confirm('O cardápio do dia de destino será substituído. Deseja continuar?')">
                <h3>Copiar de outro dia</h3>
                <p>Os campos do dia de origem serão copiados para o dia de destino.</p>

                <div class="menu-field">
                    <label for="menu-origem">Copiar conteúdo de</label>
                    <select id="menu-origem" name="origem_id" required>
                        <?php foreach ($cardapios as $cardapio): ?>
                            <option value="<?= (int)$cardapio['id'] ?>" <?= (int)$cardapio['id'] === (int)$origemPadrao ? 'selected' : '' ?>><?= htmlspecialchars($cardapio['dia_semana']) ?> · <?= htmlspecialchars($cardapio['prato_principal']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="menu-field">
                    <label for="menu-destino">Substituir cardápio de</label>
                    <select id="menu-destino" name="dia_destino" required>
                        <?php foreach ($diasSemana as $dia): ?>
                            <option value="<?= htmlspecialchars($dia) ?>" <?= $dia === $diaDestinoPadrao ? 'selected' : '' ?>><?= htmlspecialchars($dia) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="menu-copy-submit">Copiar cardápio</button>
            </form>
        </div>
    </section>
<?php else: ?>
    <p class="menu-manager-notice">
        Para adicionar ou editar cardápios, entre com um perfil de nutricionista ou administrador.
        <a href="<?= url('login') ?>">Acessar o sistema</a>
    </p>
<?php endif; ?>


<div class="today-menu-banner">
    <div>
        <span class="menu-section-label">Cardápio de hoje · <?= htmlspecialchars($cardapioHoje['dia_semana']) ?></span>
        <h2><?= htmlspecialchars($cardapioHoje['prato_principal']) ?></h2>
        <p><?= htmlspecialchars($cardapioHoje['acompanhamento']) ?></p>
        <div class="today-menu-details">
            <span><strong>Sobremesa</strong><?= htmlspecialchars($cardapioHoje['sobremesa']) ?></span>
            <span><strong>Bebida</strong><?= htmlspecialchars($cardapioHoje['bebida']) ?></span>
            <span><strong>Energia</strong><?= (int)$cardapioHoje['calorias'] ?> kcal</span>
        </div>
    </div>
</div>


<h3 class="menu-week-heading">Programação da semana</h3>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
    <?php foreach ($cardapios as $c): ?>
        <?php
            $isHoje = ($c['dia_semana'] === $cardapioHoje['dia_semana']);
            $tags = explode(',', $c['tags']);
        ?>
        <div class="menu-day-card <?= $isHoje ? 'is-today' : '' ?>">

            <div>

                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2">
                        <div>
                            <h4 class="font-brand font-bold text-lg text-slate-800"><?= htmlspecialchars($c['dia_semana']) ?></h4>
                            <?php if ($isHoje): ?>
                                <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Cardápio de hoje</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">
                        <?= $c['calorias'] ?> kcal
                    </span>
                </div>


                <div class="mb-3">
                    <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">Prato Principal</span>
                    <p class="font-brand font-bold text-base text-slate-800 leading-snug">
                        <?= htmlspecialchars($c['prato_principal']) ?>
                    </p>
                </div>


                <div class="mb-4">
                    <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">Acompanhamentos</span>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        <?= htmlspecialchars($c['acompanhamento']) ?>
                    </p>
                </div>


                <div class="menu-card-details">
                    <p><strong>Sobremesa</strong><?= htmlspecialchars($c['sobremesa']) ?></p>
                    <p><strong>Bebida</strong><?= htmlspecialchars($c['bebida']) ?></p>
                </div>
            </div>


            <div class="pt-3 border-t border-slate-100 flex flex-wrap gap-1.5">
                <?php foreach ($tags as $tag): ?>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <?= htmlspecialchars(trim($tag)) ?>
                    </span>
                <?php endforeach; ?>
            </div>

            <?php if ($podeGerenciar): ?>
                <div class="menu-card-actions">
                    <a href="<?= url('alimentacao/cardapio?dia=' . rawurlencode($c['dia_semana'])) ?>">Editar este dia</a>
                </div>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>
</div>


<div class="menu-guidance">
    <div>
        <h4>Restrições e alergias</h4>
        <p>Consulte os cadastros dos alunos antes de preparar ou adaptar as refeições.</p>
    </div>
    <?php if (tem_perfil('admin')): ?>
        <a href="<?= url('alunos') ?>">Consultar alunos</a>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
