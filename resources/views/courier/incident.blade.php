@extends('layouts.portal')
@section('role', 'courier')
@section('title', 'Signaler un incident')

@section('content')
<x-title title="Signaler un incident" sub="L’équipe Ops traite chaque signalement et vous répond." />

<x-card style="max-width:640px">
    <form method="POST" action="#" enctype="multipart/form-data" class="stack">
        @csrf
        <label class="f">Course concernée
            <select name="order"><option>#FD-1041 · Dar Zitouna → Rim K.</option><option>#FD-1040 · Pâtisserie El Warda → Hatem S.</option></select>
        </label>
        <label class="f">Type d’incident
            <select name="type"><option>Client absent</option><option>Adresse introuvable</option><option>Commande incorrecte</option><option>Retard au restaurant</option><option>Accident ou panne</option><option>Autre</option></select>
        </label>
        <label class="f">Description<textarea name="description" placeholder="Que s’est-il passé ?"></textarea></label>
        <label class="f">Photo (facultatif)<input type="file" name="photo" accept="image/*"></label>
        <button class="btn block" type="submit">Envoyer à l’équipe Ops</button>
    </form>
</x-card>
@endsection
