@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Modifier ' . $store->name)

@section('content')
<x-title :title="$store->name" :sub="'Fiche technique, commission et carte · Zone ' . ($store->zone?->name ?? 'Grombalia')">
    <x-slot name="actions">
        <x-btn variant="ghost" size="sm" :href="route('ops.stores')">← Liste des restaurants</x-btn>
    </x-slot>
</x-title>

@if(session('success'))
    <div class="al g mb">
        <span>✓ {{ session('success') }}</span>
    </div>
@endif

<form method="POST" action="{{ route('ops.stores.update', $store->id) }}">
    @csrf
    <div class="grid g2 mb">
        <x-card>
            <h2>Paramètres opérationnels</h2>
            <x-field label="Nom de l'établissement" name="name" :value="$store->name" required />
            <x-field label="Téléphone professionnel" name="phone" :value="$store->phone ?? '+216 72 000 000'" />
            <x-field label="Adresse physique" name="address" :value="$store->address ?? 'Centre-ville'" />
            
            <div class="grid g2">
                <x-field label="Commission (points de base, 1200 = 12%)" name="commission_bps" :value="$store->commission_bps ?? 1200" required />
                <x-field label="Préparation moyenne (minutes)" name="avg_prep_minutes" :value="$store->avg_prep_minutes ?? 15" required />
            </div>

            <label class="f">
                Zone de rattachement :
                <select name="zone_id" required>
                    @foreach($zones as $z)
                        <option value="{{ $z->id }}" {{ $store->zone_id == $z->id ? 'selected' : '' }}>
                            {{ $z->name }} ({{ $z->ar_name }})
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="ck" style="margin-top:12px;">
                <input type="checkbox" name="is_open" value="1" {{ $store->is_open ? 'checked' : '' }}>
                <strong>Restaurant actuellement ouvert aux commandes en ligne</strong>
            </label>

            <div style="margin-top:16px;">
                <button class="btn block" type="submit">Enregistrer les modifications</button>
            </div>
        </x-card>

        <x-card>
            <h2>Localisation & Coordonnées GPS</h2>
            <div style="font-size:0.9rem;margin-bottom:12px;">
                <div>Latitude : <b>{{ $store->latitude ?? 36.6175 }}</b></div>
                <div>Longitude : <b>{{ $store->longitude ?? 10.4990 }}</b></div>
            </div>
            
            <div style="padding:16px;background:#f5efe6;border-radius:8px;border:1px dashed #b08861;text-align:center;">
                <div style="font-size:2rem;margin-bottom:6px;">📍</div>
                <b>Point de retrait des livreurs</b>
                <p class="mut" style="font-size:0.85rem;margin:4px 0 10px 0;">Coordonnées utilisées par l'algorithme de dispatch pour router les coursiers.</p>
                <a class="btn ghost sm" target="_blank" href="https://www.google.com/maps?q={{ $store->latitude ?? 36.6175 }},{{ $store->longitude ?? 10.4990 }}">
                    Vérifier sur Google Maps ↗
                </a>
            </div>

            <div style="margin-top:20px;">
                <h2>Horaires de service</h2>
                <div style="display:flex;flex-direction:column;gap:6px;font-size:0.85rem;">
                    @foreach (['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'] as $d)
                        <div class="row" style="flex-wrap:nowrap;justify-content:space-between;padding:4px 0;border-bottom:1px solid #eee;">
                            <span style="font-weight:600;">{{ $d }}</span>
                            <span class="mut">11:00 — 22:30</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-card>
    </div>
</form>

<x-card>
    <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h2 style="margin:0;">Menu & Plats au catalogue ({{ $store->categories->sum(fn($c) => $c->products->count()) }})</h2>
        <x-btn variant="ghost" size="sm" :href="route('store', $store->id)">Voir la fiche restaurant ↗</x-btn>
    </div>

    @foreach($store->categories as $category)
        <div style="margin-top:16px;">
            <h3 style="font-size:1.05rem;color:var(--accent);margin-bottom:8px;border-bottom:1px solid #ddd;padding-bottom:4px;">
                {{ $category->name }}
            </h3>
            <x-table :headers="['Produit', 'Description', 'Prix', 'Disponibilité']">
                @foreach ($category->products as $p)
                    <tr>
                        <td><b>{{ $p->name }}</b></td>
                        <td class="mut" style="font-size:0.85rem;max-width:320px;">{{ $p->description }}</td>
                        <td><strong style="color:var(--olive);">{{ \App\Support\Money::tnd($p->price_millimes) }}</strong></td>
                        <td>
                            <x-badge :type="$p->is_available ? 'g' : 'x'">
                                {{ $p->is_available ? 'Disponible' : 'En rupture' }}
                            </x-badge>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </div>
    @endforeach
</x-card>
@endsection
