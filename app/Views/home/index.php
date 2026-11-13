<?php
// Apresenta o sistema, atalhos para os modulos e avisos escolares relevantes.

$tituloPagina = 'VivalCris - Central Escolar de Frequência & Alimentação';
require_once __DIR__ . '/../layouts/header.php';
?>

<section class="dashboard-intro scroll-reveal">
    <div>
        <p class="dashboard-eyebrow">Central Escolar / Visão geral</p>
        <h1>Frequência e alimentação escolar</h1>
        <p>Acompanhamento diário de presença, refeições previstas e necessidades alimentares.</p>
    </div>
    <div class="dashboard-date">
        <span>Data de referência</span>
        <strong><?= date('d/m/Y') ?></strong>
    </div>
</section>


<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">


    <div class="lg:col-span-4 scroll-reveal-left">
        <div class="dashboard-panel p-5 h-full flex flex-col justify-between">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-2">
                <div>
                    <h3 class="font-brand font-bold text-base text-slate-800 flex items-center gap-2">
                        Presença de hoje
                    </h3>
                    <p class="text-[11px] text-slate-600">Salvo automaticamente no site</p>
                </div>
                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                    <?= $statsFrequencia['taxa_presenca'] ?>%
                </span>
            </div>

            <div class="relative w-full aspect-square max-w-[230px] mx-auto my-2 flex items-center justify-center">
                <canvas id="homePizzaChart"></canvas>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-600 font-medium">Por: <?= htmlspecialchars($pizzaStats['fechado_por'] ?? 'Inspetora') ?></span>
                <a href="<?= url('frequencia/historico') ?>" class="text-brand-600 font-bold hover:underline">
                    Ver Histórico ➔
                </a>
            </div>
        </div>
    </div>


    <div class="lg:col-span-8 flex flex-col justify-between gap-4 scroll-reveal-right">

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">


            <div class="dashboard-stat">
                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block">Assiduidade</span>
                <span class="text-2xl sm:text-3xl font-brand font-bold text-emerald-700 block mt-1"><?= $statsFrequencia['taxa_presenca'] ?>%</span>
                <span class="text-[11px] text-emerald-700 font-semibold"><?= $statsFrequencia['presentes'] ?> presentes</span>
            </div>


            <div class="dashboard-stat">
                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block">Refeições Cozinha</span>
                <span class="text-2xl sm:text-3xl font-brand font-bold text-amber-700 block mt-1"><?= $pedidoHoje['total_presentes'] ?? $statsFrequencia['presentes'] ?></span>
                <span class="text-[11px] text-amber-800 font-semibold">Confirmadas hoje</span>
            </div>


            <div class="dashboard-stat">
                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block">Pratos Poupados</span>
                <span class="text-2xl sm:text-3xl font-brand font-bold text-rose-600 block mt-1"><?= $pedidoHoje['total_ausentes'] ?? $statsFrequencia['ausentes'] ?></span>
                <span class="text-[11px] text-rose-700 font-semibold">Zero desperdício</span>
            </div>


            <div class="dashboard-stat">
                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block">Salas Únicas</span>
                <span class="text-2xl sm:text-3xl font-brand font-bold text-indigo-700 block mt-1">12</span>
                <span class="text-[11px] text-indigo-800 font-semibold">1º EF ao 3º EM</span>
            </div>

        </div>

        <div class="dashboard-menu">
            <div>
                <span class="dashboard-eyebrow">Cardápio de hoje</span>
                <h2><?= htmlspecialchars($cardapioHoje['prato_principal'] ?? 'Cardápio não cadastrado') ?></h2>
                <p>Acompanhamento: <?= htmlspecialchars($cardapioHoje['acompanhamento'] ?? '—') ?>. Sobremesa: <?= htmlspecialchars($cardapioHoje['sobremesa'] ?? '—') ?>.</p>
            </div>
            <a href="<?= url('alimentacao/cardapio') ?>">Abrir cardápio</a>
        </div>

    </div>

</div>

<section class="dashboard-lower">
    <div class="dashboard-panel dashboard-operations scroll-reveal-left">
        <div class="dashboard-section-heading">
            <div><h2>Acesso operacional</h2><p>Atalhos para as rotinas da escola.</p></div>
        </div>
        <nav class="dashboard-links" aria-label="Módulos do sistema">
            <a href="<?= url('frequencia') ?>"><span><strong>Frequência</strong><small>Registro diário das 12 turmas</small></span><b aria-hidden="true">→</b></a>
            <a href="<?= url('cozinha') ?>"><span><strong>Cozinha</strong><small>Pedidos e status de preparo</small></span><b aria-hidden="true">→</b></a>
            <a href="<?= url('alimentacao/votacao') ?>"><span><strong>Votação de pratos</strong><small>Preferências do cardápio escolar</small></span><b aria-hidden="true">→</b></a>
            <?php if (tem_perfil('admin')): ?>
                <a href="<?= url('alunos') ?>"><span><strong>Alunos</strong><small>Cadastro e restrições alimentares</small></span><b aria-hidden="true">→</b></a>
            <?php endif; ?>
        </nav>
        <?php if ($liderVotacao): ?>
            <div class="dashboard-vote-summary">
                <span>Prato mais votado</span>
                <strong><?= htmlspecialchars($liderVotacao['nome']) ?></strong>
                <small><?= (int)$liderVotacao['votos'] ?> votos</small>
            </div>
        <?php endif; ?>
    </div>

    <section class="dashboard-panel dashboard-restrictions scroll-reveal-right" aria-labelledby="restrictions-title">
        <div class="dashboard-section-heading">
            <div><h2 id="restrictions-title">Restrições alimentares</h2><p>Alunos que exigem atenção no preparo.</p></div>
        </div>
        <div class="restriction-list">
            <?php foreach (array_slice($alunosComAlergia, 0, 4) as $aluno): ?>
                <div class="restriction-row">
                    <div><strong><?= htmlspecialchars($aluno['nome']) ?></strong><span><?= htmlspecialchars($aluno['turma']) ?></span></div>
                    <small><?= htmlspecialchars($aluno['restricoes']) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</section>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('homePizzaChart');
    if (!ctx) return;

    const data = <?= json_encode($pizzaStats['chart_data']) ?>;

    new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, padding: 8, font: { size: 10 } }
                }
            },
            cutout: '62%'
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
