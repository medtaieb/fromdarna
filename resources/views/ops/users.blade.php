@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Utilisateurs et rôles')

@section('content')
<x-title title="Utilisateurs et rôles" sub="Droits minimaux : chaque rôle ne voit que ce dont il a besoin.">
    <x-slot name="actions"><x-btn>Inviter un utilisateur</x-btn></x-slot>
</x-title>

<x-card>
    <x-table :headers="['Utilisateur', 'Rôle', 'Double authentification', 'Dernière connexion']" :rows="[
        ['Yosra B.|yosra@exemple.tn', 'Ops', 'g:Activée', 'Aujourd’hui, 12:02'],
        ['Karim H.|karim@exemple.tn', 'Finance', 'g:Activée', 'Hier, 18:40'],
        ['Dar Zitouna|contact@exemple.tn', 'Partenaire (propriétaire)', 'x:Non requise', 'Aujourd’hui, 11:15'],
        ['Walid T.|+216 20 *** 456', 'Livreur', 'x:Non requise', 'Aujourd’hui, 10:58'],
    ]" />
    <p class="mut">Rôles : client, partner_owner, partner_staff, courier, ops, admin, finance.</p>
</x-card>
@endsection
