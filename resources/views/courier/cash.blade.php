@extends('layouts.portal')
@section('title', 'Livreur · Cash')
@section('role', 'courier')
@section('main')
    <div class="top"><div><h1>Remise du cash</h1><div class="mut">Remise quotidienne au QG de Grombalia, ou par virement.</div></div></div>
    <div class="card col" style="max-width:520px">
        <div class="row between"><b>Cash détenu</b><b class="serif" style="font-size:26px" data-held>56,000 TND</b></div>
        <div class="bar"><i data-w="37"></i></div><span class="mut">Plafond 150,000 TND : au-delà, plus de course cash.</span>
        <button id="remit" class="btn block" style="min-height:52px">Remettre le cash au QG</button>
        <span class="mut">Tout écart est tracé et rapproché par Ops.</span>
    </div>
@endsection
