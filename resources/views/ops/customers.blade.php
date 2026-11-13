@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Clients et incidents')

@section('content')
<x-title title="Clients et incidents" />

<x-card>
    <h2>Remboursements à valider</h2>
    <x-table :headers="['Commande', 'Client', 'Motif', 'Montant', '']">
        <tr>
            <td><b>#FD-1039</b></td><td>Ines L.</td><td>Article manquant</td><td>@tnd(3500)</td>
            <td class="acts"><button class="btn olive sm" type="button">Valider</button><button class="btn ghost sm" type="button">Refuser</button></td>
        </tr>
    </x-table>
</x-card>

<x-card>
    <h2>Clients</h2>
    <x-table :headers="['Client', 'Téléphone', 'Commandes', 'Cash', 'Incidents']" :rows="[
        ['Sana M.', '+216 20 *** 456', '9', 'g:Autorisé', '0'],
        ['Hatem S.', '+216 55 *** 118', '4', 'g:Autorisé', '1'],
        ['Nader Z.', '+216 98 *** 702', '2', 'r:Désactivé (2 absences)', '2'],
    ]" />
</x-card>
@endsection
