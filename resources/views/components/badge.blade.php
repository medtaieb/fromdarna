{{-- type : g (olive) | r (rouge) | o (rouille) | x (beige) --}}
@props(['type' => 'x'])
<span {{ $attributes->merge(['class' => 'bd ' . $type]) }}>{{ $slot }}</span>
