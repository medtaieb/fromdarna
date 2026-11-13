<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
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

Route::post('/deconnexion', [AuthController::class, 'logout'])->name('logout');
Route::get('/deconnexion', [AuthController::class, 'logout'])->name('logout.get');

// ── Client ───────────────────────────────────────────────
Route::view('/restaurants', 'customer.feed')->name('feed');
Route::view('/restaurants/{id}', 'customer.store')->whereNumber('id')->name('store');
Route::view('/panier', 'customer.cart')->name('cart');
Route::view('/commande', 'customer.checkout')->name('checkout');
Route::post('/commande', fn () => redirect()->route('orders.show', 1043))->name('orders.place');
Route::view('/mes-commandes', 'customer.orders')->name('orders.index');
Route::view('/mes-commandes/{id}', 'customer.order-show')->name('orders.show');
Route::view('/compte', 'customer.account')->name('account');
Route::view('/hors-zone', 'customer.out-of-zone')->name('out-of-zone');

// ── Espace Partenaire (middleware: auth, role:partner_owner,partner_staff) ──
Route::prefix('partenaire')->middleware(['auth', 'role:partner_owner,partner_staff'])->group(function () {
    Route::view('/', 'partner.dashboard')->name('partner.dashboard');
    Route::view('/commandes', 'partner.orders')->name('partner.orders');
    Route::view('/commandes/{id}', 'partner.order-show')->name('partner.orders.show');
    Route::view('/menu', 'partner.products')->name('partner.products');
    Route::view('/releves', 'partner.statements')->name('partner.statements');
});

// ── Espace Livreur (middleware: auth, role:courier) ──────────────
Route::prefix('livreur')->middleware(['auth', 'role:courier'])->group(function () {
    Route::view('/', 'courier.dashboard')->name('courier.dashboard');
    Route::view('/courses/{id}', 'courier.delivery')->name('courier.delivery');
    Route::view('/cash-et-gains', 'courier.earnings')->name('courier.earnings');
    Route::view('/incident', 'courier.incident')->name('courier.incident');
});

// ── Espace Ops (middleware: auth, role:ops,admin,finance) ──
Route::prefix('ops')->middleware(['auth', 'role:ops,admin,finance'])->group(function () {
    Route::view('/', 'ops.dashboard')->name('ops.dashboard');
    Route::view('/commandes', 'ops.orders')->name('ops.orders');
    Route::view('/commandes/{id}', 'ops.order-show')->name('ops.orders.show');
    Route::view('/dispatch', 'ops.dispatch')->name('ops.dispatch');
    Route::view('/restaurants', 'ops.stores')->name('ops.stores');
    Route::view('/restaurants/{id}', 'ops.store-edit')->name('ops.stores.edit');
    Route::view('/zones', 'ops.zones')->name('ops.zones');
    Route::view('/livreurs', 'ops.couriers')->name('ops.couriers');
    Route::view('/clients', 'ops.customers')->name('ops.customers');
    Route::view('/grand-livre', 'ops.ledger')->name('ops.ledger');
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
