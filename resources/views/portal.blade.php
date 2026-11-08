@php
    $portal = [
        'partner' => [
            'name' => 'Partenaire',
            'title' => 'Commandes du restaurant',
            'intro' => 'Aujourd’hui, Grombalia',
            'initial' => 'P',
        ],
        'courier' => [
            'name' => 'Livreur',
            'title' => 'Votre tournée',
            'intro' => 'Aujourd’hui, Grombalia',
            'initial' => 'L',
        ],
        'ops' => [
            'name' => 'Ops',
            'title' => 'Centre des opérations',
            'intro' => 'Grombalia · service du soir',
            'initial' => 'O',
        ],
    ][$role];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f7f2">
    <title>{{ $portal['title'] }} — FromDarna</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="portal-body" data-portal-role="{{ $role }}">
    <aside class="portal-sidebar">
        <a class="brand" href="/" aria-label="FromDarna, accueil"><span class="brand-mark">f</span><span>fromdarna<span class="brand-period">.</span></span></a>
        <div class="portal-workspace"><span class="workspace-mark">{{ $portal['initial'] }}</span><span><small>Espace de travail</small><strong>{{ $portal['name'] }}</strong></span><span class="workspace-caret">⌄</span></div>
        <p class="sidebar-label">ESPACE {{ strtoupper($portal['name']) }}</p>
        <nav class="portal-nav" aria-label="Navigation {{ $portal['name'] }}">
            @if ($role === 'partner')
                <a class="portal-nav-link is-current" href="#orders"><span>▤</span>Commandes <b>3</b></a>
                <a class="portal-nav-link" href="#menu"><span>☷</span>Menu</a>
                <a class="portal-nav-link" href="#availability"><span>◷</span>Disponibilités</a>
                <a class="portal-nav-link" href="#payments"><span>↗</span>Versements</a>
            @elseif ($role === 'courier')
                <a class="portal-nav-link is-current" href="#delivery"><span>⌖</span>Ma tournée <b>1</b></a>
                <a class="portal-nav-link" href="#earnings"><span>↗</span>Mes gains</a>
                <a class="portal-nav-link" href="#history"><span>◷</span>Historique</a>
                <a class="portal-nav-link" href="#help"><span>?</span>Support</a>
            @else
                <a class="portal-nav-link is-current" href="#live-orders"><span>▤</span>Commandes <b>4</b></a>
                <a class="portal-nav-link" href="#dispatch"><span>⌖</span>Dispatch</a>
                <a class="portal-nav-link" href="#couriers"><span>◉</span>Livreurs</a>
                <a class="portal-nav-link" href="#cash"><span>↗</span>Rapprochement cash</a>
            @endif
        </nav>
        <div class="sidebar-bottom"><a href="/" class="back-to-store"><span>←</span>Voir le côté client</a><div class="signed-in"><span class="user-avatar">{{ $portal['initial'] }}</span><span><strong>{{ $portal['name'] }} · Grombalia</strong><small>Espace FromDarna</small></span></div></div>
    </aside>

    <main class="portal-main">
        <header class="portal-topbar"><div class="breadcrumbs">FromDarna <span>/</span> {{ $portal['name'] }}</div><div class="portal-top-actions"><nav class="portal-role-links" aria-label="Aperçus des espaces"><a href="{{ route('portal.partner') }}">Partenaire</a><a href="{{ route('portal.courier') }}">Livreur</a><a href="{{ route('portal.ops') }}">Ops</a></nav><span class="connection-state"><i></i>Connecté</span><button class="portal-icon-button" type="button" aria-label="Notifications">♧<b>2</b></button><span class="portal-date">{{ now()->locale('fr')->translatedFormat('l d F') }}</span></div></header>
        <div class="portal-content">
            <div class="portal-title-row"><div><p class="eyebrow">{{ strtoupper($portal['intro']) }}</p><h1>{{ $portal['title'] }}</h1></div><span class="demo-tag"><i></i>ESPACE GROMBALIA</span></div>

            @if ($role === 'partner')
                <section class="partner-status-bar" id="availability"><div class="status-store"><span class="status-icon">◷</span><span><strong>Dar El Kaskrout</strong><small>Temps de préparation habituel : 15 min</small></span></div><span class="open-state"><i></i>Ouvert</span><button class="outline-button" data-toggle-store type="button">Mettre en pause</button></section>
                <section class="metric-strip" aria-label="Résumé du jour"><div class="metric"><span>Commandes reçues</span><strong id="partner-orders-received">0</strong><small>Depuis le début du service</small></div><div class="metric"><span>À traiter</span><strong id="partner-pending-count">0</strong><small>Commandes en attente</small></div><div class="metric"><span>Temps moyen</span><strong>—</strong><small>Calculé après les premières livraisons</small></div><div class="metric" id="payments"><span>À verser</span><strong id="partner-total-to-settle">0,000 <em>TND</em></strong><small>Montant calculé sur les commandes reçues</small></div></section>
                <section class="portal-panel" id="orders"><div class="panel-heading"><div><h2>À traiter maintenant</h2><p>Les nouvelles commandes apparaissent ici.</p></div><button class="outline-button" type="button">Toutes les commandes <span>→</span></button></div>
                    <div class="table-scroll"><table class="portal-table"><thead><tr><th>Commande</th><th>Reçue à</th><th>Articles</th><th>Total</th><th>État</th><th>Action</th></tr></thead><tbody id="partner-orders"></tbody></table></div>
                    <p class="portal-empty" id="partner-orders-empty">Aucune commande reçue pour le moment.</p>
                </section>
                <section class="below-grid"><div class="portal-panel" id="menu"><div class="panel-heading"><div><h2>Menu du jour</h2><p>Visibilité des articles côté client.</p></div><button class="small-quiet" type="button">Modifier le menu</button></div><div class="availability-list"><div><span class="availability-dot available"></span><span><strong>Kaskrout escalope</strong><small>Disponible · 8,500 TND</small></span><span class="availability-label">En vente</span></div><div><span class="availability-dot available"></span><span><strong>Chapati maison</strong><small>Disponible · 7,000 TND</small></span><span class="availability-label">En vente</span></div><div><span class="availability-dot unavailable"></span><span><strong>Ojja merguez</strong><small>Indisponible · 13,000 TND</small></span><span class="availability-label">En pause</span></div></div></div><div class="portal-panel note-panel"><span class="note-icon">i</span><div><h2>Petit rappel</h2><p>Gardez le portail ouvert pendant vos heures de service. Si vous êtes occupé, mettez temporairement le restaurant en pause.</p></div></div></section>

            @elseif ($role === 'courier')
                <section class="courier-shift-bar"><div class="shift-copy"><span class="shift-indicator is-online" data-shift-indicator></span><span><strong data-shift-label>Vous êtes disponible</strong><small>Grombalia · créneau du soir</small></span></div><button class="outline-button" data-toggle-shift type="button">Passer indisponible</button></section>
                <section class="metric-strip courier-metrics" aria-label="Résumé du jour"><div class="metric"><span>Courses livrées</span><strong id="courier-delivered-count">0</strong><small>Depuis le début du service</small></div><div class="metric" id="earnings"><span>Gains du jour</span><strong id="courier-earnings">0,000 <em>TND</em></strong><small>Calculés sur les courses livrées</small></div><div class="metric"><span>Cash à remettre</span><strong id="courier-cash-held">0,000 <em>TND</em></strong><small>Plafond opérationnel à configurer</small></div><div class="metric"><span>Disponibilité</span><strong>—</strong><small>Historique non disponible</small></div></section>
                <section class="courier-assigned-section" id="delivery"><div id="courier-live-orders"><p class="portal-empty">Aucune course ne vous est attribuée pour le moment.</p></div></section>
                <section class="portal-panel history-panel" id="history"><div class="panel-heading"><div><h2>Courses récentes</h2><p>Vos livraisons terminées.</p></div></div><div id="courier-history"><p class="portal-empty">Aucune course terminée.</p></div></section>

            @else
                <section class="ops-toolbar" id="dispatch"><div class="live-indicator"><i></i>Vue en direct <span>Mise à jour automatique</span></div><div><button class="outline-button" type="button">Toutes les zones <span>⌄</span></button><button class="outline-button" type="button">Ce soir <span>⌄</span></button></div></section>
                <section class="metric-strip ops-metrics" aria-label="Résumé des opérations"><div class="metric"><span>Commandes actives</span><strong id="ops-active-count">0</strong><small>Grombalia</small></div><div class="metric"><span>À attribuer</span><strong id="ops-active-unassigned">0</strong><small class="metric-warn">En attente de dispatch</small></div><div class="metric"><span>Livreurs disponibles</span><strong id="ops-online-count">—</strong><small>Disponibilités non synchronisées</small></div><div class="metric"><span>Retards à surveiller</span><strong>—</strong><small>Délais serveur indisponibles</small></div></section>
                <section class="portal-panel" id="live-orders"><div class="panel-heading"><div><h2>Commandes à suivre</h2><p>Priorité aux commandes non attribuées et en retard.</p></div><div class="table-filters"><button class="filter-pill is-selected" data-ops-filter="all" type="button">Toutes <span id="ops-total-count">0</span></button><button class="filter-pill" data-ops-filter="unassigned" type="button">À attribuer <span id="ops-unassigned-count">0</span></button><button class="filter-pill" data-ops-filter="late" type="button">En retard <span id="ops-late-count">0</span></button></div></div>
                    <div class="table-scroll"><table class="portal-table ops-table"><thead><tr><th>Commande</th><th>Restaurant</th><th>Client / zone</th><th>Reçue à</th><th>État</th><th>Livreur</th><th>Action</th></tr></thead><tbody id="ops-orders"></tbody></table></div>
                    <p class="portal-empty" id="ops-orders-empty">Aucune commande active pour le moment.</p>
                </section>
                <section class="below-grid ops-bottom"><div class="portal-panel" id="couriers"><div class="panel-heading"><div><h2>Équipe dispatchée</h2><p>Livreurs affectés à des commandes en cours.</p></div></div><div class="courier-roster" id="ops-courier-roster"><p class="portal-empty">Aucune course attribuée.</p></div></div><div class="portal-panel cash-panel" id="cash"><div class="panel-heading"><div><h2>Rapprochement cash</h2><p>Espèces des commandes livrées, en attente de remise.</p></div></div><div class="cash-total"><strong id="ops-cash-held">0,000 TND</strong><span>À remettre au QG</span></div><button class="outline-button" type="button" data-cash-action>Enregistrer une remise <span>→</span></button></div></section>
            @endif

            <p class="portal-footnote">Les commandes et changements de statut sont enregistrés dans ce navigateur et visibles dans les autres espaces ouverts.</p>
        </div>
    </main>
    <nav class="portal-mobile-nav" aria-label="Raccourcis de portail"><a href="{{ route('portal.partner') }}">Partenaire</a><a href="{{ route('portal.courier') }}">Livreur</a><a href="{{ route('portal.ops') }}">Ops</a></nav>
</body>
</html>