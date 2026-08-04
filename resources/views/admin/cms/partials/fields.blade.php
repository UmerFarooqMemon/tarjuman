@php
    /** @var list<array<string, mixed>> $fields */
    /** @var array<string, mixed> $values */
    /** @var string $namePrefix */
    /** @var string $pathPrefix */
@endphp

@foreach ($fields as $field)
    @php
        $name = (string) ($field['name'] ?? '');
        $type = (string) ($field['type'] ?? 'bilingual_string');
        $label = (string) ($field['label'] ?? $name);
        $fieldName = $namePrefix.'['.$name.']';
        $fieldPath = $pathPrefix === '' ? $name : $pathPrefix.'.'.$name;
        $value = data_get($values, $name);
    @endphp

    @include('admin.cms.partials.field', [
        'field' => $field,
        'type' => $type,
        'label' => $label,
        'fieldName' => $fieldName,
        'fieldPath' => $fieldPath,
        'value' => $value,
    ])
@endforeach
