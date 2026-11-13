<?php
$tituloPagina = 'Painel da Cozinha & Nutricionista | VivalCris';
require_once __DIR__ . '/../layouts/header.php';

$dadosExibicao = $pedido ?: $previaTempoReal;
$statusCozinha = $pedido['status'] ?? 'pendente';
?>

<!-- Cabeçalho com Animação de Entrada -->
<div class="scroll-reveal flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-3xl shadow-sm">
            🥗
        </div>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl sm:text-3xl font-brand font-bold text-slate-800">
                    Painel da Cozinha & Nutricionista
                </h1>
                <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 text-xs font-bold">
                    Recepção de Dados
                </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-600 mt-1">
                A nutricionista <strong>(Dra. Camila)</strong> recebe as informações de frequência enviadas pelas inspetoras <strong>(Vivi, Val e Cris)</strong> direto no site para cozinhar a quantidade exata!
            </p>
        </div>
    </div>

    <!-- Seletor de Data -->
    <form method="GET" action="<?= url('cozinha') ?>" class="flex items-center gap-2 bg-white p-2 rounded-2xl border border-slate-200 shadow-sm">
        <label class="text-xs font-bold text-slate-600 pl-2">Data:</label>
        <input type="date" name="data" value="<?= htmlspecialchars($data) ?>" onchange="this.form.submit()"
               class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700">
        <button type="submit" class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition">
            Ver
        </button>
    </form>
</div>

<!-- Banner de Status de Comunicação com a Inspetoria -->
<div class="scroll-reveal mb-8">
    <?php if ($pedido): ?>
        <div class="p-5 rounded-3xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <span class="text-4xl">📨</span>
                <div>
                    <span class="inline-block px-2.5 py-0.5 rounded-full bg-white/20 text-[11px] font-bold uppercase tracking-wider mb-1">
                        Censo Oficial Recebido
                    </span>
                    <h3 class="font-brand font-bold text-lg sm:text-xl">
                        Enviado por <?= htmlspecialchars($pedido['enviado_por']) ?> às <?= htmlspecialchars($pedido['horario_envio']) ?>
                    </h3>
                    <p class="text-xs text-emerald-100 mt-1">
                        Total confirmado: <strong><?= $pedido['total_presentes'] ?> alunos presentes</strong> (<?= $pedido['total_ausentes'] ?> faltas registradas).
                    </p>
                    <?php if (!empty($pedido['observacao_inspetora'])): ?>
                        <p class="text-xs text-emerald-100 italic mt-2 bg-black/15 p-2 rounded-xl">
                            💬 Aviso das Inspetoras: "<?= htmlspecialchars($pedido['observacao_inspetora']) ?>"
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Botão de Atualização de Preparo -->
            <div class="flex flex-col sm:items-end gap-2">
                <span class="text-xs text-emerald-100 font-semibold">Status Atual do Almoço:</span>
                <span class="px-4 py-2 rounded-xl bg-white text-emerald-800 font-brand font-bold text-sm shadow capitalize">
                    🍲 <?= str_replace('_', ' ', $statusCozinha) ?>
                </span>
            </div>
        </div>
    <?php else: ?>
        <div class="p-5 rounded-3xl bg-amber-50 border-2 border-amber-300 text-amber-950 shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-4xl animate-bounce">⏱️</span>
                <div>
                    <span class="inline-block px-2.5 py-0.5 rounded-full bg-amber-200 text-amber-900 text-[11px] font-bold uppercase tracking-wider mb-1">
                        Prévia em Tempo Real
                    </span>
                    <h3 class="font-brand font-bold text-lg text-amber-950">
                        Inspetoras estão concluindo a chamada nas salas de aula
                    </h3>
                    <p class="text-xs text-amber-800 mt-1">
                        O painel abaixo já está calculando a prévia em tempo real com os alunos presentes registrados até este instante.
                    </p>
                </div>
            </div>
            <a href="<?= url('frequencia') ?>" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow transition text-center whitespace-nowrap">
                Ver Chamada das Inspetoras ➔
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Indicadores de Refeições & Combate ao Desperdício -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    
    <!-- Total de Refeições a Preparar -->
    <div class="scroll-reveal fun-card p-5 bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/20">
        <div class="flex items-center justify-between text-emerald-100 text-xs font-bold uppercase">
            <span>Refeições a Preparar</span>
            <span class="text-xl">🍽️</span>
        </div>
        <div class="text-4xl font-brand font-bold mt-2">
            <?= $dadosExibicao['total_presentes'] ?? 0 ?>
        </div>
        <p class="text-xs text-emerald-100 mt-1">
            Quantidade exata de pratos hoje
        </p>
    </div>

    <!-- Desperdício Evitado (Alunos Faltantes) -->
    <div class="scroll-reveal delay-100 fun-card p-5 bg-white border-2 border-rose-200">
        <div class="flex items-center justify-between text-slate-600 text-xs font-bold uppercase">
            <span>Desperdício Zero (Faltas)</span>
            <span class="text-xl">🌱</span>
        </div>
        <div class="text-4xl font-brand font-bold text-rose-600 mt-2">
            <?= $dadosExibicao['total_ausentes'] ?? 0 ?>
        </div>
        <p class="text-xs text-slate-600 mt-1">
            Pratos economizados com a chamada
        </p>
    </div>

    <!-- Divisão Fundamental 1 e 2 -->
    <div class="scroll-reveal delay-200 fun-card p-5 bg-white border-2 border-amber-200">
        <div class="flex items-center justify-between text-slate-600 text-xs font-bold uppercase">
            <span>Ensino Fundamental</span>
            <span class="text-xl">🎒</span>
        </div>
        <div class="flex items-baseline gap-2 mt-2">
            <span class="text-3xl font-brand font-bold text-amber-600"><?= $dadosExibicao['refeicoes_fund1'] ?? 0 ?></span>
            <span class="text-xs text-slate-600 font-semibold">(1º ao 5º EF)</span>
        </div>
        <div class="flex items-baseline gap-2 mt-1">
            <span class="text-xl font-brand font-bold text-amber-700"><?= $dadosExibicao['refeicoes_fund2'] ?? 0 ?></span>
            <span class="text-xs text-slate-600 font-semibold">(6º ao 9º EF)</span>
        </div>
    </div>

    <!-- Divisão Ensino Médio -->
    <div class="scroll-reveal delay-300 fun-card p-5 bg-white border-2 border-indigo-200">
        <div class="flex items-center justify-between text-slate-600 text-xs font-bold uppercase">
            <span>Ensino Médio</span>
            <span class="text-xl">🎓</span>
        </div>
        <div class="text-4xl font-brand font-bold text-indigo-600 mt-2">
            <?= $dadosExibicao['refeicoes_medio'] ?? 0 ?>
        </div>
        <p class="text-xs text-slate-600 mt-1">
            1º EM ao 3º EM (Salas Únicas)
        </p>
    </div>

</div>

<!-- Linha de Ação: Controle de Preparo da Cozinha + Restrições Alimentares -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
    
    <!-- Coluna 1: Ações de Controle de Cozimento da Nutricionista -->
    <div class="lg:col-span-7 scroll-reveal-left">
        <div class="fun-card p-6 bg-white border-2 border-amber-100 shadow-md h-full flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h3 class="font-brand font-bold text-lg text-slate-800 flex items-center gap-2">
                            <span>🍳</span> Controle do Fluxo de Preparo
                        </h3>
                        <p class="text-xs text-slate-600">A nutricionista atualiza o status para toda a escola acompanhar</p>
                    </div>
                    <span class="text-2xl select-none">👩‍🍳</span>
                </div>

                <!-- Passos do Workflow da Cozinha -->
                <div class="grid grid-cols-3 gap-2 my-4 text-center">
                    
                    <button type="button" onclick="atualizarStatusCozinha('<?= $data ?>', 'enviado')"
                            class="p-3 rounded-2xl border text-xs font-bold transition transform active:scale-95 <?= ($statusCozinha === 'enviado') ? 'bg-blue-50 border-blue-500 text-blue-700 ring-2 ring-blue-300' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-blue-50' ?>">
                        <span class="text-2xl block mb-1">📥</span>
                        Recebido
                    </button>

                    <button type="button" onclick="atualizarStatusCozinha('<?= $data ?>', 'em_preparo')"
                            class="p-3 rounded-2xl border text-xs font-bold transition transform active:scale-95 <?= ($statusCozinha === 'em_preparo') ? 'bg-amber-50 border-amber-500 text-amber-700 ring-2 ring-amber-300' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-amber-50' ?>">
                        <span class="text-2xl block mb-1">🔥</span>
                        Cozinhando
                    </button>

                    <button type="button" onclick="atualizarStatusCozinha('<?= $data ?>', 'pronto')"
                            class="p-3 rounded-2xl border text-xs font-bold transition transform active:scale-95 <?= ($statusCozinha === 'pronto') ? 'bg-emerald-50 border-emerald-500 text-emerald-700 ring-2 ring-emerald-300' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-emerald-50' ?>">
                        <span class="text-2xl block mb-1">✅</span>
                        Pronto p/ Servir
                    </button>

                </div>

                <!-- Formulário de Observação da Nutricionista -->
                <form method="POST" action="<?= url('cozinha/atualizarStatus') ?>" class="mt-4 pt-4 border-t border-slate-100">
                    <input type="hidden" name="data" value="<?= htmlspecialchars($data) ?>">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($statusCozinha) ?>">

                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Nota ou Recado da Nutricionista para o Painel Geral:
                    </label>
                    <div class="flex gap-2">
                        <input type="text" name="observacao_cozinha" 
                               value="<?= htmlspecialchars($pedido['observacao_cozinha'] ?? '') ?>"
                               placeholder="Ex: Almoço servido quente. Pratos especiais separados!"
                               class="flex-grow text-xs p-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition">
                            Salvar Nota
                        </button>
                    </div>
                </form>
            </div>

            <!-- Cardápio Resumido de Hoje -->
            <?php if ($cardapioHoje): ?>
                <div class="mt-4 p-4 rounded-2xl bg-amber-50/70 border border-amber-200">
                    <div class="flex items-center justify-between text-xs font-bold text-amber-900 mb-1">
                        <span>Cardápio de Hoje (<?= $cardapioHoje['dia_semana'] ?>):</span>
                        <span><?= $cardapioHoje['calorias'] ?> kcal</span>
                    </div>
                    <p class="font-bold text-sm text-slate-800">
                        <?= $cardapioHoje['emoji'] ?> <?= htmlspecialchars($cardapioHoje['prato_principal']) ?>
                    </p>
                    <p class="text-xs text-slate-600 mt-0.5">
                        <?= htmlspecialchars($cardapioHoje['acompanhamento']) ?> • Sobremesa: <?= htmlspecialchars($cardapioHoje['sobremesa']) ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Coluna 2: Alerta de Restrições Alimentares dos Alunos Presentes -->
    <div class="lg:col-span-5 scroll-reveal-right">
        <div class="fun-card p-6 bg-white border-2 border-rose-100 shadow-md h-full flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h3 class="font-brand font-bold text-lg text-slate-800 flex items-center gap-2">
                            <span>⚠️</span> Dietas Especiais dos Presentes
                        </h3>
                        <p class="text-xs text-slate-600">Alunos confirmados hoje que precisam de preparo separado</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 text-xs font-bold">
                        Alergênicos
                    </span>
                </div>

                <?php 
                    $listaRestricoes = $dadosExibicao['restricoes_lista'] ?? [];
                ?>

                <?php if (empty($listaRestricoes)): ?>
                    <div class="p-6 text-center text-slate-600 bg-slate-50 rounded-2xl">
                        <span class="text-3xl block mb-2">🎉</span>
                        Todos os alunos presentes hoje estão com alimentação padrão (sem restrições alimentares cadastradas).
                    </div>
                <?php else: ?>
                    <div class="space-y-2.5 max-h-[290px] overflow-y-auto pr-1">
                        <?php foreach ($listaRestricoes as $r): ?>
                            <div class="p-3 rounded-xl bg-rose-50/70 border border-rose-200 flex items-center justify-between text-xs">
                                <div>
                                    <strong class="text-slate-800 block"><?= htmlspecialchars($r['aluno']) ?></strong>
                                    <span class="text-[11px] text-slate-600"><?= htmlspecialchars($r['turma']) ?></span>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg bg-rose-200 text-rose-900 font-bold text-[11px]">
                                    <?= htmlspecialchars($r['restricao']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-600 flex items-center gap-2">
                <span>🛡️</span>
                <span>Alerta sanitário obrigatório: esterilize bancadas para celíacos e intolerantes a lactose.</span>
            </div>
        </div>
    </div>

</div>

<!-- Histórico Recente de Pedidos Recebidos na Cozinha -->
<div class="scroll-reveal fun-card p-6 bg-white border border-slate-200 shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
        <h3 class="font-brand font-bold text-lg text-slate-800 flex items-center gap-2">
            <span>📜</span> Histórico de Censors Recebidos pela Cozinha
        </h3>
        <span class="text-xs text-slate-600">Registros arquivados no sistema</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <th class="py-3 px-4">Data</th>
                    <th class="py-3 px-4">Inspetora</th>
                    <th class="py-3 px-4">Horário Envio</th>
                    <th class="py-3 px-4 text-center">Refeições Preparadas</th>
                    <th class="py-3 px-4 text-center">Pratos Poupados</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Observações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($ultimosPedidos)): ?>
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-600">
                            Nenhum envio registrado ainda.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ultimosPedidos as $up): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-bold text-slate-800">
                                <?= date('d/m/Y', strtotime($up['data'])) ?>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold text-emerald-800">
                                    <?= htmlspecialchars($up['enviado_por']) ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                <?= htmlspecialchars($up['horario_envio']) ?>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-emerald-700">
                                <?= $up['total_presentes'] ?>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-rose-600">
                                <?= $up['total_ausentes'] ?>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase
                                      <?= ($up['status'] === 'pronto') ? 'bg-emerald-100 text-emerald-800' : (($up['status'] === 'em_preparo') ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') ?>">
                                    <?= htmlspecialchars($up['status']) ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600 truncate max-w-xs">
                                <?= htmlspecialchars($up['observacao_cozinha'] ?: ($up['observacao_inspetora'] ?: '-')) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
