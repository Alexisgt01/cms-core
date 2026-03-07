<?php

namespace Alexisgt01\CmsCore\Filament\Concerns;

trait HasExpandableSections
{
    /** @var array<int, string> */
    public array $expandedSections = [];

    /** @var array<int, string> */
    public array $knownSectionUuids = [];

    public function expandSection(string $uuid): void
    {
        if (! in_array($uuid, $this->expandedSections, true)) {
            $this->expandedSections[] = $uuid;
        }
    }

    public function collapseSection(string $uuid): void
    {
        $this->expandedSections = array_values(
            array_filter($this->expandedSections, fn (string $id): bool => $id !== $uuid)
        );
    }

    public function isSectionExpanded(string $uuid): bool
    {
        return in_array($uuid, $this->expandedSections, true);
    }

    /**
     * Detect newly added sections and auto-expand them.
     *
     * @param  array<int, string>  $currentUuids
     */
    public function syncSectionUuids(array $currentUuids): void
    {
        if (empty($this->knownSectionUuids)) {
            $this->knownSectionUuids = $currentUuids;

            return;
        }

        $newUuids = array_diff($currentUuids, $this->knownSectionUuids);

        foreach ($newUuids as $uuid) {
            if (! in_array($uuid, $this->expandedSections, true)) {
                $this->expandedSections[] = $uuid;
            }
        }

        $removedUuids = array_diff($this->knownSectionUuids, $currentUuids);

        if (! empty($removedUuids)) {
            $this->expandedSections = array_values(
                array_filter($this->expandedSections, fn (string $id): bool => ! in_array($id, $removedUuids, true))
            );
        }

        $this->knownSectionUuids = $currentUuids;
    }
}
