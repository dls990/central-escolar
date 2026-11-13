<?php
$tituloPagina = 'Fila do Refeitório | EduNutri';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Cabeçalho -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2">
            <span class="text-3xl">🥪</span>
            <h1 class="text-2xl sm:text-3xl font-brand font-bold text-slate-800">Fila do Refeitório & Merenda</h1>
        </div>
        <p class="text-xs sm:text-sm text-slate-600 mt-1">
            Registro dinâmico de consumo da refeição escolar com alertas automáticos de alergias e intolerâncias.
        </p>
    </div>

    <!-- Filtros de Turma e Data -->
    <form method="GET" action="<?= url('alimentacao/fila') ?>" class="flex flex-wrap items-center gap-2 bg-white p-2 rounded-2xl border border-slate-200 shadow-sm">
        <input type="date" name="data" value="<?= htmlspecialchars($data) ?>" onchange="this.form.submit()" 
               class="text-xs font-semibold px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700">

        <select name="turma" onchange="this.form.submit()" 
                class="text-xs font-semibold px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700">
            <option value="todas" <?= ($turmaSelecionada === 'todas') ? 'selected' : '' ?>>Todas as Turmas</option>
            <?php foreach ($turmas as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>" <?= ($turmaSelecionada === $t) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="p-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition">
            🔍 Filtrar
        </button>
    </form>
</div>

<!-- Card Informativo do Prato Atual -->
<div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <span class="text-3xl"><?= $cardapioHoje['emoji'] ?></span>
        <div>
            <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider">Prato Servido Agora</span>
            <h3 class="font-brand font-bold text-slate-800 text-base"><?= htmlspecialchars($cardapioHoje['prato_principal']) ?></h3>
        </div>
    </div>
    <div class="flex items-center gap-4 text-xs font-semibold text-slate-600">
        <span>🍲 Refeições Hoje: <strong class="text-amber-700 text-sm"><?= $stats['total_refeicoes'] ?></strong></span>
        <span>⭐ Avaliação Média: <strong class="text-amber-700 text-sm"><?= $stats['media_avaliacao'] ?>/5</strong></span>
    </div>
</div>

<!-- Lista de Alunos e Controle de Merenda -->
<div class="fun-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-wider">
                    <th class="py-3.5 px-4">Estudante</th>
                    <th class="py-3.5 px-4">Turma</th>
                    <th class="py-3.5 px-4">Alerta de Alergia</th>
                    <th class="py-3.5 px-4 text-center">Status da Refeição</th>
                    <th class="py-3.5 px-4 text-center">Pontos Nutri</th>
                    <th class="py-3.5 px-4 text-center">Ação do Refeitório</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                <?php foreach ($alunos as $aluno): ?>
                    <?php 
                        $jaComeu = isset($consumoMap[$aluno['id']]);
                        $infoConsumo = $jaComeu ? $consumoMap[$aluno['id']] : null;
                        $temAlergia = (!empty($aluno['restricoes']) && strtolower($aluno['restricoes']) !== 'nenhuma');
                    ?>
                    <tr class="hover:bg-amber-50/30 transition-colors">
                        
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <span class="text-3xl select-none"><?= $aluno['avatar'] ?></span>
                                <div>
                                    <p class="font-bold text-slate-800"><?= htmlspecialchars($aluno['nome']) ?></p>
                                    <span class="text-[11px] text-slate-600 font-mono">Matrícula: <?= htmlspecialchars($aluno['matricula']) ?></span>
                                </div>
                            </div>
                        </td>

                        <td class="py-3.5 px-4 font-semibold text-slate-700">
                            <?= htmlspecialchars($aluno['turma']) ?>
                        </td>

                        <!-- Alerta de Restrição -->
                        <td class="py-3.5 px-4">
                            <?php if ($temAlergia): ?>
                                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-xl bg-rose-100 text-rose-800 border border-rose-300 animate-pulse-subtle">
                                    <span>⚠️</span> <?= htmlspecialchars($aluno['restricoes']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-xs text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200">
                                    ✓ Sem restrições
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- Status da Refeição -->
                        <td class="py-3.5 px-4 text-center">
                            <?php if ($jaComeu): ?>
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    <span>🍽️</span> Almoçou (<?= str_repeat('⭐', $infoConsumo['avaliacao'] ?? 5) ?>)
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-500">
                                    Ainda não almoçou
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- Pontos Nutri -->
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 font-bold text-xs text-amber-700 bg-amber-100 px-2.5 py-1 rounded-full">
                                <span>⭐</span> <?= $aluno['pontos_nutri'] ?? 50 ?> pts
                            </span>
                        </td>

                        <!-- Ação -->
                        <td class="py-3.5 px-4 text-center">
                            <?php if ($jaComeu): ?>
                                <button onclick="toggleConsumoMerenda(<?= $aluno['id'] ?>, <?= $cardapioHoje['id'] ?>, '<?= $data ?>', 'cancelar')" 
                                        class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-200 hover:bg-rose-100 hover:text-rose-700 text-slate-700 transition">
                                    Cancelar Registro
                                </button>
                            <?php else: ?>
                                <button onclick="abrirModalAvaliarMerenda(<?= $aluno['id'] ?>, '<?= htmlspecialchars(addslashes($aluno['nome'])) ?>', <?= $temAlergia ? 'true' : 'false' ?>, '<?= htmlspecialchars(addslashes($aluno['restricoes'])) ?>')"
                                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-600 text-white shadow-sm transition transform active:scale-95 flex items-center gap-1 mx-auto">
                                    <span>🍽️</span> Marcar Merenda
                                </button>
                            <?php endif; ?>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Divertido de Avaliação e Registro de Merenda -->
<div id="modalAvaliarMerenda" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border-4 border-amber-300 transform transition-all animate-scale-up">
        
        <div class="text-center mb-4">
            <span class="text-5xl block mb-2 animate-bounce">😋</span>
            <h3 class="font-brand font-bold text-xl text-slate-800">Hora do Lanche & Almoço!</h3>
            <p id="modalMerendaAlunoNome" class="text-xs text-slate-500 font-semibold"></p>
        </div>

        <input type="hidden" id="modalAlunoId" value="">
        <input type="hidden" id="modalTemAlergia" value="false">
        <input type="hidden" id="modalRestricao" value="">

        <!-- Alerta de Restrição se houver -->
        <div id="alertaRestricaoModal" class="hidden p-3 bg-rose-50 border border-rose-300 rounded-2xl mb-4 text-xs text-rose-800">
            <strong>⚠️ Atenção Cozinha:</strong> <span id="alertaRestricaoTexto"></span>
            <p class="text-[11px] text-rose-600 mt-1">Certifique-se de que o prato entregue foi devidamente adaptado.</p>
        </div>

        <!-- Avaliação com Estrelas / Emojis -->
        <div class="mb-4">
            <label class="block text-center text-xs font-bold text-slate-700 mb-2">O que o aluno achou do prato?</label>
            <div class="flex justify-center gap-2 text-2xl" id="estrelasContainer">
                <button type="button" onclick="setEstrelas(1)" class="star-btn transition transform hover:scale-125">⭐</button>
                <button type="button" onclick="setEstrelas(2)" class="star-btn transition transform hover:scale-125">⭐</button>
                <button type="button" onclick="setEstrelas(3)" class="star-btn transition transform hover:scale-125">⭐</button>
                <button type="button" onclick="setEstrelas(4)" class="star-btn transition transform hover:scale-125">⭐</button>
                <button type="button" onclick="setEstrelas(5)" class="star-btn transition transform hover:scale-125 scale-110">⭐</button>
            </div>
            <input type="hidden" id="avaliacaoInput" value="5">
            <p id="avaliacaoLabel" class="text-center text-xs font-bold text-amber-600 mt-1">Adorei! Nota 5 ⭐</p>
        </div>

        <!-- Comentário Rápido -->
        <div class="mb-5">
            <input type="text" id="comentarioInput" placeholder="Algum elogio ou observação? (Opcional)" class="w-full text-xs p-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
        </div>

        <div class="flex justify-end gap-2">
            <button onclick="document.getElementById('modalAvaliarMerenda').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                Voltar
            </button>
            <button onclick="confirmarEntregaMerenda()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow transform active:scale-95">
                Confirmar Merenda (+10 Pts) 🎉
            </button>
        </div>

    </div>
</div>

<script>
    function abrirModalAvaliarMerenda(alunoId, nome, temAlergia, restricao) {
        window.appAudio?.playPop();
        document.getElementById('modalAlunoId').value = alunoId;
        document.getElementById('modalMerendaAlunoNome').textContent = `Aluno(a): ${nome}`;
        document.getElementById('modalTemAlergia').value = temAlergia;
        document.getElementById('modalRestricao').value = restricao;
        document.getElementById('comentarioInput').value = '';

        const alertaDiv = document.getElementById('alertaRestricaoModal');
        if (temAlergia) {
            alertaDiv.classList.remove('hidden');
            document.getElementById('alertaRestricaoTexto').textContent = `Aluno possui restrição: "${restricao}".`;
        } else {
            alertaDiv.classList.add('hidden');
        }

        setEstrelas(5);
        document.getElementById('modalAvaliarMerenda').classList.remove('hidden');
    }

    function setEstrelas(num) {
        window.appAudio?.playPop();
        document.getElementById('avaliacaoInput').value = num;
        const labels = {
            1: 'Não gostei muito 😐',
            2: 'Mais ou menos 🤔',
            3: 'Gostoso! 👍',
            4: 'Muito bom! 😋',
            5: 'Adorei! Nota 5 ⭐'
        };
        document.getElementById('avaliacaoLabel').textContent = labels[num] || `${num} estrelas`;
    }

    async function confirmarEntregaMerenda() {
        const alunoId = document.getElementById('modalAlunoId').value;
        const avaliacao = document.getElementById('avaliacaoInput').value;
        const comentario = document.getElementById('comentarioInput').value.trim();
        const data = '<?= $data ?>';
        const cardapioId = <?= (int)($cardapioHoje['id'] ?? 1) ?>;

        document.getElementById('modalAvaliarMerenda').classList.add('hidden');

        try {
            const formData = new FormData();
            formData.append('aluno_id', alunoId);
            formData.append('cardapio_id', cardapioId);
            formData.append('data', data);
            formData.append('acao', 'consumir');
            formData.append('avaliacao', avaliacao);
            formData.append('comentario', comentario);

            const response = await fetch(`${BASE_URL}/alimentacao/salvarConsumo`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                window.appAudio?.playFanfare();
                window.launchConfetti?.(50);
                showToast(result.message);
                setTimeout(() => window.location.reload(), 800);
            }
        } catch (err) {
            console.error(err);
            showToast('Erro ao confirmar merenda', 'error');
        }
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
