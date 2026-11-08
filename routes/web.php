<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('home');
});

Route::get('/test', function () {
    return view('teste'); // On ajoute le "e"
});

Route::view('/partner', 'portal', ['role' => 'partner'])->name('portal.partner');
Route::view('/courier', 'portal', ['role' => 'courier'])->name('portal.courier');
Route::view('/ops', 'portal', ['role' => 'ops'])->name('portal.ops');