@extends('layouts.portal')
@section('title', 'Partenaire · Menu')
@section('role', 'partner')
@section('main')
    <div class="top"><div><h1>Menu et ruptures</h1><div class="mut">Un produit indisponible disparaît tout de suite côté client.</div></div></div>
    <div class="card scroll-x">
        <table>
            <thead><tr><th>Produit</th><th>Prix</th><th>État</th><th>En vente</th></tr></thead>
            <tbody>
                @foreach ($store['items'] as $it)
                    <tr class="up" style="--i:{{ $loop->index }}">
                        <td><b class="name">{{ $it['name'] }}</b><div class="ar mut">{{ $it['ar'] }}</div></td>
                        <td>{{ App\Support\Demo::tnd($it['price']) }}</td>
                        <td class="st">Disponible</td>
                        <td><label class="switch"><input type="checkbox" data-row checked aria-label="En vente : {{ $it['name'] }}"><i></i></label></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
