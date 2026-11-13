<?php
$tituloPagina = 'Relatórios Escolares | VivalCris';
require_once __DIR__ . '/../layouts/header.php';
?>

<section class="report-page">
    <header class="report-heading">
        <div>
            <p class="report-eyebrow">VivalCris · <?= date('d/m/Y', strtotime($hoje)) ?></p>
            <h1>Relatório escolar</h1>
            <p class="report-description">Resumo diário de frequência, refeições e cuidados alimentares.</p>
        </div>
        <button type="button" class="report-print no-print" onclick="window.print()">
            <span aria-hidden="true">▣</span> Imprimir relatório
        </button>
    </header>

    <div class="report-kpis" aria-label="Indicadores do dia">
        <article class="report-kpi report-kpi-primary">
            <span>Presença registrada</span>
            <strong><?= (float)$statsFreq['taxa_presenca'] ?>%</strong>
            <small><?= (int)$statsFreq['presentes'] ?> estudantes presentes</small>
        </article>
        <article class="report-kpi report-kpi-accent">
            <span>Refeições servidas</span>
            <strong><?= (float)$statsRef['porcentagem_atendida'] ?>%</strong>
            <small><?= (int)$statsRef['total_refeicoes'] ?> refeições registradas</small>
        </article>
        <article class="report-kpi report-kpi-neutral">
            <span>Avaliação da merenda</span>
            <strong><?= number_format((float)$statsRef['media_avaliacao'], 1, ',', '.') ?><small>/5</small></strong>
            <small>Média das avaliações registradas</small>
        </article>
    </div>

    <div class="report-panels">
        <section class="report-panel" aria-labelledby="report-attendance-title">
            <div class="report-panel-heading">
                <div>
                    <p class="report-eyebrow">Frequência</p>
                    <h2 id="report-attendance-title">Presença por turma</h2>
                </div>
                <span class="report-target">Meta: 85%</span>
            </div>

            <?php if (empty($resumoPorTurma)): ?>
                <p class="report-empty">Não há turmas cadastradas para exibir.</p>
            <?php else: ?>
                <div class="report-attendance-list">
                    <?php foreach ($resumoPorTurma as $rt): ?>
                        <?php $porcentagem = max(0, min(100, (float)$rt['porcentagem'])); ?>
                        <div class="report-attendance-row">
                            <div class="report-attendance-label">
                                <strong><?= htmlspecialchars($rt['turma']) ?></strong>
                                <span><?= (int)$rt['presentes'] ?> de <?= (int)$rt['total'] ?> · <?= number_format($porcentagem, 1, ',', '.') ?>%</span>
                            </div>
                            <div class="report-progress" role="progressbar"
                                 aria-label="Presença em <?= htmlspecialchars($rt['turma']) ?>"
                                 aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $porcentagem ?>">
                                <span class="<?= $porcentagem >= 85 ? 'is-on-target' : '' ?>"
                                      style="width: <?= $porcentagem ?>%"></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="report-footnote">Alunos sem frequência marcada não são contabilizados como presentes.</p>
            <?php endif; ?>
        </section>

        <section class="report-panel" aria-labelledby="report-diet-title">
            <div class="report-panel-heading">
                <div>
                    <p class="report-eyebrow">Alimentação</p>
                    <h2 id="report-diet-title">Restrições e cuidados</h2>
                </div>
                <span class="report-target"><?= count($restricoesContagem) ?> categorias</span>
            </div>

            <?php if (empty($restricoesContagem)): ?>
                <p class="report-empty">Não há dados de restrições cadastrados.</p>
            <?php else: ?>
                <div class="report-diet-list">
                    <?php foreach ($restricoesContagem as $restricao => $qtd): ?>
                        <?php
                            $isLivre = strtolower($restricao) === 'nenhuma';
                            $pct = ($totalAlunos > 0) ? round(($qtd / $totalAlunos) * 100, 1) : 0;
                        ?>
                        <div class="report-diet-row <?= $isLivre ? 'is-clear' : '' ?>">
                            <div>
                                <strong><?= htmlspecialchars($restricao) ?></strong>
                                <span><?= number_format($pct, 1, ',', '.') ?>% dos estudantes</span>
                            </div>
                            <b><?= (int)$qtd ?></b>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <p class="report-footnote">Total de estudantes cadastrados: <?= (int)$totalAlunos ?>.</p>
        </section>
    </div>

    <aside class="report-note">
        <span class="report-note-icon" aria-hidden="true">i</span>
        <div>
            <h2>Sobre este relatório</h2>
            <p>Os indicadores refletem os registros disponíveis para <?= date('d/m/Y', strtotime($hoje)) ?>. Confira a frequência e os dados alimentares antes de usar este resumo para decisões operacionais.</p>
            <small>Emitido em <?= date('d/m/Y \à\s H:i') ?></small>
        </div>
    </aside>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
