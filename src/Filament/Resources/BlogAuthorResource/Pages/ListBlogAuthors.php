<?php

namespace Alexisgt01\CmsCore\Filament\Resources\BlogAuthorResource\Pages;

use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Alexisgt01\CmsCore\Filament\Resources\BlogAuthorResource;
use Alexisgt01\CmsCore\Models\BlogAuthor;

class ListBlogAuthors extends ListRecords
{
    protected static string $resource = BlogAuthorResource::class;

    /**
     * @return array<Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('createFromUser')
                ->label('Créer depuis un utilisateur')
                ->icon('heroicon-o-user-plus')
                ->color('gray')
                ->form([
                    Select::make('user_id')
                        ->label('Utilisateur')
                        ->options(function (): array {
                            $existingUserIds = BlogAuthor::query()
                                ->whereNotNull('user_id')
                                ->pluck('user_id')
                                ->toArray();

                            return User::query()
                                ->whereNotIn('id', $existingUserIds)
                                ->get()
                                ->mapWithKeys(fn (User $user): array => [
                                    $user->id => ($user->first_name ?? '') . ' ' . ($user->last_name ?? '') . ' (' . $user->email . ')',
                                ])
                                ->toArray();
                        })
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $user = User::findOrFail($data['user_id']);
                    $displayName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email;

                    BlogAuthor::create([
                        'user_id' => $user->id,
                        'display_name' => $displayName,
                        'slug' => BlogAuthor::generateSlug($displayName),
                        'email' => $user->email,
                    ]);
                }),
        ];
    }
}
