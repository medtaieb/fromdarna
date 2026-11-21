@extends('layouts.portal')
@section('role', 'partner')
@section('title', 'Relevés & Versements · ' . $store->name)

@section('content')
<x-title :title="'Relevés & Versements · ' . $store->name" sub="Suivi transparent de vos ventes, déduction de commission et paiements par virement.">
    <x-slot name="actions">
        <button class="btn ghost sm" type="button" onclick="window.print()">🖨️ Imprimer le relevé</button>
    </x-slot>
</x-title>

{{-- ── Résumé global ── --}}
<div class="grid g3" style="margin-bottom:16px">
    <x-card class="glow">
        <div class="mut" style="font-size:13px">Total des ventes (hors livraison)</div>
        <div style="font-size:26px;font-weight:800;color:var(--olive);margin-top:4px">
            @tnd($totalSales)
        </div>
        <div class="mut" style="font-size:11.5px;margin-top:2px">Commandes livrées cumulées</div>
    </x-card>

    <x-card>
        <div class="mut" style="font-size:13px">Commission FromDarna ({{ ($store->commission_bps ?? 1200) / 100 }} %)</div>
        <div style="font-size:24px;font-weight:800;color:var(--tomate);margin-top:4px">
            − @tnd($totalCommission)
        </div>
        <div class="mut" style="font-size:11.5px;margin-top:2px">Plateforme, mise en relation, marketing</div>
    </x-card>

    <x-card class="glow-brand">
        <div class="mut" style="font-size:13px">Net total dû au restaurant</div>
        <div style="font-size:26px;font-weight:800;color:#2E7D32;margin-top:4px">
            @tnd($totalNet)
        </div>
        <div class="mut" style="font-size:11.5px;margin-top:2px">Versé chaque semaine par virement bancaire</div>
    </x-card>
</div>

<x-card>
    <h2>Historique des périodes de versement</h2>

    @if($weeks->count())
        <table style="width:100%;border-collapse:collapse;font-size:13.5px;margin-top:10px">
            <thead>
                <tr style="border-bottom:1.5px solid var(--line);text-align:left">
                    <th style="padding:10px 8px">Période</th>
                    <th style="padding:10px 8px">Commandes</th>
                    <th style="padding:10px 8px">Ventes brutes</th>
                    <th style="padding:10px 8px">Commission FromDarna</th>
                    <th style="padding:10px 8px">Net à verser</th>
                    <th style="padding:10px 8px">Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($weeks as $w)
                    <tr style="border-bottom:1px solid var(--line)">
                        <td style="padding:12px 8px"><strong>{{ $w['period'] }}</strong></td>
                        <td style="padding:12px 8px">{{ $w['count'] }} commande(s)</td>
                        <td style="padding:12px 8px">@tnd($w['sales'])</td>
                        <td style="padding:12px 8px;color:var(--tomate)">− @tnd($w['commission'])</td>
                        <td style="padding:12px 8px;font-weight:700;color:#2E7D32">@tnd($w['net'])</td>
                        <td style="padding:12px 8px"><span class="bd o">En cours</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align:center;padding:32px">
            <span style="font-size:36px">📊</span>
            <p class="mut" style="margin-top:8px">Vos premiers relevés apparaîtront ici dès que vos commandes seront livrées.</p>
        </div>
    @endif

    <p class="mut" style="margin-top:16px;font-size:12.5px">
        ℹ️ Le virement bancaire est exécuté chaque mardi sur le compte IBAN de votre établissement pour les commandes clôturées de la semaine précédente.
    </p>
</x-card>
@endsection
