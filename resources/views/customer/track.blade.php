@extends('layouts.phone')
@section('title', 'Suivi')
@section('tab', 'orders')
@section('screen')
    <div class="row between"><h1 style="font-size:22px">Suivi de commande</h1><span class="bd hot row" style="gap:6px"><i class="live"></i>En direct</span></div>
    <div class="hero g-warm"><span class="mut" style="color:var(--soft)">Livraison estimée</span><h1 data-eta style="font-size:36px;margin-top:4px">≈ 35 min</h1><p class="ar" style="color:var(--soft);margin-top:4px">طلبك في الطريق إليك</p></div>
    <div class="card" style="padding:22px 20px">
        <div class="tl"><i class="fill"></i>
            @foreach ([['Commande reçue', 'تم استلام الطلب'], ['Acceptée, prête dans 15 min', 'تم قبول الطلب'], ['Commande prête', 'الطلب جاهز'], ['Livreur en route', 'المُوصّل في الطريق'], ['Livrée', 'تم التوصيل']] as [$t, $a])
                <div class="tl-step"><b>{{ $t }}</b><div class="ar mut">{{ $a }}</div></div>
            @endforeach
        </div>
    </div>
    <div class="card row between" data-otp style="display:none;animation:pop .5s ease both"><div><b>Code de livraison</b><div class="mut">À donner au livreur</div></div><span class="serif" style="font-size:30px;letter-spacing:6px;color:var(--brown);font-weight:700">4729</span></div>
    <div class="card row between"><div><b>Dar Zitouna</b><div class="mut">Cash à la livraison</div></div><b>21,900 TND</b></div>
    <div class="row"><button id="replay" class="btn o grow">Rejouer</button><a class="btn grow" href="{{ route('c.help') }}">Support</a></div>
@endsection
