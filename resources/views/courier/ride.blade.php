@extends('layouts.portal')
@section('title', 'Livreur · Course')
@section('role', 'courier')
@section('main')
    <div class="top"><div><h1>Course CD-221</h1><div class="mut">Grombalia · navigation externe</div></div><span class="bd ok row" style="gap:6px"><i class="live" style="background:var(--olive);animation:none"></i>Disponible</span></div>
    <div class="grid">
        <div class="card col">
            <b style="font-size:17px">Dar Zitouna → Avenue Habib Bourguiba</b>
            <div class="mut">Retrait : 2 min · Livraison : 12 min</div>
            @foreach (['Arrivé au restaurant', 'Commande récupérée', 'Arrivé chez le client', 'Livrée, code client + cash encaissé'] as $s)
                <div class="ride-step row" style="padding:10px 0;border-bottom:1px solid var(--line)"><span class="tile" style="width:28px;height:28px;border-radius:50%;font-size:13px;background:var(--soft);color:var(--mut)">{{ $loop->iteration }}</span><span>{{ $s }}</span></div>
            @endforeach
            <button id="ride-next" class="btn block" style="min-height:52px">Commande récupérée</button>
            <button class="btn o block">Signaler un incident</button>
            <div id="paid" class="card" style="display:none;background:#E3E8C8;animation:pop .5s ease both"><b>Course terminée</b><div class="mut">Cash encaissé : 21,900 TND, ajouté à votre solde.</div></div>
        </div>
        <div class="col">
            <div class="card g-warm"><span style="color:var(--soft);font-size:13px">À encaisser chez le client</span><div class="serif" style="font-size:38px;font-weight:700;margin-top:4px" data-count="21.900" data-dec="3" data-suffix=" TND">0</div></div>
            <div class="card col"><div class="row between"><b>Cash détenu</b><span class="mut">Plafond 150,000 TND</span></div><div class="bar"><i data-w="37"></i></div><b data-cash>56,000 TND</b></div>
        </div>
    </div>
    <style>.ride-step.done .tile{background:var(--olive)!important;color:var(--cream)!important}.ride-step.now .tile{background:var(--brown)!important;color:var(--cream)!important;animation:pulse 1.6s infinite}.ride-step.now span:last-child{font-weight:700}.ride-step.done span:last-child{color:var(--mut)}</style>
@endsection
