<?php
$tituloPagina = 'Painel do Administrador Geral | VivalCris';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Cabeçalho do ADM com Animação de Entrada -->
<div class="scroll-reveal flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-14 h-14 rounded-2xl bg-indigo-100 text-indigo-800 flex items-center justify-center text-3xl shadow-sm">
            👑
        </div>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl sm:text-3xl font-brand font-bold text-slate-800">
                    Painel Geral do Administrador
                </h1>
                <span class="px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-900 text-xs font-bold">
                    Monitoramento Integrado
                </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-600 mt-1">
                Acesso total aos dois lados da escola: <strong>Lado das Inspetoras</strong> (frequência e chamada) e <strong>Lado da Cozinha</strong> (nutrição e refeições).
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <a href="<?= url('admin/usuarios') ?>" 
           class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-brand font-bold text-xs transition shadow-sm flex items-center gap-1.5">
            <span>⚙️</span> Gerenciar Usuários
        </a>
    </div>
</div>

<!-- Cards Comparativos dos Dois Lados (Inspetoria VS Cozinha) -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    
    <!-- Lado 1: Inspetoria e Frequência Escolar -->
    <div class="scroll-reveal-left fun-card p-6 bg-white border-2 border-emerald-100 shadow-md">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div class="flex items-center gap-2">
                <span class="text-2xl">📋</span>
                <div>
                    <h3 class="font-brand font-bold text-lg text-slate-800">Lado das Inspetoras</h3>
                    <p class="text-xs text-emerald-700 font-semibold">Vivi, Val e Cris (Chamada das 12 Salas)</p>
                </div>
            </div>
            <a href="<?= url('frequencia') ?>" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">
                Acessar ➔
            </a>
        </div>

        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="p-3 bg-emerald-50 rounded-xl text-center">
                <span class="text-xs text-slate-600 block">Taxa Presença</span>
                <strong class="text-2xl font-brand text-emerald-700"><?= $statsFrequencia['taxa_presenca'] ?>%</strong>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl text-center">
                <span class="text-xs text-slate-600 block">Presentes</span>
                <strong class="text-2xl font-brand text-slate-800"><?= $statsFrequencia['presentes'] ?></strong>
            </div>
            <div class="p-3 bg-rose-50 rounded-xl text-center">
                <span class="text-xs text-slate-600 block">Faltas</span>
                <strong class="text-2xl font-brand text-rose-700"><?= $statsFrequencia['ausentes'] ?></strong>
            </div>
        </div>

        <div class="p-3 rounded-xl bg-slate-50 text-xs space-y-1.5 text-slate-700">
            <div class="flex justify-between">
                <span>Total de Salas Monitoradas:</span>
                <strong class="text-slate-900">12 Salas Únicas (1º EF ao 3º EM)</strong>
            </div>
            <div class="flex justify-between">
                <span>Inspetora Ativa Hoje:</span>
                <strong class="text-emerald-700"><?= htmlspecialchars($pizzaStats['fechado_por'] ?? 'Inspetora') ?></strong>
            </div>
            <div class="flex justify-between">
                <span>Backup Automático do Gráfico:</span>
                <span class="text-emerald-700 font-bold">✓ Salvo no Banco de Dados</span>
            </div>
        </div>
    </div>

    <!-- Lado 2: Cozinha e Alimentação Escolar -->
    <div class="scroll-reveal-right fun-card p-6 bg-white border-2 border-amber-100 shadow-md">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🍲</span>
                <div>
                    <h3 class="font-brand font-bold text-lg text-slate-800">Lado da Cozinha</h3>
                    <p class="text-xs text-amber-800 font-semibold">Dra. Camila (Nutricionista Chefe)</p>
                </div>
            </div>
            <a href="<?= url('cozinha') ?>" class="text-xs font-bold text-amber-700 hover:text-amber-800">
                Acessar ➔
            </a>
        </div>

        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="p-3 bg-amber-50 rounded-xl text-center">
                <span class="text-xs text-slate-600 block">Refeições</span>
                <strong class="text-2xl font-brand text-amber-800"><?= $pedidoHoje['total_presentes'] ?? $statsFrequencia['presentes'] ?></strong>
            </div>
            <div class="p-3 bg-emerald-50 rounded-xl text-center">
                <span class="text-xs text-slate-600 block">Poupados</span>
                <strong class="text-2xl font-brand text-emerald-700"><?= $pedidoHoje['total_ausentes'] ?? $statsFrequencia['ausentes'] ?></strong>
            </div>
            <div class="p-3 bg-indigo-50 rounded-xl text-center">
                <span class="text-xs text-slate-600 block">Status Cozinha</span>
                <strong class="text-sm font-brand text-indigo-700 block mt-2 capitalize">
                    <?= htmlspecialchars($pedidoHoje['status'] ?? 'Aguardando') ?>
                </strong>
            </div>
        </div>

        <div class="p-3 rounded-xl bg-slate-50 text-xs space-y-1.5 text-slate-700">
            <div class="flex justify-between">
                <span>Cardápio Hoje:</span>
                <strong class="text-slate-900"><?= $cardapioHoje['emoji'] ?? '🍽️' ?> <?= htmlspecialchars($cardapioHoje['prato_principal'] ?? 'Cardápio Padrão') ?></strong>
            </div>
            <div class="flex justify-between">
                <span>Dietas Especiais:</span>
                <strong class="text-rose-700"><?= htmlspecialchars($pedidoHoje['restricoes_resumo'] ?? 'Consultar Cozinha') ?></strong>
            </div>
            <div class="flex justify-between">
                <span>Prevenção de Desperdício:</span>
                <span class="text-emerald-700 font-bold">100% Sincronizado</span>
            </div>
        </div>
    </div>

</div>

<!-- Linha de Gestão: Gráfico de Pizza Geral + Quadro de Usuários -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
    
    <!-- Gráfico de Pizza Central do ADM -->
    <div class="lg:col-span-5 scroll-reveal-left">
        <div class="fun-card p-6 bg-white border border-slate-200 shadow-sm h-full flex flex-col justify-between">
            <div class="border-b border-slate-100 pb-3 mb-3">
                <h3 class="font-brand font-bold text-base text-slate-800 flex items-center gap-2">
                    <span>🥧</span> Gráfico de Pizza Consolidado (Hoje)
                </h3>
                <p class="text-xs text-slate-600">Presenças, ausências e justificados da escola inteira</p>
            </div>

            <div class="relative w-full aspect-square max-w-[260px] mx-auto my-2 flex items-center justify-center">
                <canvas id="adminPizzaChart"></canvas>
            </div>

            <div class="pt-3 border-t border-slate-100 text-center">
                <span class="text-xs text-slate-600">Total de <?= $totalAlunos ?> alunos matriculados nas 12 turmas</span>
            </div>
        </div>
    </div>

    <!-- Usuários Cadastrados no Sistema (Inspetoras, Nutricionista, ADM) -->
    <div class="lg:col-span-7 scroll-reveal-right">
        <div class="fun-card p-6 bg-white border border-slate-200 shadow-sm h-full">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="font-brand font-bold text-base text-slate-800 flex items-center gap-2">
                        <span>👥</span> Usuários e Permissões Ativas
                    </h3>
                    <p class="text-xs text-slate-600">Contas configuradas no sistema MVC</p>
                </div>
                <a href="<?= url('admin/usuarios') ?>" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                    + Adicionar / Editar
                </a>
            </div>

            <div class="space-y-3">
                <?php foreach ($usuarios as $u): ?>
                    <div class="p-3 rounded-2xl border border-slate-100 hover:border-slate-300 transition flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl select-none"><?= $u['avatar'] ?></span>
                            <div>
                                <h4 class="font-bold text-sm text-slate-800 leading-tight">
                                    <?= htmlspecialchars($u['nome']) ?>
                                    <span class="font-mono text-xs text-slate-600 font-normal">(<?= htmlspecialchars($u['usuario']) ?>)</span>
                                </h4>
                                <span class="text-xs text-slate-600 block"><?= htmlspecialchars($u['cargo']) ?></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase
                                <?= ($u['perfil'] === 'admin') ? 'bg-indigo-100 text-indigo-800' : (($u['perfil'] === 'nutricionista') ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') ?>">
                                <?= htmlspecialchars($u['perfil']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('adminPizzaChart');
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
