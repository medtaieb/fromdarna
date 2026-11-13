@extends('layouts.site')
@section('title', 'Hors zone')

@section('content')
<x-card style="align-items:center;text-align:center;padding:48px 24px">
    <div style="font-size:54px" aria-hidden="true">📍</div>
    <h1>Nous ne livrons pas encore chez vous</h1>
    <p class="mut" style="max-width:34em">Nous ouvrons une ville à la fois : Grombalia d’abord, puis Béni Khalled, Soliman et Bou Argoub. Écrivez-nous pour savoir où nous en sommes.</p>
    <div class="acts" style="justify-content:center">
        <x-btn :href="'https://wa.me/' . config('cooked.whatsapp')">Écrire sur WhatsApp</x-btn>
        <x-btn variant="ghost" :href="route('feed')">Changer d’adresse</x-btn>
    </div>
</x-card>
@endsection
