{{-- variant : ghost | red | olive ; size : sm --}}
@props(['href' => '#', 'variant' => null, 'size' => null, 'block' => false])
<a href="{{ $href }}" {{ $attributes->merge(['class' => trim('btn ' . $variant . ' ' . $size . ($block ? ' block' : ''))]) }}>{{ $slot }}</a>
