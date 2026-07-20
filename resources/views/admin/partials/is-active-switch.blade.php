@props([
    'id' => 'is_active',
    'label' => 'Active',
    'active' => true,
    'disabled' => false,
])

@php
    $checked = filter_var(old('is_active', $active), FILTER_VALIDATE_BOOLEAN);
@endphp

<div class="form-check form-switch">
    @if ($disabled)
        <input type="hidden" name="is_active" value="{{ $checked ? 1 : 0 }}">
        <input
            class="form-check-input"
            type="checkbox"
            id="{{ $id }}"
            value="1"
            @checked($checked)
            disabled
        >
    @else
        <input type="hidden" name="is_active" value="0">
        <input
            class="form-check-input"
            type="checkbox"
            name="is_active"
            id="{{ $id }}"
            value="1"
            @checked($checked)
        >
    @endif
    <label class="form-check-label" for="{{ $id }}">{{ $label }}</label>
</div>
