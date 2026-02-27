<?php

namespace Alexisgt01\CmsCore\Collections;

use InvalidArgumentException;

class CollectionRegistry
{
    /** @var array<string, class-string<CollectionType>> */
    protected array $types = [];

    /**
     * Register a CollectionType class.
     *
     * @param  class-string<CollectionType>  $typeClass
     */
    public function register(string $typeClass): void
    {
        if (! is_subclass_of($typeClass, CollectionType::class)) {
            throw new InvalidArgumentException(
                "Class {$typeClass} must extend " . CollectionType::class
            );
        }

        $this->types[$typeClass::key()] = $typeClass;
    }

    /**
     * @return array<string, class-string<CollectionType>>
     */
    public function all(): array
    {
        return $this->types;
    }

    /**
     * Resolve a type class by its key.
     *
     * @return class-string<CollectionType>|null
     */
    public function resolve(string $key): ?string
    {
        return $this->types[$key] ?? null;
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
