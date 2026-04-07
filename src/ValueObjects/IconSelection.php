<?php

namespace Alexisgt01\CmsCore\ValueObjects;

class IconSelection implements \JsonSerializable
{
    public function __construct(
        public readonly string $name,
        public readonly string $set,
        public readonly ?string $variant = null,
        public readonly ?string $label = null,
        public readonly ?string $svg = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            set: $data['set'] ?? '',
            variant: $data['variant'] ?? null,
            label: $data['label'] ?? null,
            svg: $data['svg'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'set' => $this->set,
            'variant' => $this->variant,
            'label' => $this->label,
            'svg' => $this->svg,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param  array<string, string>  $attributes
     */
    public function toSvg(string $class = '', array $attributes = []): string
    {
        if ($this->svg !== null && $this->svg !== '') {
            return $this->svg;
        }

        if ($this->name === '') {
            return '';
        }

        return svg($this->name, $class, $attributes)->toHtml();
    }

    /**
     * Render the icon respecting the configured render mode.
     *
     * In 'class' mode, Font Awesome icons render as <i> tags.
     * All other icons (Heroicons, Simple Icons…) fall back to SVG.
     *
     * @param  array<string, string>  $attributes
     */
    public function toHtml(string $class = '', array $attributes = []): string
    {
        if ($this->name === '') {
            return '';
        }

        if (config('cms-icons.render_mode') === 'class') {
            $faClass = self::toFontAwesomeClass($this->name);

            if ($faClass !== null) {
                $classes = trim($faClass . ($class !== '' ? ' ' . $class : ''));
                $attrs = '';
                foreach ($attributes as $key => $value) {
                    if ($key !== 'width' && $key !== 'height') {
                        $attrs .= ' ' . e($key) . '="' . e($value) . '"';
                    }
                }

                return '<i class="' . e($classes) . '"' . $attrs . '></i>';
            }
        }

        return $this->toSvg($class, $attributes);
    }

    /**
     * Convert an icon name to Font Awesome CSS class if applicable.
     */
    public static function toFontAwesomeClass(string $name): ?string
    {
        return match (true) {
            str_starts_with($name, 'fa-solid fa-'),
            str_starts_with($name, 'fa-regular fa-'),
            str_starts_with($name, 'fa-brands fa-') => $name,
            str_starts_with($name, 'fas-') => 'fa-solid fa-' . substr($name, 4),
            str_starts_with($name, 'far-') => 'fa-regular fa-' . substr($name, 4),
            str_starts_with($name, 'fab-') => 'fa-brands fa-' . substr($name, 4),
            default => null,
        };
    }
}
