@php
    // role => [titre, [groupe => [[route, libellé, paramètres?]]]]
    $navs = [
        'partner' => ['Espace Partenaire', [
            'Service' => [['partner.dashboard', 'Tableau de bord'], ['partner.orders', 'Commandes'], ['partner.products', 'Menu et ruptures']],
            'Finance' => [['partner.statements', 'Relevés et versements']],
        ]],
        'courier' => ['Espace Livreur', [
            'Service' => [['courier.dashboard', 'Ma course'], ['courier.delivery', 'Étapes de livraison', ['id' => 1041]], ['courier.incident', 'Signaler un incident']],
            'Finance' => [['courier.earnings', 'Cash et gains']],
        ]],
        'ops' => ['Espace Ops', [
            'Exploitation' => [['ops.dashboard', 'Tableau de bord'], ['ops.orders', 'Commandes'], ['ops.dispatch', 'Dispatch']],
            'Catalogue' => [['ops.stores', 'Restaurants'], ['ops.zones', 'Zone et tarifs']],
            'Équipes' => [['ops.couriers', 'Livreurs'], ['ops.customers', 'Clients et incidents']],
            'Finance' => [['ops.ledger', 'Grand livre'], ['ops.payouts', 'Versements']],
            'Administration' => [['ops.users', 'Utilisateurs et rôles'], ['ops.audit', 'Journal d’audit']],
        ]],
    ];
    [$title, $groups] = $navs[$role] ?? $navs['ops'];
@endphp

<aside class="side" aria-label="Navigation du portail">
    <a class="brand" href="{{ route('home') }}"><span class="logo" role="img" aria-label="Logo Cooked by Us"></span>Cooked by Us</a>
    <div class="role">{{ $title }}</div>

    @foreach ($groups as $group => $items)
        <div class="sg">{{ $group }}</div>
        @foreach ($items as $item)
            @php $active = request()->routeIs($item[0] . '*') && ! ($item[0] === 'ops.dashboard' && ! request()->routeIs('ops.dashboard')); @endphp
            <a class="sl {{ $active ? 'on' : '' }}" href="{{ route($item[0], $item[2] ?? ($item[0] === 'courier.delivery' ? ['id' => 1041] : [])) }}" @if ($active) aria-current="page" @endif>{{ $item[1] }}</a>
        @endforeach
    @endforeach

    <a class="sl" href="{{ route('logout') }}" style="margin-top:auto">
        🚪 Se déconnecter
    </a>
</aside>
