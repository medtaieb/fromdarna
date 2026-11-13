@props(['title', 'sub' => null])
<div class="ttl">
    <div>
        <h1>{{ $title }}</h1>
        @if ($sub)<div class="mut">{{ $sub }}</div>@endif
    </div>
    @isset($actions)<div class="acts">{{ $actions }}</div>@endisset
</div>
