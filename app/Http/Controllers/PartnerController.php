<?php

namespace App\Http\Controllers;

use App\Support\Demo;

class PartnerController extends Controller
{
    public function orders() { return view('partner.orders', ['orders' => Demo::partnerOrders()]); }
    public function menu() { return view('partner.menu', ['store' => Demo::store(1)]); }
    public function history() { return view('partner.history', ['rows' => Demo::history()]); }
}
