@extends('layouts.portal')
@section('role', 'courier')
@section('title', 'Signaler un incident')

@section('content')
<x-title title="Signaler un incident" sub="L’équipe Ops FromDarna traite chaque signalement en temps réel pour vous assister." />

@if(session('success'))<div class="flash ok" role="alert">{{ session('success') }}</div>@endif

<div class="cols">
    <x-card>
        <h2>Nouveau signalement</h2>
        <form method="POST" action="{{ route('courier.incident.store') }}" class="stack" style="margin-top:12px">
            @csrf

            <label class="f">
                Course concernée (facultatif)
                <select name="order_id">
                    <option value="">-- Aucune course spécifique / Panne générale --</option>
                    @foreach($recentOrders as $ro)
                        <option value="{{ $ro->id }}">#{{ $ro->ref }} · {{ $ro->store->name }} ({{ $ro->delivery_landmark ?? $ro->delivery_city }})</option>
                    @endforeach
                </select>
            </label>

            <label class="f">
                Type d’incident *
                <select name="type" required>
                    <option value="Client absent / Injoignable">Client absent / Injoignable au téléphone</option>
                    <option value="Adresse introuvable / Erreur repère">Adresse introuvable / Erreur de repère</option>
                    <option value="Panne de véhicule / Scooter">Panne de véhicule (scooter / moto / vélo)</option>
                    <option value="Retard excessif au restaurant">Retard excessif de préparation au restaurant</option>
                    <option value="Commande endommagée / Sac percé">Commande endommagée / Sac percé</option>
                    <option value="Refus de payer / Litige cash">Refus de payer / Litige montant cash</option>
                    <option value="Autre urgence terrain">Autre urgence terrain</option>
                </select>
            </label>

            <label class="f">
                Description détaillée de la situation *
                <textarea name="description" placeholder="Expliquez ce qui se passe pour que le dispatcher puisse intervenir immédiatement..." rows="4" required></textarea>
            </label>

            <button class="btn block red glow" type="submit">
                🚨 Envoyer le signalement d'urgence à l'équipe Ops
            </button>
        </form>
    </x-card>

    <x-card>
        <h2>Vos signalements récents</h2>
        @if($incidents->count())
            <div style="display:flex;flex-direction:column;gap:12px;margin-top:10px">
                @foreach($incidents as $inc)
                    <div style="padding:12px;background:var(--bg2);border-radius:12px;border:1px solid var(--line)">
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <strong>{{ $inc->type }}</strong>
                            <span class="bd {{ $inc->status === 'resolved' ? 'g' : 'r pulse' }}">
                                {{ $inc->status === 'resolved' ? 'Résolu' : 'En cours' }}
                            </span>
                        </div>
                        <p style="margin:6px 0 4px;font-size:13px">{{ $inc->description }}</p>
                        <div class="mut" style="font-size:11.5px">
                            {{ $inc->created_at->diffForHumans() }}
                            @if($inc->order) · Course #{{ $inc->order->ref }}@endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="mut" style="padding:24px;text-align:center">Aucun incident actif signalé. Tout est sous contrôle !</p>
        @endif

        <div style="margin-top:16px;padding-top:12px;border-top:1px dashed var(--line);font-size:13px" class="mut">
            📞 En cas d'accident ou d'urgence absolue, appelez immédiatement la ligne d'astreinte Ops FromDarna : <a href="tel:+21621000003">+216 21 000 003</a>.
        </div>
    </x-card>
</div>
@endsection
