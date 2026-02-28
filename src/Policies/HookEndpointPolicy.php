<?php

namespace Alexisgt01\CmsCore\Policies;

use Alexisgt01\CmsCore\Models\HookEndpoint;
use App\Models\User;

class HookEndpointPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view contact hooks');
    }

    public function view(User $user, HookEndpoint $hookEndpoint): bool
    {
        return $user->can('view contact hooks');
    }

    public function create(User $user): bool
    {
        return $user->can('create contact hooks');
    }

    public function update(User $user, HookEndpoint $hookEndpoint): bool
    {
        return $user->can('edit contact hooks');
    }

    public function delete(User $user, HookEndpoint $hookEndpoint): bool
    {
        return $user->can('delete contact hooks');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete contact hooks');
    }
}
