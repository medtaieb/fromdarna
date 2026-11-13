<?php

namespace App\Http\Controllers;

use App\Support\Demo;

class CourierController extends Controller
{
    public function ride() { return view('courier.ride'); }
    public function earnings() { return view('courier.earnings'); }
    public function cash() { return view('courier.cash'); }
}
