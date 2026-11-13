// Reproduz os efeitos sonoros da interface.
class SoundEffects {
    // Guarda o estado do som e prepara o contexto de audio sob demanda.
    constructor() {
        this.ctx = null;
        this.muted = localStorage.getItem('edunutri_muted') === 'true';
        this.updateIcon();
    }

    // Cria ou reativa o contexto exigido pelo navegador para tocar sons.
    init() {
        if (!this.ctx) {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            this.ctx = new AudioContext();
        }
        if (this.ctx.state === 'suspended') {
            this.ctx.resume();
        }
    }

    // Alterna o silencio, persiste a escolha e atualiza o icone.
    toggleMute() {
        this.muted = !this.muted;
        localStorage.setItem('edunutri_muted', this.muted);
        this.updateIcon();
        if (!this.muted) {
            this.playPop();
        }
    }

    // Atualiza o indicador visual de som ligado ou desligado.
    updateIcon() {
        const icon = document.getElementById('soundIcon');
        if (icon) {
            icon.textContent = this.muted ? '🔇' : '🔊';
        }
    }

    // Toca um sinal curto usado em cliques e mudancas de interface.
    playPop() {
        if (this.muted) return;
        this.init();

        const osc = this.ctx.createOscillator();
        const gain = this.ctx.createGain();

        osc.type = 'sine';
        osc.frequency.setValueAtTime(450, this.ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(800, this.ctx.currentTime + 0.08);

        gain.gain.setValueAtTime(0.2, this.ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, this.ctx.currentTime + 0.08);

        osc.connect(gain);
        gain.connect(this.ctx.destination);

        osc.start();
        osc.stop(this.ctx.currentTime + 0.08);
    }

    // Toca tres notas para indicar uma operacao concluida.
    playSuccess() {
        if (this.muted) return;
        this.init();

        const notes = [523.25, 659.25, 783.99]; // Acorde Maior C5, E5, G5
        notes.forEach((freq, index) => {
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();

            osc.type = 'triangle';
            osc.frequency.setValueAtTime(freq, this.ctx.currentTime + index * 0.07);

            gain.gain.setValueAtTime(0.18, this.ctx.currentTime + index * 0.07);
            gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + index * 0.07 + 0.25);

            osc.connect(gain);
            gain.connect(this.ctx.destination);

            osc.start(this.ctx.currentTime + index * 0.07);
            osc.stop(this.ctx.currentTime + index * 0.07 + 0.25);
        });
    }

    // Toca uma sequencia mais longa para celebrar uma acao.
    playFanfare() {
        if (this.muted) return;
        this.init();

        const notes = [523.25, 659.25, 783.99, 1046.50]; // C5, E5, G5, C6
        notes.forEach((freq, index) => {
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();

            osc.type = 'square';
            const startTime = this.ctx.currentTime + index * 0.1;
            osc.frequency.setValueAtTime(freq, startTime);

            gain.gain.setValueAtTime(0.12, startTime);
            gain.gain.exponentialRampToValueAtTime(0.001, startTime + 0.35);

            osc.connect(gain);
            gain.connect(this.ctx.destination);

            osc.start(startTime);
            osc.stop(startTime + 0.35);
        });
    }

    // Toca um clique leve usado como marcacao durante a roleta.
    playTick() {
        if (this.muted) return;
        this.init();

        const osc = this.ctx.createOscillator();
        const gain = this.ctx.createGain();

        osc.type = 'triangle';
        osc.frequency.setValueAtTime(900, this.ctx.currentTime);
        gain.gain.setValueAtTime(0.06, this.ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + 0.03);

        osc.connect(gain);
        gain.connect(this.ctx.destination);

        osc.start();
        osc.stop(this.ctx.currentTime + 0.03);
    }
}

window.appAudio = new SoundEffects();
