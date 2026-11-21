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
    Route::get('/incident', [CourierController::class, 'incident'])->name('courier.incident');
});

// ── Espace Ops (middleware: auth, role:ops,admin,finance) ──
Route::prefix('ops')->group(function () {
    Route::get('/', [OpsController::class, 'dashboard'])->name('ops.dashboard');
    Route::view('/commandes', 'ops.orders')->name('ops.orders');
    Route::view('/commandes/{id}', 'ops.order-show')->name('ops.orders.show');
    Route::get('/dispatch', [OpsController::class, 'dispatch'])->name('ops.dispatch');
    Route::get('/dispatch/live-data', [OpsController::class, 'liveData'])->name('ops.dispatch.live');
    Route::post('/orders/{order}/assign', [OpsController::class, 'assignCourier'])->name('ops.orders.assign');
    Route::get('/restaurants', [OpsController::class, 'restaurants'])->name('ops.stores');
    Route::view('/restaurants/{id}', 'ops.store-edit')->name('ops.stores.edit');
    Route::view('/zones', 'ops.zones')->name('ops.zones');
    Route::get('/livreurs', [OpsController::class, 'couriers'])->name('ops.couriers');
    Route::view('/clients', 'ops.customers')->name('ops.customers');
    Route::get('/grand-livre', [OpsController::class, 'ledger'])->name('ops.ledger');
    Route::view('/versements', 'ops.payouts')->name('ops.payouts');
    Route::view('/utilisateurs', 'ops.users')->name('ops.users');
    Route::view('/audit', 'ops.audit')->name('ops.audit');
});

// ── Raccourcis et alias ───────────────────────────────────
Route::get('/test', function () {
    return view('teste');
});

Route::redirect('/partner', '/partenaire')->name('portal.partner');
Route::redirect('/courier', '/livreur')->name('portal.courier');
