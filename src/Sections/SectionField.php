<?php

namespace Alexisgt01\CmsCore\Sections;

use Alexisgt01\CmsCore\Filament\Forms\Components\IconPicker;
use Alexisgt01\CmsCore\Filament\Forms\Components\MediaPicker;
use Filament\Forms;
use FilamentTiptapEditor\TiptapEditor;

class SectionField
{
    protected string $type;

    protected string $name;

    protected ?string $labelText = null;

    protected bool $isRequired = false;

    protected ?int $maxLengthValue = null;

    protected ?int $rowsValue = null;

    protected ?string $levelValue = null;

    protected ?string $placeholderText = null;

    protected ?int $maxItemsValue = null;

    protected ?int $minValue = null;

    protected ?int $maxValue = null;

    protected mixed $defaultValue = null;

    protected ?array $optionsArray = null;

    protected ?string $helperTextValue = null;

    protected bool $isColumnSpanFull = false;

    /** @var array<int, SectionField> */
    protected array $childFields = [];

    protected function __construct(string $type, string $name)
    {
        $this->type = $type;
        $this->name = $name;
    }

    // ── Static factories ─────────────────────────────────────────

    public static function text(string $name): static
    {
        return new static('text', $name);
    }

    public static function title(string $name): static
    {
        return new static('title', $name);
    }

    public static function paragraph(string $name): static
    {
        return new static('paragraph', $name);
    }

    public static function richtext(string $name): static
    {
        return new static('richtext', $name);
    }

    public static function icon(string $name): static
    {
        return new static('icon', $name);
    }

    public static function image(string $name): static
    {
        return new static('image', $name);
    }

    public static function toggle(string $name): static
    {
        return new static('toggle', $name);
    }

    public static function select(string $name): static
    {
        return new static('select', $name);
    }

    public static function link(string $name): static
    {
        return new static('link', $name);
    }

    public static function list(string $name): static
    {
        return new static('list', $name);
    }

    public static function repeater(string $name): static
    {
        return new static('repeater', $name);
    }

    public static function number(string $name): static
    {
        return new static('number', $name);
    }

    public static function color(string $name): static
    {
        return new static('color', $name);
    }

    public static function url(string $name): static
    {
        return new static('url', $name);
    }

    // ── Fluent setters ───────────────────────────────────────────

    public function label(string $label): static
    {
        $this->labelText = $label;

        return $this;
    }

    public function required(bool $condition = true): static
    {
        $this->isRequired = $condition;

        return $this;
    }

    public function maxLength(int $length): static
    {
        $this->maxLengthValue = $length;

        return $this;
    }

    public function rows(int $rows): static
    {
        $this->rowsValue = $rows;

        return $this;
    }

    public function level(string $level): static
    {
        $this->levelValue = $level;

        return $this;
    }

    public function placeholder(string $text): static
    {
        $this->placeholderText = $text;

        return $this;
    }

    public function maxItems(int $max): static
    {
        $this->maxItemsValue = $max;

        return $this;
    }

    public function min(int $min): static
    {
        $this->minValue = $min;

        return $this;
    }

    public function max(int $max): static
    {
        $this->maxValue = $max;

        return $this;
    }

    public function default(mixed $value): static
    {
        $this->defaultValue = $value;

        return $this;
    }

    /**
     * @param  array<string, string>  $options
     */
    public function options(array $options): static
    {
        $this->optionsArray = $options;

        return $this;
    }

    public function helperText(string $text): static
    {
        $this->helperTextValue = $text;

        return $this;
    }

    public function columnSpanFull(bool $condition = true): static
    {
        $this->isColumnSpanFull = $condition;

        return $this;
    }

    /**
     * @param  array<int, SectionField>  $fields
     */
    public function fields(array $fields): static
    {
        $this->childFields = $fields;

        return $this;
    }

    // ── Getters ──────────────────────────────────────────────────

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    // ── Filament conversion ──────────────────────────────────────

    /**
     * @return array<int, Forms\Components\Component>
     */
    public function toFormComponent(): array
    {
        return match ($this->type) {
            'text' => $this->buildText(),
            'title' => $this->buildTitle(),
            'paragraph' => $this->buildParagraph(),
            'richtext' => $this->buildRichtext(),
            'icon' => $this->buildIcon(),
            'image' => $this->buildImage(),
            'toggle' => $this->buildToggle(),
            'select' => $this->buildSelect(),
            'link' => $this->buildLink(),
            'list' => $this->buildList(),
            'repeater' => $this->buildRepeater(),
            'number' => $this->buildNumber(),
            'color' => $this->buildColor(),
            'url' => $this->buildUrl(),
            default => [],
        };
    }

    // ── Definition export ────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    public function toDefinition(): array
    {
        return array_filter([
            'type' => $this->type,
            'name' => $this->name,
            'label' => $this->labelText,
            'required' => $this->isRequired ?: null,
            'max_length' => $this->maxLengthValue,
            'rows' => $this->rowsValue,
            'level' => $this->levelValue,
            'placeholder' => $this->placeholderText,
            'max_items' => $this->maxItemsValue,
            'min' => $this->minValue,
            'max' => $this->maxValue,
            'default' => $this->defaultValue,
            'options' => $this->optionsArray,
            'helper_text' => $this->helperTextValue,
            'fields' => ! empty($this->childFields)
                ? array_map(fn (self $f) => $f->toDefinition(), $this->childFields)
                : null,
        ], fn ($v) => $v !== null);
    }

    // ── Private builders ─────────────────────────────────────────

    /**
     * @return array<int, Forms\Components\Component>
     */
    private function buildText(): array
    {
        $field = Forms\Components\TextInput::make($this->name);
        $this->applyCommon($field);

        if ($this->maxLengthValue !== null) {
            $field->maxLength($this->maxLengthValue);
        }

        return [$field];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private function buildTitle(): array
    {
        $text = Forms\Components\TextInput::make($this->name);
        $this->applyCommon($text);

        if ($this->maxLengthValue !== null) {
            $text->maxLength($this->maxLengthValue);
        }

        $level = Forms\Components\Select::make($this->name . '_level')
            ->label('Niveau')
            ->options([
                'h1' => 'H1',
                'h2' => 'H2',
                'h3' => 'H3',
                'h4' => 'H4',
                'h5' => 'H5',
                'h6' => 'H6',
            ])
            ->default($this->levelValue ?? 'h2');

        return [$text, $level];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private function buildParagraph(): array
    {
        $field = Forms\Components\Textarea::make($this->name);
        $this->applyCommon($field);
        $field->rows($this->rowsValue ?? 3);

        if ($this->maxLengthValue !== null) {
            $field->maxLength($this->maxLengthValue);
        }

        return [$field];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private function buildRichtext(): array
    {
        $field = TiptapEditor::make($this->name)
            ->profile('default')
            ->columnSpanFull();

        if ($this->labelText !== null) {
            $field->label($this->labelText);
        }

        if ($this->isRequired) {
            $field->required();
        }

        if ($this->helperTextValue !== null) {
            $field->helperText($this->helperTextValue);
        }

        return [$field];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private function buildIcon(): array
    {
        $field = IconPicker::make($this->name);
        $this->applyCommon($field);

        return [$field];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private function buildImage(): array
    {
        $field = MediaPicker::make($this->name);
        $this->applyCommon($field);

        return [$field];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private function buildToggle(): array
    {
        $field = Forms\Components\Toggle::make($this->name);

        if ($this->labelText !== null) {
            $field->label($this->labelText);
        }

        if ($this->defaultValue !== null) {
            $field->default($this->defaultValue);
        }

        if ($this->helperTextValue !== null) {
            $field->helperText($this->helperTextValue);
        }

        return [$field];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private function buildSelect(): array
    {
        $field = Forms\Components\Select::make($this->name);
        $this->applyCommon($field);
        $field->options($this->optionsArray ?? []);

        if ($this->defaultValue !== null) {
            $field->default($this->defaultValue);
        }

        return [$field];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private function buildLink(): array
    {
        $url = Forms\Components\TextInput::make($this->name . '_url')
            ->label(($this->labelText ?? $this->name) . ' — URL')
            ->inputMode('url')
            ->rule('url');

        if ($this->isRequired) {
            $url->required();
        }

        if ($this->placeholderText !== null) {
            $url->placeholder($this->placeholderText);
        }

        $label = Forms\Components\TextInput::make($this->name . '_label')
            ->label(($this->labelText ?? $this->name) . ' — Libelle');

        return [$url, $label];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private function buildList(): array
    {
        $field = Forms\Components\Repeater::make($this->name)
            ->simple(
                Forms\Components\TextInput::make('text')
                    ->required()
                    ->hiddenLabel(),
            )
            ->defaultItems(0);

        if ($this->labelText !== null) {
            $field->label($this->labelText);
        }

        if ($this->maxItemsValue !== null) {
            $field->maxItems($this->maxItemsValue);
        }

        if ($this->isColumnSpanFull) {
            $field->columnSpanFull();
        }

        return [$field];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private function buildRepeater(): array
    {
        $schema = [];

        foreach ($this->childFields as $child) {
            foreach ($child->toFormComponent() as $component) {
                $schema[] = $component;
            }
        }

        $field = Forms\Components\Repeater::make($this->name)
            ->schema($schema)
            ->defaultItems(0)
            ->collapsible()
            ->collapsed()
            ->columnSpanFull();

        if ($this->labelText !== null) {
            $field->label($this->labelText);
        }

        if ($this->maxItemsValue !== null) {
            $field->maxItems($this->maxItemsValue);
        }

        return [$field];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private function buildNumber(): array
    {
        $field = Forms\Components\TextInput::make($this->name)
            ->numeric();
        $this->applyCommon($field);

        if ($this->minValue !== null) {
            $field->minValue($this->minValue);
        }

        if ($this->maxValue !== null) {
            $field->maxValue($this->maxValue);
        }

        return [$field];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private function buildColor(): array
    {
        $field = Forms\Components\ColorPicker::make($this->name);

        if ($this->labelText !== null) {
            $field->label($this->labelText);
        }

        if ($this->isRequired) {
            $field->required();
        }

        if ($this->helperTextValue !== null) {
            $field->helperText($this->helperTextValue);
        }

        return [$field];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private function buildUrl(): array
    {
        $label = $this->labelText ?? $this->name;

        $type = Forms\Components\Radio::make($this->name . '_type')
            ->label($label . ' — Type')
            ->options([
                'page' => 'Page interne',
                'external' => 'Lien externe',
            ])
            ->default('external')
            ->inline()
            ->live();

        $page = Forms\Components\Select::make($this->name . '_page')
            ->label($label . ' — Page')
            ->options(function (): array {
                $options = [];
                $pages = \Alexisgt01\CmsCore\Models\Page::query()
                    ->published()
                    ->roots()
                    ->orderBy('name')
                    ->with('children')
                    ->get();

                foreach ($pages as $root) {
                    $options[$root->id] = $root->name;

                    foreach ($root->children->sortBy('name') as $child) {
                        $options[$child->id] = '— ' . $child->name;
                    }
                }

                return $options;
            })
            ->searchable()
            ->visible(fn (Forms\Get $get): bool => $get($this->name . '_type') === 'page');

        if ($this->isRequired) {
            $page->required(fn (Forms\Get $get): bool => $get($this->name . '_type') === 'page');
        }

        $url = Forms\Components\TextInput::make($this->name . '_url')
            ->label($label . ' — URL')
            ->inputMode('url')
            ->rule('url')
            ->visible(fn (Forms\Get $get): bool => $get($this->name . '_type') === 'external');

        if ($this->isRequired) {
            $url->required(fn (Forms\Get $get): bool => $get($this->name . '_type') === 'external');
        }

        if ($this->placeholderText !== null) {
            $url->placeholder($this->placeholderText);
        }

        $labelField = Forms\Components\TextInput::make($this->name . '_label')
            ->label($label . ' — Libelle');

        return [$type, $page, $url, $labelField];
    }

    // ── Shared helpers ───────────────────────────────────────────

    private function applyCommon(Forms\Components\Field $field): void
    {
        if ($this->labelText !== null) {
            $field->label($this->labelText);
        }

        if ($this->isRequired) {
            $field->required();
        }

        if ($this->placeholderText !== null) {
            $field->placeholder($this->placeholderText);
        }

        if ($this->helperTextValue !== null) {
            $field->helperText($this->helperTextValue);
        }

        if ($this->isColumnSpanFull) {
            $field->columnSpanFull();
        }

        if ($this->defaultValue !== null) {
            $field->default($this->defaultValue);
        }
    }
}
