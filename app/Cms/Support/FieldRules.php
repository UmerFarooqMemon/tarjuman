<?php

namespace App\Cms\Support;

class FieldRules
{
    /**
     * Build Laravel validation rules from schema field definitions.
     *
     * @param  list<array<string, mixed>>  $fields
     * @return array<string, list<string|\Illuminate\Contracts\Validation\ValidationRule>>
     */
    public static function fromFields(array $fields, string $prefix = 'content'): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $name = (string) ($field['name'] ?? '');
            $type = (string) ($field['type'] ?? 'bilingual_string');
            $path = $prefix.'.'.$name;
            $extra = $field['rules'] ?? [];

            match ($type) {
                'bilingual_string' => self::bilingual($rules, $path, 'string', $extra),
                'bilingual_text' => self::bilingual($rules, $path, 'string', $extra),
                'image', 'icon' => self::asset($rules, $path, $extra),
                'group' => self::merge($rules, self::fromFields($field['fields'] ?? [], $path)),
                'repeater' => self::repeater($rules, $path, $field),
                default => null,
            };
        }

        return $rules;
    }

    /**
     * @param  array<string, list<mixed>>  $rules
     * @param  list<mixed>  $extra
     */
    protected static function bilingual(array &$rules, string $path, string $base, array $extra): void
    {
        $rules[$path] = ['nullable', 'array'];
        $rules[$path.'.en'] = array_merge(['nullable', $base], $extra);
        $rules[$path.'.ar'] = array_merge(['nullable', $base], $extra);
    }

    /**
     * @param  array<string, list<mixed>>  $rules
     * @param  list<mixed>  $extra
     */
    protected static function asset(array &$rules, string $path, array $extra): void
    {
        $rules[$path] = array_merge(['nullable', 'string', 'max:500'], $extra);
    }

    /**
     * @param  array<string, list<mixed>>  $rules
     * @param  array<string, mixed>  $field
     */
    protected static function repeater(array &$rules, string $path, array $field): void
    {
        $min = (int) ($field['min'] ?? 0);
        $max = (int) ($field['max'] ?? 50);

        $rules[$path] = ['nullable', 'array', 'min:'.$min, 'max:'.$max];

        self::merge($rules, self::fromFields($field['fields'] ?? [], $path.'.*'));
    }

    /**
     * @param  array<string, list<mixed>>  $into
     * @param  array<string, list<mixed>>  $from
     */
    protected static function merge(array &$into, array $from): void
    {
        foreach ($from as $key => $value) {
            $into[$key] = $value;
        }
    }
}
