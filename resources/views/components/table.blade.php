{{--
    <x-table :headers="[...]" :rows="[[...]]" />   cellules : "g:Badge" ou "Titre|Sous-titre"
    <x-table :headers="[...]"> <tr>…</tr> </x-table>   lignes HTML libres
--}}
@props(['headers' => [], 'rows' => null])
@php
    $cell = function ($c) {
        $c = (string) $c;
        if (preg_match('/^([gorx]):(.*)$/s', $c, $m)) {
            return '<span class="bd ' . $m[1] . '">' . e($m[2]) . '</span>';
        }
        if (\Illuminate\Support\Str::contains($c, '|')) {
            [$a, $b] = explode('|', $c, 2);
            return '<b>' . e($a) . '</b><div class="mut">' . e($b) . '</div>';
        }
        return e($c);
    };
@endphp
<div class="ov">
    <table>
        <thead><tr>@foreach ($headers as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
        <tbody>
            @if ($rows !== null)
                @foreach ($rows as $row)
                    <tr>@foreach ($row as $c)<td>{!! $cell($c) !!}</td>@endforeach</tr>
                @endforeach
            @else
                {{ $slot }}
            @endif
        </tbody>
    </table>
</div>
