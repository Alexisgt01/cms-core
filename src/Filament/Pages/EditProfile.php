<?php

namespace Alexisgt01\CmsCore\Filament\Pages;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    protected function getFirstNameFormComponent(): Component
    {
        return TextInput::make('first_name')
            ->label('First name')
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getLastNameFormComponent(): Component
    {
        return TextInput::make('last_name')
            ->label('Last name')
            ->required()
            ->maxLength(255);
    }

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        $canEditProfile = $this->getUser()->can('edit profile');

        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getFirstNameFormComponent()
                            ->disabled(! $canEditProfile),
                        $this->getLastNameFormComponent()
                            ->disabled(! $canEditProfile),
                        $this->getEmailFormComponent()
                            ->disabled(! $canEditProfile),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->operation('edit')
                    ->model($this->getUser())
                    ->statePath('data')
                    ->inlineLabel(! static::isSimple()),
            ),
        ];
    }
}
