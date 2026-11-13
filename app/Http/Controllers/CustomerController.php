<?php

namespace App\Http\Controllers;

use App\Support\Demo;

class CustomerController extends Controller
{
    public function login() { return view('customer.login'); }
    public function home() { return view('customer.home', ['stores' => Demo::stores()]); }
    public function store(int $id) { return view('customer.store', ['store' => Demo::store($id)]); }
    public function cart() { return view('customer.cart'); }
    public function track() { return view('customer.track'); }
    public function orders() { return view('customer.orders'); }
    public function help() { return view('customer.help'); }
}
