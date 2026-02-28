<?php

namespace Alexisgt01\CmsCore\Policies;

use Alexisgt01\CmsCore\Models\ContactRequest;
use App\Models\User;

class ContactRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view contact requests');
    }

    public function view(User $user, ContactRequest $contactRequest): bool
    {
        return $user->can('view contact requests');
    }

    public function create(User $user): bool
    {
        return $user->can('create contact requests');
    }

    public function update(User $user, ContactRequest $contactRequest): bool
    {
        return $user->can('edit contact requests');
    }

    public function delete(User $user, ContactRequest $contactRequest): bool
    {
        return $user->can('delete contact requests');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete contact requests');
    }
}
