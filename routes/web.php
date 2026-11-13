<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Les paramètres de Route::view (ex. {id}) sont transmis aux vues.

// ── Public ───────────────────────────────────────────────
Route::get('/', HomeController::class)->name('home');

// ── Authentification ─────────────────────────────────────
Route::view('/connexion', 'auth.login')->name('login');
Route::post('/connexion', fn () => redirect()->route('otp.verify'))->name('otp.send');
Route::view('/connexion/code', 'auth.verify')->name('otp.verify');
Route::post('/connexion/code', fn () => redirect()->route('feed'))->name('otp.check');
Route::view('/equipe/connexion', 'auth.staff-login')->name('staff.login');
Route::post('/equipe/connexion', fn () => redirect()->route('staff.mfa'))->name('staff.attempt');
Route::view('/equipe/verification', 'auth.mfa')->name('staff.mfa');
Route::post('/equipe/verification', fn () => redirect()->route('ops.dashboard'))->name('staff.mfa.check');

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

// ── Espace Partenaire (ajouter ->middleware(['auth', 'role:partner_owner,partner_staff'])) ──
Route::prefix('partenaire')->group(function () {
    Route::view('/', 'partner.dashboard')->name('partner.dashboard');
    Route::view('/commandes', 'partner.orders')->name('partner.orders');
    Route::view('/commandes/{id}', 'partner.order-show')->name('partner.orders.show');
    Route::view('/menu', 'partner.products')->name('partner.products');
    Route::view('/releves', 'partner.statements')->name('partner.statements');
});

// ── Espace Livreur (middleware role:courier) ──────────────
Route::prefix('livreur')->group(function () {
    Route::view('/', 'courier.dashboard')->name('courier.dashboard');
    Route::view('/courses/{id}', 'courier.delivery')->name('courier.delivery');
    Route::view('/cash-et-gains', 'courier.earnings')->name('courier.earnings');
    Route::view('/incident', 'courier.incident')->name('courier.incident');
});

// ── Espace Ops (middleware role:ops,admin,finance + MFA) ──
Route::prefix('ops')->group(function () {
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

// ── Raccourcis et tests ───────────────────────────────────
Route::get('/test', function () {
    return view('teste');
});

Route::view('/partner', 'portal', ['role' => 'partner'])->name('portal.partner');
Route::view('/courier', 'portal', ['role' => 'courier'])->name('portal.courier');
Route::view('/ops', 'portal', ['role' => 'ops'])->name('portal.ops');
