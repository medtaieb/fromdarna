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
        <button class="location-button" type="button" aria-label="Adresse de livraison">
            <span class="pin-icon" aria-hidden="true">⌖</span>
            <span class="location-copy"><small>Livrer à</small><strong>Grombalia <span aria-hidden="true">⌄</span></strong></span>
        </button>
        <form class="search-form" role="search">
            <span class="search-icon" aria-hidden="true"></span>
            <input id="restaurant-search" type="search" placeholder="Restaurant, plat, envie..." aria-label="Rechercher un restaurant ou un plat">
            <kbd>⌘ K</kbd>
        </form>
        <div class="top-actions">
            <button class="account-button" type="button"><span aria-hidden="true">◯</span><span>Connexion</span></button>
            <button class="cart-button" id="open-cart" type="button" aria-label="Ouvrir le panier">
                <span aria-hidden="true">▱</span><span class="cart-label">Panier</span><span class="cart-count" id="cart-count">0</span>
            </button>
        </div>
    </header>

    <main>
        <section class="welcome-row" aria-label="Votre ville">
            <div><p class="eyebrow">LE GOÛT DU COIN, LIVRÉ CHEZ VOUS</p><h1>Salem, Grombalia <span class="sun-mark">✳</span></h1></div>
            <p class="welcome-note">Les bonnes adresses de votre ville,<br>à portée de commande.</p>
        </section>

        <section class="hero-banner" aria-label="Sélection du moment">
            <img src="https://images.unsplash.com/photo-1579751626657-72bc17010498?auto=format&fit=crop&w=1800&q=85" alt="Table de cuisine tunisienne généreusement garnie">
            <div class="hero-shade"></div>
            <div class="hero-copy"><span class="hero-kicker">GROMBALIA · NOS ADRESSES</span><h2>Un bon repas.<br>Tout près.</h2><p>Vos restaurants préférés, sans chercher loin.</p><a href="#restaurants" class="hero-link">Explorer les restaurants <span aria-hidden="true">↘</span></a></div>
            <div class="hero-index"><span>01</span><span class="index-line"></span><span>03</span></div>
        </section>

        <section class="category-section" aria-label="Catégories de restaurants">
            <div class="section-heading"><div><p class="eyebrow">ON A FAIM DE QUOI ?</p><h2>Choisir une catégorie</h2></div><a class="text-link" href="#restaurants">Tout voir <span aria-hidden="true">→</span></a></div>
            <div class="category-list" role="group" aria-label="Filtrer par catégorie">
                <button class="category-chip is-active" data-category="all" type="button"><span class="category-symbol">✳</span><span>Tout</span></button>
                <button class="category-chip" data-category="sandwich" type="button"><span class="category-symbol">◒</span><span>Sandwichs</span></button>
                <button class="category-chip" data-category="pizza" type="button"><span class="category-symbol">◉</span><span>Pizza</span></button>
                <button class="category-chip" data-category="grillade" type="button"><span class="category-symbol">♨</span><span>Grillades</span></button>
                <button class="category-chip" data-category="patisserie" type="button"><span class="category-symbol">✿</span><span>Pâtisseries</span></button>
                <button class="category-chip" data-category="cafe" type="button"><span class="category-symbol">◡</span><span>Cafés</span></button>
            </div>
        </section>

        <section class="restaurants-section" id="restaurants">
            <div class="section-heading restaurant-heading"><div><p class="eyebrow">GROMBALIA · LIVRAISON LOCALE</p><h2>Les incontournables <span class="result-count" id="result-count">04 adresses</span></h2></div><button class="sort-button" type="button">Les plus populaires <span aria-hidden="true">⌄</span></button></div>
            <div class="restaurant-grid" id="restaurant-grid">
                <article class="restaurant-card" data-category="sandwich grillade" data-name="Dar El Kaskrout">
                    <button class="restaurant-image" type="button" data-menu="dar" aria-label="Voir le menu de Dar El Kaskrout"><img src="https://images.unsplash.com/photo-1553909489-cd47e0ef937f?auto=format&fit=crop&w=900&q=80" alt="Sandwich garni et grillé"><span class="image-tag">POPULAIRE</span><span class="favorite-button" aria-hidden="true">♡</span></button>
                    <div class="restaurant-details"><div class="restaurant-title"><h3>Dar El Kaskrout</h3><span class="rating">★ 4,8</span></div><p>Sandwichs · Grillades <span class="dot-separator">·</span> 1,2 km</p><div class="restaurant-meta"><span>25–35 min</span><span>Livraison <strong>3,000 TND</strong></span></div><button class="menu-link" type="button" data-menu="dar">Voir le menu <span aria-hidden="true">↗</span></button></div>
                </article>
                <article class="restaurant-card" data-category="pizza" data-name="La Piazza Grombalia">
                    <button class="restaurant-image" type="button" data-menu="piazza" aria-label="Voir le menu de La Piazza Grombalia"><img src="https://images.unsplash.com/photo-1579751626657-72bc17010498?auto=format&fit=crop&w=900&q=80" alt="Pizza fraîche sortie du four"><span class="image-tag image-tag-light">NOUVEAU</span><span class="favorite-button" aria-hidden="true">♡</span></button>
                    <div class="restaurant-details"><div class="restaurant-title"><h3>La Piazza Grombalia</h3><span class="rating">★ 4,6</span></div><p>Pizza · Pâtes <span class="dot-separator">·</span> 2,0 km</p><div class="restaurant-meta"><span>30–40 min</span><span>Livraison <strong>3,000 TND</strong></span></div><button class="menu-link" type="button" data-menu="piazza">Voir le menu <span aria-hidden="true">↗</span></button></div>
                </article>
                <article class="restaurant-card" data-category="grillade" data-name="Le Mechoui du Cap Bon">
                    <button class="restaurant-image" type="button" data-menu="mechoui" aria-label="Voir le menu du Mechoui du Cap Bon"><img src="https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=900&q=80" alt="Assiette de grillades"><span class="favorite-button" aria-hidden="true">♡</span></button>
                    <div class="restaurant-details"><div class="restaurant-title"><h3>Le Mechoui du Cap Bon</h3><span class="rating">★ 4,9</span></div><p>Grillades · Tunisien <span class="dot-separator">·</span> 1,8 km</p><div class="restaurant-meta"><span>35–45 min</span><span>Livraison <strong>3,000 TND</strong></span></div><button class="menu-link" type="button" data-menu="mechoui">Voir le menu <span aria-hidden="true">↗</span></button></div>
                </article>
                <article class="restaurant-card" data-category="patisserie cafe" data-name="Maison Zina">
                    <button class="restaurant-image" type="button" data-menu="zina" aria-label="Voir le menu de Maison Zina"><img src="https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=900&q=80" alt="Pâtisserie maison aux fruits"><span class="favorite-button" aria-hidden="true">♡</span></button>
                    <div class="restaurant-details"><div class="restaurant-title"><h3>Maison Zina</h3><span class="rating">★ 4,7</span></div><p>Pâtisseries · Café <span class="dot-separator">·</span> 0,9 km</p><div class="restaurant-meta"><span>20–30 min</span><span>Livraison <strong>3,000 TND</strong></span></div><button class="menu-link" type="button" data-menu="zina">Voir le menu <span aria-hidden="true">↗</span></button></div>
                </article>
            </div>
            <p class="empty-state" id="empty-state" hidden>Aucune adresse ne correspond à cette recherche.</p>
        </section>
        <section class="order-tracking" id="order-tracking" hidden aria-live="polite">
            <div><p class="eyebrow">VOTRE COMMANDE</p><h2 id="tracking-title">Commande confirmée</h2><p id="tracking-detail"></p></div>
            <span class="state-pill state-new" id="tracking-status">Envoyée au restaurant</span>
        </section>
        <footer class="site-footer"><a class="brand" href="/"><span class="brand-mark">f</span><span>fromdarna<span class="brand-period">.</span></span></a><span>Les bonnes choses sont tout près.</span><nav class="store-portal-links" aria-label="Aperçus des espaces professionnels"><a href="{{ route('portal.partner') }}">Partenaire</a><a href="{{ route('portal.courier') }}">Livreur</a><a href="{{ route('portal.ops') }}">Ops</a></nav></footer>
    </main>

    <dialog class="menu-dialog" id="menu-dialog" aria-labelledby="menu-title">
        <div class="dialog-header"><div><p class="eyebrow">MENU DU RESTAURANT</p><h2 id="menu-title"></h2></div><button class="icon-button" type="button" data-close="menu-dialog" aria-label="Fermer">×</button></div>
        <div class="menu-items" id="menu-items"></div>
    </dialog>
    <dialog class="cart-dialog" id="cart-dialog" aria-labelledby="cart-title">
        <div class="dialog-header"><div><p class="eyebrow">VOTRE COMMANDE</p><h2 id="cart-title">Le panier</h2></div><button class="icon-button" type="button" data-close="cart-dialog" aria-label="Fermer">×</button></div>
        <p class="cart-restaurant" id="cart-restaurant"></p>
        <div class="cart-items" id="cart-items"></div>
        <div class="cart-summary"><div><span>Sous-total</span><strong id="cart-subtotal">0,000 TND</strong></div><div><span>Livraison</span><strong>3,000 TND</strong></div><div class="cart-total"><span>Total estimé</span><strong id="cart-total">0,000 TND</strong></div></div>
        <div class="payment-note"><span aria-hidden="true">◉</span><span>Paiement en espèces à la livraison</span></div>
        <button class="checkout-button" id="checkout-button" type="button" disabled>Continuer la commande <span aria-hidden="true">→</span></button>
    </dialog>
    <dialog class="checkout-dialog" id="checkout-dialog" aria-labelledby="checkout-title">
        <div class="dialog-header"><div><p class="eyebrow">DERNIÈRE ÉTAPE</p><h2 id="checkout-title">Où vous livrer ?</h2></div><button class="icon-button" type="button" data-close="checkout-dialog" aria-label="Fermer">×</button></div>
        <form id="checkout-form" class="checkout-form">
            <label>Votre nom<input name="customer_name" autocomplete="name" required maxlength="80" placeholder="Nom et prénom"></label>
            <label>Téléphone<input name="phone" type="tel" autocomplete="tel" inputmode="tel" required minlength="8" maxlength="20" placeholder="+216 XX XXX XXX"></label>
            <label>Adresse de livraison<textarea name="address" autocomplete="street-address" required maxlength="240" rows="3" placeholder="Rue, quartier, repère à proximité"></textarea></label>
            <div class="checkout-cash"><span aria-hidden="true">◉</span><span><strong>Paiement en espèces</strong><small>À remettre au livreur à la réception.</small></span><b id="checkout-total">0,000 TND</b></div>
            <button class="checkout-button" type="submit">Confirmer la commande <span aria-hidden="true">→</span></button>
        </form>
    </dialog>
    <div class="dialog-backdrop" id="dialog-backdrop"></div>
    <nav class="mobile-nav" aria-label="Navigation principale"><a class="mobile-nav-active" href="/"><span aria-hidden="true">⌂</span>Accueil</a><a href="#restaurants"><span aria-hidden="true">⌕</span>Explorer</a><button type="button" id="mobile-cart"><span aria-hidden="true">▱</span>Panier <b id="mobile-cart-count">0</b></button></nav>
</body>
</html>