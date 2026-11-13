{{-- steps : [[libellé, détail?], ...] ; current : index de l'étape en cours (>= count => tout est terminé) --}}
@props(['steps', 'current' => 0])
<ol class="tlx">
    @foreach ($steps as $i => $s)
        <li class="{{ $i < $current ? 'done' : ($i === $current ? 'now' : '') }}">
            <b>{{ $s[0] }}</b>
            @if (! empty($s[1]))<span class="mut">{{ $s[1] }}</span>@endif
        </li>
    @endforeach
</ol>
