@extends('layouts.portal')
@section('role', 'partner')
@section('title', 'Console Cuisine · ' . $store->name)

@push('styles')
<style>
    .kitchen-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        padding-bottom: 6px;
    }
    .kitchen-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        margin: 16px 0;
    }
    .order-card {
        border-radius: 16px;
        border: 1.5px solid var(--line);
        padding: 16px;
        background: var(--card);
        margin-bottom: 12px;
        transition: transform .2s, box-shadow .2s;
    }
    .order-card.urgent {
        border-color: #C41317;
        background: rgba(196, 19, 23, 0.03);
    }
    .order-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        border-bottom: 1px solid var(--line);
        padding-bottom: 10px;
        margin-bottom: 12px;
    }
    .order-items-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 14px;
    }
    .order-item-row {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
    }
    .order-actions-bar {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }
    .countdown-badge {
        font-variant-numeric: tabular-nums;
        font-weight: 800;
        font-size: 15px;
    }
</style>
@endpush

@section('content')
{{-- ── En-tête de la Console Cuisine ── --}}
<div class="kitchen-header">
    <div>
        <h1 style="margin:0;font-size:24px">{{ $store->emoji }} {{ $store->name }}</h1>
        <div class="mut" style="font-size:13px;margin-top:2px">
            Zone : <strong>{{ $store->zone->name ?? 'Grombalia' }}</strong> · Prép. moyenne : <strong>{{ $store->avg_prep_minutes }} min</strong>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
        <button id="btn-enable-notifs" class="btn sm olive glow" type="button" style="display:none;" title="Recevoir les alertes sur l'écran">
            🔔 Activer notifications
        </button>
        <button id="btn-test-sound" class="btn ghost sm" type="button" title="Tester l'alerte sonore">
            🔊 Tester le son
        </button>
        <form method="POST" action="{{ route('partner.store.toggle', $store) }}" style="display:inline">
            @csrf
            <button class="sw {{ $store->is_open ? 'glow' : '' }}" type="submit"
                    aria-pressed="{{ $store->is_open ? 'true' : 'false' }}"
                    data-on="Prend des commandes" data-off="En pause">
                <i></i><span class="lbl">{{ $store->is_open ? 'Prend des commandes' : 'En pause' }}</span>
            </button>
        </form>
    </div>
</div>

@if(session('success'))<div class="flash ok" role="alert">{{ session('success') }}</div>@endif
@if(session('info'))<div class="flash info" role="alert">{{ session('info') }}</div>@endif

{{-- ── Statistiques du jour en direct ── --}}
<div class="kitchen-stats">
    <x-card class="glow">
        <div class="mut" style="font-size:12.5px">Commandes du jour</div>
        <div style="font-size:24px;font-weight:800;color:var(--rust);margin-top:2px">
            {{ $todayOrdersCount }}
        </div>
    </x-card>
    <x-card class="glow">
        <div class="mut" style="font-size:12.5px">Ventes brutes (plats)</div>
        <div style="font-size:24px;font-weight:800;color:var(--olive);margin-top:2px">
            {{ \App\Support\Money::tnd($todayRevenue) }}
        </div>
    </x-card>
    <x-card>
        <div class="mut" style="font-size:12.5px">Commission FromDarna (12 %)</div>
        <div style="font-size:22px;font-weight:700;color:var(--tomate);margin-top:2px">
            − {{ \App\Support\Money::tnd($todayCommission) }}
        </div>
    </x-card>
    <x-card class="glow-brand">
        <div class="mut" style="font-size:12.5px">Net restaurant à recevoir</div>
        <div style="font-size:24px;font-weight:800;color:#2E7D32;margin-top:2px">
            {{ \App\Support\Money::tnd($todayNet) }}
        </div>
    </x-card>
</div>

{{-- ── 1. NOUVELLES COMMANDES URGENTES (À ACCEPTER EN 60s) ── --}}
@if($newOrders->count())
<div class="glow border-grad" style="padding:18px;border-radius:18px;background:var(--card);margin-bottom:20px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
        <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:24px">🚨</span>
            <h2 style="margin:0;color:var(--tomate)">{{ $newOrders->count() }} NOUVELLE(S) COMMANDE(S) EN ATTENTE !</h2>
        </div>
        <span class="bd r pulse">Réponse requise dans 60s</span>
    </div>

    @foreach($newOrders as $order)
        <div class="order-card urgent">
            <div class="order-card-header">
                <div>
                    <strong style="font-size:17px">#{{ $order->ref }}</strong>
                    <span class="mut" style="font-size:13px">· Client : <b>{{ $order->user->name ?? 'Client' }}</b> ({{ $order->delivery_phone }})</span>
                </div>
                <div style="text-align:right">
                    <strong style="font-size:17px;color:var(--rust)">{{ \App\Support\Money::tnd($order->subtotal_millimes) }}</strong>
                    <div class="mut" style="font-size:12px">Reçue à {{ $order->created_at->format('H:i:s') }}</div>
                </div>
            </div>

            <div class="order-items-list">
                @foreach($order->items as $it)
                    <div class="order-item-row">
                        <span><strong>{{ $it->quantity }} ×</strong> {{ $it->product_name }}</span>
                        <span class="mut">{{ \App\Support\Money::tnd($it->line_total_millimes) }}</span>
                    </div>
                @endforeach
            </div>

            @if($order->customer_note)
                <div class="al" style="margin-bottom:12px;padding:8px 12px;font-size:13px">
                    📝 <strong>Remarque client :</strong> « {{ $order->customer_note }} »
                </div>
            @endif

            <div class="order-actions-bar">
                {{-- Bouton d'acceptation directe 15 min --}}
                <form method="POST" action="{{ route('partner.orders.accept', $order) }}" style="display:inline">
                    @csrf
                    <input type="hidden" name="prep_minutes" value="15">
                    <button class="btn olive glow" type="submit">
                        ✓ Accepter (15 min)
                    </button>
                </form>

                {{-- Choix d'un autre délai --}}
                <form method="POST" action="{{ route('partner.orders.accept', $order) }}" style="display:inline-flex;gap:6px">
                    @csrf
                    <select name="prep_minutes" class="f" style="padding:4px 8px;font-size:13px;border-radius:8px">
                        <option value="10">10 min</option>
                        <option value="20">20 min</option>
                        <option value="25">25 min</option>
                        <option value="35">35 min</option>
                        <option value="45">45 min</option>
                    </select>
                    <button class="btn ghost sm" type="submit">Accepter avec ce délai</button>
                </form>

                {{-- Formulaire de refus avec motif --}}
                <form method="POST" action="{{ route('partner.orders.reject', $order) }}" style="display:inline-flex;gap:6px;margin-left:auto">
                    @csrf
                    <select name="reason" class="f" style="padding:4px 8px;font-size:12px;border-radius:8px" required>
                        <option value="Produit en rupture de stock">Produit en rupture</option>
                        <option value="Cuisine surchargée">Cuisine surchargée</option>
                        <option value="Fermeture exceptionnelle">Fermeture exceptionnelle</option>
                    </select>
                    <button class="btn red sm" type="submit" onclick="return confirm('Refuser cette commande ?')">Refuser</button>
                </form>

                <a href="{{ route('partner.orders.show', $order->id) }}" class="btn ghost sm">
                    Détails du ticket ➔
                </a>
            </div>
        </div>
    @endforeach
</div>
@endif

{{-- ── 2. COLONNES KITCHEN : EN PRÉPARATION & PRÊTES ── --}}
<div class="cols">
    {{-- En cours de préparation --}}
    <x-card>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <h2>🍳 En préparation ({{ $preparingOrders->count() }})</h2>
            <span class="mut" style="font-size:12px">En cuisine</span>
        </div>

        @forelse($preparingOrders as $ord)
            @php
                $minsElapsed = $ord->accepted_at ? $ord->accepted_at->diffInMinutes(now()) : 0;
            @endphp
            <div class="order-card">
                <div class="order-card-header">
                    <div>
                        <strong>#{{ $ord->ref }}</strong>
                        <div class="mut" style="font-size:12px">Client : {{ $ord->user->name ?? 'Client' }}</div>
                    </div>
                    <div style="text-align:right">
                        <span class="bd o pulse">En cuisson ({{ $minsElapsed }} min)</span>
                    </div>
                </div>

                <div class="order-items-list">
                    @foreach($ord->items as $it)
                        <div class="order-item-row">
                            <span><strong>{{ $it->quantity }} ×</strong> {{ $it->product_name }}</span>
                        </div>
                    @endforeach
                </div>

                @if($ord->customer_note)
                    <div class="al" style="margin-bottom:10px;padding:6px 10px;font-size:12.5px">
                        📝 {{ $ord->customer_note }}
                    </div>
                @endif

                <div class="order-actions-bar">
                    <form method="POST" action="{{ route('partner.orders.ready', $ord) }}" style="display:inline;width:100%">
                        @csrf
                        <button class="btn olive block glow" type="submit">
                            🔔 Marquer comme prête pour le coursier
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="mut" style="padding:24px;text-align:center">Aucun plat en cours de cuisson.</p>
        @endforelse
    </x-card>

    {{-- Prêtes & En attente du coursier --}}
    <x-card>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <h2>📦 Prêtes au comptoir ({{ $readyOrders->count() }})</h2>
            <span class="mut" style="font-size:12px">En attente coursier</span>
        </div>

        @forelse($readyOrders as $ord)
            <div class="order-card" style="border-color:#2E7D32">
                <div class="order-card-header">
                    <div>
                        <strong>#{{ $ord->ref }}</strong>
                        <div class="mut" style="font-size:12px">Prête depuis {{ $ord->ready_at ? $ord->ready_at->diffForHumans() : 'récemment' }}</div>
                    </div>
                    <x-badge type="g">Prête</x-badge>
                </div>

                <div class="order-items-list">
                    @foreach($ord->items as $it)
                        <div class="order-item-row">
                            <span><strong>{{ $it->quantity }} ×</strong> {{ $it->product_name }}</span>
                        </div>
                    @endforeach
                </div>

                <div style="background:var(--bg2);padding:8px 12px;border-radius:10px;font-size:12.5px;margin-bottom:8px">
                    🛵 <strong>Coursier :</strong> {{ $ord->courier->name ?? 'En cours d’attribution' }}
                    @if($ord->courier?->phone) · <a href="tel:{{ $ord->courier->phone }}">{{ $ord->courier->phone }}</a>@endif
                </div>

                <a href="{{ route('partner.orders.show', $ord->id) }}" class="btn ghost sm block">
                    Détails de la commande
                </a>
            </div>
        @empty
            <p class="mut" style="padding:24px;text-align:center">Aucune commande en attente de retrait.</p>
        @endforelse
    </x-card>
</div>

{{-- ── Audio Chime Web Audio API (Alerte sonore 100% autonome sans fichier externe) ── --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Synthétiseur d'alerte sonore de cuisine (Deux bips clairs)
    function playKitchenChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const now = ctx.currentTime;

            // Bip 1 (note aiguë)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(880, now); // Note La
            gain1.gain.setValueAtTime(0.3, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.35);

            // Bip 2 (note plus haute harmonique)
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'triangle';
            osc2.frequency.setValueAtTime(1174.66, now + 0.18); // Note Ré
            gain2.gain.setValueAtTime(0.4, now + 0.18);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.65);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.18);
            osc2.stop(now + 0.65);
        } catch (e) {
            console.log("AudioContext bloqué par le navigateur", e);
        }
    }

    // Gestion des notifications système
    const btnNotifs = document.getElementById('btn-enable-notifs');
    if (btnNotifs && "Notification" in window) {
        if (Notification.permission !== "granted") {
            btnNotifs.style.display = 'inline-block';
            btnNotifs.addEventListener('click', function () {
                FromDarnaNotifier.requestPermission(function (perm) {
                    if (perm === "granted") {
                        btnNotifs.style.display = 'none';
                        FromDarnaNotifier.send("✓ Notifications activées !", {
                            body: "Vous recevrez les alertes de commandes même en arrière-plan.",
                            soundType: 'success'
                        });
                    }
                });
            });
        }
    }

    // Bouton de test du son
    const btnSound = document.getElementById('btn-test-sound');
    if (btnSound) {
        btnSound.addEventListener('click', function () {
            FromDarnaNotifier.playBeep('default');
            FromDarnaNotifier.send("Test d'alerte cuisine", {
                body: "Le carillon de notification fonctionne correctement.",
                soundType: 'default'
            });
        });
    }

    // Surveillance automatique des nouvelles commandes toutes les 4 secondes
    let previousNewCount = {{ $newOrders->count() }};
    let previousActiveCount = {{ $preparingOrders->count() + $readyOrders->count() }};

    function checkLiveOrders() {
        fetch("{{ route('partner.api.live') }}")
            .then(res => res.json())
            .then(data => {
                if (data.new_count > previousNewCount || (previousActiveCount !== null && data.active_count !== previousActiveCount)) {
                    if (data.new_count > previousNewCount) {
                        const first = data.new_orders && data.new_orders[0] ? data.new_orders[0] : null;
                        const refText = first ? ` #${first.ref} (${first.total_tnd})` : '';
                        
                        FromDarnaNotifier.send("🔔 Nouvelle commande reçue !", {
                            body: `Commande${refText} en attente de préparation en cuisine !`,
                            soundType: 'urgent',
                            vibrate: [300, 150, 300]
                        });
                    }
                    // Recharger la page pour synchroniser la console en direct
                    setTimeout(() => window.location.reload(), 1200);
                }
                previousNewCount = data.new_count;
                previousActiveCount = data.active_count;
            })
            .catch(err => console.log("Erreur polling commandes", err));
    }

    setInterval(checkLiveOrders, 4000);
});
</script>
@endsection
