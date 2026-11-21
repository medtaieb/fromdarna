@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Ajouter un Restaurant Partenaire')

@section('content')
<x-title title="Ajouter un Restaurant Partenaire" sub="Création de la fiche établissement et configuration de la zone">
    <x-slot name="actions">
        <x-btn variant="ghost" size="sm" :href="route('ops.stores')">← Retour à la liste</x-btn>
    </x-slot>
</x-title>

@if ($errors->any())
    <div class="al r mb">
        <ul style="margin:0;padding-left:16px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('ops.stores.store') }}">
    @csrf
    <div class="grid g2">
        <x-card>
            <h2>Informations Générales</h2>
            <x-field label="Nom du restaurant *" name="name" :value="old('name')" placeholder="Ex: Restaurant El Bey, Pizzeria Capri..." required />

            <div class="grid g2">
                <x-field label="Spécialité / Cuisine *" name="kind" :value="old('kind', 'Restaurant tunisien')" placeholder="Ex: Grillades & Plats, Pizza..." required />
                <x-field label="Emoji emblème" name="emoji" :value="old('emoji', '🍲')" placeholder="🍲, 🍕, 🥩, 🥐, 🥪" />
            </div>

            <label class="f">
                Zone de livraison couverte * :
                <select name="zone_id" required>
                    @foreach($zones as $z)
                        <option value="{{ $z->id }}" {{ old('zone_id') == $z->id ? 'selected' : '' }}>
                            {{ $z->name }} ({{ $z->ar_name }})
                        </option>
                    @endforeach
                </select>
            </label>

            <div class="grid g2">
                <x-field label="Téléphone contact" name="phone" :value="old('phone', '+216 72 000 000')" />
                <x-field label="Adresse physique" name="address" :value="old('address')" placeholder="Avenue Habib Bourguiba, Centre..." />
            </div>

            <label class="ck" style="margin-top:14px;">
                <input type="checkbox" name="is_open" value="1" {{ old('is_open', true) ? 'checked' : '' }}>
                <strong>Activer et ouvrir immédiatement aux commandes en ligne</strong>
            </label>
        </x-card>

        <x-card>
            <h2>Conditions Commerciales & Dispatch</h2>
            <div class="grid g2">
                <x-field 
                    label="Taux de Commission (bps) *" 
                    name="commission_bps" 
                    type="number" 
                    :value="old('commission_bps', 1200)" 
                    helper="1200 bps = 12% standard FromDarna" 
                    required 
                />
                <x-field 
                    label="Préparation moyenne (min) *" 
                    name="avg_prep_minutes" 
                    type="number" 
                    :value="old('avg_prep_minutes', 15)" 
                    helper="Délai estimé cuisine" 
                    required 
                />
            </div>

            <x-field 
                label="Commande minimum (millimes)" 
                name="min_order_millimes" 
                type="number" 
                :value="old('min_order_millimes', 10000)" 
                helper="10000 millimes = 10.000 TND" 
            />

            <h2>Coordonnées GPS (Retrait livreurs)</h2>
            <div class="grid g2">
                <x-field label="Latitude" name="latitude" type="text" :value="old('latitude', '36.6175')" placeholder="36.6175" />
                <x-field label="Longitude" name="longitude" type="text" :value="old('longitude', '10.4990')" placeholder="10.4990" />
            </div>
            <p class="mut" style="font-size:0.8rem;">
                ℹ️ Coordonnées par défaut centrées sur Grombalia. Vous pourrez les ajuster à tout moment sur la carte.
            </p>

            <div style="margin-top:20px;">
                <button class="btn block glow" type="submit">✓ Créer le restaurant & Passer au Menu</button>
            </div>
        </x-card>
    </div>
</form>
@endsection
