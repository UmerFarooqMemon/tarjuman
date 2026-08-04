<?php

namespace App\Cms\Contracts;

interface SectionSchema
{
    public function type(): string;

    public function label(): string;

    /**
     * Ordered field definitions for admin rendering + validation.
     *
     * @return list<array<string, mixed>>
     */
    public function fields(): array;

    /**
     * Default bilingual content used for seeding / empty fallbacks.
     *
     * @return array<string, mixed>
     */
    public function defaults(): array;

    /**
     * Max instances of this type on a single page (null = unlimited).
     */
    public function maxInstancesPerPage(): ?int;
}
