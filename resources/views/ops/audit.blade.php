@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Journal d’Audit')

@section('content')
<x-title title="Journal d’audit & Traçabilité" sub="Enregistrement immuable de toutes les actions opérationnelles et financières" />

<x-card>
    <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h2 style="margin:0;">Événements récents tracés sur la plateforme</h2>
    </div>

    @if($events->isEmpty())
        <div style="text-align:center;padding:32px;color:var(--muted);">
            Aucun événement enregistré dans le journal d'audit pour l'instant.
        </div>
    @else
        <x-table :headers="['Date / Heure', 'Auteur / Acteur', 'Action opérée', 'Objet concerné']">
            @foreach ($events as $event)
                <tr>
                    <td class="mut" style="font-size:0.85rem;white-space:nowrap;">
                        <b>{{ $event['date'] }}</b>
                    </td>
                    <td>
                        <b>{{ $event['author'] }}</b>
                    </td>
                    <td>
                        <span>{{ $event['action'] }}</span>
                    </td>
                    <td>
                        <strong style="color:var(--olive);">{{ $event['object'] }}</strong>
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif
    <p class="mut" style="font-size:0.8rem;margin-top:12px;">
        ℹ️ Le journal d'audit est en ajout seul (append-only). Aucune écriture ne peut être modifiée ni supprimée.
    </p>
</x-card>
@endsection
