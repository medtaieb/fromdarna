@extends('layouts.site')
@section('title', 'Mon compte')

@section('content')
<x-title title="Mon compte" />

<div class="grid g2">
    <x-card>
        <h2>Profil</h2>
        <x-field label="Prénom et nom" name="name" value="Sana M." />
        <x-field label="Téléphone" name="phone" type="tel" value="+216 20 123 456" disabled />
        <button class="btn" type="button" style="align-self:flex-start">Enregistrer</button>
    </x-card>

    <x-card>
        <h2>Langue et notifications</h2>
        <label class="ck"><input type="radio" name="lang" checked> Français</label>
        <label class="ck"><input type="radio" name="lang"> <span class="ar">العربية</span></label>
        <label class="ck"><input type="checkbox" checked> Être prévenu(e) de l’état de mes commandes</label>
        <label class="ck"><input type="checkbox"> Recevoir les offres de ma ville</label>
    </x-card>

    <x-card>
        <h2>Adresse enregistrée</h2>
        <div class="map" style="height:130px"><span>Grombalia · repère : en face de la pharmacie</span></div>
        <x-btn variant="ghost" size="sm" :href="route('checkout')">Modifier</x-btn>
    </x-card>

    <x-card>
        <h2>Supprimer mon compte</h2>
        <p class="mut">Vos données personnelles sont effacées. Cette action est définitive.</p>
        <button class="btn red" type="button" style="align-self:flex-start" data-confirm="Supprimer définitivement votre compte ?">Supprimer mon compte</button>
    </x-card>
</div>
@endsection
