@extends('layouts.portal')
@section('role', 'courier')
@section('title', 'Remise de cash au QG')

@section('content')
<x-title title="Remise de cash au QG" sub="Déposez le cash encaissé auprès de l'agence FromDarna Grombalia pour réinitialiser votre plafond." />

@if(session('info'))<div class="flash info" role="alert">{{ session('info') }}</div>@endif

<div class="cols">
    <x-card>
        <h2>Dépôt d'espèces</h2>
        <div style="background:var(--bg2);border:1px solid var(--line);border-radius:14px;padding:16px;margin:14px 0">
            <div style="display:flex;justify-content:space-between;align-items:center">
                <span>Cash détenu en main :</span>
                <strong style="font-size:26px;color:{{ $heldCash >= $cashLimit ? 'var(--tomate)' : 'var(--rust)' }}">
                    {{ \App\Support\Money::tnd($heldCash) }}
                </strong>
            </div>

            <div class="bar {{ $cashPercent > 80 ? 'warn' : '' }}" style="margin:10px 0">
                <i style="width:{{ $cashPercent }}%"></i>
            </div>

            <div style="display:flex;justify-content:space-between;font-size:12px" class="mut">
                <span>Niveau : {{ $cashPercent }} %</span>
                <span>Plafond : {{ \App\Support\Money::tnd($cashLimit) }}</span>
            </div>
        </div>

        @if($heldCash > 0)
            <form method="POST" action="{{ route('courier.cash.drop') }}" class="stack">
                @csrf
                <p class="mut" style="font-size:13px">
                    En cliquant ci-dessous, vous confirmez avoir remis la totalité du montant de <strong>{{ \App\Support\Money::tnd($heldCash) }}</strong> à l'agent d'accueil du bureau FromDarna de Grombalia.
                </p>

                <label class="f">
                    Commentaire / Reçu de caisse (facultatif)
                    <input type="text" name="notes" placeholder="Remis en billets de 10 et 20 TND...">
                </label>

                <button class="btn block olive glow grad-animate" type="submit" onclick="return confirm('Confirmer la remise de {{ \App\Support\Money::tnd($heldCash) }} au QG ?')">
                    💵 Valider la remise de {{ \App\Support\Money::tnd($heldCash) }} au QG
                </button>
            </form>
        @else
            <div class="al g" style="margin-top:12px">
                <span>✅ Vous n'avez aucun cash en attente de versement. Votre plafond est disponible à 100%.</span>
            </div>
            <x-btn :href="route('courier.dashboard')" variant="ghost" style="margin-top:12px">Retour à mes courses</x-btn>
        @endif
    </x-card>

    <x-card>
        <h2>Dernières remises enregistrées</h2>
        @if($cashDrops->count())
            <div style="display:flex;flex-direction:column;gap:10px;margin-top:12px">
                @foreach($cashDrops as $drop)
                    <div style="padding:10px 12px;background:var(--bg2);border-radius:12px;border:1px solid var(--line)">
                        <div style="display:flex;justify-content:space-between;font-size:14px">
                            <strong>#{{ $drop->reference }}</strong>
                            <strong style="color:#2E7D32">{{ \App\Support\Money::tnd($drop->amount_millimes) }}</strong>
                        </div>
                        <div class="mut" style="font-size:12px;margin-top:4px">
                            {{ $drop->created_at->format('d/m/Y à H:i') }} · <span class="bd g" style="font-size:11px">Validé</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="mut" style="padding:16px;text-align:center">Aucun reçu de versement archivé.</p>
        @endif
    </x-card>
</div>
@endsection
