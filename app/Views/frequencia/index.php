<?php
// Permite filtrar turmas, registrar presencas e enviar o total diario para a cozinha.

$tituloPagina = 'Chamada Interativa & Frequência | VivalCris';
$alunosPorTurma = [];
foreach ($alunos as $aluno) {
    $alunosPorTurma[$aluno['turma']][] = $aluno;
}

$turmasExibidas = $turmaSelecionada === 'todas'
    ? $turmas
    : (in_array($turmaSelecionada, $turmas, true) ? [$turmaSelecionada] : []);

require_once __DIR__ . '/../layouts/header.php';
?>


<div class="scroll-reveal flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2">
            <span class="text-3xl">📋</span>
            <h1 class="text-2xl sm:text-3xl font-brand font-bold text-slate-800">Chamada & Frequência Escolar</h1>
        </div>
        <p class="text-xs sm:text-sm text-slate-600 mt-1">
            Painel das Inspetoras <strong>(Vivi, Val e Cris)</strong>: marque presenças e ausências para calcular a alimentação escolar e enviar em tempo real para a cozinha!
        </p>
    </div>


    <!-- Os filtros recarregam a chamada para a data e turma escolhidas. -->
    <form method="GET" action="<?= url('frequencia') ?>" class="flex flex-wrap items-center gap-2 bg-white p-2 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <label class="sr-only">Data</label>
            <input type="date" name="data" value="<?= htmlspecialchars($data, ENT_QUOTES, 'UTF-8') ?>"
                   max="<?= date('Y-m-d') ?>" required
                   onchange="this.form.requestSubmit()"
                   class="text-xs font-semibold px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <div>
            <label class="sr-only">Turma</label>
            <select name="turma" onchange="this.form.submit()"
                    class="text-xs font-semibold px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="todas" <?= ($turmaSelecionada === 'todas') ? 'selected' : '' ?>>Todas as 12 Salas Únicas</option>
                <?php foreach ($turmas as $t): ?>
                    <option value="<?= htmlspecialchars($t) ?>" <?= ($turmaSelecionada === $t) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="p-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
            🔍 Filtrar
        </button>
    </form>
</div>


<!-- Informa se o censo do dia ja chegou a cozinha ou ainda precisa ser enviado. -->
<div class="scroll-reveal mb-6">
    <?php if ($pedidoCozinha): ?>
        <div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-lg flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-3xl">🍲</span>
                <div>
                    <h3 class="font-brand font-bold text-base sm:text-lg">
                        Censo de Alimentação Enviado para a Cozinha!
                    </h3>
                    <p class="text-xs text-emerald-100">
                        Enviado por <strong><?= htmlspecialchars($pedidoCozinha['enviado_por']) ?></strong> às <?= htmlspecialchars($pedidoCozinha['horario_envio']) ?> •
                        Total de <strong><?= $pedidoCozinha['total_presentes'] ?> refeições</strong> confirmadas para preparo hoje.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 rounded-xl bg-white/20 backdrop-blur-md text-xs font-bold capitalize">
                    Status Cozinha: <?= htmlspecialchars($pedidoCozinha['status']) ?>
                </span>
            </div>
        </div>
    <?php else: ?>
        <div class="p-4 rounded-2xl bg-amber-50 border-2 border-amber-200 text-amber-900 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-3xl animate-bounce">📢</span>
                <div>
                    <h3 class="font-brand font-bold text-base text-amber-950">
                        Aviso da Cozinha: Aguardando Envio da Chamada de Hoje
                    </h3>
                    <p class="text-xs text-amber-800">
                        Após marcar a presença dos alunos, clique no botão ao lado para enviar a quantidade exata de refeições para a nutricionista.
                    </p>
                </div>
            </div>
            <button onclick="document.getElementById('modalEnvioCozinha').classList.remove('hidden')"
                    class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-brand font-bold text-xs transition transform hover:scale-105 shadow-md shadow-amber-500/20 whitespace-nowrap">
                🚀 Enviar Censo para a Cozinha
            </button>
        </div>
    <?php endif; ?>
</div>


<!-- Reune o grafico, os indicadores e as acoes gerais da chamada. -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">


    <!-- Grafico e acesso rapido ao historico do dia selecionado. -->
    <div class="lg:col-span-5 scroll-reveal-left">
        <div class="fun-card p-5 bg-white border-2 border-emerald-100 h-full flex flex-col justify-between">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                <div>
                    <h3 class="font-brand font-bold text-base text-slate-800 flex items-center gap-2">
                        <span>🥧</span> Gráfico de Pizza Diário
                    </h3>
                    <p class="text-[11px] text-slate-600">Salva automaticamente a cada presença</p>
                </div>
                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Auto-Save
                </span>
            </div>


            <div class="relative w-full aspect-square max-w-[270px] mx-auto my-2 flex items-center justify-center">
                <canvas id="graficoPizzaDiario"></canvas>
            </div>


            <div class="pt-3 border-t border-slate-100 space-y-2 text-xs">
                <div class="flex items-center justify-between text-slate-600">
                    <span>Responsável pelo registro:</span>
                    <strong class="text-emerald-700" id="pie-responsavel">
                        <?= htmlspecialchars($pizzaStats['fechado_por'] ?? 'Inspetora') ?>
                    </strong>
                </div>
                <div class="flex items-center justify-between">
                    <a href="<?= url('frequencia/historico?data=' . $data) ?>"
                       class="text-xs font-bold text-brand-600 hover:text-brand-700 flex items-center gap-1 transition">
                        <span>📅</span> Ver Histórico de Dias Salvos
                    </a>
                    <button onclick="document.getElementById('modalEnvioCozinha').classList.remove('hidden')"
                            class="text-xs font-bold text-emerald-600 hover:text-emerald-700 underline">
                        Avisar Cozinha
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div class="lg:col-span-7 flex flex-col justify-between gap-4">


        <!-- Indicadores que mostram o resultado agregado da chamada. -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 scroll-reveal">

            <div class="fun-card p-3.5 bg-gradient-to-br from-emerald-500 to-emerald-600 text-white">
                <div class="flex items-center justify-between text-emerald-100 text-[10px] font-bold uppercase">
                    <span>Taxa de Presença</span>
                    <span>⭐</span>
                </div>
                <div class="text-2xl sm:text-3xl font-brand font-bold mt-1" id="stat-taxa">
                    <?= $stats['taxa_presenca'] ?>%
                </div>
                <p class="text-[10px] text-emerald-100 mt-1 font-medium">Assiduidade Geral</p>
            </div>

            <div class="fun-card p-3.5 bg-white border-emerald-200">
                <div class="flex items-center justify-between text-slate-600 text-[10px] font-bold uppercase">
                    <span>Presentes</span>
                    <span class="text-emerald-600 font-bold">✓</span>
                </div>
                <div class="text-2xl sm:text-3xl font-brand font-bold text-emerald-600 mt-1" id="stat-presentes">
                    <?= $stats['presentes'] ?>
                </div>
                <p class="text-[10px] text-slate-600 mt-1 font-medium">Refeições p/ Cozinha</p>
            </div>

            <div class="fun-card p-3.5 bg-white border-rose-200">
                <div class="flex items-center justify-between text-slate-600 text-[10px] font-bold uppercase">
                    <span>Ausentes</span>
                    <span class="text-rose-600 font-bold">✕</span>
                </div>
                <div class="text-2xl sm:text-3xl font-brand font-bold text-rose-600 mt-1" id="stat-ausentes">
                    <?= $stats['ausentes'] ?>
                </div>
                <p class="text-[10px] text-slate-600 mt-1 font-medium">Pratos Economizados</p>
            </div>

            <div class="fun-card p-3.5 bg-white border-amber-200">
                <div class="flex items-center justify-between text-slate-600 text-[10px] font-bold uppercase">
                    <span>Justificados</span>
                    <span class="text-amber-600 font-bold">•</span>
                </div>
                <div class="text-2xl sm:text-3xl font-brand font-bold text-amber-600 mt-1" id="stat-justificados">
                    <?= $stats['justificados'] ?>
                </div>
                <p class="text-[10px] text-slate-600 mt-1 font-medium">Atestado / Aviso</p>
            </div>

        </div>


        <div class="scroll-reveal p-4 bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-600 font-semibold">
                <span>Filtro Ativo:</span>
                <span class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 font-bold">
                    <?= ($turmaSelecionada === 'todas') ? 'Todas as 12 Salas Únicas' : htmlspecialchars($turmaSelecionada) ?>
                </span>
                <span>(<?= count($alunos) ?> alunos)</span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button onclick="marcarTodosPresentes(this.dataset.data, this.dataset.turma)"
                        data-data="<?= htmlspecialchars($data, ENT_QUOTES, 'UTF-8') ?>"
                        data-turma="<?= htmlspecialchars($turmaSelecionada, ENT_QUOTES, 'UTF-8') ?>"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-xl transition transform active:scale-95 shadow-sm shadow-emerald-500/20">
                    <span>🎉</span> Marcar Todos Presentes
                </button>

                <button onclick="document.getElementById('modalEnvioCozinha').classList.remove('hidden')"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-xs font-bold rounded-xl transition transform active:scale-95 shadow-sm shadow-orange-500/20">
                    <span>🚀</span> Enviar para Cozinha
                </button>
            </div>
        </div>


        <div class="p-3.5 rounded-2xl bg-indigo-50/70 border border-indigo-100 text-indigo-900 text-xs flex items-center gap-3">
            <span class="text-2xl select-none">💡</span>
            <div>
                <strong>Dica da Feira Tecnológica:</strong> A integração entre Inspetoria e Cozinha elimina a sobra de comida e garante que alunos com restrições alimentares (como sem lactose ou celíacos) recebam o prato adequado pontualmente!
            </div>
        </div>

    </div>

</div>


<!-- Organiza a lista por turma para a inspetora abrir somente a sala em chamada. -->
<section class="attendance-classes scroll-reveal" aria-labelledby="attendance-classes-title">
    <header class="attendance-section-heading">
        <div>
            <p class="dashboard-eyebrow">Chamada organizada por sala</p>
            <h2 id="attendance-classes-title">Turmas e estudantes</h2>
            <p>Abra uma turma para consultar os alunos e registrar cada presença.</p>
        </div>
        <span class="attendance-total"><?= count($alunos) ?> estudantes na seleção</span>
    </header>

    <?php if (empty($turmasExibidas)): ?>
        <div class="attendance-empty">
            <span aria-hidden="true">🎒</span>
            <p>Nenhuma turma encontrada para este filtro.</p>
        </div>
    <?php else: ?>
        <div class="attendance-class-list">
            <?php foreach ($turmasExibidas as $indiceTurma => $turma): ?>
                <?php $alunosDaTurma = $alunosPorTurma[$turma] ?? []; ?>
                <details class="attendance-class-card" <?= ($turmaSelecionada !== 'todas' || $indiceTurma === 0) ? 'open' : '' ?>>
                    <summary class="attendance-class-summary">
                        <span class="attendance-room-icon" aria-hidden="true">🏫</span>
                        <span class="attendance-room-title">
                            <strong><?= htmlspecialchars($turma, ENT_QUOTES, 'UTF-8') ?></strong>
                            <small><?= count($alunosDaTurma) ?> <?= count($alunosDaTurma) === 1 ? 'estudante' : 'estudantes' ?></small>
                        </span>
                        <span class="attendance-room-toggle">Ver lista</span>
                    </summary>

                    <?php if (empty($alunosDaTurma)): ?>
                        <p class="attendance-empty-room">Nenhum estudante cadastrado nesta turma.</p>
                    <?php else: ?>
                        <div class="attendance-class-toolbar">
                            <span>Selecione o status de cada estudante para registrar a chamada.</span>
                            <button type="button"
                                    onclick="marcarTodosPresentes(this.dataset.data, this.dataset.turma)"
                                    data-data="<?= htmlspecialchars($data, ENT_QUOTES, 'UTF-8') ?>"
                                    data-turma="<?= htmlspecialchars($turma, ENT_QUOTES, 'UTF-8') ?>"
                                    class="attendance-mark-room">
                                Marcar turma presente
                            </button>
                        </div>

                        <div class="attendance-student-list">
                            <?php foreach ($alunosDaTurma as $aluno): ?>
                                <?php
                                    $alunoId = (int)$aluno['aluno_id'];
                                    $status = $aluno['status'] ?? '';
                                ?>
                                <article id="aluno-row-<?= $alunoId ?>" class="attendance-student">
                                    <div class="attendance-student-identity">
                                        <span class="attendance-student-avatar" aria-hidden="true">
                                            <?= htmlspecialchars($aluno['avatar'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <div>
                                            <h3><?= htmlspecialchars($aluno['nome'], ENT_QUOTES, 'UTF-8') ?></h3>
                                            <span class="attendance-student-meta">
                                                Matrícula <?= htmlspecialchars($aluno['matricula'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                            <span id="horario-<?= $alunoId ?>" class="attendance-student-meta">
                                                <?= !empty($aluno['horario']) ? 'Chegada: ' . htmlspecialchars($aluno['horario'], ENT_QUOTES, 'UTF-8') : '' ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="attendance-student-status">
                                        <?php if (!empty($aluno['restricoes']) && strtolower($aluno['restricoes']) !== 'nenhuma'): ?>
                                            <span class="attendance-restriction">
                                                <span aria-hidden="true">⚠️</span>
                                                <?= htmlspecialchars($aluno['restricoes'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="attendance-no-restriction">Sem restrições alimentares</span>
                                        <?php endif; ?>

                                        <span id="badge-status-<?= $alunoId ?>"
                                              class="attendance-status-badge <?= ($status === 'presente') ? 'badge-presente' : (($status === 'ausente') ? 'badge-ausente' : (($status === 'justificado') ? 'badge-justificado' : 'badge-pendente')) ?>">
                                            <?php
                                                if ($status === 'presente') echo '✓ Presente';
                                                elseif ($status === 'ausente') echo '✕ Ausente';
                                                elseif ($status === 'justificado') echo '• Justificado';
                                                else echo 'Não marcado';
                                            ?>
                                        </span>
                                    </div>

                                    <div class="attendance-student-actions" role="group" aria-label="Frequência de <?= htmlspecialchars($aluno['nome'], ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="button"
                                                onclick="atualizarFrequencia(<?= $alunoId ?>, 'presente', this.dataset.data)"
                                                data-data="<?= htmlspecialchars($data, ENT_QUOTES, 'UTF-8') ?>"
                                                data-aluno="<?= $alunoId ?>"
                                                data-status="presente"
                                                class="attendance-status-button px-3 py-1.5 rounded-xl font-bold text-xs transition transform active:scale-90
                                                <?= ($status === 'presente') ? 'bg-emerald-500 text-white shadow-sm ring-2 ring-offset-1 ring-emerald-400' : 'bg-white text-slate-600 hover:bg-slate-100' ?>"
                                                title="Marcar Presente">
                                            ✓ Presente
                                        </button>
                                        <button type="button"
                                                onclick="atualizarFrequencia(<?= $alunoId ?>, 'ausente', this.dataset.data)"
                                                data-data="<?= htmlspecialchars($data, ENT_QUOTES, 'UTF-8') ?>"
                                                data-aluno="<?= $alunoId ?>"
                                                data-status="ausente"
                                                class="attendance-status-button px-3 py-1.5 rounded-xl font-bold text-xs transition transform active:scale-90
                                                <?= ($status === 'ausente') ? 'bg-rose-500 text-white shadow-sm ring-2 ring-offset-1 ring-rose-400' : 'bg-white text-slate-600 hover:bg-slate-100' ?>"
                                                title="Marcar Falta">
                                            ✕ Ausente
                                        </button>
                                        <button type="button"
                                                onclick="atualizarFrequencia(<?= $alunoId ?>, 'justificado', this.dataset.data)"
                                                data-data="<?= htmlspecialchars($data, ENT_QUOTES, 'UTF-8') ?>"
                                                data-aluno="<?= $alunoId ?>"
                                                data-status="justificado"
                                                class="attendance-status-button px-3 py-1.5 rounded-xl font-bold text-xs transition transform active:scale-90
                                                <?= ($status === 'justificado') ? 'bg-amber-500 text-white shadow-sm ring-2 ring-offset-1 ring-amber-400' : 'bg-white text-slate-600 hover:bg-slate-100' ?>"
                                                title="Marcar Falta Justificada">
                                            • Justificado
                                        </button>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </details>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>


<!-- Confirma quantidades e observacao antes de enviar o resumo para a cozinha. -->
<div id="modalEnvioCozinha" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border-4 border-amber-300 transform transition-all animate-scale-up">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="text-3xl">🍲</span>
                <div>
                    <h3 class="font-brand font-bold text-xl text-slate-800">Enviar Censo para a Cozinha</h3>
                    <p class="text-xs text-amber-700 font-semibold">Previsão exata de refeições para a merenda</p>
                </div>
            </div>
            <button onclick="document.getElementById('modalEnvioCozinha').classList.add('hidden')" class="p-2 text-slate-400 hover:text-slate-600 text-xl">✕</button>
        </div>

        <div class="my-5 space-y-4">
            <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-emerald-900 font-bold">Total de Alunos Presentes Hoje:</span>
                    <span class="text-2xl font-brand font-bold text-emerald-700" id="modalTotalPresentes"><?= $stats['presentes'] ?></span>
                </div>
                <p class="text-xs text-emerald-800 mt-1">
                    Essa será a quantidade enviada para o painel da <strong>Nutricionista</strong> preparar no almoço escolar.
                </p>
            </div>

            <div class="p-4 bg-rose-50 rounded-2xl border border-rose-200">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-rose-900 font-bold">Alunos Ausentes (Desperdício Evitado):</span>
                    <span class="text-2xl font-brand font-bold text-rose-700"><?= $stats['ausentes'] ?></span>
                </div>
                <p class="text-xs text-rose-800 mt-1">
                    Economia garantida de ingredientes!
                </p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Observação ou Aviso para a Cozinha (Opcional):
                </label>
                <textarea id="obsEnvioCozinha" rows="3" placeholder="Ex: Alunos do 7º e 8º EF têm aula de laboratório antes do almoço..."
                          class="w-full text-xs p-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500"></textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <button onclick="document.getElementById('modalEnvioCozinha').classList.add('hidden')"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                Cancelar
            </button>
            <button id="btnEnviarCozinhaConfirm" onclick="enviarParaCozinha('<?= $data ?>')"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-brand font-bold text-sm transition transform hover:scale-105 shadow-md shadow-orange-500/20">
                🚀 Confirmar Envio para a Cozinha
            </button>
        </div>
    </div>
</div>


<script>
let graficoPizza = null;

document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('graficoPizzaDiario');
    if (!ctx) return;

    const initialData = <?= json_encode($pizzaStats['chart_data']) ?>;

    graficoPizza = new Chart(ctx, {
        type: 'doughnut',
        data: initialData,
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
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const val = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const perc = total > 0 ? Math.round((val / total) * 100) : 0;
                            return ` ${label}: ${val} (${perc}%)`;
                        }
                    }
                }
            },
            cutout: '62%',
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });


    window.atualizarGraficoPizza = function(pizzaData) {
        if (!graficoPizza || !pizzaData) return;

        graficoPizza.data.datasets[0].data = [
            pizzaData.presentes,
            pizzaData.ausentes,
            pizzaData.justificados,
            pizzaData.pendentes
        ];
        graficoPizza.update();

        const respEl = document.getElementById('pie-responsavel');
        if (respEl && pizzaData.fechado_por) {
            respEl.textContent = pizzaData.fechado_por;
        }

        const modalPres = document.getElementById('modalTotalPresentes');
        if (modalPres) modalPres.textContent = pizzaData.presentes;
    };
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
