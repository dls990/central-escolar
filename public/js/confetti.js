// Cria efeitos visuais de celebracao.
class ConfettiEngine {
    constructor() {
        this.canvas = document.getElementById('confettiCanvas');
        if (!this.canvas) return;
        this.ctx = this.canvas.getContext('2d');
        this.particles = [];
        this.animationFrame = null;

        this.resize();
        window.addEventListener('resize', () => this.resize());
    }

    resize() {
        if (!this.canvas) return;
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
    }

    launch(count = 70) {
        if (!this.canvas) return;
        const colors = ['#22c55e', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6', '#10b981', '#fbbf24'];

        for (let i = 0; i < count; i++) {
            this.particles.push({
                x: window.innerWidth * (0.3 + Math.random() * 0.4),
                y: window.innerHeight * 0.4,
                w: Math.random() * 8 + 4,
                h: Math.random() * 12 + 6,
                color: colors[Math.floor(Math.random() * colors.length)],
                vx: (Math.random() - 0.5) * 16,
                vy: (Math.random() - 0.8) * 18,
                rotation: Math.random() * 360,
                rotationSpeed: (Math.random() - 0.5) * 15,
                opacity: 1,
                decay: Math.random() * 0.015 + 0.008
            });
        }

        if (!this.animationFrame) {
            this.animate();
        }
    }

    animate() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        for (let i = this.particles.length - 1; i >= 0; i--) {
            const p = this.particles[i];
            p.x += p.vx;
            p.y += p.vy;
            p.vy += 0.4; // Gravidade
            p.vx *= 0.98; // Resistência do ar
            p.rotation += p.rotationSpeed;
            p.opacity -= p.decay;

            if (p.opacity <= 0 || p.y > this.canvas.height) {
                this.particles.splice(i, 1);
                continue;
            }

            this.ctx.save();
            this.ctx.translate(p.x, p.y);
            this.ctx.rotate((p.rotation * Math.PI) / 180);
            this.ctx.globalAlpha = p.opacity;
            this.ctx.fillStyle = p.color;
            this.ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
            this.ctx.restore();
        }

        if (this.particles.length > 0) {
            this.animationFrame = requestAnimationFrame(() => this.animate());
        } else {
            this.animationFrame = null;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.confetti = new ConfettiEngine();
    window.launchConfetti = (count) => window.confetti?.launch(count);
});
