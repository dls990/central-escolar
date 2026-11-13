// Gerencia as interacoes da interface escolar.
document.addEventListener('DOMContentLoaded', () => {
    initScrollReveal();
});

// Anima cada elemento quando ele entra pela primeira vez na tela.
function initScrollReveal() {
    const targets = document.querySelectorAll('.scroll-reveal, .scroll-reveal-left, .scroll-reveal-right, .scroll-reveal-scale');
    if (!targets.length) return;

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-revealed');
                obs.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -40px 0px'
    });

    targets.forEach(el => observer.observe(el));
}

// Exibe avisos temporarios de sucesso, informacao ou erro.
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'toast-msg animate-scale-up';

    let icon = '🎉';
    let borderColor = '#22c55e';
    if (type === 'error') {
        icon = '⚠️';
        borderColor = '#ef4444';
    } else if (type === 'info') {
        icon = '💡';
        borderColor = '#3b82f6';
    }

    toast.style.borderLeftColor = borderColor;
    toast.innerHTML = `
        <span class="text-xl">${icon}</span>
        <span class="text-slate-700">${message}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(60px)';
        setTimeout(() => toast.remove(), 300);
    }, 3800);
}

// Salva uma presenca e atualiza linha, indicadores e grafico sem recarregar.
async function atualizarFrequencia(alunoId, status, data) {
    window.appAudio?.playPop();

    const row = document.getElementById(`aluno-row-${alunoId}`);
    if (row) {
        row.style.opacity = '0.6';
    }

    try {
        const formData = new FormData();
        formData.append('aluno_id', alunoId);
        formData.append('status', status);
        formData.append('data', data);

        const response = await fetch(`${BASE_URL}/frequencia/salvar`, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (row) {
            row.style.opacity = '1';
        }

        if (result.success) {
            window.appAudio?.playSuccess();
            atualizarLinhaFrequencia(alunoId, status, result.horario);
            atualizarCardsEstatisticas(result.stats);

            if (result.pizza && window.atualizarGraficoPizza) {
                window.atualizarGraficoPizza(result.pizza);
            }

            showToast(result.message);

            if (result.stats && result.stats.taxa_presenca === 100) {
                window.launchConfetti?.(80);
                window.appAudio?.playFanfare();
            }
        } else {
            showToast(result.message || 'Erro ao registrar frequência', 'error');
        }
    } catch (err) {
        console.error(err);
        if (row) row.style.opacity = '1';
        showToast('Erro de comunicação com o servidor', 'error');
    }
}

// Reflete a situacao escolhida no selo, horario e botoes do aluno.
function atualizarLinhaFrequencia(alunoId, status, horario) {
    const badge = document.getElementById(`badge-status-${alunoId}`);
    const timeSpan = document.getElementById(`horario-${alunoId}`);

    if (badge) {
        badge.className = 'px-3 py-1 rounded-xl text-xs font-bold capitalize transition-all duration-300';
        if (status === 'presente') {
            badge.classList.add('badge-presente');
            badge.innerHTML = '✓ Presente';
        } else if (status === 'ausente') {
            badge.classList.add('badge-ausente');
            badge.innerHTML = '✕ Ausente';
        } else {
            badge.classList.add('badge-justificado');
            badge.innerHTML = '• Justificado';
        }
    }

    if (timeSpan) {
        timeSpan.textContent = (status === 'presente' && horario) ? `Chegada: ${horario}` : '';
    }

    const buttons = document.querySelectorAll(`[data-aluno="${alunoId}"]`);
    buttons.forEach(btn => {
        const btnStatus = btn.dataset.status;
        if (btnStatus === status) {
            btn.className = `px-3 py-1.5 rounded-xl font-bold text-xs transition transform active:scale-90 text-white shadow-sm ring-2 ring-offset-1 ${
                status === 'presente' ? 'bg-emerald-500 ring-emerald-400' :
                (status === 'ausente' ? 'bg-rose-500 ring-rose-400' : 'bg-amber-500 ring-amber-400')
            }`;
        } else {
            btn.className = 'px-3 py-1.5 rounded-xl font-bold text-xs transition transform active:scale-90 bg-white text-slate-600 hover:bg-slate-100';
        }
    });
}

// Atualiza os totais de presenca mostrados nos cards da chamada.
function atualizarCardsEstatisticas(stats) {
    if (!stats) return;

    const elTaxa = document.getElementById('stat-taxa');
    const elPres = document.getElementById('stat-presentes');
    const elAus = document.getElementById('stat-ausentes');
    const elJust = document.getElementById('stat-justificados');

    if (elTaxa) elTaxa.textContent = `${stats.taxa_presenca}%`;
    if (elPres) elPres.textContent = stats.presentes;
    if (elAus) elAus.textContent = stats.ausentes;
    if (elJust) elJust.textContent = stats.justificados;
}

// Solicita confirmacao antes de registrar presenca para a turma filtrada.
async function marcarTodosPresentes(data, turma) {
    if (!confirm('Deseja marcar TODOS os alunos listados como presentes hoje?')) return;

    try {
        const formData = new FormData();
        formData.append('data', data);
        formData.append('turma', turma);
        formData.append('status', 'presente');

        const response = await fetch(`${BASE_URL}/frequencia/marcarTodos`, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            window.launchConfetti?.(70);
            window.appAudio?.playFanfare();
            showToast(result.message);
            setTimeout(() => location.reload(), 900);
        } else {
            showToast('Erro ao marcar presença em massa', 'error');
        }
    } catch (e) {
        console.error(e);
        showToast('Erro ao comunicar com o servidor', 'error');
    }
}

// Envia a contagem diaria e a observacao da inspetoria para a cozinha.
async function enviarParaCozinha(data) {
    const obsInput = document.getElementById('obsEnvioCozinha');
    const obs = obsInput ? obsInput.value : '';

    const btn = document.getElementById('btnEnviarCozinhaConfirm');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = 'Enviando dados... ⏳';
    }

    try {
        const formData = new FormData();
        formData.append('data', data);
        formData.append('observacao', obs);

        const response = await fetch(`${BASE_URL}/frequencia/enviarCozinha`, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            window.launchConfetti?.(90);
            window.appAudio?.playFanfare();
            showToast(result.message);

            const modal = document.getElementById('modalEnvioCozinha');
            if (modal) modal.classList.add('hidden');

            setTimeout(() => location.reload(), 1200);
        } else {
            showToast(result.message || 'Erro ao enviar para a cozinha', 'error');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '🚀 Confirmar Envio para a Cozinha';
            }
        }
    } catch (e) {
        console.error(e);
        showToast('Erro de conexão ao enviar para a cozinha', 'error');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '🚀 Confirmar Envio para a Cozinha';
        }
    }
}

// Registra o novo estado de preparo informado pela cozinha.
async function atualizarStatusCozinha(data, status, obs = '') {
    window.appAudio?.playPop();

    try {
        const formData = new FormData();
        formData.append('data', data);
        formData.append('status', status);
        formData.append('observacao_cozinha', obs);

        const response = await fetch(`${BASE_URL}/cozinha/atualizarStatus`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const result = await response.json();

        if (result.success) {
            window.appAudio?.playSuccess();
            showToast(result.message);
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('Erro ao atualizar status', 'error');
        }
    } catch (e) {
        console.error(e);
        showToast('Erro de comunicação com o servidor', 'error');
    }
}
