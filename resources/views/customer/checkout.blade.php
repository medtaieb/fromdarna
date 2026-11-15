@extends('layouts.site')
@section('title', 'Confirmer la commande')

@section('content')
<x-title title="Confirmer la commande" sub="Dar Zitouna · paiement en cash à la livraison" />

<form method="POST" action="{{ route('orders.place') }}" class="cols">
    @csrf
    {{-- Clé d'idempotence : évite les doublons si le client touche deux fois --}}
    <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">

    <div class="stack">
        <x-card>
            @php
                $user = Auth::user();
                $savedAddress = $user?->defaultAddress ?? $user?->addresses()->first();
            @endphp
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                <h2>Où livrer ?</h2>
                @if($user)
                    <a href="{{ route('account') }}" class="mut" style="font-size:13px;color:var(--rust);text-decoration:none">
                        Mes adresses ({{ $user->addresses()->count() }}) ➔
                    </a>
                @endif
            </div>

            @if($savedAddress)
                <div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:10px 14px;margin-bottom:10px;font-size:13.5px;display:flex;align-items:center;gap:8px">
                    <span>🏠</span>
                    <span><strong>Adresse principale :</strong> {{ $savedAddress->city }} · {{ $savedAddress->landmark }}</span>
                </div>
            @endif

            <div class="map"><span>Déplacez l’épingle sur votre porte à Grombalia</span></div>
            <x-field label="Repère indispensable *" name="landmark" placeholder="En face de la pharmacie, portail vert…" :value="old('landmark', $savedAddress?->landmark)" required />
            <div class="grid g3">
                <x-field label="Bâtiment" name="building" :value="old('building', $savedAddress?->building)" />
                <x-field label="Étage" name="floor" :value="old('floor', $savedAddress?->floor)" />
                <x-field label="Porte" name="door" :value="old('door', $savedAddress?->door)" />
            </div>
            <x-field label="Téléphone pour le livreur" name="phone" type="tel" placeholder="+216 20 123 456" :value="old('phone', $savedAddress?->contact_phone ?? $user?->phone ?? '+216 20 123 456')" required />
        </x-card>

        <x-card>
            <h2>Paiement</h2>
            <label class="ck"><input type="radio" name="payment" value="cash" checked> Cash à la livraison (Espèces)</label>
            <label class="ck" style="opacity:.55"><input type="radio" name="payment" value="online" disabled> Carte bancaire, D17, Flouci <x-badge type="x">Bientôt au Lot 2</x-badge></label>
        </x-card>
    </div>

    <x-card>
        <h2>Récapitulatif</h2>
        <div class="sum"><span>1 × Couscous à l’agneau</span><span>@tnd(18000)</span></div>
        <div class="sum"><span>2 × Brik à l’œuf</span><span>@tnd(7000)</span></div>
        <div class="sum"><span>Livraison (Grombalia)</span><span>@tnd(3000)</span></div>
        <div class="sum t"><span>Total à payer</span><span>@tnd(28000)</span></div>
        <button class="btn block" type="submit">Confirmer la commande (Cash)</button>
    </x-card>
</form>
@endsection
