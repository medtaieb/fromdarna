<?php

namespace App\Http\Controllers;

use App\Support\Demo;

class OpsController extends Controller
{
    public function dispatch() { return view('ops.dispatch', ['couriers' => Demo::couriers()]); }
    public function restaurants() { return view('ops.restaurants', ['stores' => Demo::stores()]); }
    public function couriers() { return view('ops.couriers', ['couriers' => Demo::couriers()]); }
    public function ledger() { return view('ops.ledger', ['lines' => Demo::ledger()]); }
}
