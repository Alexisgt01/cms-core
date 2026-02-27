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
}
