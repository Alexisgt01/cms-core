<?php

namespace Alexisgt01\CmsCore\Filament\Resources;

use Alexisgt01\CmsCore\Filament\Resources\ContactResource\Pages;
use Alexisgt01\CmsCore\Models\Contact;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Contact';

    protected static ?string $navigationLabel = 'Contacts';

    protected static ?string $modelLabel = 'Contact';

    protected static ?string $pluralModelLabel = 'Contacts';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view contacts') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('edit contacts') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete contacts') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Contact')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informations')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Telephone')
                                    ->tel()
                                    ->maxLength(255),
                                Forms\Components\TagsInput::make('tags')
                                    ->label('Tags'),
                                Forms\Components\KeyValue::make('consents')
                                    ->label('Consentements'),
                                Forms\Components\KeyValue::make('attribs')
                                    ->label('Attributs'),
                            ]),
                        Forms\Components\Tabs\Tab::make('Demandes')
                            ->icon('heroicon-o-inbox')
                            ->schema([
                                Forms\Components\Placeholder::make('requests_list')
                                    ->label('')
                                    ->content(fn (?Contact $record): string => $record
                                        ? $record->requests()->count() . ' demande(s)'
                                        : '0 demande(s)'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telephone')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('tags')
                    ->label('Tags')
                    ->badge()
                    ->separator(',')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('requests_count')
                    ->label('Demandes')
                    ->counts('requests')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cree le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('search_tags')
                    ->form([
                        Forms\Components\TextInput::make('tag')
                            ->label('Tag'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['tag'],
                        fn (Builder $q, string $tag): Builder => $q->whereJsonContains('tags', $tag),
                    )),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContacts::route('/'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
