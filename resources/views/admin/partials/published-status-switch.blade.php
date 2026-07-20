@props([
    'id' => 'status',
    'label' => 'Published',
    'status' => 'draft',
])

@php
    $current = old('status', $status);
    $valueInputId = $id . '_value';
@endphp

<div class="form-check form-switch">
    <input type="hidden" name="status" id="{{ $valueInputId }}" value="{{ $current === 'published' ? 'published' : 'draft' }}">
    <input
        class="form-check-input js-published-switch"
        type="checkbox"
        id="{{ $id }}"
        data-target="{{ $valueInputId }}"
        @checked($current === 'published')
    >
    <label class="form-check-label" for="{{ $id }}">{{ $label }}</label>
</div>
