<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f7f2">
    <title>FromDarna — Les bonnes adresses près de chez vous</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="topbar">
        <a class="brand" href="/" aria-label="FromDarna, accueil"><span class="brand-mark">f</span><span>fromdarna<span class="brand-period">.</span></span></a>
        <button class="location-button" id="open-address" type="button" aria-label="Adresse de livraison">
            <span class="pin-icon" aria-hidden="true">⌖</span>
            <span class="location-copy"><small>Livrer à</small><strong id="location-label">Choisir une adresse <span aria-hidden="true">⌄</span></strong></span>
        </button>
        <form class="search-form" role="search">
            <span class="search-icon" aria-hidden="true"></span>
            <input id="restaurant-search" type="search" placeholder="Restaurant, plat, envie..." aria-label="Rechercher un restaurant ou un plat">
            <kbd>⌘ K</kbd>
            <button id="close-mobile-search" type="button" aria-label="Fermer la recherche">×</button>
        </form>
        <div class="top-actions">
            <button class="language-toggle" id="language-toggle" type="button" aria-label="Changer la langue">عربي</button>
            <button class="account-button" id="open-account" type="button"><span aria-hidden="true">◯</span><span id="account-label">Connexion</span></button>
            <button class="orders-button" id="open-history" data-i18n="myOrders" type="button">Mes commandes</button>
            <button class="cart-button" id="open-cart" type="button" aria-label="Ouvrir le panier">
                <span aria-hidden="true">▱</span><span class="cart-label">Panier</span><span class="cart-count" id="cart-count">0</span>
            </button>
        </div>
    </header>

    <main>
        <section class="welcome-row" aria-label="Votre ville">
            <div><p class="eyebrow" data-i18n="welcomeEyebrow">LE GOÛT DU COIN, LIVRÉ CHEZ VOUS</p><h1>Salem, Grombalia <span class="sun-mark">✳</span></h1></div>
            <p class="welcome-note" data-i18n="welcomeNote">Les bonnes adresses de votre ville,<br>à portée de commande.</p>
        </section>
        <section class="customer-alert" id="outside-zone" hidden role="status"><span aria-hidden="true">!</span><p><strong data-i18n="outsideZone">Adresse hors zone de livraison</strong><small data-i18n="outsideZoneDetail">Cette adresse ne semble pas couverte par la zone pilote actuelle.</small></p><button class="small-quiet" id="change-address" data-i18n="editAddress" type="button">Modifier l’adresse</button></section>

        <section class="hero-banner" aria-label="Sélection du moment">
            <img src="https://images.unsplash.com/photo-1579751626657-72bc17010498?auto=format&fit=crop&w=1800&q=85" alt="Table de cuisine tunisienne généreusement garnie">
            <div class="hero-shade"></div>
            <div class="hero-copy"><span class="hero-kicker" data-i18n="heroKicker">GROMBALIA · NOS ADRESSES</span><h2 data-i18n="heroTitle">Un bon repas.<br>Tout près.</h2><p data-i18n="heroDescription">Vos restaurants préférés, sans chercher loin.</p><a href="#restaurants" class="hero-link"><span data-i18n="exploreRestaurants">Explorer les restaurants</span><span aria-hidden="true">↘</span></a></div>
            <div class="hero-index"><span>01</span><span class="index-line"></span><span>03</span></div>
        </section>

        <section class="category-section" aria-label="Catégories de restaurants">
            <div class="section-heading"><div><p class="eyebrow" data-i18n="categoryEyebrow">ON A FAIM DE QUOI ?</p><h2 data-i18n="categoryTitle">Choisir une catégorie</h2></div><a class="text-link" href="#restaurants"><span data-i18n="seeAll">Tout voir</span><span aria-hidden="true">→</span></a></div>
            <div class="category-list" role="group" aria-label="Filtrer par catégorie">
                <button class="category-chip is-active" data-category="all" type="button"><span class="category-symbol">✳</span><span data-i18n="all">Tout</span></button>
                <button class="category-chip" data-category="sandwich" type="button"><span class="category-symbol">◒</span><span data-i18n="sandwiches">Sandwichs</span></button>
                <button class="category-chip" data-category="pizza" type="button"><span class="category-symbol">◉</span><span data-i18n="pizza">Pizza</span></button>
                <button class="category-chip" data-category="grillade" type="button"><span class="category-symbol">♨</span><span data-i18n="grills">Grillades</span></button>
                <button class="category-chip" data-category="patisserie" type="button"><span class="category-symbol">✿</span><span data-i18n="pastries">Pâtisseries</span></button>
                <button class="category-chip" data-category="cafe" type="button"><span class="category-symbol">◡</span><span data-i18n="cafes">Cafés</span></button>
            </div>
        </section>

        <section class="restaurants-section" id="restaurants">
            <div class="section-heading restaurant-heading"><div><p class="eyebrow" data-i18n="localDelivery">GROMBALIA · LIVRAISON LOCALE</p><h2><span data-i18n="popularPicks">Les incontournables</span> <span class="result-count" id="result-count">04 adresses</span></h2></div><button class="sort-button" id="sort-restaurants" type="button"><span data-i18n="sortPopular">Les plus populaires</span> <span aria-hidden="true">⌄</span></button></div>
            <div class="restaurant-grid" id="restaurant-grid">
                <article class="restaurant-card" data-category="sandwich grillade" data-name="Dar El Kaskrout" data-rating="4.8" data-eta="30" data-distance="1.2">
                    <button class="restaurant-image" type="button" data-menu="dar" aria-label="Voir le menu de Dar El Kaskrout"><img src="https://images.unsplash.com/photo-1553909489-cd47e0ef937f?auto=format&fit=crop&w=900&q=80" alt="Sandwich garni et grillé"><span class="image-tag">POPULAIRE</span><span class="favorite-button" aria-hidden="true">♡</span></button>
                    <div class="restaurant-details"><div class="restaurant-title"><h3>Dar El Kaskrout</h3><span class="rating">★ 4,8</span></div><p>Sandwichs · Grillades <span class="dot-separator">·</span> 1,2 km</p><div class="restaurant-meta"><span>25–35 min</span><span>Livraison <strong>3,000 TND</strong></span></div><button class="menu-link" type="button" data-menu="dar">Voir le menu <span aria-hidden="true">↗</span></button></div>
                </article>
                <article class="restaurant-card" data-category="pizza" data-name="La Piazza Grombalia" data-rating="4.6" data-eta="35" data-distance="2">
                    <button class="restaurant-image" type="button" data-menu="piazza" aria-label="Voir le menu de La Piazza Grombalia"><img src="https://images.unsplash.com/photo-1579751626657-72bc17010498?auto=format&fit=crop&w=900&q=80" alt="Pizza fraîche sortie du four"><span class="image-tag image-tag-light">NOUVEAU</span><span class="favorite-button" aria-hidden="true">♡</span></button>
                    <div class="restaurant-details"><div class="restaurant-title"><h3>La Piazza Grombalia</h3><span class="rating">★ 4,6</span></div><p>Pizza · Pâtes <span class="dot-separator">·</span> 2,0 km</p><div class="restaurant-meta"><span>30–40 min</span><span>Livraison <strong>3,000 TND</strong></span></div><button class="menu-link" type="button" data-menu="piazza">Voir le menu <span aria-hidden="true">↗</span></button></div>
                </article>
                <article class="restaurant-card" data-category="grillade" data-name="Le Mechoui du Cap Bon" data-rating="4.9" data-eta="40" data-distance="1.8">
                    <button class="restaurant-image" type="button" data-menu="mechoui" aria-label="Voir le menu du Mechoui du Cap Bon"><img src="https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=900&q=80" alt="Assiette de grillades"><span class="favorite-button" aria-hidden="true">♡</span></button>
                    <div class="restaurant-details"><div class="restaurant-title"><h3>Le Mechoui du Cap Bon</h3><span class="rating">★ 4,9</span></div><p>Grillades · Tunisien <span class="dot-separator">·</span> 1,8 km</p><div class="restaurant-meta"><span>35–45 min</span><span>Livraison <strong>3,000 TND</strong></span></div><button class="menu-link" type="button" data-menu="mechoui">Voir le menu <span aria-hidden="true">↗</span></button></div>
                </article>
                <article class="restaurant-card" data-category="patisserie cafe" data-name="Maison Zina" data-rating="4.7" data-eta="25" data-distance="0.9">
                    <button class="restaurant-image" type="button" data-menu="zina" aria-label="Voir le menu de Maison Zina"><img src="https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=900&q=80" alt="Pâtisserie maison aux fruits"><span class="favorite-button" aria-hidden="true">♡</span></button>
                    <div class="restaurant-details"><div class="restaurant-title"><h3>Maison Zina</h3><span class="rating">★ 4,7</span></div><p>Pâtisseries · Café <span class="dot-separator">·</span> 0,9 km</p><div class="restaurant-meta"><span>20–30 min</span><span>Livraison <strong>3,000 TND</strong></span></div><button class="menu-link" type="button" data-menu="zina">Voir le menu <span aria-hidden="true">↗</span></button></div>
                </article>
            </div>
            <p class="empty-state" id="empty-state" data-i18n="searchEmpty" hidden>Aucune adresse ne correspond à cette recherche.</p>
        </section>
        <section class="order-tracking" id="order-tracking" hidden aria-live="polite">
            <div><p class="eyebrow" data-i18n="yourOrder">VOTRE COMMANDE</p><h2 id="tracking-title">Commande confirmée</h2><p id="tracking-detail"></p></div>
            <span class="state-pill state-new" id="tracking-status">Envoyée au restaurant</span>
        </section>
        <section class="customer-orders" id="customer-orders" hidden>
            <div class="section-heading"><div><p class="eyebrow" data-i18n="yourAccount">VOTRE COMPTE</p><h2 data-i18n="orderHistory">Historique des commandes</h2></div><button class="small-quiet" id="close-history" data-i18n="close" type="button">Fermer</button></div>
            <div id="order-history-list"></div>
        </section>
        <footer class="site-footer"><a class="brand" href="/"><span class="brand-mark">f</span><span>fromdarna<span class="brand-period">.</span></span></a><span>Les bonnes choses sont tout près.</span><nav class="store-portal-links" aria-label="Aperçus des espaces professionnels"><a href="{{ route('portal.partner') }}">Partenaire</a><a href="{{ route('portal.courier') }}">Livreur</a><a href="{{ route('portal.ops') }}">Ops</a></nav></footer>
    </main>

    <dialog class="menu-dialog" id="menu-dialog" aria-labelledby="menu-title">
        <div class="dialog-header"><div><p class="eyebrow" data-i18n="restaurantMenu">MENU DU RESTAURANT</p><h2 id="menu-title"></h2></div><button class="icon-button" type="button" data-close="menu-dialog" aria-label="Fermer">×</button></div>
        <div class="menu-items" id="menu-items"></div>
    </dialog>
    <dialog class="cart-dialog" id="cart-dialog" aria-labelledby="cart-title">
        <div class="dialog-header"><div><p class="eyebrow" data-i18n="yourOrder">VOTRE COMMANDE</p><h2 id="cart-title" data-i18n="cart">Le panier</h2></div><button class="icon-button" type="button" data-close="cart-dialog" aria-label="Fermer">×</button></div>
        <p class="cart-restaurant" id="cart-restaurant"></p>
        <div class="cart-items" id="cart-items"></div>
        <p class="cart-alert" id="cart-unavailable" role="status" hidden></p>
        <div class="cart-summary"><div><span data-i18n="subtotal">Sous-total</span><strong id="cart-subtotal">0,000 TND</strong></div><div id="small-basket-row" hidden><span data-i18n="smallBasketFee">Frais petit panier</span><strong id="small-basket-fee">1,000 TND</strong></div><div><span data-i18n="delivery">Livraison</span><strong>3,000 TND</strong></div><div class="cart-total"><span data-i18n="estimatedTotal">Total estimé</span><strong id="cart-total">0,000 TND</strong></div></div>
        <div class="payment-note"><span aria-hidden="true">◉</span><span data-i18n="cashPayment">Paiement en espèces à la livraison</span></div>
        <button class="checkout-button" id="checkout-button" type="button" disabled><span data-i18n="continueOrder">Continuer la commande</span><span aria-hidden="true">→</span></button>
    </dialog>
    <dialog class="checkout-dialog" id="checkout-dialog" aria-labelledby="checkout-title">
        <div class="dialog-header"><div><p class="eyebrow" data-i18n="lastStep">DERNIÈRE ÉTAPE</p><h2 id="checkout-title" data-i18n="whereToDeliver">Où vous livrer ?</h2></div><button class="icon-button" type="button" data-close="checkout-dialog" aria-label="Fermer">×</button></div>
        <form id="checkout-form" class="checkout-form">
            <label><span data-i18n="yourName">Votre nom</span><input name="customer_name" autocomplete="name" required maxlength="80" placeholder="Nom et prénom"></label>
            <label><span data-i18n="phone">Téléphone</span><input name="phone" type="tel" autocomplete="tel" inputmode="tel" required minlength="8" maxlength="20" placeholder="+216 XX XXX XXX"></label>
            <div class="checkout-address"><span><small>LIVRER À</small><strong id="checkout-address-label">Aucune adresse enregistrée</strong></span><button class="small-quiet" id="change-checkout-address" type="button">Modifier</button></div>
            <input id="checkout-address-value" name="address" type="hidden">
            <div class="checkout-cash"><span aria-hidden="true">◉</span><span><strong>Paiement en espèces</strong><small>À remettre au livreur à la réception.</small></span><b id="checkout-total">0,000 TND</b></div>
            <button class="checkout-button" type="submit"><span data-i18n="confirmOrder">Confirmer la commande</span><span aria-hidden="true">→</span></button>
        </form>
    </dialog>
    <dialog class="address-dialog" id="address-dialog" aria-labelledby="address-title">
        <div class="dialog-header"><div><p class="eyebrow" data-i18n="homeDelivery">LIVRAISON À DOMICILE</p><h2 id="address-title" data-i18n="chooseAddress">Choisir une adresse</h2></div><button class="icon-button" type="button" data-close="address-dialog" aria-label="Fermer">×</button></div>
        <p class="address-intro" data-i18n="pinInstruction">Placez l’épingle sur votre adresse, puis ajoutez un repère pour le livreur.</p>
        <div class="address-map-wrap"><div id="address-map" role="application" aria-label="Carte pour choisir l’adresse de livraison"></div><button class="map-locate" id="locate-me" type="button"><span aria-hidden="true">⌖</span><span data-i18n="myLocation">Ma position</span></button></div>
        <div class="zone-status is-covered" id="coverage-status" role="status"><i></i><span>Zone pilote de Grombalia · contour indicatif</span></div>
        <form id="address-form" class="checkout-form address-form">
            <label><span data-i18n="streetAddress">Adresse, rue et quartier</span><input name="street" required maxlength="180" placeholder="Ex. Avenue Habib Bourguiba"></label>
            <label><span data-i18n="landmark">Repère pour le livreur</span><input name="landmark" maxlength="140" placeholder="Ex. en face de la pharmacie"></label>
            <button class="checkout-button" type="submit"><span data-i18n="saveAddress">Enregistrer cette adresse</span><span aria-hidden="true">→</span></button>
        </form>
    </dialog>
    <dialog class="account-dialog" id="account-dialog" aria-labelledby="account-title">
        <div class="dialog-header"><div><p class="eyebrow" data-i18n="yourAccount">VOTRE COMPTE FROMDARNA</p><h2 id="account-title" data-i18n="phoneLogin">Connexion par téléphone</h2></div><button class="icon-button" type="button" data-close="account-dialog" aria-label="Fermer">×</button></div>
        <div class="signed-in-account" id="signed-in-account" hidden><p>Connecté avec <strong id="signed-in-phone"></strong></p><button class="text-button" id="sign-out" type="button">Se déconnecter</button></div>
        <form id="phone-form" class="checkout-form">
            <p class="account-copy" data-i18n="phoneIntro">Saisissez votre numéro pour continuer et retrouver vos commandes.</p>
            <label>Numéro de téléphone<input name="phone" type="tel" inputmode="tel" autocomplete="tel" required minlength="8" maxlength="20" placeholder="+216 XX XXX XXX"></label>
            <button class="checkout-button" type="submit"><span data-i18n="continue">Continuer</span><span aria-hidden="true">→</span></button>
        </form>
        <form id="otp-form" class="checkout-form" hidden>
            <p class="account-copy"><span data-i18n="otpInstruction">Entrez le code à 6 chiffres associé au numéro</span> <strong id="otp-phone-label"></strong>.</p>
            <label>Code de vérification<input name="otp" inputmode="numeric" autocomplete="one-time-code" required minlength="6" maxlength="6" pattern="[0-9]{6}" placeholder="000000"></label>
            <p class="account-hint" id="otp-hint">La vérification SMS sera activée avec le service d’authentification.</p>
            <button class="checkout-button" type="submit"><span data-i18n="verifyCode">Vérifier le code</span><span aria-hidden="true">→</span></button>
            <button class="text-button" id="edit-phone" type="button">Modifier le numéro</button>
        </form>
    </dialog>
    <dialog class="order-detail-dialog" id="order-detail-dialog" aria-labelledby="order-detail-title">
        <div class="dialog-header"><div><p class="eyebrow">DÉTAIL DE COMMANDE</p><h2 id="order-detail-title"></h2></div><button class="icon-button" type="button" data-close="order-detail-dialog" aria-label="Fermer">×</button></div>
        <div id="order-detail-content"></div>
    </dialog>
    <div class="dialog-backdrop" id="dialog-backdrop"></div>
    <nav class="mobile-nav" aria-label="Navigation principale"><a class="mobile-nav-active" href="/"><span aria-hidden="true">⌂</span>Accueil</a><a href="#restaurants"><span aria-hidden="true">⌕</span>Explorer</a><button type="button" id="mobile-search"><span aria-hidden="true">⌕</span><span data-i18n="search">Rechercher</span></button><button type="button" id="mobile-history"><span aria-hidden="true">◷</span>Commandes</button><button type="button" id="mobile-cart"><span aria-hidden="true">▱</span>Panier <b id="mobile-cart-count">0</b></button></nav>
</body>
</html>