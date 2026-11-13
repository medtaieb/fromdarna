@props(['label', 'name' => null, 'type' => 'text', 'placeholder' => '', 'value' => null])
<label class="f">{{ $label }}
    <input type="{{ $type }}" @if ($name) name="{{ $name }}" @endif placeholder="{{ $placeholder }}" @if ($value !== null) value="{{ $value }}" @endif {{ $attributes }}>
</label>
