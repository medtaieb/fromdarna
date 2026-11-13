/* FromDarna · prototype — JS vanille, aucune dépendance. État du panier dans localStorage. */
(() => {
  const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const fmt = (m) => (m / 1000).toFixed(3).replace('.', ',') + ' TND';
  const toast = (t) => { const el = $('#toast'); if (!el) return; el.textContent = t; el.classList.add('show'); clearTimeout(toast.t); toast.t = setTimeout(() => el.classList.remove('show'), 2200); };
  const load = () => { try { return JSON.parse(localStorage.getItem('fd_cart') || '{}'); } catch (e) { return {}; } };
  const save = (c) => { try { localStorage.setItem('fd_cart', JSON.stringify(c)); } catch (e) {} badge(); };
  const badge = () => { const n = Object.values(load()).reduce((a, i) => a + i.q, 0); $$('.badge').forEach((b) => { b.textContent = n; b.classList.toggle('on', n > 0); }); };

  /* index d'apparition + compteurs + barres */
  $$('.reveal>*,.mn>*').forEach((el, i) => el.style.setProperty('--i', i));
  const countUp = (el) => { const to = parseFloat(el.dataset.count), dec = +(el.dataset.dec || 0), suf = el.dataset.suffix || '', t0 = performance.now(), d = 1100;
    const f = (n) => { const p = Math.min(1, (n - t0) / d), e = 1 - Math.pow(1 - p, 3); el.textContent = (to * e).toFixed(dec).replace('.', ',') + suf; if (p < 1) requestAnimationFrame(f); }; requestAnimationFrame(f); };
  $$('[data-count]').forEach(countUp);
  setTimeout(() => $$('.bar>i[data-w]').forEach((b) => { b.style.width = b.dataset.w + '%'; if (+b.dataset.w > 85) b.classList.add('warn'); }), 250);

  /* langue FR / AR */
  const setLang = (l) => { document.documentElement.dir = l === 'ar' ? 'rtl' : 'ltr'; document.documentElement.lang = l;
    $$('[data-ar]').forEach((el) => { if (!el.dataset.fr) el.dataset.fr = el.textContent; el.textContent = l === 'ar' ? el.dataset.ar : el.dataset.fr; });
    $$('[data-lang]').forEach((b) => b.classList.toggle('on', b.dataset.lang === l)); try { localStorage.setItem('fd_lang', l); } catch (e) {} };
  $$('[data-lang]').forEach((b) => b.addEventListener('click', () => setLang(b.dataset.lang)));
  try { const l = localStorage.getItem('fd_lang'); if (l === 'ar') setLang('ar'); } catch (e) {}

  /* OTP */
  const otp = $$('.otp input');
  otp.forEach((inp, i) => { inp.addEventListener('input', () => { inp.value = inp.value.replace(/\D/g, '').slice(0, 1); if (inp.value && otp[i + 1]) otp[i + 1].focus(); });
    inp.addEventListener('keydown', (e) => { if (e.key === 'Backspace' && !inp.value && otp[i - 1]) otp[i - 1].focus(); }); });
  const go = $('#otp-go'); if (go) go.addEventListener('click', () => { go.classList.add('loading'); toast('Code vérifié'); setTimeout(() => (location.href = go.dataset.next), 900); });

  /* chips catégories (page restaurant) */
  $$('.chip[data-cat]').forEach((c) => c.addEventListener('click', () => { $$('.chip[data-cat]').forEach((x) => x.classList.remove('on')); c.classList.add('on');
    $$('.dish').forEach((d) => { const show = c.dataset.cat === 'all' || d.dataset.cat === c.dataset.cat; d.style.display = show ? '' : 'none'; if (show) d.style.animation = 'up .4s ease both'; }); }));

  /* ajout au panier */
  $$('.add').forEach((b) => b.addEventListener('click', () => { const c = load(), id = b.dataset.id; c[id] = c[id] || { n: b.dataset.name, p: +b.dataset.price, q: 0 }; c[id].q++; save(c); toast(b.dataset.name + ' ajouté'); b.animate([{ transform: 'scale(1)' }, { transform: 'scale(1.15)' }, { transform: 'scale(1)' }], 250); }));
  badge();

  /* page panier */
  const list = $('#cart-list');
  const renderCart = () => { if (!list) return; const c = load(), ids = Object.keys(c); let sub = 0; list.innerHTML = '';
    ids.forEach((id) => { const i = c[id]; sub += i.p * i.q; const r = document.createElement('div'); r.className = 'row card up';
      r.innerHTML = `<div class="grow"><b>${i.n}</b><div class="mut">${fmt(i.p)}</div></div><div class="qty"><button class="o" aria-label="Retirer" data-d="${id}">−</button><b>${i.q}</b><button aria-label="Ajouter" data-u="${id}">+</button></div>`; list.appendChild(r); });
    const small = sub > 0 && sub < 10000; $('#cart-empty').style.display = ids.length ? 'none' : 'block'; $('#cart-sum').style.display = ids.length ? 'flex' : 'none';
    $('[data-sub]').textContent = fmt(sub); $('[data-small]').style.display = small ? 'flex' : 'none'; $('[data-total]').textContent = fmt(sub + (ids.length ? 3000 : 0) + (small ? 1000 : 0));
    $('#checkout').disabled = !ids.length; };
  if (list) { list.addEventListener('click', (e) => { const c = load(), u = e.target.dataset.u, d = e.target.dataset.d; if (u) c[u].q++; if (d) { c[d].q--; if (c[d].q <= 0) delete c[d]; } if (u || d) { save(c); renderCart(); } }); renderCart(); }
  const co = $('#checkout'); if (co) co.addEventListener('click', () => { co.classList.add('loading'); setTimeout(() => { save({}); toast('Commande envoyée'); location.href = co.dataset.next; }, 1200); });

  /* suivi animé */
  const steps = $$('.tl-step');
  if (steps.length) { const eta = ['≈ 35 min', '≈ 28 min', '≈ 20 min', '≈ 12 min', 'Livrée'], fill = $('.tl .fill'); let s = 0, timer;
    const draw = () => { steps.forEach((el, i) => { el.classList.toggle('done', i < s); el.classList.toggle('now', i === s); }); fill.style.height = (s / (steps.length - 1)) * 92 + '%'; $('[data-eta]').textContent = eta[s]; const o = $('[data-otp]'); o.style.display = s >= 3 && s < 4 ? 'flex' : 'none'; if (s === 4) { clearInterval(timer); toast('Bon appétit !'); } };
    const run = () => { s = 0; draw(); clearInterval(timer); timer = setInterval(() => { s++; draw(); }, 2800); }; run(); const rp = $('#replay'); if (rp) rp.addEventListener('click', run); }

  /* kanban partenaire */
  const kb = $$('[data-col]');
  if (kb.length) { const count = () => { $$('[data-colcount]').forEach((c) => (c.textContent = $(`[data-col="${c.dataset.colcount}"]`).children.length)); const n = $('[data-col="new"]').children.length; const bn = $('#banner'); if (bn) { bn.style.display = n ? 'flex' : 'none'; $('[data-newn]').textContent = n; } };
    const move = (card, col, html) => { card.classList.add('leaving'); setTimeout(() => { card.classList.remove('leaving'); card.querySelector('.acts').innerHTML = html; $(`[data-col="${col}"]`).appendChild(card); card.classList.add('arrive'); count(); }, 320); };
    document.addEventListener('click', (e) => { const a = e.target.closest('[data-act]'); if (!a) return; const card = a.closest('.kcard');
      if (a.dataset.act === 'accept') { move(card, 'prep', '<button class="btn block" data-act="ready">Marquer prête</button>'); toast(card.dataset.id + ' acceptée, prête dans 15 min'); }
      if (a.dataset.act === 'reject') { card.classList.add('leaving'); setTimeout(() => { card.remove(); count(); }, 320); toast(card.dataset.id + ' refusée'); }
      if (a.dataset.act === 'ready') { move(card, 'ready', '<span class="bd ok">En attente du livreur</span>'); toast(card.dataset.id + ' prête'); } });
    $$('#store-status button').forEach((b) => b.addEventListener('click', () => { $$('#store-status button').forEach((x) => x.classList.remove('on')); b.classList.add('on'); toast('Restaurant : ' + b.textContent); })); count(); }

  /* ruptures de menu */
  $$('input[data-row]').forEach((i) => i.addEventListener('change', () => { const r = i.closest('tr'); r.classList.toggle('out', !i.checked); $('.st', r).textContent = i.checked ? 'Disponible' : 'Indisponible'; toast(i.checked ? 'Produit remis en vente' : 'Produit indisponible'); }));

  /* dispatch Ops */
  $$('.assign').forEach((b) => b.addEventListener('click', () => { const tr = b.closest('tr'), sel = $('select', tr); if (!sel.value) { toast('Choisissez un livreur'); return; } $('.st', tr).className = 'st bd ok'; $('.st', tr).textContent = 'Offre envoyée à ' + sel.value; b.textContent = 'Réassigner'; toast('Course proposée à ' + sel.value); }));
  $$('[data-dismiss]').forEach((b) => b.addEventListener('click', () => { b.closest('.alert').classList.add('gone'); toast('Alerte traitée et journalisée'); }));

  /* filtre grand livre */
  $$('[data-filter] button').forEach((b) => b.addEventListener('click', () => { $$('[data-filter] button').forEach((x) => x.classList.remove('on')); b.classList.add('on'); $$('[data-type]').forEach((r) => (r.style.display = b.dataset.f === 'all' || r.dataset.type === b.dataset.f ? '' : 'none')); }));

  /* course livreur */
  const rs = $$('.ride-step'), nx = $('#ride-next');
  if (nx) { const labels = ['Arrivé au restaurant', 'Commande récupérée', 'Arrivé chez le client', 'Livrée : code client + cash encaissé', 'Course terminée'], held = $('[data-cash]'); let k = 1;
    const draw = () => { rs.forEach((r, i) => { r.classList.toggle('done', i < k); r.classList.toggle('now', i === k); }); nx.textContent = labels[Math.min(k, 4)]; if (k >= 4) { nx.classList.add('o'); nx.disabled = true; } };
    nx.addEventListener('click', () => { k++; draw(); if (k === 4) { held.dataset.count = 77900; held.dataset.dec = 0; held.textContent = fmt(77900); toast('Cash encaissé : 21,900 TND'); $('#paid').style.display = 'block'; } else toast('Étape enregistrée'); }); draw(); }
  const rm = $('#remit'); if (rm) rm.addEventListener('click', () => { rm.classList.add('loading'); setTimeout(() => { rm.classList.remove('loading'); $('[data-held]').textContent = fmt(0); $('.bar>i').style.width = '0%'; rm.disabled = true; rm.textContent = 'Remise enregistrée'; toast('Remise validée par Ops'); }, 1000); });
})();
