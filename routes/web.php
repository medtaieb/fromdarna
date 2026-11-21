<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OpsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PartnerController;
use Illuminate\Support\Facades\Route;

// ── Public ───────────────────────────────────────────────
Route::get('/', HomeController::class)->name('home');

// ── Authentification ─────────────────────────────────────
Route::get('/connexion', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/connexion', [AuthController::class, 'sendOtp'])->name('otp.send');
Route::get('/connexion/code', [AuthController::class, 'showVerifyForm'])->name('otp.verify');
Route::post('/connexion/code', [AuthController::class, 'checkOtp'])->name('otp.check');

Route::get('/equipe/connexion', [AuthController::class, 'showStaffLoginForm'])->name('staff.login');
Route::post('/equipe/connexion', [AuthController::class, 'staffAttempt'])->name('staff.attempt');
Route::get('/equipe/verification', [AuthController::class, 'showMfaForm'])->name('staff.mfa');
Route::post('/equipe/verification', [AuthController::class, 'checkMfa'])->name('staff.mfa.check');

Route::match(['get', 'post'], '/deconnexion', [AuthController::class, 'logout'])->name('logout');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout']);

// ── Client ───────────────────────────────────────────────
Route::get('/restaurants', [CustomerController::class, 'feed'])->name('feed');
Route::get('/restaurants/{id}', [CustomerController::class, 'store'])->whereNumber('id')->name('store');
Route::get('/panier', [OrderController::class, 'cart'])->name('cart');
Route::post('/panier/ajouter', [OrderController::class, 'addToCart'])->name('cart.add');
Route::patch('/panier/{cartItem}', [OrderController::class, 'updateCart'])->name('cart.update');
Route::delete('/panier/{cartItem}', [OrderController::class, 'removeFromCart'])->name('cart.remove');
Route::post('/panier/vider', [OrderController::class, 'clearCart'])->name('cart.clear');
Route::get('/commande', [OrderController::class, 'checkout'])->name('checkout');
Route::post('/commande', [OrderController::class, 'placeOrder'])->name('orders.place');
Route::get('/mes-commandes', [OrderController::class, 'orders'])->name('orders.index');
Route::get('/mes-commandes/{order}', [OrderController::class, 'orderShow'])->name('orders.show');
Route::get('/commandes/{order}/live-tracking', [OrderController::class, 'liveTracking'])->name('orders.tracking.live');
Route::get('/compte', [CustomerController::class, 'account'])->name('account');
Route::post('/compte/profil', [CustomerController::class, 'updateProfile'])->name('account.profile');
Route::post('/compte/adresses', [CustomerController::class, 'storeAddress'])->name('account.address.store');
Route::put('/compte/adresses/{address}', [CustomerController::class, 'updateAddress'])->name('account.address.update');
Route::delete('/compte/adresses/{address}', [CustomerController::class, 'deleteAddress'])->name('account.address.delete');
Route::post('/compte/supprimer', [CustomerController::class, 'deleteAccount'])->name('account.delete');
Route::view('/hors-zone', 'customer.out-of-zone')->name('out-of-zone');

// ── Espace Partenaire (Console Restaurant) ──────────────────────────────────
Route::prefix('partenaire')->group(function () {
    Route::get('/', [PartnerController::class, 'dashboard'])->name('partner.dashboard');
    Route::get('/commandes', [PartnerController::class, 'orders'])->name('partner.orders');
    Route::get('/commandes/{id}', [PartnerController::class, 'orderShow'])->name('partner.orders.show');
    Route::post('/commandes/{order}/accept', [PartnerController::class, 'acceptOrder'])->name('partner.orders.accept');
    Route::post('/commandes/{order}/ready', [PartnerController::class, 'readyOrder'])->name('partner.orders.ready');
    Route::post('/commandes/{order}/reject', [PartnerController::class, 'rejectOrder'])->name('partner.orders.reject');
    Route::get('/releves', [PartnerController::class, 'statements'])->name('partner.statements');
    Route::get('/api/live-orders', [PartnerController::class, 'liveOrders'])->name('partner.api.live');
    Route::get('/menu', [PartnerController::class, 'products'])->name('partner.products');
    Route::post('/produits/{product}/toggle', [PartnerController::class, 'toggleProduct'])->name('partner.products.toggle');
    Route::post('/magasin/{store}/toggle', [PartnerController::class, 'toggleStoreStatus'])->name('partner.store.toggle');
});

// ── Espace Livreur (middleware: auth, role:courier) ──────────────
Route::prefix('livreur')->group(function () {
    Route::get('/', [CourierController::class, 'dashboard'])->name('courier.dashboard');
    Route::get('/courses/{id?}', [CourierController::class, 'delivery'])->name('courier.delivery');
    Route::post('/courses/{order}/status', [CourierController::class, 'updateOrderStatus'])->name('courier.order.status');
    Route::post('/location/ping', [CourierController::class, 'updateLocation'])->name('courier.location.ping');
    Route::get('/cash-et-gains', [CourierController::class, 'earnings'])->name('courier.earnings');
    Route::get('/remise-cash', [CourierController::class, 'cash'])->name('courier.cash');
    Route::post('/remise-cash', [CourierController::class, 'dropCash'])->name('courier.cash.drop');
    Route::get('/incident', [CourierController::class, 'incident'])->name('courier.incident');
    Route::post('/incident', [CourierController::class, 'storeIncident'])->name('courier.incident.store');
});

// ── Espace Ops (middleware: auth, role:ops,admin,finance) ──
Route::prefix('ops')->group(function () {
    Route::get('/', [OpsController::class, 'dashboard'])->name('ops.dashboard');
    Route::get('/commandes', [OpsController::class, 'orders'])->name('ops.orders');
    Route::get('/commandes/{id}', [OpsController::class, 'orderShow'])->name('ops.orders.show');
    Route::post('/commandes/{order}/assign', [OpsController::class, 'assignCourier'])->name('ops.orders.assign');
    Route::post('/commandes/{order}/status', [OpsController::class, 'updateStatus'])->name('ops.orders.status');
    Route::post('/commandes/{order}/cancel', [OpsController::class, 'cancelOrRefund'])->name('ops.orders.cancel');

    Route::get('/dispatch', [OpsController::class, 'dispatch'])->name('ops.dispatch');
    Route::get('/dispatch/live-data', [OpsController::class, 'liveData'])->name('ops.dispatch.live');

    Route::get('/restaurants', [OpsController::class, 'restaurants'])->name('ops.stores');
    Route::get('/restaurants/creer', [OpsController::class, 'storeCreate'])->name('ops.stores.create');
    Route::post('/restaurants', [OpsController::class, 'storeStore'])->name('ops.stores.store');
    Route::get('/restaurants/{id}', [OpsController::class, 'storeEdit'])->name('ops.stores.edit');
    Route::post('/restaurants/{id}', [OpsController::class, 'storeUpdate'])->name('ops.stores.update');
    Route::post('/restaurants/{id}/toggle', [OpsController::class, 'storeToggleStatus'])->name('ops.stores.toggle');
    Route::delete('/restaurants/{id}', [OpsController::class, 'storeDestroy'])->name('ops.stores.destroy');

    // Menu : Catégories et Produits
    Route::post('/restaurants/{id}/categories', [OpsController::class, 'categoryStore'])->name('ops.categories.store');
    Route::delete('/categories/{category}', [OpsController::class, 'categoryDestroy'])->name('ops.categories.destroy');
    Route::post('/restaurants/{id}/produits', [OpsController::class, 'productStore'])->name('ops.products.store');
    Route::post('/produits/{product}', [OpsController::class, 'productUpdate'])->name('ops.products.update');
    Route::post('/produits/{product}/toggle', [OpsController::class, 'productToggle'])->name('ops.products.toggle');
    Route::delete('/produits/{product}', [OpsController::class, 'productDestroy'])->name('ops.products.destroy');

    Route::get('/zones', [OpsController::class, 'zones'])->name('ops.zones');
    Route::post('/zones/{zone}/toggle', [OpsController::class, 'zoneToggleStatus'])->name('ops.zones.toggle');

    Route::get('/livreurs', [OpsController::class, 'couriers'])->name('ops.couriers');
    Route::post('/livreurs/cash-drop/{drop}/validate', [OpsController::class, 'validateCashDrop'])->name('ops.couriers.drop.validate');

    Route::get('/clients', [OpsController::class, 'customers'])->name('ops.customers');
    Route::post('/incidents/{incident}/resolve', [OpsController::class, 'resolveIncident'])->name('ops.incidents.resolve');

    Route::get('/grand-livre', [OpsController::class, 'ledger'])->name('ops.ledger');
    Route::get('/versements', [OpsController::class, 'payouts'])->name('ops.payouts');
    Route::get('/utilisateurs', [OpsController::class, 'users'])->name('ops.users');
    Route::get('/audit', [OpsController::class, 'audit'])->name('ops.audit');
});

// ── Raccourcis et alias ───────────────────────────────────
Route::get('/hub', function () {
    return view('hub');
})->name('hub');

Route::get('/test', function () {
    return view('teste');
});

Route::redirect('/partner', '/partenaire')->name('portal.partner');
Route::redirect('/courier', '/livreur')->name('portal.courier');
