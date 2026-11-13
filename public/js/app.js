/* Cooked by Us · interactions légères (menu, quantités, interrupteurs, OTP, minuteries, son) */
(() => {
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const fmt = m => (m / 1000).toFixed(3).replace('.', ',') + ' TND';

  $$('[data-menu]').forEach(b => b.addEventListener('click', () =>
    document.getElementById('shell')?.classList.toggle('open')));

  // Quantités : [data-qty data-price="millimes"] + <output>, totaux [data-total] [data-count] [data-grand data-fee]
  function recalc() {
    let n = 0, t = 0;
    $$('[data-qty]').forEach(q => {
      const v = +q.querySelector('output').textContent;
      n += v; t += v * +q.dataset.price;
      const line = q.closest('[data-line]')?.querySelector('[data-line-total]');
      if (line) line.textContent = fmt(v * +q.dataset.price);
    });
    $$('[data-count]').forEach(e => (e.textContent = n));
    $$('[data-total]').forEach(e => (e.textContent = fmt(t)));
    $$('[data-grand]').forEach(e => (e.textContent = fmt(t + (+e.dataset.fee || 0))));
  }
  $$('[data-qty]').forEach(q => q.addEventListener('click', e => {
    const b = e.target.closest('button'); if (!b) return;
    const o = q.querySelector('output');
    o.textContent = Math.max(0, Math.min(20, +o.textContent + (b.dataset.step === '+' ? 1 : -1)));
    recalc();
  }));
  recalc();

  // Interrupteurs : <button class="sw" aria-pressed data-on="Ouvert" data-off="Fermé">
  $$('.sw').forEach(b => b.addEventListener('click', () => {
    const on = b.getAttribute('aria-pressed') !== 'true';
    b.setAttribute('aria-pressed', on);
    const l = b.querySelector('.lbl'); if (l && b.dataset.on) l.textContent = on ? b.dataset.on : b.dataset.off;
  }));

  // Code OTP à 6 cases
  $$('.otp').forEach(g => {
    const i = [...g.querySelectorAll('input')];
    i.forEach((x, k) => {
      x.addEventListener('input', () => { x.value = x.value.replace(/\D/g, '').slice(0, 1); if (x.value && i[k + 1]) i[k + 1].focus(); });
      x.addEventListener('keydown', e => { if (e.key === 'Backspace' && !x.value && i[k - 1]) i[k - 1].focus(); });
    });
  });

  // Minuterie d'acceptation : <b data-countdown="60">
  $$('[data-countdown]').forEach(e => {
    let s = +e.dataset.countdown;
    const t = () => (e.textContent = s > 0 ? s + ' s' : 'délai dépassé');
    t(); const h = setInterval(() => { s--; t(); if (s <= 0) clearInterval(h); }, 1000);
  });

  // Test du son (doit suivre une interaction pour être autorisé par le navigateur)
  $$('[data-beep]').forEach(b => b.addEventListener('click', () => {
    const C = window.AudioContext || window.webkitAudioContext; if (!C) return;
    const c = new C(), o = c.createOscillator(), g = c.createGain();
    o.connect(g); g.connect(c.destination); o.frequency.value = 880; g.gain.value = .15;
    o.start(); o.stop(c.currentTime + .4);
  }));

  $$('[data-confirm]').forEach(e => e.addEventListener('click', ev => { if (!confirm(e.dataset.confirm)) ev.preventDefault(); }));
})();
