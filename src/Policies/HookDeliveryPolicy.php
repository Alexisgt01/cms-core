<?php

namespace Alexisgt01\CmsCore\Policies;

use Alexisgt01\CmsCore\Models\HookDelivery;
use App\Models\User;

class HookDeliveryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view contact hooks');
    }

    public function view(User $user, HookDelivery $hookDelivery): bool
    {
        return $user->can('view contact hooks');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, HookDelivery $hookDelivery): bool
    {
        return false;
    }

    public function delete(User $user, HookDelivery $hookDelivery): bool
    {
        return false;
    }

    public function replay(User $user): bool
    {
        return $user->can('replay contact hooks');
    }
}
