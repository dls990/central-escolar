<?php
$tituloPagina = 'Histórico Diário de Gráficos de Frequência | EduNutri';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="scroll-reveal flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2">
            <span class="text-3xl">📅</span>
            <h1 class="text-2xl sm:text-3xl font-brand font-bold text-slate-800">
                Histórico Diário de Frequência & Gráficos
            </h1>
        </div>
        <p class="text-xs sm:text-sm text-slate-600 mt-1">
            Gráficos de pizza salvos automaticamente a cada dia. Selecione uma data no histórico para inspecionar a presença e a comunicação com a cozinha.
        </p>
    </div>

    <a href="<?= url('frequencia') ?>" class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-200 transition shadow-sm">
        <span>←</span> Voltar para a Chamada de Hoje
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    
    <!-- Coluna 1: Gráfico de Pizza do Dia Selecionado -->
    <div class="lg:col-span-6 scroll-reveal-left">
        <div class="fun-card p-6 bg-white border-2 border-emerald-100 shadow-xl">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                <div>
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider block">Gráfico Salvo</span>
                    <h2 class="font-brand font-bold text-xl text-slate-800">
                        Dia <?= date('d/m/Y', strtotime($dataSelecionada)) ?>
                    </h2>
                </div>
                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                    Taxa: <?= $pizzaStats['taxa_presenca'] ?>%
                </span>
            </div>

            <!-- Gráfico de Pizza Salvo -->
            <div class="relative w-full aspect-square max-w-[320px] mx-auto my-4 flex items-center justify-center">
                <canvas id="graficoHistoricoPizza"></canvas>
            </div>

            <!-- Detalhes do Registro Arquivado -->
            <div class="grid grid-cols-3 gap-2 text-center my-4">
                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                    <span class="text-xs text-emerald-700 font-bold block">Presentes</span>
                    <strong class="text-xl font-brand text-emerald-800"><?= $pizzaStats['presentes'] ?></strong>
                </div>
                <div class="p-3 rounded-xl bg-rose-50 border border-rose-100">
                    <span class="text-xs text-rose-700 font-bold block">Ausentes</span>
                    <strong class="text-xl font-brand text-rose-800"><?= $pizzaStats['ausentes'] ?></strong>
                </div>
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-100">
                    <span class="text-xs text-amber-700 font-bold block">Justificados</span>
                    <strong class="text-xl font-brand text-amber-800"><?= $pizzaStats['justificados'] ?></strong>
                </div>
            </div>

            <!-- Metadados da Comunicação -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-slate-600 font-medium">Registrado / Fechado por:</span>
                    <span class="font-bold text-slate-800"><?= htmlspecialchars($pizzaStats['fechado_por'] ?? 'Inspetora') ?></span>
                </div>

                <?php if ($pedido): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600 font-medium">Enviado para a Cozinha:</span>
                        <span class="font-bold text-emerald-700"><?= htmlspecialchars($pedido['horario_envio']) ?> por <?= htmlspecialchars($pedido['enviado_por']) ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600 font-medium">Status no Refeitório:</span>
                        <span class="font-bold capitalize text-slate-800"><?= htmlspecialchars($pedido['status']) ?></span>
                    </div>
                    <?php if (!empty($pedido['observacao_cozinha'])): ?>
                        <div class="pt-2 border-t border-slate-200 text-slate-600">
                            <strong>Feedback da Cozinha:</strong> <em>"<?= htmlspecialchars($pedido['observacao_cozinha']) ?>"</em>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-slate-600 italic">Nenhum envio formal para a cozinha registrado nesta data.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- Coluna 2: Lista de Todos os Dias Salvos no Histórico -->
    <div class="lg:col-span-6 scroll-reveal-right space-y-4">
        
        <div class="flex items-center justify-between">
            <h3 class="font-brand font-bold text-lg text-slate-800 flex items-center gap-2">
                <span>📚</span> Dias Arquivados no Sistema
            </h3>
            <span class="text-xs text-slate-600 font-semibold">
                <?= count($historicos) ?> dias salvos
            </span>
        </div>

        <div class="space-y-3">
            <?php if (empty($historicos)): ?>
                <div class="fun-card p-8 text-center text-slate-600 bg-white">
                    <span class="text-4xl block mb-2">📅</span>
                    Nenhum histórico diário gravado ainda. Marque presenças na chamada para salvar o primeiro dia!
                </div>
            <?php else: ?>
                <?php foreach ($historicos as $h): ?>
                    <?php 
                        $isAtivo = ($h['data'] === $dataSelecionada);
                    ?>
                    <a href="<?= url('frequencia/historico?data=' . $h['data']) ?>" 
                       class="block p-4 rounded-2xl border transition-all duration-200 <?= $isAtivo ? 'bg-emerald-50 border-emerald-500 shadow-md ring-2 ring-emerald-300' : 'bg-white border-slate-200 hover:border-emerald-300 hover:shadow-sm' ?>">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl <?= $isAtivo ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700' ?> flex items-center justify-center font-bold text-sm">
                                    <?= date('d', strtotime($h['data'])) ?>
                                </div>
                                <div>
                                    <h4 class="font-brand font-bold text-sm text-slate-800">
                                        <?= date('d/m/Y', strtotime($h['data'])) ?>
                                        <?php if ($h['data'] === date('Y-m-d')): ?>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-200 text-emerald-900 ml-1">Hoje</span>
                                        <?php endif; ?>
                                    </h4>
                                    <span class="text-xs text-slate-600 block">
                                        Fechado por: <strong><?= htmlspecialchars($h['fechado_por'] ?? 'Inspetora') ?></strong>
                                    </span>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="text-sm font-brand font-bold text-emerald-700 block">
                                    <?= $h['taxa_presenca'] ?>% Assiduidade
                                </span>
                                <span class="text-[11px] text-slate-600 font-medium">
                                    <?= $h['presentes'] ?> presentes • <?= $h['ausentes'] ?> ausentes
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</div>

<!-- Script de Inicialização do Gráfico Salvo -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('graficoHistoricoPizza');
    if (!ctx) return;

    const chartData = <?= json_encode($pizzaStats['chart_data']) ?>;

    new Chart(ctx, {
        type: 'doughnut',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 12,
                        font: { family: 'Inter', size: 11, weight: '600' }
                    }
                }
            },
            cutout: '60%',
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
