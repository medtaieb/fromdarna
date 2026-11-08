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
                <section class="partner-status-bar" id="availability"><div class="status-store"><span class="status-icon">◷</span><span><strong>Dar El Kaskrout</strong><small>Temps de préparation habituel : <b id="partner-prep-label">15 min</b></small></span></div><label class="prep-time-control">Préparation<select id="partner-prep-minutes" aria-label="Temps de préparation habituel"><option>10</option><option selected>15</option><option>20</option><option>25</option><option>30</option><option>40</option></select><span>min</span></label><span class="open-state"><i></i>Ouvert</span><button class="outline-button" data-toggle-store type="button">Mettre en pause</button></section>
                <section class="metric-strip" aria-label="Résumé du jour"><div class="metric"><span>Commandes reçues</span><strong id="partner-orders-received">0</strong><small>Depuis le début du service</small></div><div class="metric"><span>À traiter</span><strong id="partner-pending-count">0</strong><small>Commandes en attente</small></div><div class="metric"><span>Temps moyen</span><strong>—</strong><small>Calculé après les premières livraisons</small></div><div class="metric" id="payments"><span>À verser</span><strong id="partner-total-to-settle">0,000 <em>TND</em></strong><small>Montant calculé sur les commandes reçues</small></div></section>
                <section class="portal-panel" id="orders"><div class="panel-heading"><div><h2 id="partner-order-heading">À traiter maintenant</h2><p id="partner-order-subheading">Les nouvelles commandes apparaissent ici.</p></div><button class="outline-button" id="partner-orders-toggle" type="button">Voir l’historique <span>→</span></button></div>
                    <div class="table-scroll"><table class="portal-table"><thead><tr><th>Commande</th><th>Reçue à</th><th>Articles</th><th>Total</th><th>État</th><th>Action</th></tr></thead><tbody id="partner-orders"></tbody></table></div>
                    <p class="portal-empty" id="partner-orders-empty">Aucune commande reçue pour le moment.</p>
                </section>
                <section class="below-grid"><div class="portal-panel" id="menu"><div class="panel-heading"><div><h2>Menu du jour</h2><p>Activez ou suspendez les articles visibles aux clients.</p></div><span class="availability-label">3 articles</span></div><div class="availability-list"><div><span class="availability-dot available" data-product-dot="kaskrout_escalope"></span><span><strong>Kaskrout escalope</strong><small>Disponible · 8,500 TND</small></span><label class="availability-toggle"><input type="checkbox" data-product-availability="kaskrout_escalope" checked><span>En vente</span></label></div><div><span class="availability-dot available" data-product-dot="chapati_maison"></span><span><strong>Chapati maison</strong><small>Disponible · 7,000 TND</small></span><label class="availability-toggle"><input type="checkbox" data-product-availability="chapati_maison" checked><span>En vente</span></label></div><div><span class="availability-dot unavailable" data-product-dot="ojja_merguez"></span><span><strong>Ojja merguez</strong><small>Disponible · 13,000 TND</small></span><label class="availability-toggle"><input type="checkbox" data-product-availability="ojja_merguez"><span>En pause</span></label></div></div></div><div class="portal-panel note-panel"><span class="note-icon">i</span><div><h2>Petit rappel</h2><p>Les changements de disponibilité sont visibles dans le menu client ouvert dans cet appareil.</p></div></div></section>

                <dialog class="partner-dialog" id="partner-reject-dialog" aria-labelledby="partner-reject-title"><div class="dialog-header"><div><p class="eyebrow">COMMANDE PARTENAIRE</p><h2 id="partner-reject-title">Refuser la commande</h2></div><button class="icon-button" type="button" data-close-partner-dialog aria-label="Fermer">×</button></div><form id="partner-reject-form" class="partner-dialog-form"><input type="hidden" name="order_id"><label>Motif<select name="reason" required><option value="">Choisir un motif</option><option>Article indisponible</option><option>Restaurant trop occupé</option><option>Adresse hors zone</option><option>Autre</option></select></label><label>Précision (facultatif)<textarea name="note" rows="2" maxlength="160" placeholder="Ajouter une précision"></textarea></label><div class="partner-dialog-actions"><button class="small-quiet" type="button" data-close-partner-dialog>Annuler</button><button class="small-primary" type="submit">Confirmer le refus</button></div></form></dialog>
                <dialog class="partner-dialog" id="partner-order-dialog" aria-labelledby="partner-order-detail-title"><div class="dialog-header"><div><p class="eyebrow">DÉTAIL DE COMMANDE</p><h2 id="partner-order-detail-title">Commande</h2></div><button class="icon-button" type="button" data-close-partner-dialog aria-label="Fermer">×</button></div><div id="partner-order-detail-content"></div></dialog>

            @elseif ($role === 'courier')
                <section class="courier-shift-bar"><div class="shift-copy"><span class="shift-indicator is-online" data-shift-indicator></span><span><strong data-shift-label>Vous êtes disponible</strong><small>Grombalia · créneau du soir</small></span></div><button class="outline-button" data-toggle-shift type="button">Passer indisponible</button></section>
                <section class="metric-strip courier-metrics" aria-label="Résumé du jour"><div class="metric"><span>Courses livrées</span><strong id="courier-delivered-count">0</strong><small>Depuis le début du service</small></div><div class="metric" id="earnings"><span>Gains du jour</span><strong id="courier-earnings">0,000 <em>TND</em></strong><small>Calculés sur les courses livrées</small></div><div class="metric"><span>Cash à remettre</span><strong id="courier-cash-held">0,000 <em>TND</em></strong><small>Plafond opérationnel à configurer</small></div><div class="metric"><span>Disponibilité</span><strong>—</strong><small>Historique non disponible</small></div></section>
                <section class="courier-assigned-section" id="delivery"><div id="courier-live-orders"><p class="portal-empty">Aucune course ne vous est attribuée pour le moment.</p></div></section>
                <section class="portal-panel history-panel" id="history"><div class="panel-heading"><div><h2>Courses récentes</h2><p>Vos livraisons terminées.</p></div></div><div id="courier-history"><p class="portal-empty">Aucune course terminée.</p></div></section>
                <dialog class="partner-dialog" id="courier-delivery-dialog" aria-labelledby="courier-delivery-title"><div class="dialog-header"><div><p class="eyebrow">PREUVE DE LIVRAISON</p><h2 id="courier-delivery-title">Confirmer la livraison</h2></div><button class="icon-button" type="button" data-close-courier-dialog aria-label="Fermer">×</button></div><form id="courier-delivery-form" class="partner-dialog-form"><input type="hidden" name="order_id"><p>Demandez au client son code de livraison avant de confirmer la remise.</p><label>Code à 6 chiffres<input name="otp" inputmode="numeric" autocomplete="one-time-code" required minlength="6" maxlength="6" pattern="[0-9]{6}" placeholder="000000"></label><p class="courier-form-error" id="courier-delivery-error" hidden>Le code ne correspond pas. Vérifiez-le avec le client.</p><div class="partner-dialog-actions"><button class="small-quiet" type="button" data-close-courier-dialog>Annuler</button><button class="small-primary" type="submit">Confirmer la livraison</button></div></form></dialog>
                <dialog class="partner-dialog" id="courier-incident-dialog" aria-labelledby="courier-incident-title"><div class="dialog-header"><div><p class="eyebrow">ASSISTANCE LIVREUR</p><h2 id="courier-incident-title">Signaler un incident</h2></div><button class="icon-button" type="button" data-close-courier-dialog aria-label="Fermer">×</button></div><form id="courier-incident-form" class="partner-dialog-form"><input type="hidden" name="order_id"><label>Type d’incident<select name="type" required><option value="">Choisir un motif</option><option>Restaurant fermé ou retard</option><option>Client injoignable</option><option>Adresse introuvable</option><option>Problème de paiement</option><option>Autre</option></select></label><label>Précision<textarea name="note" rows="3" maxlength="240" placeholder="Décrivez brièvement le problème"></textarea></label><div class="partner-dialog-actions"><button class="small-quiet" type="button" data-close-courier-dialog>Annuler</button><button class="small-primary" type="submit">Envoyer à Ops</button></div></form></dialog>

            @else
                <section class="ops-toolbar" id="dispatch"><div class="live-indicator"><i></i>Vue en direct <span>Mise à jour automatique</span></div><div class="ops-filter-controls"><label>Ville<select id="ops-zone-filter"><option value="all">Toutes les villes</option><option>Grombalia</option><option>Soliman</option><option>Béni Khalled</option><option>Bou Argoub</option></select></label><label>Période<select id="ops-time-filter"><option value="all">Toutes les périodes</option><option value="today">Aujourd’hui</option><option value="7days">7 derniers jours</option></select></label></div></section>
                <section class="metric-strip ops-metrics" aria-label="Résumé des opérations"><div class="metric"><span>Commandes actives</span><strong id="ops-active-count">0</strong><small>Grombalia</small></div><div class="metric"><span>À attribuer</span><strong id="ops-active-unassigned">0</strong><small class="metric-warn">En attente de dispatch</small></div><div class="metric"><span>Livreurs disponibles</span><strong id="ops-online-count">—</strong><small>Disponibilités non synchronisées</small></div><div class="metric"><span>Retards à surveiller</span><strong>—</strong><small>Délais serveur indisponibles</small></div></section>
                <section class="portal-panel" id="live-orders"><div class="panel-heading"><div><h2>Commandes à suivre</h2><p>Priorité aux commandes non attribuées et en retard.</p></div><div class="table-filters"><button class="filter-pill is-selected" data-ops-filter="all" type="button">Toutes <span id="ops-total-count">0</span></button><button class="filter-pill" data-ops-filter="unassigned" type="button">À attribuer <span id="ops-unassigned-count">0</span></button><button class="filter-pill" data-ops-filter="late" type="button">En retard <span id="ops-late-count">0</span></button><button class="filter-pill" data-ops-filter="completed" type="button">Terminées <span id="ops-completed-count">0</span></button></div></div>
                    <div class="table-scroll"><table class="portal-table ops-table"><thead><tr><th>Commande</th><th>Restaurant</th><th>Client / zone</th><th>Reçue à</th><th>État</th><th>Livreur</th><th>Action</th></tr></thead><tbody id="ops-orders"></tbody></table></div>
                    <p class="portal-empty" id="ops-orders-empty">Aucune commande active pour le moment.</p>
                </section>
                <section class="below-grid ops-bottom"><div class="portal-panel" id="couriers"><div class="panel-heading"><div><h2>Équipe dispatchée</h2><p>Livreurs affectés à des commandes en cours.</p></div></div><div class="courier-roster" id="ops-courier-roster"><p class="portal-empty">Aucune course attribuée.</p></div></div><div class="portal-panel cash-panel" id="cash"><div class="panel-heading"><div><h2>Rapprochement cash</h2><p>Espèces des commandes livrées, en attente de remise.</p></div></div><div class="cash-total"><strong id="ops-cash-held">0,000 TND</strong><span>À remettre au QG</span></div><button class="outline-button" type="button" data-cash-action>Enregistrer une remise <span>→</span></button></div></section>
                <dialog class="partner-dialog" id="ops-order-dialog" aria-labelledby="ops-order-detail-title"><div class="dialog-header"><div><p class="eyebrow">DOSSIER OPÉRATIONNEL</p><h2 id="ops-order-detail-title">Commande</h2></div><button class="icon-button" type="button" data-close-ops-dialog aria-label="Fermer">×</button></div><div id="ops-order-detail-content"></div><div class="ops-detail-actions"><button class="small-quiet" data-ops-reassign type="button">Réaffecter</button><button class="small-primary" data-ops-refund type="button">Enregistrer un remboursement</button></div></dialog>
                <dialog class="partner-dialog" id="ops-reassign-dialog" aria-labelledby="ops-reassign-title"><div class="dialog-header"><div><p class="eyebrow">DISPATCH</p><h2 id="ops-reassign-title">Réaffecter la course</h2></div><button class="icon-button" type="button" data-close-ops-dialog aria-label="Fermer">×</button></div><form id="ops-reassign-form" class="partner-dialog-form"><input type="hidden" name="order_id"><label>Livreur<select name="courier" required><option value="">Sélectionner</option><option>Livreur Grombalia 1</option><option>Livreur Grombalia 2</option><option>Livreur Grombalia 3</option></select></label><div class="partner-dialog-actions"><button class="small-quiet" type="button" data-close-ops-dialog>Annuler</button><button class="small-primary" type="submit">Confirmer</button></div></form></dialog>
                <dialog class="partner-dialog" id="ops-refund-dialog" aria-labelledby="ops-refund-title"><div class="dialog-header"><div><p class="eyebrow">SUPPORT CLIENT</p><h2 id="ops-refund-title">Enregistrer un remboursement</h2></div><button class="icon-button" type="button" data-close-ops-dialog aria-label="Fermer">×</button></div><form id="ops-refund-form" class="partner-dialog-form"><input type="hidden" name="order_id"><label>Motif<select name="reason" required><option value="">Choisir un motif</option><option>Article manquant</option><option>Commande incorrecte</option><option>Annulation restaurant</option><option>Incident de livraison</option><option>Autre</option></select></label><label>Montant remboursé (TND)<input name="amount" type="number" min="0.001" step="0.001" required></label><label>Note de suivi<textarea name="note" rows="2" maxlength="180" placeholder="Détail pour le journal Ops"></textarea></label><div class="partner-dialog-actions"><button class="small-quiet" type="button" data-close-ops-dialog>Annuler</button><button class="small-primary" type="submit">Confirmer le remboursement</button></div></form></dialog>
            @endif

            <p class="portal-footnote">Les commandes et changements de statut sont enregistrés dans ce navigateur et visibles dans les autres espaces ouverts.</p>
        </div>
    </main>
    <nav class="portal-mobile-nav" aria-label="Raccourcis de portail"><a href="{{ route('portal.partner') }}">Partenaire</a><a href="{{ route('portal.courier') }}">Livreur</a><a href="{{ route('portal.ops') }}">Ops</a></nav>
</body>
</html>