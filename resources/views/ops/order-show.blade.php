@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Détail de commande')

@section('content')
<x-title :title="'Commande #FD-' . ($id ?? 1043)" sub="Dar Zitouna → Sana M. · Grombalia">
    <x-slot name="actions"><x-badge type="o">Reçue</x-badge><x-btn variant="ghost" size="sm" :href="route('ops.dispatch')">Assigner un livreur</x-btn></x-slot>
</x-title>

<div class="cols">
    <x-card>
        <h2>Historique (journal des transitions)</h2>
        <x-table :headers="['Heure', 'Événement', 'Auteur']" :rows="[
            ['12:44:03', 'Commande créée (cash)', 'Client'],
            ['12:44:04', 'Statut PLACED · restaurant notifié', 'Système'],
            ['12:45:05', 'Alerte : aucune réponse du restaurant', 'Système'],
            ['12:45:40', 'Appel de relance au restaurant', 'Dispatcher · Yosra'],
        ]" />
        <p class="mut">Le journal est en ajout seul : aucune ligne ne peut être modifiée.</p>
    </x-card>

    <div class="stack">
        <x-card>
            <h2>Montants</h2>
            <div class="sum"><span>Plats</span><span>@tnd(21500)</span></div>
            <div class="sum"><span>Livraison</span><span>@tnd(3000)</span></div>
            <div class="sum t"><span>Total cash</span><span>@tnd(24500)</span></div>
        </x-card>
        <x-card>
            <h2>Remboursement ou annulation</h2>
            <form method="POST" action="#" class="stack">
                @csrf
                <label class="f">Motif<select name="reason"><option>Article manquant</option><option>Mauvaise commande</option><option>Retard important</option><option>Annulée par le restaurant</option></select></label>
                <x-field label="Montant (TND)" name="amount" placeholder="0,000" inputmode="decimal" />
                <button class="btn red block" type="submit" data-confirm="Valider ce remboursement ? L’action est tracée.">Valider (tracé dans l’audit)</button>
            </form>
        </x-card>
    </div>
</div>
@endsection
