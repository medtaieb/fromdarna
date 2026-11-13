@extends('layouts.phone')
@section('title', 'Aide')
@section('tab', 'help')
@section('screen')
    <div><h1 style="font-size:24px">Aide</h1><span class="ar mut">مساعدة</span></div>
    <div class="hero g-olive"><b class="serif" style="font-size:20px">Un souci avec votre commande ?</b><p class="mut" style="color:var(--soft);margin-top:6px">Réponse visée en moins de 2 minutes aux heures de pointe.</p>
        <a class="btn block" style="margin-top:14px;background:var(--cream);color:var(--olive)" href="#" onclick="document.getElementById('toast').textContent='Ouverture de WhatsApp (prototype)';document.getElementById('toast').classList.add('show');return false">Écrire sur WhatsApp</a></div>
    @foreach ([['Retard de plus de 20 min', 'Geste commercial possible, validé par Ops.'], ['Article manquant', 'Remboursement validé et tracé.'], ['Client absent', 'Frais de livraison dus ; 2e absence : cash désactivé.'], ['Supprimer mon compte', 'Disponible dans l’application.']] as $i => [$q, $a])
        <details class="card up" style="--i:{{ $i }}"><summary style="cursor:pointer;font-weight:600;min-height:28px">{{ $q }}</summary><p class="mut" style="margin-top:8px">{{ $a }}</p></details>
    @endforeach
@endsection
