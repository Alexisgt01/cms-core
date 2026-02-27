<?php

namespace Alexisgt01\CmsCore\Sections;

use Filament\Forms\Components\Builder\Block;
use InvalidArgumentException;

class SectionRegistry
{
    /** @var array<string, class-string<SectionType>> */
    protected array $types = [];

    /**
     * Register a SectionType class.
     *
     * @param  class-string<SectionType>  $typeClass
     */
    public function register(string $typeClass): void
    {
        if (! is_subclass_of($typeClass, SectionType::class)) {
            throw new InvalidArgumentException(
                "Class {$typeClass} must extend " . SectionType::class
            );
        }

        $this->types[$typeClass::key()] = $typeClass;
    }

    /**
     * @return array<string, class-string<SectionType>>
     */
    public function all(): array
    {
        return $this->types;
    }

    /**
     * Resolve a type class by its key.
     *
     * @return class-string<SectionType>|null
     */
    public function resolve(string $key): ?string
    {
        return $this->types[$key] ?? null;
    }

    /**
     * All registered types as Filament Builder\Block instances.
     *
     * @return array<int, Block>
     */
    public function blocks(): array
    {
        return array_values(
            array_map(fn (string $class) => $class::toBlock(), $this->types)
        );
    }

    /**
     * All type definitions for programmatic use.
     *
     * @return array<int, array<string, mixed>>
     */
    public function definitions(): array
    {
        return array_values(
            array_map(fn (string $class) => $class::toDefinition(), $this->types)
        );
    }
}
