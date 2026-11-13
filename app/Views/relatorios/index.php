<?php
$tituloPagina = 'Relatórios Escolares | VivalCris';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Cabeçalho -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <div class="flex items-center gap-2">
            <span class="text-3xl">📊</span>
            <h1 class="text-2xl sm:text-3xl font-brand font-bold text-slate-800">Relatório Executivo Escolar</h1>
        </div>
        <p class="text-xs sm:text-sm text-slate-600 mt-1">
            Consolidado diário de assiduidade escolar, engajamento na merenda e cuidados nutricionais.
        </p>
    </div>

    <button onclick="window.print()" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-5 rounded-2xl shadow transition transform hover:scale-105 active:scale-95 text-xs">
        <span>🖨️</span> Imprimir Relatório Oficial
    </button>
</div>

<!-- Indicadores Globais -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
    
    <div class="fun-card p-6 bg-emerald-500 text-white">
        <span class="text-xs font-bold text-emerald-100 uppercase tracking-wider">Índice Geral de Presença</span>
        <div class="text-4xl font-brand font-bold mt-2"><?= $statsFreq['taxa_presenca'] ?>%</div>
        <p class="text-xs text-emerald-100 mt-1"><?= $statsFreq['presentes'] ?> estudantes presentes hoje</p>
    </div>

    <div class="fun-card p-6 bg-amber-500 text-white">
        <span class="text-xs font-bold text-amber-100 uppercase tracking-wider">Adesão à Merenda</span>
        <div class="text-4xl font-brand font-bold mt-2"><?= $statsRef['porcentagem_atendida'] ?>%</div>
        <p class="text-xs text-amber-100 mt-1"><?= $statsRef['total_refeicoes'] ?> refeições servidas hoje</p>
    </div>

    <div class="fun-card p-6 bg-indigo-600 text-white">
        <span class="text-xs font-bold text-indigo-100 uppercase tracking-wider">Satisfação dos Alunos</span>
        <div class="text-4xl font-brand font-bold mt-2">⭐ <?= $statsRef['media_avaliacao'] ?>/5.0</div>
        <p class="text-xs text-indigo-100 mt-1">Excelente aprovação da comunidade escolar</p>
    </div>

</div>

<!-- Detalhamento por Turma e por Restrição -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    
    <!-- Presença por Turma -->
    <div class="fun-card p-6 bg-white">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
            <h3 class="font-brand font-bold text-lg text-slate-800 flex items-center gap-2">
                <span>🏫</span> Assiduidade por Turma
            </h3>
            <span class="text-xs font-bold text-slate-600">Meta: 85%</span>
        </div>

        <div class="space-y-5">
            <?php foreach ($resumoPorTurma as $rt): ?>
                <div>
                    <div class="flex justify-between items-center text-xs font-bold text-slate-700 mb-1.5">
                        <span><?= htmlspecialchars($rt['turma']) ?></span>
                        <span class="<?= ($rt['porcentagem'] >= 85) ? 'text-emerald-600' : 'text-amber-600' ?>">
                            <?= $rt['presentes'] ?> de <?= $rt['total'] ?> (<?= $rt['porcentagem'] ?>%)
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                        <div class="h-3 rounded-full transition-all duration-700 <?= ($rt['porcentagem'] >= 85) ? 'bg-emerald-500' : 'bg-amber-500' ?>" 
                             style="width: <?= $rt['porcentagem'] ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Restrições e Alergias Mapeadas -->
    <div class="fun-card p-6 bg-white">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
            <h3 class="font-brand font-bold text-lg text-slate-800 flex items-center gap-2">
                <span>🥗</span> Mapeamento de Dietas Especiais
            </h3>
            <span class="text-xs font-bold text-rose-600">Total: <?= count($restricoesContagem) ?> Categorias</span>
        </div>

        <div class="space-y-3">
            <?php foreach ($restricoesContagem as $restricao => $qtd): ?>
                <?php 
                    $isLivre = strtolower($restricao) === 'nenhuma';
                    $pct = ($totalAlunos > 0) ? round(($qtd / $totalAlunos) * 100, 1) : 0;
                ?>
                <div class="flex items-center justify-between p-3 rounded-xl <?= $isLivre ? 'bg-slate-50' : 'bg-rose-50/60 border border-rose-200' ?>">
                    <div class="flex items-center gap-2">
                        <span class="text-base"><?= $isLivre ? '🥦' : '⚠️' ?></span>
                        <span class="text-xs font-bold <?= $isLivre ? 'text-slate-700' : 'text-rose-800' ?>">
                            <?= htmlspecialchars($restricao) ?>
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-bold text-slate-800"><?= $qtd ?> alunos</span>
                        <span class="text-[10px] text-slate-600 block">(<?= $pct ?>%)</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- Apresentação Escolar & Conclusão Pedagógica -->
<div class="fun-card p-6 bg-gradient-to-r from-slate-900 to-indigo-950 text-white">
    <div class="flex items-center gap-3 mb-2 text-emerald-400 text-xs font-bold uppercase tracking-wider">
        <span>🎓</span> Avaliação Pedagógica do Sistema Integrado
    </div>
    <h4 class="text-xl font-brand font-bold mb-2">Impacto no Cotidiano Escolar</h4>
    <p class="text-xs text-slate-300 leading-relaxed mb-4">
        A união da lista de chamada em tempo real com o controle de merenda do refeitório permite à gestão prever com precisão o número de refeições necessárias para o dia, erradica o desperdício de alimentos, garante segurança imediata para estudantes alérgicos e estimula hábitos saudáveis por meio da gamificação e democracia com cardápio participativo.
    </p>
    <div class="flex flex-wrap items-center justify-between text-[11px] text-slate-400 pt-3 border-t border-slate-800">
        <span>Desenvolvido em PHP Moderno • Arquitetura MVC • Banco SQLite Integrado</span>
        <span>Data de Emissão: <?= date('d/m/Y - H:i') ?></span>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
