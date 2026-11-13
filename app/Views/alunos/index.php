<?php
// Lista e filtra os alunos e oferece atalhos para cadastrar ou editar registros.


$tituloPagina = 'Gestão de Alunos | VivalCris';
require_once __DIR__ . '/../layouts/header.php';
?>


<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2">
            <span class="text-3xl">👥</span>
            <h1 class="text-2xl sm:text-3xl font-brand font-bold text-slate-800">Estudantes Cadastrados</h1>
        </div>
        <p class="text-xs sm:text-sm text-slate-600 mt-1">
            Controle de matrículas, turmas, fichas de saúde e pontuação no jogo saudável.
        </p>
    </div>

    <a href="<?= url('alunos/novo') ?>" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-5 rounded-2xl shadow-md shadow-emerald-600/20 transition transform hover:scale-105 active:scale-95 text-xs sm:text-sm">
        <span>➕</span> Cadastrar Novo Aluno
    </a>
</div>


<div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-col md:flex-row items-center justify-between gap-3">

    <form method="GET" action="<?= url('alunos') ?>" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
        <div class="relative flex-grow sm:flex-grow-0 sm:w-64">
            <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Buscar por nome ou matrícula..."
                   class="w-full text-xs p-2.5 pl-8 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <span class="absolute left-2.5 top-2.5 text-slate-600 text-xs">🔍</span>
        </div>

        <select name="turma" onchange="this.form.submit()" class="text-xs font-semibold p-2.5 rounded-xl border border-slate-200 text-slate-700">
            <option value="todas" <?= ($turmaSelecionada === 'todas') ? 'selected' : '' ?>>Todas as Turmas</option>
            <?php foreach ($turmas as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>" <?= ($turmaSelecionada === $t) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="p-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition">
            Filtrar
        </button>

        <?php if (!empty($busca) || $turmaSelecionada !== 'todas'): ?>
            <a href="<?= url('alunos') ?>" class="text-xs text-rose-600 font-bold hover:underline">
                Limpar Filtros ✕
            </a>
        <?php endif; ?>
    </form>

    <div class="text-xs font-bold text-slate-600">
        Total: <span class="text-emerald-700 font-bold"><?= $total ?></span> alunos encontrados
    </div>
</div>


<div class="fun-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-wider">
                    <th class="py-3.5 px-4">Estudante</th>
                    <th class="py-3.5 px-4">Matrícula</th>
                    <th class="py-3.5 px-4">Turma</th>
                    <th class="py-3.5 px-4">Restrições Alimentares</th>
                    <th class="py-3.5 px-4">Contato Responsável</th>
                    <th class="py-3.5 px-4 text-center">Pontos Nutri</th>
                    <th class="py-3.5 px-4 text-center">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                <?php if (empty($alunos)): ?>
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-600">
                            <span class="text-4xl block mb-2">🔍</span>
                            Nenhum aluno encontrado com esses critérios.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($alunos as $aluno): ?>
                        <tr class="hover:bg-slate-50/70 transition-colors">

                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-3xl select-none"><?= $aluno['avatar'] ?></span>
                                    <div>
                                        <p class="font-bold text-slate-800"><?= htmlspecialchars($aluno['nome']) ?></p>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 font-mono text-xs text-slate-600">
                                <?= htmlspecialchars($aluno['matricula']) ?>
                            </td>

                            <td class="py-3.5 px-4 font-semibold text-slate-700">
                                <?= htmlspecialchars($aluno['turma']) ?>
                            </td>

                            <td class="py-3.5 px-4">
                                <?php if (!empty($aluno['restricoes']) && strtolower($aluno['restricoes']) !== 'nenhuma'): ?>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-xl bg-rose-50 text-rose-700 border border-rose-200">
                                        <span>⚠️</span> <?= htmlspecialchars($aluno['restricoes']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-emerald-700 font-semibold bg-emerald-50 px-2.5 py-1 rounded-xl border border-emerald-200">
                                        ✓ Nenhuma
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="py-3.5 px-4 text-xs text-slate-600">
                                <?= !empty($aluno['telefone_responsavel']) ? htmlspecialchars($aluno['telefone_responsavel']) : '<span class="text-slate-600">Não informado</span>' ?>
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center gap-1 font-bold text-xs text-amber-700 bg-amber-100 px-2.5 py-1 rounded-full">
                                    <span>⭐</span> <?= $aluno['pontos_nutri'] ?? 50 ?> pts
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="<?= url('alunos/editar?id=' . $aluno['id']) ?>"
                                       class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition"
                                       title="Editar dados">
                                        ✏️
                                    </a>
                                    <form method="POST" action="<?= url('alunos/excluir') ?>" onsubmit="return confirm('Deseja realmente excluir este aluno do sistema?');">
                                        <input type="hidden" name="id" value="<?= $aluno['id'] ?>">
                                        <button type="submit" class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 transition" title="Excluir aluno">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
