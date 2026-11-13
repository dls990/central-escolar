<?php
// Lista os pratos em disputa e apresenta as opcoes para registrar um voto.


$tituloPagina = 'Votação da Sexta Especial | VivalCris';
require_once __DIR__ . '/../layouts/header.php';
?>


<div class="text-center max-w-2xl mx-auto mb-10">
    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-800 text-xs font-bold uppercase tracking-wider mb-3">
        <span>🗳️</span> Democracia Escolar & Alimentação
    </div>
    <h1 class="text-3xl sm:text-4xl font-brand font-bold text-slate-800 mb-2">
        Qual Prato Saudável Você Quer na <span class="text-violet-600">Sexta Especial?</span>
    </h1>
    <p class="text-slate-600 text-sm">
        Vote no seu prato favorito da semana! A opção mais votada pelos alunos será preparada com muito carinho pela nossa equipe de cozinha nesta sexta-feira.
    </p>
    <div class="mt-4 inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border border-slate-200 text-xs font-bold text-slate-700 shadow-sm">
        <span>📊</span> Total de votos computados: <span id="totalVotosContador" class="text-violet-700"><?= $totalVotos ?> votos</span>
    </div>
</div>


<div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-5xl mx-auto mb-12">
    <?php foreach ($pratos as $p): ?>
        <?php
            $porcentagem = ($totalVotos > 0) ? round(($p['votos'] / $totalVotos) * 100) : 0;
            $isLider = ($lider && $lider['id'] === $p['id'] && $p['votos'] > 0);
        ?>
        <div class="fun-card p-6 flex flex-col justify-between relative overflow-hidden <?= $isLider ? 'border-2 border-violet-400 bg-gradient-to-b from-white to-violet-50/30' : 'bg-white' ?>">

            <?php if ($isLider): ?>
                <div class="absolute top-0 right-0 bg-violet-600 text-white text-[11px] font-bold px-3 py-1 rounded-bl-xl shadow-sm flex items-center gap-1">
                    <span>👑</span> Líder da Votação
                </div>
            <?php endif; ?>

            <div>

                <div class="flex items-start gap-4 mb-3">
                    <span class="text-5xl select-none transform hover:scale-110 transition"><?= $p['emoji'] ?></span>
                    <div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 uppercase">
                            <?= htmlspecialchars($p['categoria']) ?>
                        </span>
                        <h3 class="font-brand font-bold text-xl text-slate-800 mt-1"><?= htmlspecialchars($p['nome']) ?></h3>
                    </div>
                </div>


                <p class="text-xs text-slate-600 leading-relaxed mb-4">
                    <?= htmlspecialchars($p['descricao']) ?>
                </p>


                <div class="mb-4">
                    <div class="flex justify-between items-center text-xs font-bold mb-1.5">
                        <span id="votos-prato-<?= $p['id'] ?>" class="text-slate-600"><?= $p['votos'] ?> votos</span>
                        <span id="porcentagem-prato-<?= $p['id'] ?>" class="text-violet-700"><?= $porcentagem ?>%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                        <div id="progresso-prato-<?= $p['id'] ?>"
                             class="bg-gradient-to-r from-violet-500 to-indigo-500 h-3 rounded-full transition-all duration-700 ease-out"
                             style="width: <?= $porcentagem ?>%"></div>
                    </div>
                </div>
            </div>


            <button onclick="votarNoPrato(<?= $p['id'] ?>)"
                    class="w-full py-3 px-4 rounded-xl font-brand font-bold text-sm bg-violet-600 hover:bg-violet-700 text-white transition transform active:scale-95 shadow-md shadow-violet-600/20 flex items-center justify-center gap-2">
                <span>🗳️</span> Votar Neste Prato!
            </button>

        </div>
    <?php endforeach; ?>
</div>


<div class="max-w-3xl mx-auto p-6 bg-white rounded-3xl border border-slate-200 text-center shadow-sm">
    <span class="text-3xl block mb-2">🥕</span>
    <h4 class="font-brand font-bold text-lg text-slate-800 mb-1">Por que o Cardápio Participativo é importante?</h4>
    <p class="text-xs text-slate-600 leading-relaxed max-w-xl mx-auto">
        Estimular a participação dos alunos na escolha dos alimentos cria um vínculo positivo com a comida saudável, reduz o desperdício de merenda e incentiva novos hábitos alimentares para a vida toda!
    </p>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
