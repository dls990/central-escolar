<?php
$tituloPagina = 'Roleta de Lanches Saudáveis | VivalCris';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="text-center max-w-2xl mx-auto mb-8">
    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-800 text-xs font-bold uppercase tracking-wider mb-2">
        <span>🎡</span> Jogo Interativo da Merenda
    </div>
    <h1 class="text-3xl sm:text-4xl font-brand font-bold text-slate-800 mb-2">
        Roleta de Lanches Saudáveis!
    </h1>
    <p class="text-slate-600 text-xs sm:text-sm">
        Não sabe o que escolher para o lanche da tarde? Gire a roleta mágica do Chef Corujito e ganhe uma dica super nutritiva e cheia de energia!
    </p>
</div>

<!-- Roleta Interativa -->
<div class="max-w-xl mx-auto flex flex-col items-center">
    
    <!-- Ponteiro Indicador da Roleta -->
    <div class="relative z-20 -mb-4 flex flex-col items-center">
        <div class="w-0 h-0 border-l-[16px] border-l-transparent border-r-[16px] border-r-transparent border-t-[28px] border-t-rose-600 drop-shadow-md"></div>
    </div>

    <!-- Canvas da Roleta -->
    <div class="relative p-2 bg-white rounded-full shadow-2xl border-8 border-amber-300">
        <canvas id="roletaCanvas" width="380" height="380" class="rounded-full"></canvas>
    </div>

    <!-- Botão de Girar -->
    <div class="mt-8 flex flex-col sm:flex-row items-center gap-4">
        <button id="btnGirarRoleta" onclick="girarRoleta()" 
                class="px-8 py-3.5 bg-gradient-to-r from-rose-500 to-amber-500 hover:from-rose-600 hover:to-amber-600 text-white font-brand font-bold text-lg rounded-2xl shadow-xl shadow-rose-500/20 transition transform hover:scale-105 active:scale-95 flex items-center gap-2">
            <span>🎡</span> GIRAR A ROLETA!
        </button>
    </div>

    <!-- Card de Resultado da Roleta -->
    <div id="resultadoRoleta" class="hidden mt-8 fun-card p-6 max-w-md w-full text-center border-4 border-amber-300 bg-amber-50/50 animate-scale-up">
        <span id="resultadoEmoji" class="text-6xl block mb-2 animate-bounce">🍎</span>
        <h3 id="resultadoTitulo" class="font-brand font-bold text-2xl text-slate-800 mb-1">Maçã com Canela</h3>
        <p id="resultadoDescricao" class="text-xs text-slate-600 leading-relaxed mb-4">
            Rica em fibras e antioxidantes, a maçã combinada com uma pitada de canela acelera a energia do cérebro para aprender!
        </p>
        <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs">
            ⭐ +10 Pontos de Sabedoria Nutricional!
        </span>
    </div>

</div>

<script>
    const lanches = [
        { nome: 'Maçã com Canela', emoji: '🍎', desc: 'Rica em fibras e antioxidantes, mantém a mente atenta nas aulas!', cor: '#ef4444' },
        { nome: 'Cenourinhas Baby', emoji: '🥕', desc: 'Crocantes e cheias de betacaroteno, protegem a visão e a pele!', cor: '#f97316' },
        { nome: 'Salada de Frutas', emoji: '🍓', desc: 'Uma explosão colorida de vitaminas C, A e sais minerais naturais!', cor: '#ec4899' },
        { nome: 'Pipoca Caseira', emoji: '🍿', desc: 'Pipoca de milho integral é rica em polifenóis e fibras do bem!', cor: '#eab308' },
        { nome: 'Banana & Aveia', emoji: '🍌', desc: 'Dá saciedade prolongada e potássio para correr muito no recreio!', cor: '#84cc16' },
        { nome: 'Sanduíche Colorido', emoji: '🥪', desc: 'Pão integral, pasta de ricota, cenoura e alface bem lavadinha!', cor: '#10b981' },
        { nome: 'Melancia Fatiada', emoji: '🍉', desc: 'Super hidratante e docinha, perfeita para os dias de calor!', cor: '#06b6d4' },
        { nome: 'Iogurte Natural', emoji: '🥣', desc: 'Cálcio para ossos fortes e probióticos amigos da digestão!', cor: '#8b5cf6' }
    ];

    const canvas = document.getElementById('roletaCanvas');
    const ctx = canvas.getContext('2d');
    const numSlices = lanches.length;
    const arc = (2 * Math.PI) / numSlices;
    let anguloAtual = 0;
    let girando = false;

    function desenharRoleta() {
        const radius = canvas.width / 2;
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        for (let i = 0; i < numSlices; i++) {
            const angle = anguloAtual + i * arc;
            ctx.fillStyle = lanches[i].cor;
            ctx.beginPath();
            ctx.arc(radius, radius, radius, angle, angle + arc, false);
            ctx.lineTo(radius, radius);
            ctx.fill();

            // Borda interna suave
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = 3;
            ctx.stroke();

            // Texto & Emoji
            ctx.save();
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 13px Inter, sans-serif';
            ctx.translate(radius, radius);
            ctx.rotate(angle + arc / 2);
            ctx.textAlign = 'right';
            ctx.fillText(`${lanches[i].emoji} ${lanches[i].nome}`, radius - 20, 5);
            ctx.restore();
        }

        // Círculo central
        ctx.beginPath();
        ctx.arc(radius, radius, 28, 0, 2 * Math.PI);
        ctx.fillStyle = '#ffffff';
        ctx.fill();
        ctx.strokeStyle = '#f59e0b';
        ctx.lineWidth = 5;
        ctx.stroke();

        ctx.font = '20px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('⭐', radius, radius);
    }

    function girarRoleta() {
        if (girando) return;
        girando = true;
        document.getElementById('btnGirarRoleta').disabled = true;
        document.getElementById('resultadoRoleta').classList.add('hidden');

        window.appAudio?.playPop();

        const voltas = 5 + Math.floor(Math.random() * 5); // 5 a 10 voltas completas
        const grausExtras = Math.floor(Math.random() * 360);
        const anguloTotal = (voltas * 360 + grausExtras) * (Math.PI / 180);

        const duracao = 4000;
        const inicio = performance.now();
        const anguloInicial = anguloAtual;

        let ultimoTick = 0;

        function animar(tempoAtual) {
            const decorrido = tempoAtual - inicio;
            const progresso = Math.min(decorrido / duracao, 1);

            // Easing Ease-Out Cubic
            const easeOut = 1 - Math.pow(1 - progresso, 3);
            anguloAtual = anguloInicial + anguloTotal * easeOut;

            // Som de Tick
            if (decorrido - ultimoTick > 120 * (1 + easeOut * 2)) {
                window.appAudio?.playTick();
                ultimoTick = decorrido;
            }

            desenharRoleta();

            if (progresso < 1) {
                requestAnimationFrame(animar);
            } else {
                girando = false;
                document.getElementById('btnGirarRoleta').disabled = false;
                finalizarGiro();
            }
        }

        requestAnimationFrame(animar);
    }

    function finalizarGiro() {
        // Calcula o índice onde o ponteiro do topo (-90 graus ou 3*PI/2) parou
        const radius = canvas.width / 2;
        const anguloNormalizado = (anguloAtual % (2 * Math.PI) + 2 * Math.PI) % (2 * Math.PI);
        
        // O ponteiro está no topo: 3*PI/2 (270 graus)
        const anguloPonteiro = (3 * Math.PI / 2);
        let anguloRelativo = (anguloPonteiro - anguloNormalizado + 2 * Math.PI) % (2 * Math.PI);
        const index = Math.floor(anguloRelativo / arc);

        const premiado = lanches[index % numSlices];

        window.appAudio?.playFanfare();
        window.launchConfetti?.(80);

        document.getElementById('resultadoEmoji').textContent = premiado.emoji;
        document.getElementById('resultadoTitulo').textContent = premiado.nome;
        document.getElementById('resultadoDescricao').textContent = premiado.desc;
        document.getElementById('resultadoRoleta').classList.remove('hidden');
    }

    // Inicializa a roleta ao carregar
    desenharRoleta();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
