<?php

namespace Vendor\CmsCore\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view users');
    }

    public function view(User $user, User $model): bool
    {
        if ($user->is($model)) {
            return true;
        }

        return $user->can('view users');
    }

    public function create(User $user): bool
    {
        return $user->can('create users');
    }

    public function update(User $user, User $model): bool
    {
        if ($user->is($model)) {
            return $user->can('edit profile');
        }

        return $user->can('edit users');
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->is($model)) {
            return $user->can('delete profile');
        }

        return $user->can('delete users');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete users');
    }
}
