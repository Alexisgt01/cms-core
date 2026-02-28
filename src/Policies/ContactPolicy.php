<?php

namespace Alexisgt01\CmsCore\Policies;

use Alexisgt01\CmsCore\Models\Contact;
use App\Models\User;

class ContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view contacts');
    }

    public function view(User $user, Contact $contact): bool
    {
        return $user->can('view contacts');
    }

    public function create(User $user): bool
    {
        return $user->can('create contacts');
    }

    public function update(User $user, Contact $contact): bool
    {
        return $user->can('edit contacts');
    }

    public function delete(User $user, Contact $contact): bool
    {
        return $user->can('delete contacts');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete contacts');
    }
}
