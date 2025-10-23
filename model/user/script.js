(function () {
  const canvas = document.getElementById('embers-canvas');
  const ctx = canvas.getContext('2d', { alpha: true });
  let W = canvas.width = innerWidth;
  let H = canvas.height = innerHeight;
  const particles = [];
  const max = 140;
  function rand(min, max) { return Math.random() * (max - min) + min; }
  function create() {
    const p = {
      x: rand(0, W),
      y: rand(-H, H),
      vx: rand(-0.05, 0.5),
      vy: rand(0.2, 1.4),
      size: rand(0.6, 3.6),
      life: rand(40, 220),
      hue: rand(10, 25),
      alpha: rand(0.08, 0.7),
      flicker: Math.random() > 0.6
    };
    particles.push(p);
  }
  for (let i = 0; i < max; i++) create();
  function resize() { W = canvas.width = innerWidth; H = canvas.height = innerHeight; }
  addEventListener('resize', resize);
  function draw() {
    ctx.clearRect(0, 0, W, H);
    for (let i = 0; i < particles.length; i++) {
      const p = particles[i];
      p.x += p.vx;
      p.y += p.vy;
      p.life--;
      if (p.x > W + 50) p.x = -50;
      if (p.y > H + 50) {
        p.y = rand(-100, -10);
        p.x = rand(0, W);
        p.life = rand(40, 220);
      }
      const g = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, p.size * 10);
      g.addColorStop(0, `rgba(${220},${40},${10},${p.alpha})`);
      g.addColorStop(0.3, `rgba(${255},${120},${40},${p.alpha * 0.18})`);
      g.addColorStop(1, `rgba(0,0,0,0)`);
      ctx.beginPath();
      ctx.fillStyle = g;
      ctx.arc(p.x, p.y, p.size * 6, 0, Math.PI * 2);
      ctx.fill();
      if (Math.random() > 0.2) {
        ctx.beginPath();
        const coreAlpha = p.alpha * (p.flicker ? (Math.random() * 0.8 + 0.2) : 1);
        ctx.fillStyle = `rgba(255,${Math.floor(100 + Math.random() * 120)},${Math.floor(30 + Math.random() * 60)},${coreAlpha})`;
        ctx.arc(p.x + Math.sin(p.life / 10) * 0.5, p.y, p.size, 0, Math.PI * 2);
        ctx.fill();
      }
    }
    while (particles.length < max) create();
    requestAnimationFrame(draw);
  }
  draw();
})();

document.getElementById('enterBtn').addEventListener('click', function () {
  this.animate([
    { transform: 'translateY(0) scale(1)', boxShadow: '0 12px 40px rgba(255,60,60,0.24)' },
    { transform: 'translateY(-6px) scale(1.02)', boxShadow: '0 22px 60px rgba(255,60,60,0.34)' },
    { transform: 'translateY(0) scale(1)', boxShadow: '0 12px 40px rgba(255,60,60,0.24)' }
  ], { duration: 380, easing: 'cubic-bezier(.2,.9,.3,1)' });
  flashScreen();
  console.log('Entrando al Infierno...');
});

function flashScreen() {
  const flash = document.createElement('div');
  flash.style.position = 'fixed';
  flash.style.inset = '0';
  flash.style.zIndex = 9999;
  flash.style.pointerEvents = 'none';
  flash.style.background = 'radial-gradient(circle at 50% 30%, rgba(255,180,120,0.9), rgba(255,60,60,0.2) 30%, rgba(0,0,0,0) 60%)';
  flash.style.opacity = '0';
  document.body.appendChild(flash);
  flash.animate([{ opacity: 0 }, { opacity: 1 }, { opacity: 0 }], { duration: 600 }).onfinish = () => flash.remove();
}