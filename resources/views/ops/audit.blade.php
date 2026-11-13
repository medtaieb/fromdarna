@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Journal d’audit')

@section('content')
<x-title title="Journal d’audit" sub="Toutes les actions sensibles sont enregistrées avec leur auteur." />
<x-card>
    <x-table :headers="['Date', 'Auteur', 'Action', 'Objet']" :rows="[
        ['Aujourd’hui 12:45', 'Yosra B.', 'Relance restaurant', '#FD-1043'],
        ['Aujourd’hui 12:31', 'Yosra B.', 'Assignation manuelle', '#FD-1041 → Walid T.'],
        ['Aujourd’hui 11:58', 'Karim H.', 'Validation de versement', 'Sultan Grill · semaine 42'],
        ['Hier 17:20', 'Yosra B.', 'Remboursement validé', '#FD-1031 · 3,500 TND'],
        ['Hier 09:05', 'Admin', 'Changement de commission', 'Dar Zitouna · 1200 bps'],
    ]" />
</x-card>
@endsection
