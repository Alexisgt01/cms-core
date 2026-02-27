<?php

namespace Alexisgt01\CmsCore\Collections;

abstract class CollectionType
{
    /**
     * Unique identifier (e.g. 'services').
     */
    abstract public static function key(): string;

    /**
     * Human-readable plural label (e.g. 'Services').
     */
    abstract public static function label(): string;

    /**
     * Human-readable singular label (e.g. 'Service').
     */
    abstract public static function singularLabel(): string;

    /**
     * Heroicon name for navigation.
     */
    abstract public static function icon(): string;

    /**
     * Field definitions for this collection type.
     *
     * @return array<int, \Alexisgt01\CmsCore\Sections\SectionField>
     */
    abstract public static function fields(): array;

    /**
     * Optional description.
     */
    public static function description(): string
    {
        return '';
    }

    /**
     * Whether entries have a slug column.
     */
    public static function hasSlug(): bool
    {
        return false;
    }

    /**
     * Whether entries have SEO fields.
     */
    public static function hasSeo(): bool
    {
        return false;
    }

    /**
     * Whether entries use Draft/Published states.
     */
    public static function hasStates(): bool
    {
        return false;
    }

    /**
     * Whether entries are manually sortable.
     */
    public static function sortable(): bool
    {
        return false;
    }

    /**
     * Max number of entries (0 = unlimited).
     */
    public static function maxEntries(): int
    {
        return 0;
    }

    /**
     * Which data field to use for auto-generating the slug.
     */
    public static function slugFrom(): string
    {
        return 'title';
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
     * Serializable definition for programmatic use.
     *
     * @return array<string, mixed>
     */
    public static function toDefinition(): array
    {
        return array_filter([
            'key' => static::key(),
            'label' => static::label(),
            'singular_label' => static::singularLabel(),
            'icon' => static::icon(),
            'description' => static::description() ?: null,
            'has_slug' => static::hasSlug(),
            'has_seo' => static::hasSeo(),
            'has_states' => static::hasStates(),
            'sortable' => static::sortable(),
            'max_entries' => static::maxEntries(),
            'fields' => array_map(
                fn (\Alexisgt01\CmsCore\Sections\SectionField $f) => $f->toDefinition(),
                static::fields()
            ),
        ], fn ($v) => $v !== null);
    }
}
