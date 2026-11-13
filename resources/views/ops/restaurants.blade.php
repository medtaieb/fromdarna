@extends('layouts.portal')
@section('title', 'Ops · Restaurants')
@section('role', 'ops')
@section('main')
    <div class="top"><div><h1>Restaurants et menus</h1><div class="mut">Saisie des menus par l’équipe, commission par partenaire.</div></div><button class="btn">Ajouter un restaurant</button></div>
    <div class="card scroll-x"><table><thead><tr><th>Restaurant</th><th>Cuisine</th><th>Commission</th><th>Prépa moyenne</th><th>Actif</th></tr></thead><tbody>
        @foreach ($stores as $s)
            <tr class="up" style="--i:{{ $loop->index }}"><td><div class="row"><span class="tile" style="width:40px;height:40px;font-size:16px;border-radius:12px;background:linear-gradient(135deg,{{ $s['c1'] }},{{ $s['c2'] }})">{{ mb_substr($s['name'], 0, 1) }}</span><b>{{ $s['name'] }}</b></div></td><td>{{ $s['cuisine'] }}</td><td>12 %</td><td>{{ 12 + $loop->index * 3 }} min</td>
            <td><label class="switch"><input type="checkbox" data-row {{ $s['open'] ? 'checked' : '' }} aria-label="Actif : {{ $s['name'] }}"><i></i></label><span class="st" hidden></span></td></tr>
        @endforeach
    </tbody></table></div>
@endsection
