@props(['label', 'value', 'sub' => null])
<div {{ $attributes->merge(['class' => 'card stat']) }}>
    <span class="mut">{{ $label }}</span>
    <b>{{ $value }}</b>
    @if ($sub)<span class="mut">{{ $sub }}</span>@endif
</div>
