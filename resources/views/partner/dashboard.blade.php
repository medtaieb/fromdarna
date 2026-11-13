@extends('layouts.portal')
@section('role', 'partner')
@section('title', 'Tableau de bord partenaire')

@push('styles')
<style>
    /* Améliorations de l'interface partenaire */
    .partner-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        padding-bottom: 6px;
    }
    .partner-brand {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .partner-avatar {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: linear-gradient(135deg, var(--rust), #B5692A);
        display: grid;
        place-items: center;
        font-size: 26px;
        box-shadow: 0 6px 16px var(--sh);
        flex-shrink: 0;
    }
    .live-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 5px 12px;
        border-radius: 99px;
        background: #E3E8C4;
        color: #363C14;
        font-size: 13px;
        font-weight: 700;
    }
    .live-pulse-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #2E7D32;
        box-shadow: 0 0 0 0 rgba(46, 125, 50, 0.7);
        animation: pulse-ring 1.8s infinite;
    }
    @keyframes pulse-ring {
        0% { box-shadow: 0 0 0 0 rgba(46, 125, 50, 0.7); }
        70% { box-shadow: 0 0 0 8px rgba(46, 125, 50, 0); }
        100% { box-shadow: 0 0 0 0 rgba(46, 125, 50, 0); }
    }
    .live-clock {
        font-variant-numeric: tabular-nums;
        font-weight: 600;
        color: var(--mut);
        font-size: 13.5px;
    }

    /* Carte Alerte Commande Urgente */
    .urgent-order-card {
        background: linear-gradient(135deg, #FFF1F0 0%, #FFF7F5 100%);
        border: 2px solid #FFA39E;
        border-radius: 20px;
        padding: 22px;
        box-shadow: 0 10px 28px rgba(196, 19, 23, 0.12);
        position: relative;
        overflow: hidden;
    }
    :root[data-theme="dark"] .urgent-order-card,
    :root:not([data-theme="light"]) .urgent-order-card {
        background: linear-gradient(135deg, #2D1A1A 0%, #241A18 100%);
        border-color: #7A2424;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.35);
    }
    .urgent-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .urgent-title-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .urgent-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--red);
        color: #fff;
        padding: 4px 10px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
        animation: urgent-glow 2s infinite ease-in-out;
    }
    @keyframes urgent-glow {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.03); opacity: 0.9; }
    }
    .countdown-box {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(196, 19, 23, 0.08);
        border: 1px solid rgba(196, 19, 23, 0.25);
        padding: 5px 12px;
        border-radius: 99px;
        font-size: 13.5px;
        font-weight: 700;
        color: var(--red);
    }
    .countdown-bar-wrap {
        width: 100%;
        height: 6px;
        background: rgba(196, 19, 23, 0.15);
        border-radius: 99px;
        overflow: hidden;
        margin-bottom: 16px;
    }
    .countdown-bar-inner {
        height: 100%;
        width: 100%;
        background: linear-gradient(90deg, #FA541C, var(--red));
        border-radius: 99px;
        transition: width 1s linear;
    }
    .urgent-body {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 20px;
        align-items: center;
    }
    @media (max-width: 820px) {
        .urgent-body { grid-template-columns: 1fr; }
    }
    .order-items-preview {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 12px 16px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .order-items-list {
        font-size: 14.5px;
        font-weight: 500;
    }
    .order-note {
        font-size: 13.5px;
        color: var(--mut);
        background: rgba(152, 85, 28, 0.08);
        padding: 6px 10px;
        border-radius: 8px;
        margin-top: 4px;
        border-left: 3px solid var(--rust);
    }
    .urgent-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    @media (max-width: 820px) {
        .urgent-actions { justify-content: flex-start; }
    }

    /* Grille de stats raffinée */
    .stat-card-pro {
        position: relative;
        overflow: hidden;
        transition: transform .2s, box-shadow .2s;
    }
    .stat-card-pro:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px var(--sh);
    }
    .stat-card-pro .stat-icon {
        position: absolute;
        top: 18px;
        right: 18px;
        font-size: 26px;
        opacity: 0.85;
    }
    .stat-card-pro.urgent {
        border-color: #FFA39E;
        background: linear-gradient(135deg, var(--card), #FFF5F5);
    }
    :root[data-theme="dark"] .stat-card-pro.urgent,
    :root:not([data-theme="light"]) .stat-card-pro.urgent {
        border-color: #7A2424;
        background: linear-gradient(135deg, var(--card), #2D1A1A);
    }

    /* Filtrage interactif des commandes */
    .table-header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 6px;
    }
    .filter-chips {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 99px;
        border: 1.5px solid var(--line);
        background: var(--card);
        color: var(--tx);
        font-size: 13.5px;
        font-weight: 500;
        cursor: pointer;
        transition: all .2s;
    }
    .filter-chip:hover {
        border-color: var(--rust);
    }
    .filter-chip.active {
        background: var(--rust);
        border-color: var(--rust);
        color: #fff;
        box-shadow: 0 4px 12px var(--sh);
    }
    .chip-count {
        padding: 1px 7px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 700;
        background: rgba(0,0,0,.08);
    }
    .filter-chip.active .chip-count {
        background: rgba(255,255,255,.25);
        color: #fff;
    }

    /* Cellules du tableau */
    .order-id-badge {
        font-weight: 800;
        font-size: 15px;
        letter-spacing: .02em;
    }
    .order-time {
        font-size: 12.5px;
        color: var(--mut);
        margin-top: 2px;
    }
    .courier-info {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .courier-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 13px;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
    {{-- En-tête Partenaire & Contrôles de service --}}
    <div class="partner-header">
        <div class="partner-brand">
            <div class="partner-avatar" aria-hidden="true">🍲</div>
            <div>
                <h1 style="display:flex;align-items:center;gap:10px;margin-bottom:3px">
                    Dar Zitouna
                    <span class="live-status-pill">
                        <i class="live-pulse-dot"></i> Connecté en direct
                    </span>
                </h1>
                <div class="mut">
                    Grombalia Centre · Cuisine du terroir · <span class="live-clock" id="liveClock">12:45:00</span>
                </div>
            </div>
        </div>

        <div class="acts">
            <button class="sw" type="button" id="storeSwitch" aria-pressed="true" data-on="Ouvert aux commandes" data-off="Fermé temporairement">
                <i></i>
                <span class="lbl" style="font-weight:700">Ouvert aux commandes</span>
            </button>
            <button class="btn ghost sm" type="button" data-custom-beep id="beepBtn" title="Tester l'alerte sonore des commandes">
                🔔 Tester le son
            </button>
            <x-btn variant="ghost" size="sm" :href="route('partner.products')">
                📋 Gérer le menu
            </x-btn>
        </div>
    </div>

    {{-- Bannière d'alerte urgente : Nouvelle commande reçue --}}
    <div class="urgent-order-card" role="alert" id="urgentCard">
        <div class="urgent-top">
            <div class="urgent-title-group">
                <span class="urgent-tag">⚡ Nouvelle commande</span>
                <span style="font-size:17px;font-weight:800;color:var(--tx)">#FD-1043</span>
                <span class="bd o" style="font-size:14px;font-weight:800">24,500 TND</span>
            </div>
            <div class="countdown-box">
                ⏱️ À accepter dans <b id="orderCountdown" data-countdown="60">60 s</b>
            </div>
        </div>

        <div class="countdown-bar-wrap">
            <div class="countdown-bar-inner" id="countdownBar"></div>
        </div>

        <div class="urgent-body">
            <div class="order-items-preview">
                <div class="order-items-list">
                    <strong>Client : Sana M.</strong> · <span>2 articles commandés</span>
                </div>
                <div class="mut" style="font-size:14px;line-height:1.4">
                    • 1 × Couscous à l’agneau (18,000 TND)<br>
                    • 2 × Brik à l’œuf (7,000 TND)
                </div>
                <div class="order-note">
                    💬 Remarque : « Sans piment, s’il vous plaît. »
                </div>
            </div>

            <div class="urgent-actions">
                <a class="btn red sm" href="{{ route('partner.orders.show', 1043) }}">
                    Ouvrir la commande
                </a>
                <a class="btn olive sm" href="{{ route('partner.orders.show', 1043) }}#accept" title="Accepter directement avec 15 min de préparation">
                    ✓ Accepter (15 min)
                </a>
            </div>
        </div>
    </div>

    {{-- Statistiques et indicateurs clés en temps réel --}}
    <div class="grid g4">
        <div class="card stat stat-card-pro">
            <span class="stat-icon">📦</span>
            <span class="mut">Commandes aujourd’hui</span>
            <b>14</b>
            <span class="mut">3 en cours · 284,500 TND</span>
        </div>

        <div class="card stat stat-card-pro urgent">
            <span class="stat-icon">🚨</span>
            <span class="mut" style="color:var(--red);font-weight:700">À accepter</span>
            <b style="color:var(--red)">1</b>
            <span style="color:var(--red);font-size:13px;font-weight:600">Action requise &lt; 60 s</span>
        </div>

        <div class="card stat stat-card-pro">
            <span class="stat-icon">🍳</span>
            <span class="mut">En préparation</span>
            <b>1</b>
            <span class="mut">Moyenne : 17 min (cible 15)</span>
        </div>

        <div class="card stat stat-card-pro">
            <span class="stat-icon">🛵</span>
            <span class="mut">Prêtes à récupérer</span>
            <b>1</b>
            <span class="mut">Livreur Walid T. en approche</span>
        </div>
    </div>

    {{-- Gestion des commandes avec filtres et tableau riche --}}
    <x-card>
        <div class="table-header-bar">
            <div>
                <h2>Commandes en cours</h2>
                <div class="mut" style="font-size:13.5px;margin-top:2px">
                    Suivi direct des préparations et des récupérations coursiers
                </div>
            </div>

            <div class="filter-chips">
                <button type="button" class="filter-chip active" data-filter="all">
                    Toutes <span class="chip-count">3</span>
                </button>
                <button type="button" class="filter-chip" data-filter="pending">
                    À accepter <span class="chip-count" style="background:#FBD9D9;color:#9E0F12">1</span>
                </button>
                <button type="button" class="filter-chip" data-filter="ready">
                    Prêtes <span class="chip-count" style="background:#E3E8C4;color:#363C14">1</span>
                </button>
                <button type="button" class="filter-chip" data-filter="delivered">
                    Livrées <span class="chip-count">1</span>
                </button>
            </div>
        </div>

        <x-table :headers="['Commande', 'Client & Articles', 'Total', 'Statut', 'Livreur', 'Actions']">
            {{-- Commande 1 : À accepter --}}
            <tr data-status-row="pending">
                <td>
                    <span class="order-id-badge" style="color:var(--rust)">#FD-1043</span>
                    <div class="order-time">12:44 · Il y a 2 min</div>
                </td>
                <td>
                    <strong>Sana M.</strong>
                    <div class="mut" style="font-size:13px">1× Couscous à l'agneau, 2× Brik</div>
                </td>
                <td>
                    <strong style="font-size:15px">24,500 TND</strong>
                </td>
                <td>
                    <x-badge type="o">⚡ À accepter</x-badge>
                </td>
                <td>
                    <span class="mut" style="font-size:13px">— En attente acceptation</span>
                </td>
                <td>
                    <x-btn variant="red" size="sm" :href="route('partner.orders.show', 1043)">
                        Traiter
                    </x-btn>
                </td>
            </tr>

            {{-- Commande 2 : Prête --}}
            <tr data-status-row="ready">
                <td>
                    <span class="order-id-badge">#FD-1041</span>
                    <div class="order-time">12:33 · Il y a 13 min</div>
                </td>
                <td>
                    <strong>Rim K.</strong>
                    <div class="mut" style="font-size:13px">Ojja merguez, Thé à la menthe</div>
                </td>
                <td>
                    <strong style="font-size:15px">31,000 TND</strong>
                </td>
                <td>
                    <x-badge type="g">✓ Prête</x-badge>
                </td>
                <td>
                    <div class="courier-info">
                        <span class="courier-badge" style="color:var(--olive)">
                            🛵 Walid T. <span class="mut">(en approche)</span>
                        </span>
                    </div>
                </td>
                <td>
                    <x-btn variant="ghost" size="sm" :href="route('partner.orders.show', 1041)">
                        Ouvrir
                    </x-btn>
                </td>
            </tr>

            {{-- Commande 3 : Livrée --}}
            <tr data-status-row="delivered">
                <td>
                    <span class="order-id-badge">#FD-1039</span>
                    <div class="order-time">12:02 · Il y a 44 min</div>
                </td>
                <td>
                    <strong>Ines L.</strong>
                    <div class="mut" style="font-size:13px">Lablabi, 2× Brik, Boisson</div>
                </td>
                <td>
                    <strong style="font-size:15px">28,000 TND</strong>
                </td>
                <td>
                    <x-badge type="x">Livrée</x-badge>
                </td>
                <td>
                    <div class="courier-info">
                        <span class="courier-badge mut">
                            ✓ Marwen A. (livré)
                        </span>
                    </div>
                </td>
                <td>
                    <x-btn variant="ghost" size="sm" :href="route('partner.orders.show', 1039)">
                        Détails
                    </x-btn>
                </td>
            </tr>
        </x-table>
    </x-card>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Horloge en direct
    const clockEl = document.getElementById('liveClock');
    if (clockEl) {
        const updateClock = () => {
            const now = new Date();
            clockEl.textContent = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        };
        updateClock();
        setInterval(updateClock, 1000);
    }

    // 2. Barre de progression du compte à rebours
    const countdownEl = document.getElementById('orderCountdown');
    const barEl = document.getElementById('countdownBar');
    if (countdownEl && barEl) {
        let maxSec = 60;
        let currentSec = 60;
        const interval = setInterval(() => {
            currentSec--;
            if (currentSec >= 0) {
                countdownEl.textContent = currentSec + ' s';
                const percent = Math.max(0, (currentSec / maxSec) * 100);
                barEl.style.width = percent + '%';
                if (currentSec <= 15) {
                    barEl.style.background = 'var(--red)';
                }
            } else {
                clearInterval(interval);
                countdownEl.textContent = 'Délai dépassé';
                barEl.style.width = '0%';
            }
        }, 1000);
    }

    // 3. Carillon sonore réaliste (Web Audio API)
    const playChime = () => {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();
            
            // Séquence harmonique agréable (style caisse enregistreuse / cuisine moderne)
            const notes = [587.33, 880.00, 1174.66]; // D5, A5, D6
            const times = [0, 0.12, 0.24];

            notes.forEach((freq, idx) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(freq, ctx.currentTime + times[idx]);
                
                gain.gain.setValueAtTime(0.001, ctx.currentTime + times[idx]);
                gain.gain.exponentialRampToValueAtTime(0.2, ctx.currentTime + times[idx] + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + times[idx] + 0.45);
                
                osc.connect(gain);
                gain.connect(ctx.destination);
                
                osc.start(ctx.currentTime + times[idx]);
                osc.stop(ctx.currentTime + times[idx] + 0.5);
            });
        } catch (e) {
            console.error('Audio error:', e);
        }
    };

    const customBeep = document.getElementById('beepBtn');
    if (customBeep) {
        customBeep.addEventListener('click', () => {
            playChime();
        });
    }

    // 4. Filtrage dynamique par statut
    const filterChips = document.querySelectorAll('.filter-chip');
    const rows = document.querySelectorAll('[data-status-row]');
    
    filterChips.forEach(chip => {
        chip.addEventListener('click', () => {
            filterChips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            
            const filter = chip.dataset.filter;
            rows.forEach(row => {
                if (filter === 'all' || row.dataset.statusRow === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
});
</script>
@endpush


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Horloge en direct
    const clockEl = document.getElementById('liveClock');
    if (clockEl) {
        const updateClock = () => {
            const now = new Date();
            clockEl.textContent = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        };
        updateClock();
        setInterval(updateClock, 1000);
    }

    // 2. Barre de progression du compte à rebours
    const countdownEl = document.getElementById('orderCountdown');
    const barEl = document.getElementById('countdownBar');
    if (countdownEl && barEl) {
        let maxSec = 60;
        let currentSec = 60;
        const interval = setInterval(() => {
            currentSec--;
            if (currentSec >= 0) {
                countdownEl.textContent = currentSec + ' s';
                const percent = Math.max(0, (currentSec / maxSec) * 100);
                barEl.style.width = percent + '%';
                if (currentSec <= 15) {
                    barEl.style.background = 'var(--red)';
                }
            } else {
                clearInterval(interval);
                countdownEl.textContent = 'Délai dépassé';
                barEl.style.width = '0%';
            }
        }, 1000);
    }

    // 3. Carillon sonore réaliste (Web Audio API)
    const playChime = () => {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();
            
            // Séquence harmonique agréable (style caisse enregistreuse / cuisine moderne)
            const notes = [587.33, 880.00, 1174.66]; // D5, A5, D6
            const times = [0, 0.12, 0.24];

            notes.forEach((freq, idx) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(freq, ctx.currentTime + times[idx]);
                
                gain.gain.setValueAtTime(0.001, ctx.currentTime + times[idx]);
                gain.gain.exponentialRampToValueAtTime(0.2, ctx.currentTime + times[idx] + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + times[idx] + 0.45);
                
                osc.connect(gain);
                gain.connect(ctx.destination);
                
                osc.start(ctx.currentTime + times[idx]);
                osc.stop(ctx.currentTime + times[idx] + 0.5);
            });
        } catch (e) {
            console.error('Audio error:', e);
        }
    };

    const customBeep = document.getElementById('beepBtn');
    if (customBeep) {
        customBeep.addEventListener('click', () => {
            playChime();
        });
    }

    // 4. Filtrage dynamique par statut
    const filterChips = document.querySelectorAll('.filter-chip');
    const rows = document.querySelectorAll('[data-status-row]');
    
    filterChips.forEach(chip => {
        chip.addEventListener('click', () => {
            filterChips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            
            const filter = chip.dataset.filter;
            rows.forEach(row => {
                if (filter === 'all' || row.dataset.statusRow === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
});
</script>
@endpush