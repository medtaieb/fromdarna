@php
    $svg = fn ($p) => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
    $tabs = [
        ['home', 'c.home', 'Accueil', 'الرئيسية', '<path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/>'],
        ['cart', 'c.cart', 'Panier', 'السلة', '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M3 4h3l2.5 11h10L21 7H7"/>'],
        ['orders', 'c.orders', 'Commandes', 'طلباتي', '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6"/>'],
        ['help', 'c.help', 'Aide', 'مساعدة', '<path d="M4 13v-1a8 8 0 0116 0v1"/><rect x="3" y="13" width="4" height="6" rx="1.5"/><rect x="17" y="13" width="4" height="6" rx="1.5"/>'],
    ];
@endphp
<nav class="tabbar" aria-label="Navigation principale">
    @foreach ($tabs as [$key, $route, $fr, $ar, $icon])
        <a href="{{ route($route) }}" class="{{ $active === $key ? 'on' : '' }}">
            {!! $svg($icon) !!}<span data-ar="{{ $ar }}">{{ $fr }}</span>
            @if ($key === 'cart')<i class="badge"></i>@endif
        </a>
    @endforeach
</nav>
