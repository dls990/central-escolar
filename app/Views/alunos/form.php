<?php
// Formulario reutilizado para inserir ou alterar dados escolares de um aluno.


$tituloPagina = $titulo . ' | VivalCris';
require_once __DIR__ . '/../layouts/header.php';

$isEdit = !empty($aluno);
$actionUrl = $isEdit ? url('alunos/atualizar') : url('alunos/salvar');
?>

<div class="max-w-2xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-brand font-bold text-slate-800"><?= htmlspecialchars($titulo) ?></h1>
            <p class="text-xs text-slate-500">Preencha as informações do estudante para a central de frequência e alimentação.</p>
        </div>
        <a href="<?= url('alunos') ?>" class="text-xs font-bold text-slate-600 hover:text-slate-800 bg-white px-3 py-2 rounded-xl border border-slate-200">
            ← Voltar
        </a>
    </div>

    <div class="fun-card p-6 sm:p-8 bg-white border-2 border-emerald-100">
        <form method="POST" action="<?= $actionUrl ?>" class="space-y-5">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= $aluno['id'] ?>">
            <?php endif; ?>


            <?php if (!$isEdit): ?>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Escolha um Avatar Divertido
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <?php
                            $opcoesAvatares = ['👦', '👧', '🧒', '👩', '🧑', '🎒', '⭐', '🎈'];
                            $avatarAtual = $aluno['avatar'] ?? '👦';
                        ?>
                        <?php foreach ($opcoesAvatares as $av): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="avatar" value="<?= $av ?>" <?= ($avatarAtual === $av) ? 'checked' : '' ?> class="sr-only peer">
                                <span class="w-12 h-12 flex items-center justify-center text-2xl rounded-2xl bg-slate-50 border-2 border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition transform hover:scale-110">
                                    <?= $av ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>


            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Nome Completo do Aluno *
                </label>
                <input type="text" name="nome" required value="<?= htmlspecialchars($aluno['nome'] ?? '') ?>" placeholder="Ex: Lucas Gabriel Oliveira"
                       class="w-full text-sm p-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>


            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Número de Matrícula *
                    </label>
                    <input type="text" name="matricula" required value="<?= htmlspecialchars($aluno['matricula'] ?? '2026' . rand(1000, 9999)) ?>"
                           class="w-full text-sm p-3 rounded-xl border border-slate-200 font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Turma / Ano *
                    </label>
                    <select name="turma" required class="w-full text-sm p-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <?php
                            $turmasPadrao = [
                                '1º Ano EF', '2º Ano EF', '3º Ano EF', '4º Ano EF', '5º Ano EF',
                                '6º Ano EF', '7º Ano EF', '8º Ano EF', '9º Ano EF',
                                '1º Ano EM', '2º Ano EM', '3º Ano EM'
                            ];
                            $turmasDisponiveis = !empty($turmas) ? $turmas : $turmasPadrao;
                            $turmaAtual = $aluno['turma'] ?? '1º Ano EF';
                        ?>
                        <?php foreach ($turmasDisponiveis as $t): ?>
                            <option value="<?= htmlspecialchars($t) ?>" <?= ($turmaAtual === $t) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>


            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Restrições Alimentares ou Alergias (Segurança no Refeitório)
                </label>
                <select name="restricoes_sugestao" onchange="if(this.value) document.getElementById('campoRestricao').value = this.value" class="w-full text-xs p-2.5 rounded-xl border border-slate-200 mb-2">
                    <option value="">Selecione uma opção rápida ou digite abaixo...</option>
                    <option value="Nenhuma">✓ Nenhuma (Livre para qualquer cardápio)</option>
                    <option value="Sem Lactose">🥛 Sem Lactose (Intolerância ou Alergia a Leite)</option>
                    <option value="Sem Glúten">🌾 Sem Glúten (Doença Celíaca)</option>
                    <option value="Vegetariano">🥦 Vegetariano (Não consome carnes)</option>
                    <option value="Alérgica a Amendoim">🥜 Alergia Severa a Amendoim / Nozes</option>
                    <option value="Alérgico a Frutos do Mar">🦐 Alergia a Frutos do Mar</option>
                </select>
                <input type="text" id="campoRestricao" name="restricoes" value="<?= htmlspecialchars($aluno['restricoes'] ?? 'Nenhuma') ?>"
                       placeholder="Ex: Sem Lactose ou Nenhuma"
                       class="w-full text-sm p-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>


            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Telefone de Contato do Responsável
                </label>
                <input type="text" name="telefone_responsavel" value="<?= htmlspecialchars($aluno['telefone_responsavel'] ?? '') ?>" placeholder="(11) 98765-4321"
                       class="w-full text-sm p-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="<?= url('alunos') ?>" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-600/20 transition transform active:scale-95">
                    <?= $isEdit ? 'Atualizar Dados' : 'Cadastrar Aluno 🚀' ?>
                </button>
            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
