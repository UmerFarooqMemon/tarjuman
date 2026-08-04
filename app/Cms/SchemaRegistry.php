<?php

namespace App\Cms;

use App\Cms\Contracts\SectionSchema;
use App\Cms\Schemas\StatsTrustSchema;
use App\Cms\Schemas\SupportedDocumentsSchema;
use App\Cms\Schemas\WhyChooseUsSchema;
use InvalidArgumentException;

class SchemaRegistry
{
    /**
     * @var array<string, class-string<SectionSchema>>
     */
    protected static array $schemas = [
        'stats_trust' => StatsTrustSchema::class,
        'supported_documents' => SupportedDocumentsSchema::class,
        'why_choose_us' => WhyChooseUsSchema::class,
    ];

    /**
     * @return array<string, class-string<SectionSchema>>
     */
    public static function map(): array
    {
        return self::$schemas;
    }

    /**
     * @return list<SectionSchema>
     */
    public static function all(): array
    {
        return array_map(
            fn (string $class) => app($class),
            array_values(self::$schemas)
        );
    }

    public static function has(string $type): bool
    {
        return isset(self::$schemas[$type]);
    }

    public static function get(string $type): SectionSchema
    {
        if (! self::has($type)) {
            throw new InvalidArgumentException("Unknown CMS section type [{$type}].");
        }

        return app(self::$schemas[$type]);
    }

    /**
     * @param  class-string<SectionSchema>  $schemaClass
     */
    public static function register(string $type, string $schemaClass): void
    {
        self::$schemas[$type] = $schemaClass;
    }

    /**
     * @return list<string>
     */
    public static function types(): array
    {
        return array_keys(self::$schemas);
    }
}
