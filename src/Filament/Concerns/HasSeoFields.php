<?php

namespace Alexisgt01\CmsCore\Filament\Concerns;

use Alexisgt01\CmsCore\Filament\Forms\Components\MediaPicker;
use Alexisgt01\CmsCore\Filament\Forms\Components\OgPreview;
use Alexisgt01\CmsCore\Filament\Forms\Components\SerpPreview;
use Alexisgt01\CmsCore\Filament\Forms\Components\TwitterPreview;
use Alexisgt01\CmsCore\Models\BlogSetting;
use Filament\Forms;
use FilamentTiptapEditor\TiptapEditor;

trait HasSeoFields
{
    /**
     * @return array<int, Forms\Components\Component>
     */
    protected static function seoKeywordFields(): array
    {
        return [
            Forms\Components\TextInput::make('focus_keyword')
                ->label('Mot-cle principal')
                ->maxLength(255),
            Forms\Components\TagsInput::make('secondary_keywords')
                ->label('Mots-cles secondaires'),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    protected static function seoIndexingFields(): array
    {
        return [
            Forms\Components\Toggle::make('indexing')
                ->label('Indexation')
                ->default(true),
            Forms\Components\TextInput::make('canonical_url')
                ->label('URL canonique (laisser vide = auto)')
                ->url()
                ->maxLength(255)
                ->helperText('Si vide, l\'URL canonique sera generee automatiquement'),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    protected static function seoMetaFields(): array
    {
        return [
            Forms\Components\TextInput::make('meta_title')
                ->label('Titre meta')
                ->maxLength(255)
                ->helperText('60 caracteres recommandes')
                ->live(onBlur: true),
            Forms\Components\Textarea::make('meta_description')
                ->label('Description meta')
                ->rows(2)
                ->helperText('120-160 caracteres recommandes'),
        ];
    }

    protected static function robotsFieldset(): Forms\Components\Fieldset
    {
        return Forms\Components\Fieldset::make('Robots')
            ->schema([
                Forms\Components\Toggle::make('robots_index')
                    ->label('Index'),
                Forms\Components\Toggle::make('robots_follow')
                    ->label('Follow'),
                Forms\Components\Toggle::make('robots_noarchive')
                    ->label('Noarchive'),
                Forms\Components\Toggle::make('robots_nosnippet')
                    ->label('Nosnippet'),
                Forms\Components\TextInput::make('robots_max_snippet')
                    ->label('Max snippet')
                    ->numeric()
                    ->nullable(),
                Forms\Components\Select::make('robots_max_image_preview')
                    ->label('Max image preview')
                    ->options([
                        'none' => 'None',
                        'standard' => 'Standard',
                        'large' => 'Large',
                    ]),
                Forms\Components\TextInput::make('robots_max_video_preview')
                    ->label('Max video preview')
                    ->numeric()
                    ->nullable(),
            ])
            ->columns(4);
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    protected static function ogFields(): array
    {
        $settings = BlogSetting::instance();

        return [
            Forms\Components\Select::make('og_type')
                ->label('Type OG')
                ->options([
                    'article' => 'Article',
                    'website' => 'Website',
                    'blog' => 'Blog',
                    'profile' => 'Profile',
                ]),
            Forms\Components\TextInput::make('og_locale')
                ->label('Locale OG')
                ->placeholder($settings->og_locale ?? 'fr_FR')
                ->maxLength(10)
                ->helperText('Defaut : ' . ($settings->og_locale ?? 'fr_FR')),
            Forms\Components\TextInput::make('og_site_name')
                ->label('Nom du site OG')
                ->placeholder($settings->og_site_name ?? '')
                ->maxLength(255)
                ->helperText($settings->og_site_name ? 'Defaut : ' . $settings->og_site_name : ''),
            Forms\Components\TextInput::make('og_title')
                ->label('Titre OG')
                ->maxLength(255),
            Forms\Components\Textarea::make('og_description')
                ->label('Description OG')
                ->rows(2),
            MediaPicker::make('og_image')
                ->label('Image OG'),
            Forms\Components\TextInput::make('og_image_width')
                ->label('Largeur image OG (px)')
                ->numeric()
                ->nullable(),
            Forms\Components\TextInput::make('og_image_height')
                ->label('Hauteur image OG (px)')
                ->numeric()
                ->nullable(),
            OgPreview::make(),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    protected static function twitterFields(): array
    {
        $settings = BlogSetting::instance();

        return [
            Forms\Components\Select::make('twitter_card')
                ->label('Type de carte')
                ->options([
                    'summary' => 'Summary',
                    'summary_large_image' => 'Summary Large Image',
                ])
                ->live(),
            Forms\Components\TextInput::make('twitter_site')
                ->label('Compte @site')
                ->placeholder($settings->twitter_site ?? '@monsite')
                ->maxLength(255)
                ->helperText($settings->twitter_site ? 'Defaut : ' . $settings->twitter_site : ''),
            Forms\Components\TextInput::make('twitter_creator')
                ->label('Compte @createur')
                ->placeholder($settings->twitter_creator ?? '@auteur')
                ->maxLength(255)
                ->helperText($settings->twitter_creator ? 'Defaut : ' . $settings->twitter_creator : ''),
            Forms\Components\TextInput::make('twitter_title')
                ->label('Titre Twitter')
                ->maxLength(255),
            Forms\Components\Textarea::make('twitter_description')
                ->label('Description Twitter')
                ->rows(2),
            MediaPicker::make('twitter_image')
                ->label('Image Twitter'),
            TwitterPreview::make(),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    protected static function schemaFields(): array
    {
        return [
            Forms\Components\Select::make('schema_types')
                ->label('Types de schema')
                ->multiple()
                ->options(static::schemaTypeOptions()),
            Forms\Components\Textarea::make('schema_json')
                ->label('JSON-LD personnalise')
                ->rows(6)
                ->formatStateUsing(fn ($state): ?string => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                ->dehydrateStateUsing(fn ($state) => is_string($state) && $state !== '' ? json_decode($state, true) : $state)
                ->rule(static function (): \Closure {
                    return static function (string $attribute, $value, \Closure $fail): void {
                        if ($value !== null && $value !== '') {
                            json_decode(is_string($value) ? $value : '');
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                $fail('Le JSON-LD n\'est pas valide : ' . json_last_error_msg());
                            }
                        }
                    };
                })
                ->helperText('JSON valide qui sera fusionne avec le schema genere'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected static function schemaTypeOptions(): array
    {
        return [
            'WebPage' => 'WebPage',
            'CollectionPage' => 'CollectionPage',
            'ItemList' => 'ItemList',
            'Article' => 'Article',
            'BlogPosting' => 'BlogPosting',
            'NewsArticle' => 'NewsArticle',
            'FAQPage' => 'FAQPage',
            'BreadcrumbList' => 'BreadcrumbList',
            'Person' => 'Person',
            'Organization' => 'Organization',
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    protected static function contentSeoFields(): array
    {
        return [
            TiptapEditor::make('content_seo_top')
                ->label('Contenu SEO (haut de page)')
                ->profile('default')
                ->nullable()
                ->columnSpanFull(),
            TiptapEditor::make('content_seo_bottom')
                ->label('Contenu SEO (bas de page)')
                ->profile('default')
                ->nullable()
                ->columnSpanFull(),
        ];
    }
}
