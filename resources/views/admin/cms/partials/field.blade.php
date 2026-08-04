@php
    /** @var array<string, mixed> $field */
    /** @var string $type */
    /** @var string $label */
    /** @var string $fieldName */
    /** @var string $fieldPath */
    /** @var mixed $value */
@endphp

<div class="mb-3 cms-field" data-field-type="{{ $type }}">
    @if ($type === 'bilingual_string' || $type === 'bilingual_text')
        <label class="form-label">{{ $label }}</label>
        <div class="cms-locale-fields">
            <div class="cms-locale-pane" data-locale-pane="en">
                @if ($type === 'bilingual_text')
                    <textarea
                        class="form-control"
                        name="{{ $fieldName }}[en]"
                        rows="3"
                        placeholder="EN">{{ old($fieldName.'.en', data_get($value, 'en')) }}</textarea>
                @else
                    <input
                        type="text"
                        class="form-control"
                        name="{{ $fieldName }}[en]"
                        value="{{ old($fieldName.'.en', data_get($value, 'en')) }}"
                        placeholder="EN">
                @endif
            </div>
            <div class="cms-locale-pane d-none" data-locale-pane="ar">
                @if ($type === 'bilingual_text')
                    <textarea
                        class="form-control"
                        name="{{ $fieldName }}[ar]"
                        rows="3"
                        dir="rtl"
                        placeholder="AR">{{ old($fieldName.'.ar', data_get($value, 'ar')) }}</textarea>
                @else
                    <input
                        type="text"
                        class="form-control"
                        name="{{ $fieldName }}[ar]"
                        value="{{ old($fieldName.'.ar', data_get($value, 'ar')) }}"
                        dir="rtl"
                        placeholder="AR">
                @endif
            </div>
        </div>
        @error($fieldName.'.en') <div class="text-danger small">{{ $message }}</div> @enderror
        @error($fieldName.'.ar') <div class="text-danger small">{{ $message }}</div> @enderror

    @elseif ($type === 'image' || $type === 'icon')
        <label class="form-label">{{ $label }}</label>
        @php $current = old($fieldName, is_string($value) ? $value : null); @endphp
        <input type="hidden" name="{{ $fieldName }}" value="{{ $current }}">
        @if ($current)
            <div class="mb-2 d-flex align-items-center gap-2">
                <img src="{{ cms_asset_url($current) }}" alt="" class="cms-asset-thumb" style="width:40px;height:40px;object-fit:contain;">
                <small class="text-muted text-break">{!! __('general.cms_current_file') !!}: {{ $current }}</small>
            </div>
        @endif
        <input type="file" class="form-control" name="uploads[{{ $fieldPath }}]" accept="image/*,.svg">
        @error($fieldName) <div class="text-danger small">{{ $message }}</div> @enderror

    @elseif ($type === 'group')
        <div class="border rounded p-3">
            <div class="fw-semibold mb-2">{{ $label }}</div>
            @include('admin.cms.partials.fields', [
                'fields' => $field['fields'] ?? [],
                'values' => is_array($value) ? $value : [],
                'namePrefix' => $fieldName,
                'pathPrefix' => $fieldPath,
            ])
        </div>

    @elseif ($type === 'repeater')
        @php
            $items = is_array($value) ? array_values($value) : [];
            $min = (int) ($field['min'] ?? 0);
            $max = (int) ($field['max'] ?? 50);
            if ($items === [] && $min > 0) {
                $items = array_fill(0, $min, []);
            }
        @endphp
        <div class="cms-repeater" data-cms-repeater data-min="{{ $min }}" data-max="{{ $max }}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0">{{ $label }}</label>
                @if ($max > $min || $max > 1)
                    <button type="button" class="btn btn-sm btn-label-primary" data-cms-repeater-add>{!! __('general.cms_add_item') !!}</button>
                @endif
            </div>
            <div data-cms-repeater-list>
                @foreach ($items as $index => $item)
                    <div class="border rounded p-3 mb-2 cms-repeater-item" data-cms-repeater-item>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-label-secondary">#{{ $index + 1 }}</span>
                            @if ($max !== $min || count($items) > $min)
                                <button type="button" class="btn btn-sm btn-text-danger" data-cms-repeater-remove>{!! __('general.cms_remove_item') !!}</button>
                            @endif
                        </div>
                        @include('admin.cms.partials.fields', [
                            'fields' => $field['fields'] ?? [],
                            'values' => is_array($item) ? $item : [],
                            'namePrefix' => $fieldName.'['.$index.']',
                            'pathPrefix' => $fieldPath.'.'.$index,
                        ])
                    </div>
                @endforeach
            </div>
            <template data-cms-repeater-template>
                <div class="border rounded p-3 mb-2 cms-repeater-item" data-cms-repeater-item>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-label-secondary">#__INDEX1__</span>
                        <button type="button" class="btn btn-sm btn-text-danger" data-cms-repeater-remove>{!! __('general.cms_remove_item') !!}</button>
                    </div>
                    @include('admin.cms.partials.fields', [
                        'fields' => $field['fields'] ?? [],
                        'values' => [],
                        'namePrefix' => $fieldName.'[__INDEX__]',
                        'pathPrefix' => $fieldPath.'.__INDEX__',
                    ])
                </div>
            </template>
        </div>
    @endif
</div>
