<?php

namespace Alexisgt01\CmsCore\Sections;

use Filament\Forms\Components\Builder\Block;

abstract class SectionType
{
    /**
     * Unique identifier (e.g. 'cta_contact').
     */
    abstract public static function key(): string;

    /**
     * Human-readable label for the block picker.
     */
    abstract public static function label(): string;

    /**
     * Heroicon name for the block picker.
     */
    abstract public static function icon(): string;

    /**
     * Field definitions for this section type.
     *
     * @return array<int, SectionField>
     */
    abstract public static function fields(): array;

    /**
     * Optional description shown in the block picker.
     */
    public static function description(): string
    {
        return '';
    }

    /**
     * Convert fields() to Filament form component array.
     *
     * @return array<int, \Filament\Forms\Components\Component>
     */
    public static function schema(): array
    {
        $components = [];

        foreach (static::fields() as $field) {
            foreach ($field->toFormComponent() as $component) {
                $components[] = $component;
            }
        }

        return $components;
    }

    /**
     * Create a Filament Builder\Block from this type.
     */
    public static function toBlock(): Block
    {
        $block = Block::make(static::key())
            ->label(static::label())
            ->icon(static::icon())
            ->schema(static::schema());

        return $block;
    }

    /**
     * Serializable definition for programmatic use.
     *
     * @return array<string, mixed>
     */
    public static function toDefinition(): array
    {
        return array_filter([
            'key' => static::key(),
            'label' => static::label(),
            'icon' => static::icon(),
            'description' => static::description() ?: null,
            'fields' => array_map(
                fn (SectionField $f) => $f->toDefinition(),
                static::fields()
            ),
        ], fn ($v) => $v !== null);
    }
}
