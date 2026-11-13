@extends('layouts.portal')
@section('title', 'Partenaire · Historique')
@section('role', 'partner')
@section('main')
    <div class="top"><div><h1>Historique et relevés</h1><div class="mut">Versement chaque semaine, commission de 12 % déduite.</div></div><button class="btn o" onclick="document.getElementById('toast').textContent='Relevé exporté (prototype)';document.getElementById('toast').classList.add('show')">Exporter le relevé</button></div>
    <div class="grid">
        <div class="card kpi"><span class="mut">Commandes de la semaine</span><b data-count="43">0</b></div>
        <div class="card kpi"><span class="mut">Chiffre encaissé</span><b data-count="812.500" data-dec="3" data-suffix=" TND">0</b></div>
        <div class="card kpi g-warm"><span style="color:var(--soft);font-size:13px">Prochain versement</span><b data-count="715.000" data-dec="3" data-suffix=" TND">0</b></div>
    </div>
    <div class="card scroll-x"><table><thead><tr><th>Commande</th><th>Date</th><th>État</th><th>Total</th><th>Commission</th></tr></thead><tbody>
        @foreach ($rows as [$id, $d, $s, $t, $c])
            <tr><td><b>{{ $id }}</b></td><td>{{ $d }}</td><td><span class="bd {{ $s === 'Livrée' ? 'ok' : 'nt' }}">{{ $s }}</span></td><td>{{ App\Support\Demo::tnd($t) }}</td><td>{{ App\Support\Demo::tnd($c) }}</td></tr>
        @endforeach
    </tbody></table></div>
@endsection
