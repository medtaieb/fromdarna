# Cooked by Us · من دارنا — vues Blade (Laravel 9+)

Toutes les interfaces du site FromDarna / Cooked by Us, avec l'identité du logo
(crème #FFF4D2, charbon #1D1916, brun rustique #98551C, vert olive #363C14, rouge tomate #C41317, beige #B08861).

## Installation

1. Copiez les dossiers dans votre projet Laravel en gardant l'arborescence
   (`app/`, `config/`, `public/`, `resources/views/`, `routes/web.php` à fusionner avec le vôtre).
2. Enregistrez le provider (directive `@tnd`) :
   - Laravel 11+ : ajoutez `App\Providers\CookedServiceProvider::class` dans `bootstrap/providers.php`
   - Laravel 9/10 : ajoutez-le dans `config/app.php` → `providers`
3. `.env` :
   ```
   COOKED_WHATSAPP=216XXXXXXXX
   COOKED_PHONE=+216XXXXXXXX
   ```
4. `php artisan optimize:clear && php artisan route:list`

## Écrans (50 fichiers Blade)

| Espace | Routes | Vues |
|---|---|---|
| Public | `/` | `welcome` |
| Auth | `/connexion`, `/connexion/code`, `/equipe/connexion`, `/equipe/verification` | `auth/*` (OTP SMS client, email + mot de passe équipe, MFA) |
| Client | `/restaurants`, `/restaurants/{id}`, `/panier`, `/commande`, `/mes-commandes[/{id}]`, `/compte`, `/hors-zone` | `customer/*` |
| Partenaire | `/partenaire`, `/commandes[/{id}]`, `/menu`, `/releves` | `partner/*` |
| Livreur | `/livreur`, `/courses/{id}`, `/cash-et-gains`, `/incident` | `courier/*` |
| Ops | `/ops`, commandes, dispatch, restaurants, zones, livreurs, clients, grand-livre, versements, utilisateurs, audit | `ops/*` (13 vues) |

Structure : `layouts/` (base, site, portal, auth), `partials/` (footer, portal-nav), `components/`
(`x-card`, `x-badge`, `x-btn`, `x-stat`, `x-table`, `x-field`, `x-title`, `x-timeline`, `x-store-card`).

## À brancher

- **Auth et rôles** : les routes sont ouvertes. Ajoutez `auth` + un middleware de rôle
  (`partner_owner/partner_staff`, `courier`, `ops/admin/finance`) et MFA pour Ops ; remplacez les `Route::post` de démonstration.
- **Données** : `App\Support\Sample` contient des exemples fictifs en millimes. Remplacez par vos modèles
  Eloquent et passez les données depuis des contrôleurs. `Route::view` transmet déjà `{id}` aux vues.
- **Boutons d'action** (accepter, refuser, proposer, valider…) : formulaires `action="#"` à relier à vos routes.
- **Temps réel** : le portail partenaire est prévu pour WebSocket (alerte, minuterie, test du son déjà en place côté navigateur).
- Les montants sont stockés en millimes ; `@tnd($m)` affiche « 28,000 TND ».
- Les prix, commissions et plafonds affichés sont les hypothèses de la roadmap : à confirmer avant publication.
